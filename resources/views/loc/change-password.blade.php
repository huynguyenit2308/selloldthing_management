@extends('loc.app')
@section('title', 'Đổi Mật Khẩu')

@section('content')
<div class="account-box mx-auto" style="max-width:400px;">
    <h5 class="text-center mb-4 fw-bold">Đổi Mật Khẩu</h5>

    {{-- ⚠️ Thông báo lỗi --}}
    @if(session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    {{-- ⚠️ Lỗi xác thực --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('account.updatePassword') }}">
        @csrf
        {{-- Mật khẩu cũ --}}
        <div class="mb-3 position-relative">
            <label>Mật Khẩu Cũ</label>
            <input type="password" name="current_password" id="current_password" class="form-control" required>
            <i class="fa fa-eye toggle-password" data-target="current_password"></i>
        </div>

        {{-- Mật khẩu mới --}}
        <div class="mb-3 position-relative">
            <label>Mật Khẩu Mới</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required>
            <i class="fa fa-eye toggle-password" data-target="new_password"></i>
        </div>

        {{-- Xác nhận mật khẩu mới --}}
        <div class="mb-3 position-relative">
            <label>Nhập Lại Mật Khẩu Mới</label>
            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
            <i class="fa fa-eye toggle-password" data-target="new_password_confirmation"></i>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-gradient w-50">Xác Nhận</button>
            <a href="{{ route('account.info') }}" class="btn btn-outline-secondary w-45">Thoát</a>
        </div>
    </form>
</div>

{{-- ✅ Modal hiển thị sau khi đổi mật khẩu thành công --}}
@if(session('password_changed'))
<div class="modal fade show" id="passwordSuccessModal" tabindex="-1" aria-modal="true" style="display:block; background-color:rgba(0,0,0,0.4);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4 animate__animated animate__fadeIn">
        <div class="text-success mb-2" style="font-size:40px;">
            <i class="fa fa-check-circle"></i>
        </div>
        <h5 class="mb-3 text-success fw-bold">Thay đổi mật khẩu thành công!</h5>
        <p>Bạn sẽ được đăng xuất và quay về trang chủ.</p>
        <form method="POST" action="{{ route('account.confirmLogout') }}">
            @csrf
            <button type="submit" class="btn btn-success mt-2 px-4">Xác Nhận</button>
        </form>
    </div>
  </div>
</div>
<script>
  document.body.classList.add('modal-open');
</script>
@endif

{{-- 👁️ Script con mắt xem mật khẩu --}}
<script>
document.querySelectorAll('.toggle-password').forEach(icon => {
    icon.style.position = 'absolute';
    icon.style.right = '10px';
    icon.style.top = '38px';
    icon.style.cursor = 'pointer';
    icon.style.color = '#888';
    icon.addEventListener('click', function() {
        const targetInput = document.getElementById(this.dataset.target);
        const type = targetInput.getAttribute('type') === 'password' ? 'text' : 'password';
        targetInput.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
});
</script>

{{-- Font Awesome & Animation (nếu chưa có) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
@endsection
