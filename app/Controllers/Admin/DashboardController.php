<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\CatalogModel;
use App\Models\TransactionModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $catalogModel = new CatalogModel();
        $transModel = new TransactionModel();

        $stats = [
            'total_users' => $userModel->countAllResults(),
            'total_catalogs' => $catalogModel->countAllResults(),
            'total_revenue' => $transModel->where('type', 'charge')->where('status', 'success')->selectSum('amount')->first()['amount'] ?? 0,
        ];

        // Chart Data: Revenue per day for last 7 days
        // Simplified query for demo
        $chartData = [
            'labels' => [],
            'data' => []
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartData['labels'][] = $date;
            $amount = $transModel->where('type', 'charge')
                                 ->where('status', 'success')
                                 ->like('created_at', $date)
                                 ->selectSum('amount')
                                 ->first()['amount'] ?? 0;
            $chartData['data'][] = $amount;
        }

        return view('admin/dashboard', ['stats' => $stats, 'chartData' => $chartData]);
    }
}
