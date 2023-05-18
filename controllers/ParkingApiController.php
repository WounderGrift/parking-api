<?php

namespace app\controllers;

use app\models\Cars;
use app\models\Clients;
use yii\filters\Cors;
use yii\helpers\Json;
use yii\web\Controller;

class ParkingApiController extends Controller
{
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
            ],
        ];
    }
    
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }
    
    /**
     * Метод для получения списка клиентов с их автомобилями на основе фильтрации.
     * @return string Список клиентов с их автомобилями в формате JSON.
     * @example
     * ```php
     * // Пример использования метода actionIndex
     * // Отправляем POST-запрос на URL /clients с JSON-телом запроса
     * // {
     * // "client": {
     * // "name":     "",
     * // "gender":    "",
     * // "phone":     ""
     * // },
     * // "car": {
     * // "maker":      "",
     * // "model":      "",
     * // "color":      "black",
     * // "number":     "",
     * // "in_parking": ""
     * // },
     * // "limit": 10
     * // }
     * // [{"id":"19","full_name":"Armand Prosacco IV","gender":"male","phone":"+1 (463) 767-8920",
     * // "address":"1835 Crist Trail South Maverickhaven, GA 84251-5457","id_client":"10","id_car":"19",
     * // "maker":"Toyota", "model":"rerum","color":"black","number":"К466СМ","in_parking":"0"}]
     * ```
     */
    public function actionIndex()
    {
        $request      = Json::decode(\Yii::$app->request->getRawBody(), true);

        $clientParams = $request['client'];
        $autoParams   = $request['car'];
    
        $result  = Clients::find()
            ->select('*')
            ->leftJoin('cars', 'cars.id_client = clients.id')
            ->filterWhere(['LIKE', 'full_name', $clientParams['name']])
            ->andFilterWhere(['gender'     => $clientParams['gender']])
            ->andFilterWhere(['phone'      => $clientParams['phone']])
            ->andFilterWhere(['maker'      => $autoParams['maker']])
            ->andFilterWhere(['color'      => $autoParams['color']])
            ->andFilterWhere(['model'      => $autoParams['model']])
            ->andFilterWhere(['number'     => $autoParams['number']])
            ->andFilterWhere(['in_parking' => $autoParams['inParking']])
            ->limit($request['limit'])
            ->createCommand()->queryAll();
    
        return Json::encode($result);
    }
    
    /**
     * Метод для создания нового клиента и связанных с ним автомобилей.
     *
     * @return string Результат операции в формате JSON.
     *
     * @example
     * ```php
     * // Пример использования метода actionCreate
     *
     * // Отправляем POST-запрос на URL /clients/create с JSON-телом запроса
     * // {
     * //   "client": {
     * //     "name": "John Doe",
     * //     "gender": "male",
     * //     "phone": "123456789",
     * //     "address": "123 Main St"
     * //   },
     * //   "cars": [
     * //     {
     * //       "maker": "Toyota",
     * //       "model": "Corolla",
     * //       "color": "blue",
     * //       "number": "ABC123",
     * //       "in_parking": true
     * //     },
     * //     {
     * //       "maker": "Honda",
     * //       "model": "Civic",
     * //       "color": "red",
     * //       "number": "DEF456",
     * //       "in_parking": false
     * //     }
     * //   ]
     * // }
     *
     * // Ожидаемый ответ:
     * // {
     * //   "result": "ok"
     * // }
     * ```
     */
    public function actionCreate()
    {
        $request      = Json::decode(\Yii::$app->request->getRawBody(), true);
        $clientParams = $request['client'];
        $carsData     = $request['cars'];
        if (!$clientParams['id'])
        {
            $clientModel = new Clients();
            $clientModel->setAttributes($clientParams, true);
            if (!$clientModel->validate())
            {
                return $this->asJson(['error' => $clientModel->errors]);
            }
        }
        else
        {
            $clientModel = Clients::findOne($clientParams['id']);
        }
        
        if (!$clientModel)
        {
            return $this->asJson(['error' => 'Client not found']);
        }
        $clientModel->save();
        
        foreach ($carsData as $carData)
        {
            $carModel            = new Cars();
            $carModel->setAttributes($carData);
            $carModel->id_client = $clientModel->id;
            
            if (!$carModel->validate())
            {
                return $this->asJson(['error' => $carModel->errors]);
            }
            $carModel->save();
        }
        
        return $this->asJson(['result' => 'ok']);
    }
    
    /**
     * Метод для обновления информации о клиенте и его автомобилях.
     *
     * @return string Результат операции в формате JSON.
     *
     * @example
     * ```php
     * // Пример использования метода actionUpdate
     *
     * // Отправляем POST-запрос на URL /clients/update с JSON-телом запроса
     * // {
     * //   "client": {
     * //     "id": 1,
     * //     "name": "John Doe",
     * //     "gender": "male",
     * //     "phone": "987654321",
     * //     "address": "456 Elm St"
     * //   },
     * //   "cars": [
     * //     {
     * //       "id": 1,
     * //       "maker": "Toyota",
     * //       "model": "Camry",
     * //       "color": "silver",
     * //       "number": "XYZ789",
     * //       "in_parking": true
     * //     },
     * //     {
     * //       "id": 2,
     * //       "maker": "Honda",
     * //       "model": "Civic",
     * //       "color": "black",
     * //       "number": "MNO123",
     * //       "in_parking": false
     * //     }
     * //   ]
     * // }
     *
     * // Ожидаемый ответ:
     * // {
     * //   "result": "ok"
     * // }
     * ```
     */
    public function actionUpdate()
    {
        $request      = Json::decode(\Yii::$app->request->getRawBody(), true);
        $clientParams = $request['client'];
        
        if ($clientParams)
        {
            /** @var Clients $client */
            $clientModel = Clients::findOne($clientParams['id']);
            if (!$clientModel)
            {
                return $this->asJson(['error' => 'Not Found']);
            }
            
            $clientModel->setAttributes($clientParams, true);
            if (!$clientModel->validate())
            {
                return $this->asJson(['error' => $clientModel->errors]);
            }
            
            $clientModel->full_name = $clientParams['name'];
            $clientModel->gender    = $clientParams['gender'];
            $clientModel->phone     = $clientParams['phone'];
            $clientModel->address   = $clientParams['address'];
            $clientModel->save();
        }
    
        $carNotFound = [];
        $carsParams  = $request['cars'];
        
        if ($carsParams)
        {
            foreach ($carsParams as $auto)
            {
                /** @var Cars $car */
                $carModel = Cars::findOne($auto['id']);
                
                if (!$carModel)
                {
                    $carNotFound[] = $auto['id'];
                    continue;
                }
                    
                $carModel->setAttributes($carsParams, true);
                if (!$carModel->validate())
                {
                    return $this->asJson(['error' => $carModel->errors]);
                }
    
                $carModel->maker      = $auto['maker'];
                $carModel->model      = $auto['model'];
                $carModel->color      = $auto['color'];
                $carModel->number     = $auto['number'];
                $carModel->in_parking = $auto['in_parking'];
                $carModel->save();
            }
        }

        return Json::encode(['result' => !empty($carNotFound)
            ? 'ID auto ' . implode($carNotFound) . ' not found'
            : 'ok']);
    }
    
    /**
     * Метод для удаления записи об автомобиле из базы данных.
     *
     * @return string Результат операции в формате JSON.
     *
     * @example
     * ```php
     * // Пример использования метода actionDelete
     *
     * // Отправляем POST-запрос на URL /clients/delete с Form-телом запроса
     * //
     * //   "carId" =>  1
     *
     * // Ожидаемый ответ:
     * // {
     * //   "result": "ok"
     * // }
     * ```
     */
    public function actionDeleteCar()
    {
        $request = \Yii::$app->request->getBodyParams();
        $autoId  = $request['carId'];
        
        if (!empty($autoId))
        {
            /** @var Cars $car */
            $carModel = Cars::findOne($autoId);
            $carModel->setAttributes($autoId);
   
            if (!$carModel->validate())
            {
                return $this->asJson(['error' => $carModel->errors]);
            }
    
            $carModel->delete();
        }
        
        return Json::encode(['result' => 'ok']);
    }
    
    /**
     * Метод для удаления записи о клиенте и его автомобилях из базы данных.
     *
     * @return string Результат операции в формате JSON.
     *
     * @example
     * ```php
     * // Пример использования метода actionDeleteClient
     *
     * // Отправляем POST-запрос на URL /clients/delete с Form-телом запроса
     * //
     * //   "clientId" =>  1
     *
     * // Ожидаемый ответ:
     * // {
     * //   "result": "ok"
     * // }
     * ```
     */
    public function actionDeleteClient()
    {
        $request  = \Yii::$app->request->getBodyParams();
        $clientId = $request['clientId'];
        
        if (!empty($clientId))
        {
            /** @var Cars $carModel */
            $carModel = Cars::find()->where(['id_client' => $clientId])->all();
            foreach ($carModel as $car)
            {
                $car->delete();
            }
            
            /** @var Clients $client */
            $clientModel = Clients::findOne($clientId);
            $clientModel->setAttributes($clientId);
            
            if (!$clientModel->validate())
            {
                return $this->asJson(['error' => $clientModel->errors]);
            }
            
            $clientModel->delete();
        }
        
        return Json::encode(['result' => 'ok']);
    }
}