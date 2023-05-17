<?php

use yii\db\Migration;

class m230516_105005_indexing_and_reference_tables_clients_and_cars_linking_table extends Migration
{
    public function up()
    {
        $this->execute("CREATE TABLE `client_car_ref` (
            `id`        INT NOT NULL AUTO_INCREMENT,
            `id_client` INT,
            `id_car`    INT,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        $this->execute("CREATE INDEX `full_name_index` ON `clients`(`full_name`)");
        $this->execute("CREATE INDEX `gender_index`    ON `clients`(`gender`)");
        $this->execute("CREATE INDEX `phone_index`     ON `clients`(`phone`)");
        $this->execute("CREATE INDEX `address_index`   ON `clients`(`address`)");
    
        $this->execute("CREATE INDEX `maker_index`  ON `cars`(`maker`)");
        $this->execute("CREATE INDEX `model_index`  ON `cars`(`model`)");
        $this->execute("CREATE INDEX `color_index`  ON `cars`(`color`)");
        $this->execute("CREATE INDEX `number_index` ON `cars`(`number`)");
        
        $this->execute("
            ALTER TABLE    `client_car_ref`
            ADD CONSTRAINT `fk_clients`
            FOREIGN KEY (`id_client`) REFERENCES clients(`id`);
        ");
        
        $this->execute("
            ALTER TABLE    `client_car_ref`
            ADD CONSTRAINT `fk_car`
            FOREIGN KEY (`id_car`) REFERENCES cars(`id`);
        ");
    }
    
    public function down()
    {
        $this->execute("ALTER TABLE `client_car_ref` DROP FOREIGN KEY `fk_clients`;");
        $this->execute("ALTER TABLE `client_car_ref` DROP FOREIGN KEY `fk_car`;");
        $this->execute("DROP TABLE `client_car_ref`;");
    }
}