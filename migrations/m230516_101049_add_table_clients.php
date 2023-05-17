<?php

use yii\db\Migration;

class m230516_101049_add_table_clients extends Migration
{
    public function safeUp()
    {
        $this->execute("CREATE TABLE `clients` (
            `id`        INT                    NOT NULL AUTO_INCREMENT,
            `full_name` VARCHAR(255)           NOT NULL
                CHECK (LENGTH(`full_name`) > 2),
            `gender`    ENUM('male', 'female') NOT NULL,
            `phone`     VARCHAR(20)            NOT NULL UNIQUE,
            `address`   VARCHAR(255),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    }
    
    public function safeDown()
    {
        $this->execute("DROP TABLE `clients`;");
    }
}