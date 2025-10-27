<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CRUD_OrderController extends Controller
{
    /**
     * Lấy danh sách đơn hàng
     */
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

    /**
     * Thêm vào giỏ hàng
     */
    public function addToCart(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.');
        }
        
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $user = Auth::user();
        $product = Product::findOrFail($productId);


        // Kiểm tra xem user đã có order "pending" chưa
        $order = Order::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            ['total_price' => 0]
        );
        // dd($order->id);

        // Kiểm tra xem product đã có trong order chưa
        $orderItem = OrderItem::where('order_id', $order->id)
            ->where('product_id', $productId)
            ->first();

        if ($orderItem) {
            // Nếu đã có thì cộng thêm số lượng
            $orderItem->quantity += $quantity;
            $orderItem->save();
        } else {
            // Nếu chưa có thì thêm mới
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        // Cập nhật tổng tiền đơn hàng
        $order->total_price = $order->items()->with('product')->get()
            ->sum(fn($item) => $item->quantity * $item->product->price);
        $order->save();

        return redirect()->back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
    }
}
