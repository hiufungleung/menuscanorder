<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsAdminToRestaurants extends Migration
{
    public function up()
    {
        $fields = [
            'isAdmin' => [
                'type'       => 'BOOLEAN',
                'null'       => false,
                'default'    => false,
                'after'      => 'Status',  // 確保該欄位在 'status' 之後
            ],
        ];

        $this->forge->addColumn('RESTAURANTS', $fields);
    }

    public function down()
    {
        // 取消 isAdmin 欄位
        $this->forge->dropColumn('RESTAURANTS', 'isAdmin');
    }
}
