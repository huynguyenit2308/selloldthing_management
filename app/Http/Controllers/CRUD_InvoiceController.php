<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncoder;
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
        foreach ($payments as $value) {
            $value->encode_id = IdEncoder::encodeId($value->id);
        }
        return view('invoice.list', compact('payments'));
    }

    /**
     * Chi tiết hóa đơn
     */
    public function detailInvoice(Request $request)
    {
        $encodeId = $request->get('id');
        $id = IdEncoder::decodeId($encodeId);

        if (!$id) {
            return redirect()->route('invoice.list')->with('error', 'ID không hợp lệ!');
        }

        // Lấy hóa đơn kèm quan hệ user, order, voucher
        $payment = Payment::with(['user',  'order.items.product', 'voucher'])->find($id);

        if (!$payment) {
            return redirect()->route('invoice.list')->with('error', 'Hóa đơn không tồn tại!');
        }

        return view('invoice.detail', compact('payment'));
    }
}
