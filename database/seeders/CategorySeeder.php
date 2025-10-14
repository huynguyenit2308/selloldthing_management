<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Điện thoại', 'description' => 'Sản phẩm công nghệ cao', 'image' => 'phones.jpg'],
            ['name' => 'Laptop', 'description' => 'Máy tính xách tay', 'image' => 'laptops.jpg'],
            ['name' => 'Phụ kiện', 'description' => 'Tai nghe, chuột, bàn phím', 'image' => 'accessories.jpg'],
        ];

        foreach ($categories as $c) {
            Category::create($c);
        }
    }
}
