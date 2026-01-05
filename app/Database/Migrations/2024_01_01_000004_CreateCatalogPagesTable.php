<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCatalogPagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'catalog_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'page_number' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'ai_proposal' => [
                'type' => 'TEXT', // or JSON
                'null' => true,
            ],
            'user_feedback' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'final_content' => [
                'type' => 'TEXT', // or JSON
                'null' => true,
            ],
            'image_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rendered'],
                'default'    => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('catalog_id', 'catalogs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('catalog_pages');
    }

    public function down()
    {
        $this->forge->dropTable('catalog_pages');
    }
}
