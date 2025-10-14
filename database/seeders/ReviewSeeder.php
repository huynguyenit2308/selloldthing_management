<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $users = User::all();

        if ($products->count() == 0 || $users->count() == 0) {
            return;
        }

        foreach ($products as $product) {
            foreach ($users->random(min(3, $users->count())) as $user) {
                Review::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => rand(1, 5),
                    'comment' => fake()->sentence(8),
                    'media' => fake()->imageUrl(640, 480, 'products', true),
                    'status' => fake()->randomElement(['approved', 'pending', 'rejected']),
                ]);
            }
        }
    }
}
