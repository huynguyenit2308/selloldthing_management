<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng Ký</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .input-group {
            position: relative;
            margin: 8px 0;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="tel"] {
            width: 100%;
            padding: 12px;
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

        .link-login {
            font-size: 14px;
            margin: 10px 0;
        }

        .link-login a {
            color: #007bff;
            text-decoration: none;
        }

        .link-login a:hover {
            text-decoration: underline;
        }

        .btn-row {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        button {
            padding: 10px 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            background: #fff;
            width: 48%;
        }

        .btn-register {
            background: #ee4d2d;
            color: #fff;
            border: none;
        }

        .btn-back {
            background: #999;
            color: #fff;
            border: none;
        }

        .btn-back {
            display: inline-block;
            text-align: center;
            padding: 10px 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            background: #fff;
            color: #333;
            width: 40%;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: #f2f2f2;
        }

        .alert {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>Đăng Ký</h2>

        <!-- Hiển thị thông báo -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="input-group">
                <input type="email" name="email" placeholder="Nhập Email" required>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Nhập Mật Khẩu" required>
                <span class="toggle-password" onclick="togglePassword('password')">👁️</span>
            </div>

            <div class="input-group">
                <input type="password" id="password_confirmation" name="password_confirmation"
                    placeholder="Nhập Lại Mật Khẩu" required>
                <span class="toggle-password" onclick="togglePassword('password_confirmation')">👁️</span>
            </div>

            <div class="input-group">
                <input type="tel" name="phone" placeholder="Nhập Số Điện Thoại" required>
            </div>

            <div class="link-login">
                Quay lại nếu đã có tài khoản <a href="{{ route('login') }}">Đăng Nhập</a>
            </div>

            <div class="btn-row">
                <a href="{{ route('login') }}" class="btn-back">Quay Về</a>
                <button type="submit" class="btn-register">Đăng Ký</button>
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
