<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Voucher::create([
            'code' => 'SALE10',
            'discount' => 10,
            'type' => 'percent',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);

        Voucher::create([
            'code' => 'SALE20',
            'discount' => 20,
            'type' => 'percent',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);

        Voucher::create([
            'code' => 'FREESHIP200',
            'discount' => 200,
            'type' => 'fixed',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);

        Voucher::create([
            'code' => 'FREESHIP500',
            'discount' => 500,
            'type' => 'fixed',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);
    }
}
