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
     *
     * // [{"id":"19","full_name":"Armand Prosacco IV","gender":"male","phone":"+1 (463) 767-8920",
     * // "address":"1835 Crist Trail South Maverickhaven, GA 84251-5457","id_client":"10","id_car":"19","maker":"Toyota",
     * // "model":"rerum","color":"black","number":"К466СМ","in_parking":"0"}]
     * ```
     */
    public function actionIndex()
    {
        $request      = Json::decode(\Yii::$app->request->getRawBody(), true);
        $clientParams = $request['client'];
        $autoParams   = $request['car'];
    
        $result  = Clients::find()
            ->select('*')
            ->leftJoin('client_car_ref', 'clients.id = client_car_ref.id_client')
            ->leftJoin('cars', 'cars.id = client_car_ref.id_car')
            ->filterWhere(['LIKE', 'full_name', $clientParams['name']])
            ->andFilterWhere(['gender' => $clientParams['gender']])
            ->andFilterWhere(['phone' => $clientParams['phone']])
            ->andFilterWhere(['maker' => $autoParams['maker']])
            ->andFilterWhere(['color' => $autoParams['color']])
            ->andFilterWhere(['model' => $autoParams['model']])
            ->andFilterWhere(['number' => $autoParams['number']])
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
        $genderExist  = !empty($clientParams['gender'])
            && ($clientParams['gender'] == 'male' || $clientParams['gender'] == 'female');
        $clientExist = !empty($clientParams['name']) && !empty($clientParams['phone']);
    
        if (!$clientExist || !$genderExist)
            return Json::encode(['error' => 'Client params are blank']);
        
        foreach ($request['cars'] as $car)
        {
            $carsExist = !empty($car['maker']) && !empty($car['model']) && !empty($car['color'])
                && !empty($car['number']) && isset($car['in_parking']);
            if (!$carsExist)
                return Json::encode(['error' => 'Car params is blank']);
        }
        
        if (!$clientParams['id'])
        {
            $client            = new Clients();
            $client->full_name = $clientParams['name'];
            $client->gender    = $clientParams['gender'];
            $client->phone     = $clientParams['phone'];
            $client->address   = $clientParams['address'];
            $client->save();
        }
        else
            $client = Clients::find()->where(['id' => $clientParams['id']])->one();

        foreach ($request['cars'] as $auto)
        {
            $car             = new Cars();
            $car->maker      = $auto['maker'];
            $car->model      = $auto['model'];
            $car->color      = $auto['color'];
            $car->number     = $auto['number'];
            $car->in_parking = $auto['in_parking'];
            $car->save();

            $client->link('cars', $car);
        }
        
        return Json::encode(['result' => 'ok']);
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
            $genderExist = !empty($clientParams['gender'])
                && ($clientParams['gender'] == 'male' || $clientParams['gender'] == 'female');
            $clientExist = !empty($clientParams['name'])
                && !empty($clientParams['phone']) && !empty($clientParams['id']);
    
            if (!$clientExist || !$genderExist)
                return Json::encode(['error' => 'Client params are blank']);
            
            /** @var Clients $client */
            $client            = Clients::find()->where(['id' => $clientParams['id']])->one();
            $client->full_name = $clientParams['name'];
            $client->gender    = $clientParams['gender'];
            $client->phone     = $clientParams['phone'];
            $client->address   = $clientParams['address'];
            $client->save();
        }
    
        $carsParams = $request['cars'];
        if ($carsParams)
        {
            foreach ($carsParams as $auto)
            {
                $carsExist = !empty($auto['maker']) && !empty($auto['model']) && !empty($auto['color'])
                    && !empty($auto['number']) && isset($auto['in_parking']);
                if (!$carsExist)
                    return Json::encode(['error' => 'Car params is blank']);
                
                /** @var Cars $car */
                $car = Cars::find()->where(['id' => $auto['id']])->one();

                $car->maker      = $auto['maker'];
                $car->model      = $auto['model'];
                $car->color      = $auto['color'];
                $car->number     = $auto['number'];
                $car->in_parking = $auto['in_parking'];
                $car->save();
            }
        }
    
        return Json::encode(['result' => 'ok']);
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
     * // Отправляем POST-запрос на URL /clients/delete с JSON-телом запроса
     * // {
     * //   "idCar": 1
     * // }
     *
     * // Ожидаемый ответ:
     * // {
     * //   "result": "ok"
     * // }
     * ```
     */
    public function actionDelete()
    {
        $request = \Yii::$app->request->getBodyParams();
        $idAuto  = $request['idCar'];
        
        if (!$idAuto)
            return Json::encode(['error' => 'Enter or id client or id auto']);

        if ($idAuto)
        {
            /** @var Cars $auto */
            $auto = Cars::find()
                ->leftJoin('client_car_ref', 'cars.id = client_car_ref.id_car')
                ->leftJoin('clients', 'clients.id = client_car_ref.id_client')
                ->where(['cars.id' => $idAuto])
                ->with('clients')
                ->one();

            $auto->unlink('clients', $auto->clients, true);
            $auto->delete();
        }
        
        return Json::encode(['result' => 'ok']);
    }
}