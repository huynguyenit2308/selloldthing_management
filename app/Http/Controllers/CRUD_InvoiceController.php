<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class CRUD_InvoiceController extends Controller
{
    /**
     * Lấy danh sách hóa đơn
     */
    public function listInvoice()
    {
        $payments = Payment::with(['user', 'order', 'voucher'])
        ->orderBy('created_at', 'desc')
        ->paginate(6);

        return view('invoice.list', compact('payments'));
    }
}
