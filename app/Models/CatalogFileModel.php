<?php

namespace App\Models;

use CodeIgniter\Model;

class CatalogFileModel extends Model
{
    protected $table            = 'catalog_files';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'catalog_id',
        'file_type',
        'file_path',
        'original_name',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at'; // Added to schema but not mandatory
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'catalog_id'    => 'required|integer',
        'file_type'     => 'required|in_list[docx,image]',
        'file_path'     => 'required|string',
        'original_name' => 'required|string',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
