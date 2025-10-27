<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Hiển thị sản phẩm thanh toán và voucher
     */
    public function showPayment(Request $request)
    {
        // Lấy danh sách ID đơn hàng được chọn
        $itemIds = $request->input('item_ids', []);
        $voucherCode = $request->input('voucher_code');

        // Nếu không chọn đơn nào -> quay lại và báo lỗi
        if (empty($itemIds)) {
            return redirect()->route('orders.list')->with('error', 'Bạn chưa chọn hóa đơn thanh toán.');
        }

        // Lấy thông tin đơn hàng và sản phẩm liên quan
        $orders = Order::whereHas('items', function ($q) use ($itemIds) {
            $q->whereIn('id', $itemIds);
        })->with(['items.product.images'])->get();

        // Tính tổng tiền ban đầu
        $originalTotal = 0;
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if (in_array($item->id, $itemIds)) {
                    $originalTotal += $item->quantity * $item->product->price;
                }
            }
        }

        // Lấy danh sách voucher đang hoạt động
        $today = now()->toDateString();
        $vouchers = Voucher::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();

        // Áp dụng voucher (nếu có)
        $voucher = null;
        $discount = 0;

        if ($voucherCode) {
            $voucher = $vouchers->firstWhere('code', $voucherCode);

            if ($voucher) {
                if ($voucher->type === 'percent') {
                    $discount = $originalTotal * ($voucher->discount / 100);
                } else {
                    $discount = min($voucher->discount, $originalTotal);
                }
            }
        }

        // Tính tổng tiền sau giảm
        $totalAmount = max(0, $originalTotal - $discount);

        // Trả dữ liệu cho view
        return view('payment.order-payment', compact(
            'orders',
            'vouchers',
            'voucherCode',
            'originalTotal',
            'discount',
            'totalAmount',
            'itemIds'
        ));
    }

    /**
     * Cấu hình toán momo
     */
    public function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }

    public function paymentCashAndOnline(Request $request)
    {
        $paymentMethod = $request->input('payment_method');
        $itemIds = $request->input('item_ids', []);
        $voucherCode = $request->input('voucher_code');

        if (empty($itemIds)) {
            return redirect()->back()->with('error', 'Bạn chưa chọn sản phẩm để thanh toán.');
        }

        // Lấy các order có ít nhất 1 item được chọn
        $orders = Order::whereHas('items', function ($q) use ($itemIds) {
            $q->whereIn('id', $itemIds);
        })->with('items.product')->get();

        // Tính tổng tiền ban đầu
        $originalTotal = 0;
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if (in_array($item->id, $itemIds)) {
                    $originalTotal += $item->quantity * $item->product->price;
                }
            }
        }

        // Xử lý voucher
        $discount = 0;
        $voucher = null;
        if ($voucherCode) {
            $today = now()->toDateString();
            $voucher = Voucher::where('code', $voucherCode)
                ->where(function ($q) use ($today) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
                })
                ->first();

            if (!$voucher) {
                return redirect()->back()->with('error', 'Voucher không hợp lệ hoặc đã hết hạn.');
            }

            $discount = $voucher->type === 'percent'
                ? $originalTotal * ($voucher->discount / 100)
                : min($voucher->discount, $originalTotal);
        }

        $totalAmount = max(0, $originalTotal - $discount);

        // Lưu Payment cho từng order
        foreach ($orders as $order) {
            // Tính tiền của order chỉ với item được chọn
            $orderTotal = 0;
            foreach ($order->items as $item) {
                if (in_array($item->id, $itemIds)) {
                    $orderTotal += $item->quantity * $item->product->price;
                }
            }

            // Phân bổ giảm giá theo tỉ lệ
            $orderDiscount = $originalTotal > 0 ? $discount * ($orderTotal / $originalTotal) : 0;
            $orderFinal = $orderTotal - $orderDiscount;

            // Tạo payment
            Payment::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'amount' => $orderFinal,
                'payment_method' => $paymentMethod,
                'payment_status' => 'completed',
            ]);

            foreach ($order->items as $item) {
                if (in_array($item->id, $itemIds)) {
                    $item->status = 'completed';
                    $item->save();
                }
            }
        }

        return redirect()->route('orders.list')->with('success', 'Thanh toán thành công!');
    }
}
