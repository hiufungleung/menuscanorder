<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrderIDTrigger extends Migration
{
    public function up()
    {
        $trigger = "
        CREATE TRIGGER before_order_insert
        BEFORE INSERT ON ORDERS
        FOR EACH ROW
        BEGIN
            DECLARE nextOrderNumber INT DEFAULT 1;
            SELECT COALESCE(MAX(OrderNumber), 0) + 1 INTO nextOrderNumber FROM ORDERS WHERE RestaurantID = NEW.RestaurantID;
            SET NEW.OrderNumber = nextOrderNumber;
        END;
        ";

        $this->db->query($trigger);
    }

    public function down()
    {
        $this->db->query("DROP TRIGGER before_order_insert");
    }
}
