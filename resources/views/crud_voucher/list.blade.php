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
    <section id="vouchers" class="py-5" style="background-color: #f8f9fa; min-height: 100vh;">
        <div class="container" data-aos="fade-up">
            <h2 class="text-center font-weight-bold mb-5" style="padding-top: 120px;">Danh sách Voucher</h2>

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

            {{-- Nút thêm mới --}}
            <div class="d-flex justify-content-between align-items-center mb-4 px-3">
                <a href="{{ route('voucher.store') }}" class="btn btn-custom rounded-pill px-4 py-2 shadow-sm">
                    <i class="fa fa-plus mr-2"></i> Thêm Voucher
                </a>
            </div>

            {{-- Danh sách voucher --}}
            <div class="container">
                <div class="row justify-content-center">
                    @forelse ($voucher as $value)
                        <div class="col-md-6 col-lg-4 mb-4 d-flex align-items-stretch">
                            <div class="card border-danger shadow-sm w-100">
                                <div class="card-header bg-danger text-white text-center font-weight-bold">
                                    {{ $value->code }}
                                </div>
                                <div class="card-body bg-light text-dark text-center">
                                    <p class="card-text mb-2">
                                        <strong>Giảm:</strong>
                                        @if ($value->type === 'percent')
                                            {{ rtrim(rtrim($value->discount, '0'), '.') }}%
                                        @else
                                            {{ number_format($value->discount, 0, ',', ',') }}₫
                                        @endif
                                    </p>
                                    <p class="card-text mb-1">
                                        <strong>Bắt đầu:</strong>
                                        {{ \Carbon\Carbon::parse($value->start_date)->format('d/m/Y') }}
                                    </p>
                                    <p class="card-text mb-3">
                                        <strong>Kết thúc:</strong>
                                        {{ \Carbon\Carbon::parse($value->end_date)->format('d/m/Y') }}
                                    </p>

                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $isExpired = $now->gt($value->end_date);
                                    @endphp

                                    <span class="badge badge-pill {{ $isExpired ? 'badge-danger' : 'badge-success' }}">
                                        {{ $isExpired ? 'Hết hạn' : 'Còn hiệu lực' }}
                                    </span>
                                </div>

                                <div class="card-footer bg-white text-center">
                                    <a href="{{ route('voucher.detail', ['id' => $value->encode_id]) }}"
                                        class="btn btn-outline-info btn-sm mr-2">
                                        <i class="fa fa-eye"></i> Xem
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center">
                            <div class="alert alert-info">Hiện chưa có voucher nào!</div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Phân trang --}}
            <div class="d-flex justify-content-center mt-4">
                <div class="pagination-wrapper">
                    {{ $voucher->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </section>
@endsection
