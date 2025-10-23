@extends('loc.app')
@section('title', 'Thông Tin Tài Khoản')

@section('content')
<div class="account-box mx-auto" style="max-width:400px;">
    <h5 class="text-center mb-4">Thông Tin Tài Khoản</h5>

    <form>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" value="{{ $user->email }}" readonly>
        </div>
        <div class="mb-3">
            <label>Số Điện Thoại</label>
            <input type="text" class="form-control" value="{{ $user->phone ?? '' }}" readonly>
        </div>
        <div class="mb-3">
            <label>Mật Khẩu</label>
            <input type="password" class="form-control" value="********" readonly>
        </div>
    </form>

    <div class="d-flex justify-content-between mt-4">
        <button class="btn btn-danger-gradient" data-bs-toggle="modal" data-bs-target="#confirmDelete">Xóa Tài Khoản</button>
        <a href="{{ route('account.change') }}" class="btn btn-gradient">Đổi Mật Khẩu</a>
    </div>

    <div class="text-center mt-3">
        <a href="{{ url('/') }}" class="btn btn-outline-secondary">Thoát</a>
    </div>
</div>

<!-- Modal xác nhận -->
<div class="modal fade" id="confirmDelete" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
        <h5 class="mb-3">Bạn có muốn xóa tài khoản này không?</h5>
        <form method="POST" action="{{ route('account.delete') }}">
            @csrf
            <button type="submit" class="btn btn-danger-gradient me-2">Xác Nhận</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Thoát</button>
        </form>
    </div>
  </div>
</div>
@endsection
