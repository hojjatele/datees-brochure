<?php

namespace App\Models;

use CodeIgniter\Model;

class CatalogModel extends Model
{
    protected $table            = 'catalogs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'title',
        'status',
        'total_pages',
        'total_cost',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'user_id'     => 'required|integer',
        'title'       => 'required|min_length[3]',
        'status'      => 'in_list[draft,processing,completed,failed]',
        'total_pages' => 'integer',
        'total_cost'  => 'decimal',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Relationships (Manual implementation as CI4 doesn't have Eloquent-like relationships built-in fully)

    /**
     * Get the user who owns the catalog.
     */
    public function getUser(int $userId)
    {
        return $this->db->table('users')->where('id', $userId)->get()->getRowArray();
    }

    /**
     * Get files associated with the catalog.
     */
    public function getFiles(int $catalogId)
    {
        return $this->db->table('catalog_files')->where('catalog_id', $catalogId)->get()->getResultArray();
    }

    /**
     * Get pages associated with the catalog.
     */
    public function getPages(int $catalogId)
    {
        return $this->db->table('catalog_pages')->where('catalog_id', $catalogId)->get()->getResultArray();
    }
}
