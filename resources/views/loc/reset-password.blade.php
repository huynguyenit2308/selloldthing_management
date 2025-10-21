<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body style="background-color: #f8f9fa;">
    <div class="container" style="max-width: 400px; margin-top: 80px;">
        <h4 class="text-center mb-4">Nhập mật khẩu mới</h4>

        @if(session('error')) 
            <p style="color:red">{{ session('error') }}</p> 
        @endif

        <form method="POST" action="{{ route('password.reset') }}">
            @csrf

            <div class="mb-3">
                <label>Mật khẩu mới</label>
                <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu mới" required>
            </div>

            <div class="mb-3">
                <label>Nhập lại mật khẩu</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Xác nhận mật khẩu" required>
            </div>

            <button type="submit" class="btn btn-success w-100 mt-2">Xác nhận</button>
        </form>
    </div>
</body>
</html>
