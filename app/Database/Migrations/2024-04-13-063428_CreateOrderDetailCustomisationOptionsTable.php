<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrderDetailCustomisationOptionsTable extends Migration
{
    public function up()
    {
        //Define the ORDER_DETAIL_CUSTOMISATION_OPTIONS table
        $this->forge->addField([
            'ValueID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ],
            'OrderDetailID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ]
        ]);
        // $this->forge->addKey(['ValueID', 'OrderDetailID'], true, true, 'ValueIDOrderDetailID');
        $this->forge->addKey(['ValueID', 'OrderDetailID'], true);
        $this->forge->addForeignKey('ValueID', 'OPTION_VALUES', 'ValueID', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('OrderDetailID', 'ORDER_DETAILS', 'OrderDetailID', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ORDER_DETAIL_CUSTOMISATION_OPTIONS');
    }

    public function down()
    {
        $this->forge->dropTable('ORDER_DETAIL_CUSTOMISATION_OPTIONS');
    }
}
