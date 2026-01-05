<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'email',
        'password',
        'full_name',
        'phone',
        'role',
        'wallet_balance',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'email'          => 'required|valid_email|is_unique[users.email]',
        'password'       => 'required|min_length[8]',
        'full_name'      => 'permit_empty|min_length[3]',
        'phone'          => 'permit_empty|min_length[10]',
        'wallet_balance' => 'decimal',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get catalogs for the user.
     */
    public function getCatalogs(int $userId)
    {
        return $this->db->table('catalogs')->where('user_id', $userId)->get()->getResultArray();
    }

    /**
     * Get transactions for the user.
     */
    public function getTransactions(int $userId)
    {
        return $this->db->table('transactions')->where('user_id', $userId)->get()->getResultArray();
    }
}
