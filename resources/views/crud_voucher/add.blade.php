@extends('dashboard')

@section('content')
    <section class="py-5" style="background-color: #f8f9fa;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7">
                    <div class="card shadow-lg border-0">
                        <div class="card-header text-center">
                            <h2 class="text-center font-weight-bold" style="padding-top: 120px;">Thêm voucher</h2>
                        </div>

                        <div class="card-body bg-white p-4">
                            <form action="{{ route('voucher.store') }}" method="POST">
                                @csrf

                                {{-- Mã voucher --}}
                                <div class="form-group">
                                    <label for="code" class="font-weight-bold">Mã Voucher</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                        id="code" name="code" placeholder="Nhập mã voucher..."
                                        value="{{ old('code') }}">
                                    @error('code')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Loại --}}
                                <div class="form-group">
                                    <label for="type" class="font-weight-bold">Loại Voucher</label>
                                    <select class="form-control @error('type') is-invalid @enderror" id="type"
                                        name="type">
                                        <option value="">-- Chọn loại voucher --</option>
                                        <option value="percent" {{ old('type') == 'percent' ? 'selected' : '' }}>Phần trăm
                                            (%)</option>
                                        <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Giá cố định
                                            (₫)</option>
                                    </select>
                                    @error('type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Giá trị giảm --}}
                                <div class="form-group">
                                    <label for="discount" class="font-weight-bold">Giá trị giảm</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01"
                                            class="form-control @error('discount') is-invalid @enderror" id="discount"
                                            name="discount" placeholder="Nhập giá trị giảm..."
                                            value="{{ old('discount') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text font-weight-bold" id="discount-unit">₫</span>
                                        </div>
                                    </div>
                                    @error('discount')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Ngày bắt đầu --}}
                                <div class="form-group">
                                    <label for="start_date" class="font-weight-bold">Ngày bắt đầu</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                        id="start_date" name="start_date" value="{{ old('start_date') }}">
                                    @error('start_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Ngày kết thúc --}}
                                <div class="form-group">
                                    <label for="end_date" class="font-weight-bold">Ngày kết thúc</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                        id="end_date" name="end_date" value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Nút hành động --}}
                                <div class="text-center mt-4">
                                    <a href="{{ route('voucher.list') }}" class="btn btn-outline-info btn-sm mr-2">
                                        <i class="fa fa-arrow-left"></i> Quay lại
                                    </a>
                                    <button type="submit" class="btn btn-outline-danger btn-sm mr-2">
                                        <i class="fa fa-plus mr-2"></i> Thêm Voucher
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="card-footer text-center bg-light">
                            <small class="text-muted">Hãy đảm bảo thông tin chính xác trước khi lưu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Script thay đổi đơn vị hiển thị (%, ₫) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const unitSpan = document.getElementById('discount-unit');

            function updateUnit() {
                if (typeSelect.value === 'percent') {
                    unitSpan.textContent = '%';
                } else {
                    unitSpan.textContent = '₫';
                }
            }

            typeSelect.addEventListener('change', updateUnit);
            updateUnit(); // khởi tạo ban đầu
        });
    </script>
@endsection
