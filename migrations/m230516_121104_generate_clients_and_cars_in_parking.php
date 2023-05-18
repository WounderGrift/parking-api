<?php

use app\models\Cars;
use app\models\Clients;
use Faker\Factory;
use yii\db\Migration;

class m230516_121104_generate_clients_and_cars_in_parking extends Migration
{
    const CLIENT_AUTO_COUNT = [
        1, 1, 2, 2, 4, 3, 1, 1, 1, 3
    ];
    
    const LETTERS = [
        'А', 'В', 'Е', 'К', 'М', 'Н', 'О', 'Р', 'С', 'Т', 'У', 'Х'
    ];
    
    public function safeUp()
    {
        $faker = Factory::create();
        foreach (self::CLIENT_AUTO_COUNT as $count)
        {
            $client            = new Clients();
            $client->full_name = $faker->name;
            $client->gender    = $faker->randomElement(['male', 'female']);
            $client->phone     = $faker->unique()->phoneNumber;
            $client->address   = $faker->address;
            $client->save();
            
            for ($carCount = 0; $carCount < $count; $carCount++)
            {
                $car             = new Cars();
                $car->id_client  = $client->id;
                $car->maker      = $faker->randomElement(['Toyota', 'Honda', 'Ford', 'Chevrolet']);
                $car->model      = $faker->word;
                $car->color      = $faker->safeColorName;
                $car->number     = $faker->randomElement(self::LETTERS) . $faker->numerify('###')
                    . $faker->randomElement(self::LETTERS) . $faker->randomElement(self::LETTERS);
                $car->in_parking = $faker->boolean;
                $car->save();
    
                $client->link('cars', $car);
            }
        }
    }
    
    public function safeDown()
    {
        \Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
        \Yii::$app->db->createCommand()->truncateTable('clients')->execute();
        \Yii::$app->db->createCommand()->truncateTable('cars')->execute();
        \Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute();
    }
}