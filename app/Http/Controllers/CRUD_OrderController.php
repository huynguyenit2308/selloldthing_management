<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CRUD_OrderController extends Controller
{
    public function listOrder()
    {
        // Lấy danh sách đơn hàng của user hiện tại
        $orders = Order::where('user_id', Auth::id())
            ->whereHas('items', function ($query) {
                $query->where('status', 'pending'); 
            })
            ->with(['items' => function ($query) {
                $query->where('status', 'pending')->with('product');
            }])
            ->orderBy('id', 'desc')
            ->get();

        return view('order.list_order', compact('orders'));
    }
}
