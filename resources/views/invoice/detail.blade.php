@extends('dashboard')

@section('content')
    @php
        use App\Helpers\IdEncoder;
        $encodeId = IdEncoder::encodeId($payment->id);
    @endphp

    <section id="invoice-detail" class="py-5" style="background-color: #f8f9fa;">
        <div class="container" data-aos="fade-up">
            <h2 class="text-center font-weight-bold mb-5" style="padding-top: 120px;">
                Chi tiết hóa đơn
            </h2>

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

            <div class="card shadow-sm border-0 mx-auto" style="max-width: 700px;">
                <div class="card-header bg-danger text-white text-center font-weight-bold ">
                    Hóa đơn {{ $payment->id }}
                </div>

                <div class="card-body bg-light border border-danger text-center">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Khách hàng:</strong><br>
                            {{ $payment->user->name ?? 'Không có' }}
                        </div>
                    </div>

                    @if ($payment->voucher)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Voucher:</strong><br>
                                {{ $payment->voucher->code }}
                            </div>
                            <div class="col-md-6">
                                <strong>Giảm:</strong><br>
                                @if ($payment->voucher->type === 'percent')
                                    {{ rtrim(rtrim($payment->voucher->discount, '0'), '.') }}%
                                @else
                                    {{ number_format($payment->voucher->discount, 0, ',', ',') }}₫
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Số tiền:</strong><br>
                            {{ number_format($payment->amount, 0, ',', ',') }}₫
                        </div>
                        <div class="col-md-6">
                            <strong>Phương thức thanh toán:</strong><br>
                            {{ $payment->payment_method }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Trạng thái:</strong><br>
                            <span
                                class="badge badge-pill {{ $payment->payment_status === 'completed' ? 'badge-success' : 'badge-danger' }}">
                                {{ $payment->payment_status === 'completed' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Ngày thanh toán:</strong><br>
                            {{ $payment->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('invoice.list') }}" class="btn btn-outline-info btn-sm mr-2">
                            <i class="fa fa-arrow-left"></i> Quay lại
                        </a>
                        <a href="{{ route('invoice.pdf', $encodeId) }}" class="btn btn-outline-danger btn-sm">
                            <i class="fa fa-file-pdf-o"></i> In PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
