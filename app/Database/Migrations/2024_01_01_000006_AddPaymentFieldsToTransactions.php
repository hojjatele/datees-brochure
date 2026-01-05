<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentFieldsToTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transactions', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'success', 'failed'],
                'default'    => 'success', // Default to success for existing internal transactions
                'after'      => 'type'
            ],
            'invoice_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'description'
            ],
            'reference_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'invoice_id'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', ['status', 'invoice_id', 'reference_id']);
    }
}
