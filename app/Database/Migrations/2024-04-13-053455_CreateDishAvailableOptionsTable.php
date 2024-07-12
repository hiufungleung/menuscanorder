<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDishAvailableOptionsTable extends Migration
{
    public function up()
    {
        //Define the DISH_DEFAULT_VALUES table
        $this->forge->addField([
            'DishID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE,
            ],
            'OptionID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ]
        ]);
        $this->forge->addKey(['DishID', 'OptionID'], true);
        // $this->forge->addKey(['DishID', 'OptionID'], false, false, 'DishIDOptionID');
        $this->forge->addForeignKey('DishID', 'DISHES', 'DishID', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('OptionID', 'CUSTOMISATION_OPTIONS', 'OptionID', 'CASCADE', 'CASCADE');
        $this->forge->createTable('DISH_AVAILABLE_OPTIONS');
    }

    public function down()
    {
        $this->forge->dropTable('DISH_AVAILABLE_OPTIONS');
    }
}
