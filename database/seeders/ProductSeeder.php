<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            for ($i = 1; $i <= 5; $i++) {
                $product = Product::create([
                    'name' => $category->name . " mẫu $i",
                    'category_id' => $category->id,
                    'price' => rand(1000000, 10000000),
                    'description' => 'Mô tả sản phẩm ' . $i,
                    'quantity' => rand(5, 20),
                ]);

                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => 'product_' . $i . '.jpg',
                    'description' => 'Ảnh mô tả sản phẩm ' . $i,
                ]);
            }
        }
    }
}
