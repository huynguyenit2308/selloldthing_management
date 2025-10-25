<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CRUD_OrderController extends Controller
{
    public function listOrder()
    {
        // Lấy danh sách đơn hàng chưa thanh toán của người dùng đang đăng nhập
        $orders = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->orderBy('id', 'desc')
            ->get();

        // Trả về view hiển thị danh sách đơn hàng
        return view('order.list_order', compact('orders'));
    }
}
