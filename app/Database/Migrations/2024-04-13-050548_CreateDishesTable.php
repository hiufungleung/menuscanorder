<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDishesTable extends Migration
{
    public function up()
    {
        //Define the DISHES table
        $this->forge->addField([
            'DishID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'CategoryID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ],
            'DishName' => [
                'type' => 'VARCHAR',
                'constraint' => '255'
            ],
            'Description' => [
                'type' => 'TEXT',
                'constraint' => '4096'
            ],
            'BasePrice' => [
                'type' => 'decimal',
                'constraint' => '10,2'
            ]
        ]);
        $this->forge->addKey('DishID', TRUE);
        $this->forge->addUniqueKey(['RestaurantID', 'DishName'], 'RestaurantIDDishName');
        $this->forge->addForeignKey('CategoryID', 'DISH_CATEGORIES', 'CategoryID', 'CASCADE', 'SET DEFAULT');
        $this->forge->createTable('DISHES');
    }

    public function down()
    {
        $this->forge->dropTable('DISHES');
    }
}
