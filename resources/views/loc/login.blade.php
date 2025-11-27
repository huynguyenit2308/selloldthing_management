@extends('dashboard')

@section('content')

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    {{-- Thêm thư viện SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group-custom input {
            width: 100%;
            padding-right: 40px;
            /* Chừa chỗ cho icon */
            height: 45px;
            /* chiều cao input chuẩn */
        }

        .toggle-eye {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            font-size: 22px;
            cursor: pointer;
            z-index: 2;
        }

        .card {
            border-radius: 15px;
        }

        .btn-google {
            background: #ffffff;
            border: 1px solid #ccc;
            color: #444;
            font-weight: 600;
        }

        .btn-facebook {
            background: #1877f2;
            color: #fff;
            font-weight: 600;
        }

        .input-group-custom input {
            height: 45px;
            padding-right: 40px;
            /* Chừa chỗ cho icon */
        }

        .toggle-eye {
            z-index: 2;
        }
    </style>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh; margin-top: 100px;">
        <div class="card shadow-lg p-4" style="width: 420px;">

            <h3 class="text-center mb-4 font-weight-bold">Đăng Nhập</h3>

            {{-- Vẫn giữ thông báo tĩnh này để hiển thị các lỗi validate form thông thường --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="form-group">
                    <label>Email </label>
                    <input type="text" name="email" class="form-control" placeholder="Nhập email"
                        value="{{ old('email') }}" required>
                </div>

                {{-- Password --}}
                <div class="form-group"> 
                    <label>Mật khẩu</label>
                    <div class="input-group-custom"> 
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Nhập mật khẩu" required>
                        <span class="toggle-eye" id="eye-password"
                            onclick="togglePassword('password', 'eye-password')">🙈</span>
                    </div>
                </div>

                <div class="mb-3 text-right">
                    <a href="{{ route('password.forgot') }}">Quên mật khẩu?</a>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <a class="btn btn-outline-primary w-50 mr-2" href="{{ route('register') }}">Đăng Ký</a>
                    <button type="submit" class="btn btn-primary w-50 ml-2">Đăng Nhập</button>
                </div>

                <div class="text-center my-3">
                    <span class="text-muted">Hoặc</span>
                </div>

                {{-- Nút Google + Facebook --}}
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-google w-50 mr-2"
                        onclick="window.location.href='{{ url('auth/google') }}'">
                        <i class="fab fa-google mr-2" style="color:#DB4437;"></i> Google
                    </button>

                    <button type="button" class="btn btn-facebook w-50 ml-2"
                        onclick="window.location.href='{{ url('auth/facebook') }}'">
                        <i class="fab fa-facebook-f mr-2"></i> Facebook
                    </button>
                </div>
            </form>
        </div>
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

    {{-- ========================================== --}}
    {{-- PHẦN XỬ LÝ POPUP THÔNG BÁO (SWEETALERT2) --}}
    {{-- ========================================== --}}
    
    {{-- 1. Xử lý thông báo LỖI (Ví dụ: Tab 1 xóa nick, Tab 2 bị đá về đây) --}}
    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Chú ý!',
            text: "{{ session('error') }}",
            confirmButtonColor: '#d33',
            confirmButtonText: 'Đã hiểu'
        });
    </script>
    @endif

    {{-- 2. Xử lý thông báo THÀNH CÔNG (Ví dụ: Đăng ký thành công, Đổi pass thành công) --}}
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Thành công!',
            text: "{{ session('success') }}",
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Tuyệt vời'
        });
    </script>
    @endif
@endsection