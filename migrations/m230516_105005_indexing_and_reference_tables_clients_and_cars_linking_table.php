<?php

use yii\db\Migration;

class m230516_105005_indexing_and_reference_tables_clients_and_cars_linking_table extends Migration
{
    public function up()
    {
        $this->execute("CREATE INDEX `full_name_index` ON `clients`(`full_name`)");
        $this->execute("CREATE INDEX `gender_index`    ON `clients`(`gender`)");
        $this->execute("CREATE INDEX `phone_index`     ON `clients`(`phone`)");
        $this->execute("CREATE INDEX `address_index`   ON `clients`(`address`)");
    
        $this->execute("CREATE INDEX `client_index` ON `cars`(`id_client`)");
        $this->execute("CREATE INDEX `maker_index`  ON `cars`(`maker`)");
        $this->execute("CREATE INDEX `model_index`  ON `cars`(`model`)");
        $this->execute("CREATE INDEX `color_index`  ON `cars`(`color`)");
        $this->execute("CREATE INDEX `number_index` ON `cars`(`number`)");
    }
    
    public function down()
    {

    }
}