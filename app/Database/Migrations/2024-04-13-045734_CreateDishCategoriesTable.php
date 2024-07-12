<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDishCategoriesTable extends Migration
{
    public function up()
    {
        //Define the DISH_CATEGORIES table
        $this->forge->addField([
            'CategoryID' => [
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
            'CategoryName' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'default' => 'Uncategorised'
            ]
        ]);
        $this->forge->addKey('CategoryID', TRUE);
        $this->forge->addUniqueKey(['RestaurantID', 'CategoryName'], 'RestaurantIDCategoryName');
        $this->forge->addForeignKey('RestaurantID', 'RESTAURANTS', 'RestaurantID', 'CASCADE', 'CASCADE');
        $this->forge->createTable('DISH_CATEGORIES');
    }

    public function down()
    {
        $this->forge->dropTable('DISH_CATEGORIES');
    }
}
