<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Carbon\Carbon;
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
            'type' => 'fixed',
            'discount' => 200000,
            'start_date' => Carbon::createFromFormat('d/m/Y', '11/10/2025'),
            'end_date' => Carbon::createFromFormat('d/m/Y', '12/10/2025'),
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
