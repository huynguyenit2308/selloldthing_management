@extends('loc.app')
@section('content')
<div class="account-box mx-auto" style="max-width:500px;">
    <h5 class="text-center mb-4">Thông Tin Cá Nhân</h5>

    <div class="text-center mb-3">
        <img src="{{ $user->avatar ? (Str::startsWith($user->avatar, ['http']) ? $user->avatar : asset('storage/'.$user->avatar)) : asset('images/avatar-placeholder.png') }}"
             class="rounded-circle mb-2" width="100" height="100" alt="Avatar">
    </div>

    <div class="mb-3">
        <label>Tên người dùng</label>
        <input type="text" value="{{ $user->username ?? ($user->name ?? 'Chưa cập nhật') }}" class="form-control" readonly>
    </div>
    <div class="mb-3">
        <label>Họ tên</label>
        <input type="text" value="{{ $user->fullname ?? ($user->name ?? 'Chưa cập nhật') }}" class="form-control" readonly>
    </div>
    <div class="mb-3">
        <label>Số điện thoại</label>
        <input type="text" value="{{ $user->phone ?? 'Chưa cập nhật' }}" class="form-control" readonly>
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="text" value="{{ $user->email ?? 'Chưa cập nhật' }}" class="form-control" readonly>
    </div>
    <div class="mb-3">
        <label>Địa chỉ</label>
        <textarea class="form-control" rows="3" readonly>{{ $user->address ?? 'Chưa cập nhật' }}</textarea>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('profile.edit') }}" class="btn btn-gradient">Cập Nhật</a>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">Thoát</a>
    </div>
</div>
@endsection