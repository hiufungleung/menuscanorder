<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRestaurantsTable extends Migration
{
    public function up()
    {
        //Define the Restaurant table
        $this->forge->addField([
            'RestaurantID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'Name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'unique' => TRUE
            ],
            'Email' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'unique' => TRUE
            ],
            'Phone' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'unique' => TRUE
            ],
            'Address' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'Password' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
        ]);
        $this->forge->addKey('RestaurantID', TRUE);
        $this->forge->createTable('RESTAURANTS');
    }

    public function down()
    {
        $this->forge->dropTable('RESTAURANTS');
    }
}
