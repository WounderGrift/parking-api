<?php

use yii\db\Migration;

class m230516_103252_add_table_cars extends Migration
{
    public function safeUp()
    {
        $this->execute("CREATE TABLE `cars` (
            `id`     INT          NOT NULL AUTO_INCREMENT,
            `maker`  VARCHAR(255) NOT NULL,
            `model`  VARCHAR(255) NOT NULL,
            `color`  VARCHAR(255) NOT NULL,
            `number` VARCHAR(255) NOT NULL,
            `in_parking` BIT DEFAULT FALSE,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    }
    
    public function safeDown()
    {
        $this->execute("DROP TABLE `cars`;");
    }
}