<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncoder;
use App\Models\Voucher;
use App\Rules\HasAtLeastOneChar;
use App\Rules\NoFullWidthSpace;
use App\Rules\NoHTML;
use App\Rules\NotEmptyOrSpace;
use Illuminate\Http\Request;

class CRUD_VoucherController extends Controller
{
    /**
     * Danh sách voucher
     */
    public function listVoucher()
    {
        $page = request()->query('page');

        if (!$page) {
            $page = 1;
        }

        if (!is_numeric($page) || (int)$page < 1) {
            return redirect()->route('voucher.list')->with('error', 'Trang không tồn tại.');
        }

        $page = (int) $page;

        $voucher = Voucher::paginate(3, ['*'], 'page', $page);

        if ($page > $voucher->lastPage()) {
            return redirect()->route('voucher.list')->with('error', 'Trang không tồn tại.');
        }

        if ($voucher->isEmpty()) {
            return view('crud_voucher.list', compact('voucher'))->with('error', 'Không có voucher nào!!!');
        }
        foreach ($voucher as $value) {
            $value->encode_id = IdEncoder::encodeId($value->id);
        }

        return view('crud_voucher.list', compact('voucher'));
    }

    /**
     * Xem chi tiết voucher
     **/
    public function detailVoucher(Request $request)
    {
        $encodeId = $request->get('id');
        $id = IdEncoder::decodeId($encodeId);

        if (!$id || !($voucher = Voucher::find($id))) {
            return redirect()->route('voucher.list')->with('error', 'ID không hợp lệ!');
        }

        return view('crud_voucher.detail', compact('voucher'));
    }

    /**
     * Thêm voucher
     **/
    public function addVoucher()
    {
        return view('crud_voucher.add');
    }

    public function postAddVoucher(Request $request)
    {
        $request->validate([
            'code' => [
                new NoFullWidthSpace(),
                new NotEmptyOrSpace(),
                new HasAtLeastOneChar(),
                new NoHTML(),
                'max:50',
                'unique:vouchers,code',
                'regex:/^[\pL\pN\s\-]+$/u',
            ],
            'type' => 'required|in:percent,fixed',
            'discount' => 'required|numeric|min:0|max:1000000000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ], [
            'code.max' => 'Mã voucher không được vượt quá 50 ký tự.',
            'code.unique' => 'Mã voucher đã tồn tại.',
            'code.regex' => 'Mã voucher chỉ được chứa chữ cái, số, khoảng trắng và dấu gạch ngang.',
            'type.required' => 'Vui lòng chọn loại voucher.',
            'type.in' => 'Loại voucher không hợp lệ.',
            'discount.required' => 'Vui lòng nhập giá trị.',
            'discount.numeric' => 'Giá trị phải là số.',
            'discount.min' => 'Giá trị không được nhỏ hơn 0.',
            'discount.max' => 'Giá trị không được lớn hơn 1 tỷ.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'start_date.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.date' => 'Vui lòng chọn ngày kết .',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ], [
            'code' => 'Mã voucher',
        ]);

        $voucher = Voucher::create([
            'code' => $request->code,
            'type' => $request->type,
            'discount' => $request->discount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('voucher.list')->with('success', 'Thêm voucher "' . $voucher->code . '" thành công!');
    }

    /**
     * Thêm voucher
     **/
    public function updateVoucher(Request $request)
    {
        $encodedId = $request->get('id');
        $id = IdEncoder::decodeId($encodedId);

        if (!$id || !($voucher = Voucher::find($id))) {
            return redirect()->route('voucher.list')->with('error', 'ID không hợp lệ!');
        }

        return view('crud_voucher.update', compact('voucher'));
    }

    public function updatePostVoucher(Request $request)
    {
        $request->validate([
            'code' => [
                new NoFullWidthSpace(),
                new NotEmptyOrSpace(),
                new HasAtLeastOneChar(),
                new NoHTML(),
                'max:50',
                'regex:/^[\pL\pN\s\-]+$/u',
            ],
            'type' => 'required|in:percent,fixed',
            'discount' => 'required|numeric|min:0|max:1000000000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ], [
            'code.max' => 'Mã voucher không được vượt quá 50 ký tự.',
            'code.regex' => 'Mã voucher chỉ được chứa chữ cái, số, khoảng trắng và dấu gạch ngang.',
            'type.required' => 'Vui lòng chọn loại voucher.',
            'type.in' => 'Loại voucher không hợp lệ.',
            'discount.required' => 'Vui lòng nhập giá trị.',
            'discount.numeric' => 'Giá trị phải là số.',
            'discount.min' => 'Giá trị không được nhỏ hơn 0.',
            'discount.max' => 'Giá trị không được lớn hơn 1 tỷ.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'start_date.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.date' => 'Vui lòng chọn ngày kết .',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ], [
            'code' => 'Mã voucher',
        ]);
        $encodeId = $request->get('id');
        $id = IdEncoder::decodeId($encodeId);
        $voucher = Voucher::find($id);

        if (!$voucher) {
            return redirect()->route('voucher.list')->with('error', 'Voucher không tồn tại!');
        }

        $formUpdatedAt = $request->input('updated_at');
        if ($voucher->updated_at->toDateTimeString() !== $formUpdatedAt) {
            return back()->withInput()->with('error', 'Dữ liệu đã bị thay đổi bởi người khác. Vui lòng tải lại trang và thử lại.');
        }

        $voucher->update([
            'code' => $request->code,
            'type' => $request->type,
            'discount' => $request->discount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        $encodeId = IdEncoder::encodeId($voucher->id);

        return redirect()->route('voucher.detail', ['id' => $encodeId])->with('success', 'Cập nhật voucher "' . $voucher->code . '" thành công!');
    }

    /**
     * Xóa voucher
     */
    public function deleteVoucher(Request $request)
    {
        $encodedId = $request->input('id');
        $id = IdEncoder::decodeId($encodedId);

        if (!$id) {
            return redirect()->route('voucher.list')->with('error', 'ID không hợp lệ!');
        }

        $voucher = Voucher::find($id);
        if (!$voucher) {
            return redirect()->route('voucher.list')->with('error', 'Voucher đã bị xóa hoặc không tồn tại!');
        }

        $voucherName = $voucher->code;
        $voucher->delete();

        return redirect()->route('voucher.list')->with('success', 'Xóa voucher "' . $voucherName . '" thành công!');
    }
}
