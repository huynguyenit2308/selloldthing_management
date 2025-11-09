<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body style="background-color: #f8f9fa;">
    <div class="container" style="max-width: 400px; margin-top: 80px;">
        <h4 class="text-center mb-4">Quên Mật Khẩu</h4>

        @if(session('error')) 
            <p style="color:red">{{ session('error') }}</p> 
        @endif
        @if(session('success'))     
            <p style="color:green">{{ session('success') }}</p> 
        @endif

        <form method="POST" action="{{ route('password.sendCode') }}">
            @csrf
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Nhập email của bạn" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">Gửi mã xác nhận</button>
        </form>

        <!-- ✅ Nút quay về đăng nhập -->
        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="text-decoration-none">← Quay về đăng nhập</a>
        </div>
    </div>
</body>
</html>
