<?php

namespace App\Models;

use CodeIgniter\Model;

class CatalogPageModel extends Model
{
    protected $table            = 'catalog_pages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'catalog_id',
        'page_number',
        'ai_proposal',
        'user_feedback',
        'final_content',
        'image_path',
        'status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'catalog_id'  => 'required|integer',
        'page_number' => 'required|integer',
        'status'      => 'in_list[pending,approved,rendered]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
