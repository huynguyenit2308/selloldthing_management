<?php

namespace App\Helpers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

class CartCount
{
    /**
     * Cập nhật lại số lượng sản phẩm trong giỏ hàng của user hiện tại.
     */
   public static function updateCartCount()
    {
        if (!Auth::check()) {
            session(['cart_count' => 0]);
            return 0;
        }

        $userId = Auth::id();

        $productIds = OrderItem::where('status', 'pending')
            ->whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where('status', 'pending');
            })
            ->pluck('product_id');

        $totalProducts = $productIds->unique()->count();

        session(['cart_count' => $totalProducts]);

        return $totalProducts;
    }
}
