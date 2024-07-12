<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        //Define the ORDERS table
        $this->forge->addField([
            'OrderID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'RestaurantID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ],
            'OrderNumber' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ],
            'CustomerName' => [
                'type' => 'VARCHAR',
                'constraint' => '255'
            ],
            'TotalPrice' => [
                'type' => 'decimal',
                'constraint' => '10,2'
            ],
            'OrderTime' => [
                'type' => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP')
            ],
            'TableID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ],
            'Comment' => [
                'type' => 'TEXT',
                'constraint' => '4096',
                'null' => TRUE
            ],
            'Status' => [
                'type' => 'ENUM',
                'constraint' => ['Pending', 'Completed', 'Cancelled'],
                'default' => 'Pending'
            ]
        ]);
        $this->forge->addKey('OrderID', TRUE);
        $this->forge->addUniqueKey(['RestaurantID', 'OrderName'], 'RestaurantIDOrderName');
        $this->forge->addForeignKey('RestaurantID', 'RESTAURANTS', 'RestaurantID', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('TableID', 'TABLES', 'TableID', 'CASCADE', 'NO ACTION');
        $this->forge->createTable('ORDERS');
    }

    public function down()
    {
        $this->forge->dropTable('ORDERS');
    }
}
