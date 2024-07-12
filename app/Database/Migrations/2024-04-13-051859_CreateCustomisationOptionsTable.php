<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomisationOptionsTable extends Migration
{
    public function up()
    {
        //Define the CUSTOMISED_OPTIONS table
        $this->forge->addField([
            'OptionID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'OptionName' => [
                'type' => 'VARCHAR',
                'constraint' => '255'
            ],
            'RestaurantID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ]
        ]);
        $this->forge->addKey('OptionID', TRUE);
        $this->forge->addUniqueKey(['RestaurantID', 'OptionName'], 'RestaurantIDOptionName');
        $this->forge->addForeignKey('RestaurantID', 'RESTAURANTS', 'RestaurantID', 'CASCADE', 'CASCADE');
        $this->forge->createTable('CUSTOMISATION_OPTIONS');
    }

    public function down()
    {
        $this->forge->dropTable('CUSTOMISATION_OPTIONS');
    }
}
