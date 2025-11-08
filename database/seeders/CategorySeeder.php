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
            // 3 danh mục có nhiều sản phẩm nhất
            [
                'name' => 'Điện tử - Điện thoại',
                'description' => 'Điện thoại di động, smartphone, máy tính bảng cũ',
                'image' => 'images/accessories.jpg',
                'status' => 1,
            ],
            [
                'name' => 'Thời trang',
                'description' => 'Quần áo, giày dép, túi xách, phụ kiện thời trang',
                'image' => 'images/thoitrang.jpg',
                'status' => 1,
            ],
            [
                'name' => 'Đồ gia dụng',
                'description' => 'Đồ dùng nhà bếp, nội thất, đồ trang trí',
                'image' => 'images/dogiadung.jpg',
                'status' => 1,
            ],
            
            // 2 danh mục ít sản phẩm hơn
            [
                'name' => 'Sách - Văn phòng phẩm',
                'description' => 'Sách cũ, truyện tranh, văn phòng phẩm, dụng cụ học tập',
                'image' => 'images/sach.jpg',
                'status' => 1,
            ],
            [
                'name' => 'Xe cộ - Phụ tùng',
                'description' => 'Xe máy cũ, xe đạp, phụ tùng xe, phụ kiện xe',
                'image' => 'images/xeco.jpg',
                'status' => 1,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
