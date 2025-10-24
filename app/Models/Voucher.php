<?php

namespace App\Models;

use App\Helpers\IdEncoder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'discount', 'type', 'start_date', 'end_date'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Lấy voucher theo encoded ID
     */
    public static function findByEncodedId($encodedId)
    {
        $id = IdEncoder::decodeId($encodedId);
        return $id ? self::find($id) : null;
    }

    /**
     * Hàm thêm Voucher
     */
    public static function createVoucher($data)
    {
        return self::create([
            'code' => $data['code'],
            'type' => $data['type'],
            'discount' => $data['discount'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);
    }

    /**
     * Hàm sửa Voucher
     */
    public static function updateVoucher($encodedId, array $data)
    {
        $voucher = self::findByEncodedId($encodedId);
        if (!$voucher) {
            return null;
        }

        $voucher->update([
            'code' => $data['code'],
            'type' => $data['type'],
            'discount' => $data['discount'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);

        return $voucher;
    }

    /**
     * Xóa voucher theo encoded ID
     */
    public static function deleteVoucher($encodedId)
    {
        $id = IdEncoder::decodeId($encodedId);

        if (!$id) {
            return null;
        }

        $voucher = self::find($id);

        if (!$voucher) {
            return null;
        }

        $voucherName = $voucher->code;
        $voucher->delete();

        return $voucherName;
    }
}
