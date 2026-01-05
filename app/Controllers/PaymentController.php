<?php

namespace App\Controllers;

use App\Services\PaymentService;

class PaymentController extends BaseController
{
    public function charge()
    {
        return view('payment/charge');
    }

    public function processCharge()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $amount = $this->request->getPost('amount');
        if ($amount < 1000) {
             return redirect()->back()->with('error', 'حداقل مبلغ شارژ ۱۰۰۰ تومان است.');
        }

        $service = new PaymentService();
        $result = $service->initiateCharge(session()->get('id'), $amount);

        if ($result['status'] == 'success') {
            return redirect()->to($result['url']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    public function callback()
    {
        // Datees callback usually sends POST or GET data
        // Example: ?invoice=...&refid=...&status=...
        // We verify based on invoice_id (which we stored)

        $invoiceId = $this->request->getVar('invoice');
        $refId = $this->request->getVar('refid') ?? $this->request->getVar('RefId'); // Check documentation typically
        // Assuming simplistic check for now based on prompt details

        if (!$invoiceId) {
             return redirect()->to('/dashboard')->with('error', 'Invalid callback data');
        }

        $service = new PaymentService();
        if ($service->verifyTransaction($invoiceId, $refId)) {
            return redirect()->to('/dashboard')->with('success', 'کیف پول با موفقیت شارژ شد.');
        } else {
            return redirect()->to('/dashboard')->with('error', 'تراکنش ناموفق بود یا قبلاً پردازش شده است.');
        }
    }
}
