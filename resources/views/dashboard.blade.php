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

    <link rel="stylesheet" type="text/css" href="{{ asset('styles/search_overlay.css') }}">

    @stack('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        /* Notification Dropdown Styles */
        .notification {
            position: relative;
        }

        .notification-dropdown-content {
            position: absolute;
            top: 100%;
            right: 0;
            width: 380px;
            max-height: 500px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            z-index: 1000;
            margin-top: 10px;
            overflow: hidden;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .notification-header h4 {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }

        .notification-actions button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .notification-actions button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .notification-body {
            max-height: 380px;
            overflow-y: auto;
        }

        .notification-body::-webkit-scrollbar {
            width: 6px;
        }

        .notification-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .notification-body::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .notification-body::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        .notification-item {
            padding: 14px 20px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .notification-item:hover {
            background: #f9fafb;
        }

        .notification-item.unread {
            background: #eef2ff;
        }

        .notification-item.unread::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: #6366f1;
            border-radius: 50%;
        }

        .notification-item.unread .notification-content {
            padding-left: 15px;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            font-size: 18px;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .notification-message {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.4;
            margin-bottom: 6px;
        }

        .notification-time {
            font-size: 12px;
            color: #9ca3af;
        }

        .notification-loading,
        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #9ca3af;
        }

        .notification-loading i {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .notification-footer {
            padding: 12px 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .notification-footer a {
            color: #6366f1;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .notification-footer a:hover {
            color: #4f46e5;
        }

        #notification-count {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        /* Show dropdown on active */
        .notification.active .notification-dropdown-content {
            display: block !important;
        }
    </style>
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
                                                    <a class="dropdown-item" href="{{ route('watchlist.index') }}">
                                                        <i class="fa fa-heart" aria-hidden="true"></i> Theo dõi danh mục
                                                    </a>
                                                </li>
                                                <!-- của loi -->
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('favorite.hienthi') }}">
                                                        <i class="fa fa-heart" aria-hidden="true"></i> Danh sách yêu thích
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
                        <div class="col-lg-12">
                            <div class="main_nav_content">
                                <div class="logo_container">
                                    <a href="{{ route('home') }}">Cửa hàng<span> đồ cũ</span></a>
                                </div>
                                <form class="nav_search" action="{{ route('search.index') }}" method="GET">
                                    <label for="main-search" class="sr-only">Tìm kiếm sản phẩm</label>
                                    <input id="main-search" type="text" name="q"
                                        value="{{ request('q') }}" placeholder="Tìm kiếm sản phẩm..."
                                        aria-label="Tìm kiếm sản phẩm" data-search-overlay-trigger="true">
                                    <button type="submit" aria-label="Tìm kiếm">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </button>
                                </form>
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
                                    @auth
                                    <li class="notification" id="notification-dropdown">
                                        <a href="#" id="notification-bell">
                                            <i class="fa fa-bell" aria-hidden="true"></i>
                                            <span class="notification_count" id="notification-count">0</span>
                                        </a>
                                        <div class="notification-dropdown-content" id="notification-list" style="display: none;">
                                            <div class="notification-header">
                                                <h4>Thông báo</h4>
                                                <div class="notification-actions">
                                                    <button type="button" id="mark-all-read" title="Đánh dấu tất cả là đã đọc">
                                                        <i class="fa fa-check-double"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="notification-body" id="notification-items">
                                                <div class="notification-loading">
                                                    <i class="fa fa-spinner fa-spin"></i> Đang tải...
                                                </div>
                                            </div>
                                            <div class="notification-footer">
                                                <a href="#" id="load-more-notifications">Xem thêm</a>
                                            </div>
                                        </div>
                                    </li>
                                    @endauth
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
            </div>
        </header>

        <!-- Main -->

        @yield('content')

        <!-- Footer -->
        @php
            $footerCategories = \App\Models\Category::where('status', 1)
                ->orderBy('name')
                ->take(4)
                ->get();
        @endphp

        <footer class="footer" style="background:#fff; border-top:1px solid #eee; padding:40px 0 20px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-4">
                        <h5 style="font-size:16px; font-weight:600; margin-bottom:10px;">Về chúng tôi</h5>
                        <p style="font-size:13px; color:#777; margin:0;">
                            Đồ cũ giá tốt - Nơi mua bán đồ cũ uy tín,
                            chất lượng với giá cả phải chăng.
                        </p>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-4">
                        <h5 style="font-size:16px; font-weight:600; margin-bottom:10px;">Danh mục</h5>
                        <ul style="list-style:none; padding:0; margin:0; font-size:13px; color:#555;">
                            @forelse($footerCategories as $category)
                                <li>
                                    <a href="{{ route('categories.show', $category->id) }}" style="color:inherit; text-decoration:none;">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @empty
                                <li><a href="{{ route('categories.index') }}" style="color:inherit; text-decoration:none;">Tất cả danh mục</a></li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-4">
                        <h5 style="font-size:16px; font-weight:600; margin-bottom:10px;">Hỗ trợ</h5>
                        <ul style="list-style:none; padding:0; margin:0; font-size:13px; color:#555;">
                            <li><a href="#" style="color:inherit; text-decoration:none;">Trung tâm hỗ trợ</a></li>
                        </ul>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-4">
                        <h5 style="font-size:16px; font-weight:600; margin-bottom:10px;">Theo dõi chúng tôi</h5>
                        <ul style="list-style:none; padding:0; margin:0; font-size:13px; color:#555;">
                            <li><a href="#" style="color:inherit; text-decoration:none;">Google</a></li>
                            <li><a href="#" style="color:inherit; text-decoration:none;">Zalo</a></li>
                        </ul>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="footer_nav_container" style="border-top:1px solid #f2f2f2; padding-top:10px;">
                            <div class="cr" style="font-size:13px; color:#999;">Được phát triển bởi <a href="https://themewagon.com">Nhóm I</a></div>
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

        <div id="search-overlay"
            data-bootstrap-url="{{ route('search.bootstrap') }}"
            data-suggest-url="{{ route('search.suggest') }}"
            data-results-url="{{ route('search.results') }}"
            data-history-url="{{ route('search.history.index') }}"
            data-history-clear-url="{{ route('search.history.clear') }}"
            data-favorite-url-template="{{ url('search/products/__ID__/favorite') }}"
            data-cart-url-template="{{ url('search/products/__ID__/cart') }}"
            data-csrf-token="{{ csrf_token() }}">
            <div class="search-overlay__backdrop"></div>
            <div class="search-overlay__panel">
                <button type="button" class="search-overlay__close" aria-label="Đóng tìm kiếm">&times;</button>
                <div class="search-overlay__input-wrapper">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input type="text" class="search-overlay__input" id="search-overlay-input"
                        placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
                    <div class="search-overlay__actions">
                        <button type="button" class="primary" data-action="submit">Tìm kiếm</button>
                        <button type="button" data-action="clear">Xóa</button>
                    </div>
                    <div class="search-overlay__autocomplete" hidden>
                        <ul class="search-overlay__autocomplete-list"></ul>
                    </div>
                </div>
                <div class="search-overlay__error" hidden></div>
                <div class="search-overlay__sections">
                    <div class="search-overlay__section" data-section="suggestions">
                        <div class="search-overlay__section-header">
                            <h2 class="search-overlay__section-title">Gợi ý tìm kiếm</h2>
                        </div>
                        <div class="search-overlay__tags" data-role="suggestion-tags"></div>
                    </div>
                    <div class="search-overlay__section" data-section="history">
                        <div class="search-overlay__section-header">
                            <h2 class="search-overlay__section-title">Tìm kiếm gần đây</h2>
                            <button type="button" class="search-overlay__history-clear" data-action="history-clear">Xóa tất cả</button>
                        </div>
                        <ul class="search-overlay__history-list" data-role="history-list"></ul>
                    </div>
                    <div class="search-overlay__section" data-section="results">
                        <div class="search-overlay__section-header">
                            <h2 class="search-overlay__section-title">Kết quả tìm kiếm</h2>
                        </div>
                        <div class="search-overlay__loader" hidden>Đang tìm kiếm...</div>
                        <div class="search-overlay__results-empty" data-role="empty" hidden>Không tìm thấy sản phẩm phù hợp.</div>
                        <div class="search-overlay__results" data-role="results"></div>
                        <div class="search-overlay__pagination" data-role="pagination"></div>
                    </div>
                </div>
            </div>
        </div>

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
    <script src="{{ asset('js/search_overlay.js') }}"></script>
    
    @auth
    <script>
        // Notification System
        (function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const notificationBell = document.getElementById('notification-bell');
            const notificationDropdown = document.getElementById('notification-dropdown');
            const notificationList = document.getElementById('notification-list');
            const notificationItems = document.getElementById('notification-items');
            const notificationCount = document.getElementById('notification-count');
            const markAllReadBtn = document.getElementById('mark-all-read');
            const loadMoreBtn = document.getElementById('load-more-notifications');
            
            let currentPage = 1;
            let isLoading = false;

            // Toggle dropdown
            if (notificationBell) {
                notificationBell.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    notificationDropdown.classList.toggle('active');
                    
                    if (notificationDropdown.classList.contains('active')) {
                        loadNotifications();
                    }
                });
            }

            // Đóng dropdown khi click bên ngoài
            document.addEventListener('click', function(e) {
                if (!notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.remove('active');
                }
            });

            // Load notifications
            async function loadNotifications(page = 1) {
                if (isLoading) return;
                
                isLoading = true;
                
                if (page === 1) {
                    notificationItems.innerHTML = '<div class="notification-loading"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';
                }

                try {
                    const response = await fetch(`/notifications?page=${page}&per_page=10`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        if (page === 1) {
                            notificationItems.innerHTML = '';
                        }

                        if (result.data.length === 0 && page === 1) {
                            notificationItems.innerHTML = '<div class="notification-empty"><i class="fa fa-bell-slash"></i><br>Không có thông báo nào</div>';
                            loadMoreBtn.style.display = 'none';
                        } else {
                            result.data.forEach(notification => {
                                notificationItems.appendChild(createNotificationElement(notification));
                            });

                            // Hiển thị/ẩn nút load more
                            if (result.current_page >= result.last_page) {
                                loadMoreBtn.style.display = 'none';
                            } else {
                                loadMoreBtn.style.display = 'block';
                            }
                        }

                        currentPage = result.current_page;
                    }
                } catch (error) {
                    console.error('Lỗi khi tải thông báo:', error);
                    notificationItems.innerHTML = '<div class="notification-empty"><i class="fa fa-exclamation-triangle"></i><br>Không thể tải thông báo</div>';
                } finally {
                    isLoading = false;
                }
            }

            // Tạo element cho notification
            function createNotificationElement(notification) {
                const div = document.createElement('div');
                div.className = `notification-item ${!notification.is_read ? 'unread' : ''}`;
                div.dataset.notificationId = notification.id;

                const timeAgo = getTimeAgo(notification.created_at);

                div.innerHTML = `
                    <div class="notification-content">
                        <div class="notification-title">${escapeHtml(notification.title)}</div>
                        <div class="notification-message">${escapeHtml(notification.message)}</div>
                        <div class="notification-time">${timeAgo}</div>
                    </div>
                `;

                div.addEventListener('click', function() {
                    handleNotificationClick(notification);
                });

                return div;
            }

            // Xử lý khi click vào notification
            async function handleNotificationClick(notification) {
                // Đánh dấu là đã đọc
                if (!notification.is_read) {
                    await markAsRead(notification.id);
                }

                // Chuyển hướng nếu có link
                if (notification.link) {
                    window.location.href = notification.link;
                }
            }

            // Đánh dấu notification là đã đọc
            async function markAsRead(notificationId) {
                try {
                    const response = await fetch(`/notifications/${notificationId}/mark-as-read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                        if (item) {
                            item.classList.remove('unread');
                        }
                        updateUnreadCount();
                    }
                } catch (error) {
                    console.error('Lỗi khi đánh dấu đã đọc:', error);
                }
            }

            // Đánh dấu tất cả là đã đọc
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', async function() {
                    try {
                        const response = await fetch('/notifications/mark-all-as-read', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            document.querySelectorAll('.notification-item.unread').forEach(item => {
                                item.classList.remove('unread');
                            });
                            updateUnreadCount();
                        }
                    } catch (error) {
                        console.error('Lỗi khi đánh dấu tất cả đã đọc:', error);
                    }
                });
            }

            // Load more notifications
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadNotifications(currentPage + 1);
                });
            }

            // Cập nhật số lượng thông báo chưa đọc
            async function updateUnreadCount() {
                try {
                    const response = await fetch('/notifications/unread-count', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        notificationCount.textContent = result.count;
                        
                        if (result.count === 0) {
                            notificationCount.style.display = 'none';
                        } else {
                            notificationCount.style.display = 'block';
                        }
                    }
                } catch (error) {
                    console.error('Lỗi khi cập nhật số thông báo:', error);
                }
            }

            // Helper: Format time ago
            function getTimeAgo(timestamp) {
                const now = new Date();
                const time = new Date(timestamp);
                const diff = Math.floor((now - time) / 1000);

                if (diff < 60) return 'Vừa xong';
                if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
                if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
                if (diff < 604800) return `${Math.floor(diff / 86400)} ngày trước`;
                
                return time.toLocaleDateString('vi-VN');
            }

            // Helper: Escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Auto-update unread count mỗi 30 giây
            setInterval(updateUnreadCount, 30000);
            
            // Load unread count ngay khi trang load
            updateUnreadCount();
        })();
    </script>
    @endauth
    
    @stack('scripts')
</body>

</html>
