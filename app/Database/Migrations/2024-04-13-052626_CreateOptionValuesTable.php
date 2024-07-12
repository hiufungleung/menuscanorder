<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOptionValuesTable extends Migration
{
    public function up()
    {
        //Define the OPTION_VALUES table
        $this->forge->addField([
            'ValueID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'OptionID' => [
                'type' => 'INT',
                'constraint' => 16,
                'unsigned' => TRUE
            ],
            'ValueName' => [
                'type' => 'VARCHAR',
                'constraint' => '255'
            ],
            'ExtraPrice' => [
                'type' => 'decimal',
                'constraint' => '10,2'
            ]
        ]);
        $this->forge->addKey('ValueID', TRUE);
        $this->forge->addUniqueKey(['OptionID', 'ValueName'], 'OptionIDValueName');
        $this->forge->addForeignKey('OptionID', 'CUSTOMISATION_OPTIONS', 'OptionID', 'CASCADE', 'CASCADE');
        $this->forge->createTable('OPTION_VALUES');
    }

    public function down()
    {
        $this->forge->dropTable('OPTION_VALUES');
    }
}
