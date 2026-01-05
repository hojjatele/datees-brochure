<?php

namespace App\Models;

use CodeIgniter\Model;

class DownloadLogModel extends Model
{
    protected $table            = 'download_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'catalog_id',
        'type',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No update needed
    protected $deletedField  = '';
}
