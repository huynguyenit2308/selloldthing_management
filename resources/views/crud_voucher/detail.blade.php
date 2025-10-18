@extends('dashboard')

@section('content')
    @php
        use App\Helpers\IdEncoder;
        $encodeId = IdEncoder::encodeId($voucher->id);
    @endphp
    <section id="voucher-detail" class="py-5" style="background-color: #f8f9fa;">
        <div class="container" data-aos="fade-up">
            <h2 class="text-center font-weight-bold mb-5" style="padding-top: 120px;">
                Chi tiết Voucher
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
                    {{ $voucher->code }}
                </div>

                <div class="card-body bg-light border border-danger">
                    <div class="row mb-3 text-center">
                        <div class="col-md-6">
                            <strong>Giảm:</strong><br>
                            @if ($voucher->type === 'percent')
                                {{ rtrim(rtrim($voucher->discount, '0'), '.') }}%
                            @else
                                {{ number_format($voucher->discount, 0, ',', ',') }}₫
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Trạng thái:</strong><br>
                            @php
                                $now = \Carbon\Carbon::now();
                                $isExpired = $now->gt($voucher->end_date);
                            @endphp
                            <span class="badge badge-pill {{ $isExpired ? 'badge-danger' : 'badge-success' }}">
                                {{ $isExpired ? 'Hết hạn' : 'Còn hiệu lực' }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3 text-center">
                        <div class="col-md-6">
                            <strong>Ngày bắt đầu:</strong><br>
                            {{ \Carbon\Carbon::parse($voucher->start_date)->format('d/m/Y') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Ngày kết thúc:</strong><br>
                            {{ \Carbon\Carbon::parse($voucher->end_date)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('voucher.list') }}" class="btn btn-outline-info btn-sm mr-2">
                            <i class="fa fa-arrow-left"></i> Quay lại
                        </a>
                        <a href="{{ route('voucher.update', ['id' => $encodeId]) }}"
                            class="btn btn-outline-warning btn-sm mr-2">
                            <i class="fa fa-edit"></i> Sửa
                        </a>

                        <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal"
                            data-target="#confirmDeleteModal">
                            <i class="fa fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmDeleteLabel">Xác nhận xóa</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa voucher <strong>{{ $voucher->code }}</strong>?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('voucher.delete') }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" value="{{ $encodeId }}">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fa fa-trash"></i> Xóa
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fa fa-times"></i> Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
