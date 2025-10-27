<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();
        $voucher = Voucher::first();

        foreach ($users as $user) {
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => rand(1000000, 5000000),
                'status' => 'completed',
            ]);

            $products = Product::inRandomOrder()->take(3)->get();

            foreach ($products as $product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3),
                ]);
            }
        }
    }
}
