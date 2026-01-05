<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();

        return view('admin/users/index', ['users' => $users]);
    }

    public function updateWallet()
    {
        $userId = $this->request->getPost('user_id');
        $amount = $this->request->getPost('amount');

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if ($user) {
            $userModel->update($userId, ['wallet_balance' => $amount]);
            return redirect()->to('/admin/users')->with('success', 'Wallet updated successfully');
        }

        return redirect()->to('/admin/users')->with('error', 'User not found');
    }
}
