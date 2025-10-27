@extends('dashboard')

@section('content')
    <div class="container py-5">
        <h3 class="mb-4 text-center font-weight-bold" style="padding-top: 120px">XÁC NHẬN THANH TOÁN</h3>

        @if (session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger text-center">{{ session('error') }}</div>
        @endif

        {{-- Danh sách đơn hàng --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-danger text-white font-weight-bold text-center">Chi tiết đơn hàng</div>
            <div class="card-body">
                @foreach ($orders as $order)
                    @foreach ($order->items as $item)
                        @if (in_array($item->id, $itemIds))
                            <div class="card mb-3 shadow-sm border-1">
                                <div class="card-body d-flex align-items-center">
                                    <div class="mr-3">
                                        <img src="{{ $item->product->images->first() ? asset('storage/' . $item->product->images->first()->url) : asset('images/default.jpg') }}"
                                            alt="{{ $item->product->name }}" class="rounded border"
                                            style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="font-weight-bold text-dark mb-2">{{ $item->product->name }}</h5>
                                        <p class="mb-1 text-muted">
                                            Số lượng: <span class="text-dark">{{ $item->quantity }}</span>
                                        </p>
                                        <p class="mb-0 text-danger font-weight-bold h5">
                                            {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }} VND
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Tổng tiền + Voucher --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light font-weight-bold text-center">
                Thông tin thanh toán
            </div>
            <div class="card-body">
                <form action="{{ route('order.payment') }}" method="GET" class="w-100">
                    @csrf
                    @foreach ($orders as $order)
                        @foreach ($order->items as $item)
                            @if (in_array($item->id, $itemIds))
                                <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                            @endif
                        @endforeach
                    @endforeach

                    <div class="row">
                        {{-- Cột trái: Chọn mã giảm giá --}}
                        <div class="col-md-6 border-end">
                            <h5 class="text-primary fw-bold mb-3">Chọn mã giảm giá</h5>
                            <select class="form-control w-75" name="voucher_code" onchange="this.form.submit()">
                                <option value="">-- Không sử dụng voucher --</option>
                                @foreach ($vouchers as $voucher)
                                    <option value="{{ $voucher->code }}"
                                        {{ $voucher->code == $voucherCode ? 'selected' : '' }}>
                                        {{ $voucher->code }}
                                        ({{ $voucher->type == 'percent' ? $voucher->discount . '%' : number_format($voucher->discount, 0, ',', '.') . ' VND' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Cột phải: Tóm tắt thanh toán --}}
                        <div class="col-md-6">
                            <h5 class="text-warning fw-bold mb-3">Tóm tắt thanh toán</h5>

                            <p class="d-flex justify-content-between">
                                <span>Tổng tiền hàng:</span>
                                <strong>{{ number_format($originalTotal, 0, ',', '.') }} VND</strong>
                            </p>

                            <p class="d-flex justify-content-between">
                                <span>Giảm giá:</span>
                                <strong class="text-success">
                                    -{{ number_format($discount, 0, ',', '.') }} VND
                                </strong>
                            </p>

                            <hr>

                            <p class="d-flex justify-content-between text-danger fw-bold">
                                <span>Thành tiền:</span>
                                <span>{{ number_format($totalAmount, 0, ',', '.') }} VND</span>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Phương thức thanh toán --}}
        <form action="{{ route('payment.cash.online') }}" method="POST" id="checkoutForm">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light font-weight-bold">Phương thức thanh toán</div>
                <div class="card-body">
                    <div style="padding-left: 20px">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash"
                                value="cash" checked>
                            <label class="form-check-label" for="cash">Thanh toán khi nhận hàng (COD)</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="momo"
                                value="momo">
                            <label class="form-check-label" for="momo">Thanh toán qua MoMo</label>
                        </div>
                    </div>
                </div>
            </div>
            @csrf
            <input type="hidden" name="voucher_code" value="{{ $voucherCode }}">
            @foreach ($orders as $order)
                @foreach ($order->items as $item)
                    @if (in_array($item->id, $itemIds))
                        <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                    @endif
                @endforeach
            @endforeach
            <div class="text-center">
                <a href="{{ route('orders.list') }}" class="btn btn-outline-info btn-sm mr-2">
                    <i class="fa fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-outline-warning btn-sm mr-2">
                    <i class="fa fa-credit-card"></i> Xác nhận thanh toán
                </button>
            </div>
        </form>
    </div>
@endsection
