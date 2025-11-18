<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ (nếu cần)
        // User::truncate();

        // 1️⃣ Admin
        User::create([
            'is_new' => false,
            'facebook_id' => null,
            'provider' => null,
            'provider_id' => null,
            'username' => 'admin',
            'fullname' => 'Quản trị viên',
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('123456'),
            'phone' => '0909000001',
            'address' => 'Hà Nội',
            'role' => 'admin',
            'reset_code' => null,
            'reset_expires_at' => null,
            'avatar' => 'https://i.pravatar.cc/150?img=1',
        ]);

        // 2️⃣ User thường
        User::create([
            'is_new' => false,
            'facebook_id' => null,
            'provider' => null,
            'provider_id' => null,
            'username' => 'nguyenvana',
            'fullname' => 'Nguyễn Văn A',
            'name' => 'Nguyễn Văn A',
            'email' => 'user@example.com',
            'password' => Hash::make('123456'),
            'phone' => '0909000002',
            'address' => 'TP. Hồ Chí Minh',
            'role' => 'customer',
            'reset_code' => null,
            'reset_expires_at' => null,
            'avatar' => 'https://i.pravatar.cc/150?img=2',
        ]);

        // 3️⃣ User mới (mô phỏng tài khoản Google)
        User::create([
            'is_new' => true,
            'facebook_id' => null,
            'provider' => 'google',
            'provider_id' => Str::random(20),
            'username' => 'usergoogle',
            'fullname' => 'Người dùng Google',
            'name' => 'Google User',
            'email' => 'googleuser@example.com',
            'password' => Hash::make(Str::random(12)),
            'phone' => null,
            'address' => null,
            'role' => 'customer',
            'reset_code' => null,
            'reset_expires_at' => null,
            'avatar' => 'https://i.pravatar.cc/150?img=3',
        ]);

        // 4️⃣ Seller (có sản phẩm để test)
        User::create([
            'is_new' => false,
            'facebook_id' => null,
            'provider' => null,
            'provider_id' => null,
            'username' => 'seller',
            'fullname' => 'Nguyễn Thị Bán',
            'name' => 'Seller',
            'email' => 'seller@example.com',
            'password' => Hash::make('123456'),
            'phone' => '0909000999',
            'address' => 'Quận 1, TP. Hồ Chí Minh',
            'role' => 'customer',
            'reset_code' => null,
            'reset_expires_at' => null,
            'avatar' => 'https://i.pravatar.cc/150?img=4',
        ]);
    }
}
