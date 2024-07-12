<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTablesTable extends Migration
{
    public function up()
    {
        //Define the TABLES table
        $this->forge->addField([
            'TableID' => [
                'type' => 'INT',
                'constraint' => 16,
                'auto_increment' => TRUE,
                'unsigned' => TRUE
            ],
            'RestaurantID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ],
            'TableNumber' => [
                'type' => 'VARCHAR',
                'constraint' => '16',
            ],
            'Capacity' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ],
        ]);
        $this->forge->addKey('TableID', true);
        $this->forge->addUniqueKey(['RestaurantID', 'TableNumber'], 'RestaurantIDTableNumber');
        $this->forge->addForeignKey('RestaurantID', 'RESTAURANTS', 'RestaurantID', 'CASCADE', 'CASCADE');
        $this->forge->createTable('TABLES');
    }

    public function down()
    {
        $this->forge->dropTable('TABLES');
    }
}
