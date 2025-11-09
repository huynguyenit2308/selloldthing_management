<!DOCTYPE html>
<html lang="en">

<head>
    <title>Colo Shop</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Colo Shop Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="{{ asset('styles/bootstrap4/bootstrap.min.css') }}">
    <link href="{{ asset('plugins/font-awesome-4.7.0/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/OwlCarousel2-2.2.1/owl.carousel.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/OwlCarousel2-2.2.1/owl.theme.default.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/OwlCarousel2-2.2.1/animate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('styles/main_styles.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('styles/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="@yield('body-class')">
	@auth
        @php
            $user = Auth::user();
            $missingInfo = empty($user->fullname) || empty($user->phone) || empty($user->address);
        @endphp

        @if ($missingInfo)
            <div id="update-info-popup" style="
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0, 0, 0, 0.5); display: flex; align-items: center;
                    justify-content: center; z-index: 9999; transition: opacity 0.4s ease;">

                <div style="
                        background: #fff; padding: 25px 30px; border-radius: 12px; 
                        text-align: center; box-shadow: 0 0 20px rgba(0,0,0,0.3); 
                        width: 350px; position: relative;">

                    <!-- nút đóng -->
                    <button id="popup-close-btn" style="
                            position: absolute; top: 8px; right: 10px; border: none; 
                            background: transparent; font-size: 20px; color: #999; cursor: pointer;">
                        &times;
                    </button>

                    <h5 style="color:#333; margin-top: 10px;">⚠️ Vui lòng cập nhật thông tin!</h5>
                    <p style="color: #777; font-size: 15px;">Để hoàn tất hồ sơ của bạn, hãy cập nhật thông tin cá nhân.</p>
                    <a href="{{ route('profile.edit') }}" id="btn-update" class="btn btn-warning mt-3 px-4">Cập nhật ngay</a>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const popup = document.getElementById('update-info-popup');
                    const btnUpdate = document.getElementById('btn-update');
                    const btnClose = document.getElementById('popup-close-btn');

                    function hidePopup() {
                        popup.style.opacity = "0";
                        setTimeout(() => popup.style.display = "none", 400);
                    }

                    btnUpdate.addEventListener('click', hidePopup);
                    btnClose.addEventListener('click', hidePopup);
                });
            </script>
        @endif
    @endauth
    <div class="super_container">

        <!-- Header -->

        <header class="header trans_300">

            <!-- Top Navigation -->

            <div class="top_nav">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="top_nav_left">Chuyên đề phát triển web 1 - 2</div>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="top_nav_right">
                                <ul class="top_nav_menu">
                                    <li class="account">
                                        <a href="#">
                                            @auth
                                                {{ Auth::user()->name }}
                                            @else
                                                Tài khoản của tôi
                                            @endauth
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="account_selection">
                                            @guest
                                                <li>
                                                    <a href="{{ route('login') }}">
                                                        <i class="fa fa-sign-in" aria-hidden="true"></i> Đăng nhập
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('register') }}">
                                                        <i class="fa fa-user-plus" aria-hidden="true"></i> Đăng ký
                                                    </a>
                                                </li>
                                            @else
                                                <li>
                                                    <a class="dropdown-item" href="{{ route(name: 'account.info') }}">
                                                        <i class="fa fa-id-card" aria-hidden="true"></i> Thông Tin Tài Khoản
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route(name: 'account.inventory') }}">
                                                        <i class="fa fa-dashboard" aria-hidden="true"></i> Quản lý số lượng tồn kho
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route(name: 'profile.show') }}">
                                                        <i class="fa fa-user" aria-hidden="true"></i> Thông Tin Cá Nhân
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fa fa-history" aria-hidden="true"></i> Xem Lịch Sử Mua
                                                        Hàng
                                                    </a>
                                                </li>
                                                   <li>
                                                    <a class="dropdown-item" href="{{ route('products.manage') }}">
                                                        <i class="fa fa-archive" aria-hidden="true"></i> Sản phẩm của tôi
                                                    </a>
                                                </li>

                                                <li>
                                                    <div class="dropdown-divider"></div> <!-- ngăn cách -->
                                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                        <i class="fa fa-sign-out" aria-hidden="true"></i> Đăng xuất
                                                    </a>
                                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                                        style="display: none;">
                                                        @csrf
                                                    </form>
                                                </li>
                                            @endguest
                                        </ul>

                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Navigation -->

            <div class="main_nav_container">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <div class="logo_container">
                                <a href="{{ route('home') }}">Cửa hàng<span> đồ cũ</span></a>
                            </div>
                            <nav class="navbar">
                                <ul class="navbar_menu">
                                    <li><a href="{{ route('home') }}">Trang chủ</a></li>
                                    <li><a href="{{ route('categories.index') }}">Danh mục</a></li>
                                    <li><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                                     @auth
                                    @if (Auth::user()->role === 'admin')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Admin
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="adminDropdown">
                                        <a class="dropdown-item" href="{{ route('admin.products.index') }}">Quản lý sản phẩm</a>
                                        <a class="dropdown-item" href="{{ route('admin.categories.index') }}">Quản lý danh mục</a>
                                        <a class="dropdown-item" href="{{ route('voucher.list') }}">Quản lý voucher</a>
                                        <a class="dropdown-item" href="{{ route('invoice.list') }}">Quản lý hóa đơn</a>
                                    </div>
                                </li>
                                    @endif
                                @endauth
                                </ul>
                                <ul class="navbar_user">
                                    <li><a href="#"><i class="fa fa-search" aria-hidden="true"></i></a></li>
                                    <li class="notification">
                                        <a href="#">
                                            <i class="fa fa-bell" aria-hidden="true"></i>
                                            <span class="notification_count">3</span>
                                        </a>
                                    </li>
                                    <li class="checkout">
                                        <a href="{{ route('orders.list') }}">
                                            <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                            <span id="checkout_items"
                                                class="checkout_items">{{ session('cart_count', 0) }}</span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="hamburger_container">
                                    <i class="fa fa-bars" aria-hidden="true"></i>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main -->

        @yield('content')

        <!-- Footer -->

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div
                            class="footer_nav_container d-flex flex-sm-row flex-column align-items-center justify-content-lg-start justify-content-center text-center">
                            <ul class="footer_nav">
                                <li><a href="{{ route('categories.index') }}">Danh mục</a></li>
                                <li><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div
                            class="footer_social d-flex flex-row align-items-center justify-content-lg-end justify-content-center">
                            <ul>
                                <li><a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                                <li><a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                                <li><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                                <li><a href="#"><i class="fa fa-skype" aria-hidden="true"></i></a></li>
                                <li><a href="#"><i class="fa fa-pinterest" aria-hidden="true"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer_nav_container">
                            <div class="cr">Được phát triển bởi <a href="https://themewagon.com">Nhóm I</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <div id="chatbot-box">
            <div id="chatbot-header">💬 Hỗ trợ tự động</div>
            <div id="chatbot-messages"></div>
            <div id="chatbot-input">
                <input type="text" id="chatbot-text" placeholder="Nhập tin nhắn..." />
                <button id="chatbot-send">Gửi</button>
            </div>
        </div>
        <button id="chatbot-toggle">💬</button>

    </div>

    <script>
        const toggleBtn = document.getElementById('chatbot-toggle');
        const chatBox = document.getElementById('chatbot-box');
        const sendBtn = document.getElementById('chatbot-send');
        const input = document.getElementById('chatbot-text');
        const messages = document.getElementById('chatbot-messages');

        toggleBtn.onclick = () => {
            chatBox.style.display = chatBox.style.display === 'flex' ? 'none' : 'flex';
            chatBox.style.flexDirection = 'column';
        };

        async function sendMessage() {
            const text = input.value.trim();
            if (!text) return;
            messages.innerHTML += `<div class="chat-message user">${text}</div>`;
            input.value = '';

            const response = await fetch('{{ route('chat.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    message: text
                })
            });

            const data = await response.json();
            messages.innerHTML += `<div class="chat-message bot">${data.reply.replace(/\n/g, '<br>')}</div>`;
            messages.scrollTop = messages.scrollHeight;
        }

        sendBtn.onclick = sendMessage;
        input.addEventListener('keypress', e => {
            if (e.key === 'Enter') sendMessage();
        });
    </script>
    <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('styles/bootstrap4/popper.js') }}"></script>
    <script src="{{ asset('styles/bootstrap4/bootstrap.min.js') }}"></script>
    <script src="{{ asset('plugins/Isotope/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('plugins/OwlCarousel2-2.2.1/owl.carousel.js') }}"></script>
    <script src="{{ asset('plugins/easing/easing.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @stack('scripts')
</body>

</html>
