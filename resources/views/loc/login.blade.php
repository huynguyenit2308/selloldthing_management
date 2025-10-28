<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 350px;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 16px;
            color: #666;
        }

        .forgot-password {
            display: inline-block;
            font-size: 13px;
            color: #007bff;
            text-decoration: none;
            margin-bottom: 12px;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        button {
            padding: 10px 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            background: #fff;
        }

        .btn-login {
            background: #ee4d2d;
            color: #fff;
            border: none;
        }

        .btn-register {
            background: #6c757d;
            color: #fff;
            border: none;
        }

        .or {
            text-align: center;
            margin: 10px 0;
            font-size: 13px;
            color: #999;
        }

        .social-login {
            display: flex;
            justify-content: space-between;
        }

        .btn-google {
            background: #db4437;
            color: #fff;
            width: 48%;
            border: none;
        }

        .btn-facebook {
            background: #4267B2;
            color: #fff;
            width: 48%;
            border: none;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Đăng Nhập</h2>

        {{-- ✅ Thông báo thành công --}}
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        {{-- ✅ Thông báo lỗi chung --}}
        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        {{-- ✅ Thông báo lỗi validate --}}
        @if ($errors->any())
            <div class="alert-error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <input type="text" name="email" placeholder="Email, Số Điện Thoại" value="{{ old('email') }}" required>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Mật khẩu" required>
                <span class="toggle-password" onclick="togglePassword('password')">👁️</span>
            </div>

            <a href="{{ route('password.forgot') }}" class="forgot-password">Quên mật khẩu</a>

            <div class="btn-row">
                <button type="button" class="btn-register" onclick="window.location.href='{{ route('register') }}'">Đăng
                    Ký</button>
                <button type="submit" class="btn-login">Đăng Nhập</button>
            </div>

            <div class="or">Hoặc</div>

            <div class="social-login">
                <button type="button" class="btn-google"
                    onclick="window.location.href='{{ url('auth/google') }}'">Google</button>
                 <button type="button" class="btn-facebook"
                    onclick="window.location.href='{{ url('auth/facebook') }}'">Facebook</button>
            </div>

        </form>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }
    </script>
</body>

</html>