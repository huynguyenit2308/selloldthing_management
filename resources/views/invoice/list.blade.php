@extends('dashboard')

@section('content')
    <style>
        .btn-custom {
            background-color: #ff4b4b;
            border: none;
            color: #fff !important;
            transition: all 0.3s ease-in-out;
        }

        .btn-custom:hover {
            background-color: #e43f3f;
            color: #fff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(255, 75, 75, 0.3);
        }

        .btn-custom:active {
            transform: translateY(0);
            box-shadow: none;
        }
    </style>
    <section id="invoices" class="py-5" style="background-color: #f8f9fa; min-height: 100vh;">
        <div class="container" data-aos="fade-up">
            <h2 class="text-center font-weight-bold mb-5" style="padding-top: 120px;">Danh sách hóa đơn</h2>

            {{-- Thông báo --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Danh sách hóa đơn --}}
            <div class="container">
                <div class="row justify-content-center">
                    @forelse ($payments as $payment)
                        <div class="col-md-6 col-lg-4 mb-4 d-flex align-items-stretch">
                            <div class="card border-danger shadow-sm w-100">
                                <div class="card-header bg-danger text-white text-center font-weight-bold">
                                    Hóa đơn {{ $payment->id }}
                                </div>
                                <div class="card-body bg-light text-dark text-center">
                                    <p class="card-text mb-2">
                                        <strong>Khách hàng:</strong> {{ $payment->user->name ?? 'Không có' }}
                                    </p>
                                    <p class="card-text mb-2">
                                        <strong>Số tiền:</strong> {{ number_format($payment->amount, 0, ',', '.') }}₫
                                    </p>
                                    <p class="card-text mb-2">
                                        <strong>Phương thức:</strong> {{ ucfirst($payment->payment_method) }}
                                    </p>
                                    <p class="card-text mb-3">
                                        <strong>Ngày:</strong>
                                        {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}
                                    </p>

                                    @php
                                        $status = $payment->payment_status;
                                    @endphp

                                    <span
                                        class="badge badge-pill 
                            {{ $status === 'completed' ? 'badge-success' : ($status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                        {{ $status === 'completed' ? 'Hoàn tất' : ($status === 'pending' ? 'Đang chờ' : 'Thất bại') }}
                                    </span>
                                </div>

                                <div class="card-footer bg-white text-center">
                                    <a href="#" class="btn btn-outline-info btn-sm mr-2">
                                        <i class="fa fa-eye"></i> Xem
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center">
                            <div class="alert alert-info">Hiện chưa có hóa đơn nào!</div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Phân trang --}}
            <div class="d-flex justify-content-center mt-4">
                <div class="pagination-wrapper">
                    {{ $payments->links('pagination::bootstrap-4') }}
                </div>
            </div>
    </section>
@endsection
