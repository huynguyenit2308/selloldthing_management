<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
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
        } else {
            if ($paymentMethod == 'cash') {
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
                        'voucher_id' => $voucher?->id,
                        'amount' => $orderFinal,
                        'payment_method' => $paymentMethod,
                        'payment_status' => 'completed',
                    ]);

                    // Cập nhật status cho các OrderItem đã thanh toán
                    foreach ($order->items as $item) {
                        if (in_array($item->id, $itemIds)) {
                            $item->status = 'completed';
                            $item->save();
                        }
                    }

                    // Kiểm tra nếu tất cả OrderItem của Order đã completed
                    // thì cập nhật Order status thành completed để tính vào thống kê doanh thu
                    $allItemsCompleted = $order->items()->where('status', '!=', 'completed')->count() === 0;
                    if ($allItemsCompleted) {
                        $order->status = 'completed';
                        $order->save();
                    }
                }
                return redirect()->route('orders.list')->with('success', 'Thanh toán tiền mặt thành công!');
            } elseif ($paymentMethod == 'momo') {
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

                // Cấu hình thông tin MoMo test
                $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
                $partnerCode = 'MOMOBKUN20180529';
                $accessKey = 'klm05TvNBzhg7h7j';
                $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

                $orderInfo = "Thanh toán qua ví MoMo";
                $amount = (string) $totalAmount;
                $orderId = 'ORDER_' . Auth::id() . '_' . time();
                $redirectUrl = route('momo.callback');
                $ipnUrl = route('momo.callback');

                $extraData = base64_encode(json_encode([
                    'item_ids' => $itemIds,
                    'voucher_code' => $voucherCode,
                    'user_id' => Auth::id(),
                ]));

                $requestId = time() . "";
                $requestType = "payWithATM";

                // Tạo chữ ký bảo mật
                $rawHash = "accessKey=" . $accessKey .
                    "&amount=" . $amount .
                    "&extraData=" . $extraData .
                    "&ipnUrl=" . $ipnUrl .
                    "&orderId=" . $orderId .
                    "&orderInfo=" . $orderInfo .
                    "&partnerCode=" . $partnerCode .
                    "&redirectUrl=" . $redirectUrl .
                    "&requestId=" . $requestId .
                    "&requestType=" . $requestType;

                $signature = hash_hmac("sha256", $rawHash, $secretKey);

                // Tạo dữ liệu gửi đi
                $data = [
                    'partnerCode' => $partnerCode,
                    'partnerName' => "MoMoTest",
                    'storeId' => "MoMoTestStore",
                    'requestId' => $requestId,
                    'amount' => $amount,
                    'orderId' => $orderId,
                    'orderInfo' => $orderInfo,
                    'redirectUrl' => $redirectUrl,
                    'ipnUrl' => $ipnUrl,
                    'lang' => 'vi',
                    'extraData' => $extraData,
                    'requestType' => $requestType,
                    'signature' => $signature
                ];

                // Gọi API MoMo
                $result = $this->execPostRequest($endpoint, json_encode($data));
                $jsonResult = json_decode($result, true);

                if (isset($jsonResult['payUrl'])) {
                    // Nếu tạo lệnh thanh toán thành công -> chuyển hướng qua MoMo
                    return redirect()->away($jsonResult['payUrl']);
                } else {
                    // Nếu có lỗi -> quay lại
                    return redirect()->back()->with('error', 'Không thể tạo giao dịch MoMo: ' . ($jsonResult['message'] ?? 'Không xác định'));
                }
                // Tài khoản test momo:
                // 9704 0000 0000 0018
                // NGUYEN VAN A
                // 03/07
                // OTP
            } else {
                return redirect()->back()->with('error', 'Phương thức thanh toán không hợp lệ.');
            }
        }
    }

    public function momoCallback(Request $request)
    {
        // Kiểm tra mã kết quả
        if ($request->resultCode == 0) {

            // Nếu có extraData thì xử lý và lưu Payment
            if (!empty($request->extraData)) {
                $data = json_decode(base64_decode($request->extraData), true);

                if (is_array($data) && isset($data['item_ids'])) {
                    $firstOrderId = null;
                    $orderIds = [];
                    
                    // Cập nhật status cho các OrderItem đã thanh toán
                    foreach ($data['item_ids'] as $itemId) {
                        $item = \App\Models\OrderItem::find($itemId);
                        if ($item) {
                            $item->status = 'completed';
                            $item->save();

                            // Lưu lại order_id đầu tiên và tất cả order_ids
                            if (!$firstOrderId) {
                                $firstOrderId = $item->order_id;
                            }
                            if (!in_array($item->order_id, $orderIds)) {
                                $orderIds[] = $item->order_id;
                            }
                        }
                    }

                    // Tạo bản ghi thanh toán
                    \App\Models\Payment::create([
                        'order_id' => $firstOrderId,
                        'user_id' => $data['user_id'] ?? Auth::id(),
                        'voucher_id' => $data['voucher_id'] ?? null,
                        'amount' => $request->amount,
                        'payment_method' => 'momo',
                        'payment_status' => 'completed',
                    ]);

                    // Kiểm tra và cập nhật Order status thành completed
                    // nếu tất cả OrderItem đã completed (để tính vào thống kê doanh thu)
                    foreach ($orderIds as $orderId) {
                        $order = \App\Models\Order::find($orderId);
                        if ($order) {
                            $allItemsCompleted = $order->items()->where('status', '!=', 'completed')->count() === 0;
                            if ($allItemsCompleted) {
                                $order->status = 'completed';
                                $order->save();
                            }
                        }
                    }
                }
            }

            // Trả về cho cả IPN (MoMo gọi) và Callback (user redirect)
            if ($request->isMethod('post')) {
                // Trường hợp notify từ MoMo server
                return response()->json(['message' => 'Payment confirmed successfully']);
            } else {
                // Trường hợp user được redirect về website
                return redirect()->route('orders.list')->with('success', 'Thanh toán MoMo thành công!');
            }
        }

        //  Nếu thất bại
        if ($request->isMethod('post')) {
            return response()->json(['message' => 'Payment failed'], 400);
        } else {
            return redirect()->route('orders.list')->with('error', 'Thanh toán MoMo thất bại hoặc bị hủy.');
        }
    }
}
