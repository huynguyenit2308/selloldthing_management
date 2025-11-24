<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncoder;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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

    /**
     * In PDF
     */
    public function generatePDF($encodeId)
    {
        $id = IdEncoder::decodeId($encodeId);

        if (!$id) {
            return redirect()->route('invoice.list')->with('error', 'ID không hợp lệ!');
        }

        $payment = Payment::with(['user', 'order.items.product', 'voucher'])->find($id);

        if (!$payment) {
            return redirect()->route('invoice.list')->with('error', 'Hóa đơn không tồn tại!');
        }

        $pdf = PDF::loadView('invoice.pdf', compact('payment'))->setPaper('A4', 'portrait')->setOption('isFontSubsettingEnabled', true);

        // Tạo thư mục invoices nếu chưa tồn tại
        $folderPath = public_path('invoices');
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true); // true = tạo đệ quy nếu cần
        }

        // Lưu PDF vào public/invoices
        $filePath = $folderPath . '/invoice_' . $payment->id . '.pdf';
        $pdf->save($filePath);

        // Mở PDF trong trình duyệt
        return $pdf->download('invoice_' . $payment->id . '.pdf');
    }
}
