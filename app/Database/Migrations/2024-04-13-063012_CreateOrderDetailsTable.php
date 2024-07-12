<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrderDetailsTable extends Migration
{
    public function up()
    {
        //Define the ORDER_DETAILS table
        $this->forge->addField([
            'OrderDetailID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'OrderID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ],
            'DishID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ],
            'Quantity' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ]
        ]);
        $this->forge->addKey('OrderDetailID', TRUE);
        $this->forge->addUniqueKey(['OrderID', 'OrderDetailID', 'DishID'], 'OrderIDOrderDetailIDDishID');
        $this->forge->addForeignKey('OrderID', 'ORDERS', 'OrderID', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('DishID', 'DISHES', 'DishID', 'CASCADE', 'NO ACTION');
        $this->forge->createTable('ORDER_DETAILS');
    }

    public function down()
    {
        $this->forge->dropTable('ORDER_DETAILS');
    }
}
