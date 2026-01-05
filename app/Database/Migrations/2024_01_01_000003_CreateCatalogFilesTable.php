<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCatalogFilesTable extends Migration
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
            'file_type' => [
                'type'       => 'ENUM',
                'constraint' => ['docx', 'image'],
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [ // Added updated_at for consistency with Models if needed, though prompt only asked for created_at
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('catalog_id', 'catalogs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('catalog_files');
    }

    public function down()
    {
        $this->forge->dropTable('catalog_files');
    }
}
