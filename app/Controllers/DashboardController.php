<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\CatalogModel;
use App\Models\TransactionModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $userId = session()->get('id');

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        // Using the helper methods we created in Phase 1
        $catalogs = $userModel->getCatalogs($userId);
        $transactions = $userModel->getTransactions($userId);

        return view('dashboard/index', [
            'user' => $user,
            'catalogs' => $catalogs,
            'transactions' => $transactions,
        ]);
    }
}
