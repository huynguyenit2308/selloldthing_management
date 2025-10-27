@extends('dashboard')

@section('content')
    @php
        use App\Helpers\IdEncoder;
    @endphp

    <section class="py-5 mt-5">
        <div class="container">
            <h2 class="text-center mb-5 font-weight-bold" style="padding-top: 120px">
                Danh sách hóa đơn đang chờ thanh toán
            </h2>

            @if ($orders->count() > 0)
                <form action="{{ route('order.payment') }}" method="GET" id="paymentForm">
                    @csrf

                    {{-- Thông báo --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    {{-- Chọn tất cả --}}
                    <div class="d-flex justify-content-end align-items-center mb-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                            <label class="custom-control-label font-weight-bold text-primary" for="selectAll">
                                Chọn tất cả
                            </label>
                        </div>
                    </div>

                    {{-- Danh sách đơn --}}
                    <div class="list-group shadow-sm">
                        @foreach ($orders as $order)
                            @foreach ($order->items as $item)
                                @php
                                    $product = $item->product;
                                    $image = $product->images->first();
                                @endphp

                                <div
                                    class="list-group-item d-flex flex-wrap justify-content-between align-items-center border-1 border-bottom py-4">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $image ? asset('storage/' . $image->url) : asset('images/default.jpg') }}"
                                            alt="{{ $product->name }}" class="img-thumbnail mr-4"
                                            style="width: 140px; height: 140px; object-fit: cover;">

                                        <div>
                                            <h5 class="font-weight-bold mb-2">{{ $product->name }}</h5>
                                            <p class="mb-1">Giá:
                                                <span class="text-danger font-weight-bold">
                                                    {{ number_format($product->price, 0, ',', '.') }} VND
                                                </span>
                                            </p>
                                            <p class="mb-1">Số lượng: <strong>{{ $item->quantity }}</strong></p>
                                            <p class="mb-1 text-muted">
                                                Ngày đặt:
                                                {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}
                                            </p>
                                            <span
                                                class="badge badge-warning text-uppercase">{{ ucfirst($order->status) }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-end mt-3 mt-md-0">

                                        {{-- Nút Hủy --}}
                                        <button type="button"
                                            class="btn btn-outline-danger d-flex align-items-center justify-content-center px-3 py-2 font-weight-bold rounded mr-3"
                                            data-toggle="modal" data-target="#cancelModal{{ $order->id }}"
                                            style="border-width: 2px; min-width: 90px; transition: all 0.2s ease-in-out;">
                                            <i class="bi bi-trash-fill mr-1"></i> Hủy
                                        </button>

                                        {{-- Checkbox đẹp hơn --}}
                                        <input class="form-check-input" type="checkbox" id="item{{ $item->id }}"
                                            name="item_ids[]" value="{{ $item->id }}"
                                            data-price="{{ $product->price * $item->quantity }}">
                                        <label class="form-check-label" for="item{{ $item->id }}"></label>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    {{-- Tổng cộng + Nút thanh toán --}}
                    <div class="text-right mt-4" id="selectedSummary"></div>

                    <div class="text-center mt-4">
                        <a href="{{ route('home') }}" class="btn btn-outline-info btn-sm mr-2">
                            <i class="fa fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-outline-warning btn-sm mr-2">
                            <i class="fa fa-credit-card"></i> Thanh toán
                        </button>
                    </div>
                </form>

                {{-- Modal xác nhận hủy --}}
                @foreach ($orders as $order)
                    <div class="modal fade" id="cancelModal{{ $order->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="cancelModalLabel{{ $order->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content rounded">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Xác nhận.</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Bạn có chắc chắn muốn hủy đơn hàng này không?
                                </div>
                                <div class="modal-footer">
                                    <form action="#" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Xác nhận</button>
                                    </form>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-center mt-5">Bạn chưa có hóa đơn nào đang chờ thanh toán.</p>
            @endif
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const itemCheckboxes = document.querySelectorAll('input[name="item_ids[]"]');
            const summaryDiv = document.getElementById('selectedSummary');

            function formatCurrency(amount) {
                return amount.toLocaleString('vi-VN') + ' VND';
            }

            function updateTotal() {
                let total = 0;
                itemCheckboxes.forEach(cb => {
                    if (cb.checked) {
                        total += parseFloat(cb.getAttribute('data-price')) || 0;
                    }
                });

                if (total > 0) {
                    summaryDiv.innerHTML = `
                <div class="alert d-inline-block py-2 rounded shadow-sm">
                    Tổng thanh toán: <strong> ${formatCurrency(total)}</strong>
                </div>
            `;
                } else {
                    summaryDiv.innerHTML = '';
                }
            }

            // chọn tất cả
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    itemCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAll.checked;
                    });
                    updateTotal();
                });
            }

            // mỗi checkbox order
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (!this.checked) {
                        if (selectAll) selectAll.checked = false;
                    } else if (document.querySelectorAll('input[name="item_ids[]"]:checked')
                        .length === itemCheckboxes.length) {
                        if (selectAll) selectAll.checked = true;
                    }
                    updateTotal();
                });
            });
        });
    </script>
@endsection
