@extends('dashboard')

@section('content')

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

    <style>
        .register-box {
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .register-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #007bff;
            font-weight: bold;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group-custom input {
            width: 100%;
            padding-right: 40px;
            padding-left: 15px;
            /* <-- Thêm dòng này */
            /* Chừa chỗ cho icon */
            height: 45px;
        }

        .toggle-eye {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 22px;
        }

        .btn-row {
            display: flex;
            justify-content: space-between;
        }

        .btn-register,
        .btn-back {
            width: 48%;
            padding: 10px;
            border-radius: 6px;
            font-weight: bold;
        }

        .btn-register {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            /* <-- Thêm dòng này */
        }

        .btn-back {
            background-color: #6c757d;
            color: #fff;
            border: none;
            text-align: center;
            /* <-- Thêm dòng này để căn giữa */
            text-decoration: none;
            /* <-- Thêm dòng này để bỏ gạch chân */
        }

        /* Thêm đoạn này để khi rê chuột vào, chữ vẫn màu trắng và không gạch chân */
        .btn-back:hover {
            color: #fff;
            text-decoration: none;
        }

        .link-login {
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }

        .alert-error {
            background: #ffe5e5;
            border-left: 4px solid #ff4d4d;
            padding: 10px 15px;
            border-radius: 6px;
            color: #b30000;
            margin-bottom: 15px;
        }
    </style>

    <div class="register-box" style="margin-top:150px;">
        <h2>Đăng Ký</h2>

        {{-- Success --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert-error">
                <ul style="margin:0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Email --}}
            <div class="input-group-custom">
                <input type="email" name="email" placeholder="Nhập Email" required>
            </div>

            {{-- Password --}}
            <div class="input-group-custom">
                <input type="password" id="password" name="password" placeholder="Nhập Mật Khẩu" required>
                <span class="toggle-eye" id="eye-password" onclick="togglePassword('password', 'eye-password')">🙈</span>
            </div>

            {{-- Confirm Password --}}
            <div class="input-group-custom">
                <input type="password" id="password_confirmation" name="password_confirmation"
                    placeholder="Nhập Lại Mật Khẩu" required>
                <span class="toggle-eye" id="eye-password_confirmation"
                    onclick="togglePassword('password_confirmation', 'eye-password_confirmation')">🙈</span>
            </div>

            {{-- Phone --}}
            <div class="input-group-custom">
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
        function togglePassword(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);

            if (input.type === "password") {
                input.type = "text";
                eye.textContent = "👀"; // mắt mở
            } else {
                input.type = "password";
                eye.textContent = "🙈"; // mắt nhắm
            }
        }
    </script>

@endsection