@extends('loc.app')
@section('title', 'Cập Nhật Thông Tin')

@section('content')
    {{-- Thêm thư viện SweetAlert2 nếu chưa có trong layout chính --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="account-box mx-auto" style="max-width:500px;">
        <h5 class="text-center mb-4 fw-bold">Cập Nhật Thông Tin</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf

            {{-- 🟢 [QUAN TRỌNG] Input ẩn chứa thời gian cập nhật hiện tại --}}
            {{-- Giúp Server so sánh xem dữ liệu có bị cũ so với Database không --}}
            {{-- Gửi con số timestamp (VD: 167899200) thay vì chuỗi ngày tháng --}}
            <input type="hidden" name="last_updated_at" value="{{ $user->updated_at ? $user->updated_at->timestamp : 0 }}">
            <div class="text-center mb-3">
                <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://via.placeholder.com/100' }}"
                    class="rounded-circle mb-2" width="100" height="100" alt="Avatar">
                <div>
                    <input type="file" name="avatar" class="form-control mt-2" accept="image/*">
                </div>
            </div>

            <div class="mb-3">
                <label>Tên người dùng</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label>Họ người dùng</label>
                <input type="text" name="fullname" value="{{ old('fullname', $user->fullname) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label>Số điện thoại</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label>Địa chỉ</label>
                <textarea name="address" class="form-control" rows="3">{{ old('address', $user->address) }}</textarea>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-success px-4">Lưu</button>
                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary px-4">Thoát</a>
            </div>
        </form>
    </div>

    {{-- 🟢 [SCRIPT XỬ LÝ LỖI DỮ LIỆU CŨ] --}}
    {{-- Nếu Service trả về session 'reload_page', đoạn này sẽ chạy --}}
    @if(session('error') && session('reload_page'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'warning',
                    title: 'Dữ liệu không đồng bộ!',
                    text: "{{ session('error') }}", // "Thông tin đã được cập nhật ở tab khác..."
                    confirmButtonText: 'Tải lại dữ liệu mới',
                    allowOutsideClick: false, // Bắt buộc người dùng phải bấm nút
                    confirmButtonColor: '#3085d6',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tải lại trang để lấy tên/avatar mới nhất từ Database
                        window.location.reload();
                    }
                });
            });
        </script>
    @endif

@endsection