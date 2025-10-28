<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận mã</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body style="background-color: #f8f9fa;">
    <div class="container" style="max-width: 400px; margin-top: 80px;">
        <h4 class="text-center mb-4">Nhập mã xác nhận</h4>

        @if(session('error')) 
            <p style="color:red">{{ session('error') }}</p> 
        @endif
        @if(session('success')) 
            <p style="color:green">{{ session('success') }}</p> 
        @endif

        <form method="POST" action="{{ route('password.verify') }}">
            @csrf
            <div class="mb-3">
                <input type="text" name="code" class="form-control" placeholder="Nhập mã xác nhận" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Tiếp tục</button>
        </form>
    </div>
</body>
</html>
