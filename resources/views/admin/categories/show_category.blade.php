@extends('dashboard')
<!-- Thêm Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@php
    use Illuminate\Support\Str;
@endphp

@push('styles')
    <style>
        /* Category Page Styles */
        .category-page {
            margin-top: 140px;
            padding: 32px 0 56px;
            background-color: #f8f9fa;
        }

        .breadcrumb-nav {
            padding: 12px 0;
            background: transparent;
            margin-bottom: 24px;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: 14px;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: ">";
            padding: 0 8px;
            color: #6c757d;
        }

        .breadcrumb-item a {
            color: #0066cc;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            text-decoration: underline;
        }

        .breadcrumb-item.active {
            color: #495057;
        }

        .category-header {
            background: #fff;
            padding: 24px 0;
            margin-bottom: 24px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .category-title {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #212529;
        }

        .category-count {
            color: #6c757d;
            font-size: 15px;
        }

        .sidebar {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 160px;
        }

        .sidebar-section {
            margin-bottom: 28px;
        }

        .sidebar-section:last-child {
            margin-bottom: 0;
        }

        .sidebar-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #212529;
        }

        .sidebar-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-list li {
            margin-bottom: 8px;
        }

        .sidebar-list li:last-child {
            margin-bottom: 0;
        }

        .sidebar-list a,
        .sidebar-list button {
            display: block;
            padding: 8px 12px;
            color: #495057;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s ease;
            background: transparent;
            border: none;
            text-align: left;
            width: 100%;
            cursor: pointer;
        }

        .sidebar-list a:hover,
        .sidebar-list button:hover {
            background: #f8f9fa;
            color: #0066cc;
        }

        .sidebar-list a.active,
        .sidebar-list button.active {
            background: #e7f3ff;
            color: #0066cc;
            font-weight: 600;
        }

        .products-section {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .products-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e9ecef;
        }

        .sort-options {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sort-label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .sort-select {
            padding: 6px 32px 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            background: #fff;
            cursor: pointer;
            outline: none;
        }

        .sort-select:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
        }

        .refresh-btn {
            padding: 6px 16px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            background: #fff;
            color: #495057;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .refresh-btn:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .product-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .product-image-wrapper {
            position: relative;
            padding-top: 100%;
            background: #f8f9fa;
            overflow: hidden;
        }

        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #dc3545;
            color: #fff;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .favorite-icon {
            position: absolute;
            top: 12px;
            left: 12px;
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .favorite-icon:hover {
            background: #fff;
            transform: scale(1.1);
        }

        .product-info {
            padding: 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 15px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 40px;
        }

        .product-name a {
            color: inherit;
            text-decoration: none;
        }

        .product-name a:hover {
            color: #0066cc;
        }

        .product-price {
            font-size: 18px;
            font-weight: 700;
            color: #dc3545;
            margin-bottom: 4px;
        }

        .product-original-price {
            font-size: 14px;
            color: #6c757d;
            text-decoration: line-through;
            margin-bottom: 8px;
        }

        .product-location {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 12px;
            flex: 1;
        }

        .product-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .btn-product {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }

        .btn-detail {
            background: #fff;
            color: #0066cc;
            border: 1px solid #0066cc;
        }

        .btn-detail:hover {
            background: #0066cc;
            color: #fff;
        }

        .btn-buy {
            background: #dc3545;
            color: #fff;
            border: 1px solid #dc3545;
        }

        .btn-buy:hover {
            background: #c82333;
            border-color: #bd2130;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .empty-state-text {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .empty-state-subtext {
            font-size: 14px;
            color: #adb5bd;
            margin-bottom: 20px;
        }

        .empty-cta {
            margin-top: 24px;
        }

        .btn-explore {
            display: inline-block;
            padding: 12px 32px;
            background: #0066cc;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-explore:hover {
            background: #0052a3;
            color: #fff;
            text-decoration: none;
            transform: translateY(-2px);
        }

        /* Error States */
        .error-alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .error-alert.error {
            background: #f8d7da;
            border-color: #dc3545;
        }

        .error-alert.warning {
            background: #fff3cd;
            border-color: #ffc107;
        }

        .error-alert.info {
            background: #d1ecf1;
            border-color: #17a2b8;
        }

        .error-icon {
            font-size: 32px;
            flex-shrink: 0;
        }

        .error-content {
            flex: 1;
        }

        .error-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .error-message {
            font-size: 14px;
            color: #6c757d;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            margin-top: 12px;
        }

        .btn-retry, .btn-sync {
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-retry {
            background: #0066cc;
            color: #fff;
        }

        .btn-retry:hover {
            background: #0052a3;
        }

        .btn-sync {
            background: #28a745;
            color: #fff;
        }

        .btn-sync:hover {
            background: #218838;
        }

        /* Product Status */
        .btn-buy:disabled {
            background: #6c757d;
            border-color: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .product-status {
            position: absolute;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .product-status.out-of-stock {
            background: #dc3545;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .pagination {
            margin: 0;
        }

        .pagination .page-link {
            border-radius: 6px;
            margin: 0 4px;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        .pagination .page-item.active .page-link {
            background-color: #0066cc;
            border-color: #0066cc;
        }

        .pagination .page-link:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #0066cc;
        }

        @media (max-width: 768px) {
            .category-page {
                margin-top: 120px;
            }

            .products-toolbar {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
        }
    </style>
@endpush

@section('content')
    <main class="category-page">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb-nav" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Danh mục</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $category->name ?? 'Chi tiết' }}</li>
                </ol>
            </nav>

            {{-- Hiển thị lỗi --}}
            @if (isset($error) && $error)
                @if ($error['type'] === 'CONNECTION_ERROR')
                    <div class="error-alert error">
                        <div class="error-icon">⚠️</div>
                        <div class="error-content">
                            <div class="error-title">{{ $error['message'] }}</div>
                            <div class="error-message">Vui lòng kiểm tra kết nối mạng và thử lại</div>
                            @if ($error['action'] === 'retry')
                                <div class="error-actions">
                                    <button class="btn-retry" onclick="window.location.reload()">Thử lại</button>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif ($error['type'] === 'CATEGORY_INACTIVE')
                    <div class="error-alert warning">
                        <div class="error-icon">🚫</div>
                        <div class="error-content">
                            <div class="error-title">{{ $error['message'] }}</div>
                            <div class="error-message">Danh mục này hiện không khả dụng. Vui lòng chọn danh mục khác.</div>
                            <div class="error-actions">
                                <a href="{{ route('categories.index') }}" class="btn-retry">Xem danh mục khác</a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Category Header -->
            <div class="category-header">
                <div class="container">
                    <h1 class="category-title">{{ $category->name }}</h1>
                    <p class="category-count">{{ $products->total() }} sản phẩm</p>
                </div>
            </div>

            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="sidebar">
                        <!-- Bộ lọc -->
                        <div class="sidebar-section">
                            <h3 class="sidebar-title">Bộ lọc</h3>
                            <ul class="sidebar-list">
                                <li>
                                    <a href="{{ route('categories.show', ['category' => $category->id, 'filter' => 'all', 'sort' => request('sort', 'newest')]) }}"
                                        class="{{ request('filter', 'all') === 'all' ? 'active' : '' }}">
                                        Tất cả sản phẩm
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('categories.show', ['category' => $category->id, 'filter' => 'discount', 'sort' => request('sort', 'newest')]) }}"
                                        class="{{ request('filter') === 'discount' ? 'active' : '' }}">
                                        Đang giảm giá
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Danh mục -->
                        <div class="sidebar-section">
                            <h3 class="sidebar-title">Danh mục</h3>
                            <ul class="sidebar-list">
                                @foreach ($allCategories as $cat)
                                    <li>
                                        <a href="{{ route('categories.show', $cat->id) }}"
                                            class="{{ $cat->id === $category->id ? 'active' : '' }}">
                                            {{ $cat->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="col-lg-9">
                    <div class="products-section">
                        <!-- Toolbar -->
                        <div class="products-toolbar">
                            <div class="sort-options">
                                <span class="sort-label">Sắp xếp theo:</span>
                                <select class="sort-select" onchange="window.location.href=this.value">
                                    <option value="{{ route('categories.show', ['category' => $category->id, 'filter' => request('filter', 'all'), 'sort' => 'newest']) }}"
                                        {{ $sort === 'newest' ? 'selected' : '' }}>
                                        Mới nhất
                                    </option>
                                    <option value="{{ route('categories.show', ['category' => $category->id, 'filter' => request('filter', 'all'), 'sort' => 'oldest']) }}"
                                        {{ $sort === 'oldest' ? 'selected' : '' }}>
                                        Cũ nhất
                                    </option>
                                    <option value="{{ route('categories.show', ['category' => $category->id, 'filter' => request('filter', 'all'), 'sort' => 'price_asc']) }}"
                                        {{ $sort === 'price_asc' ? 'selected' : '' }}>
                                        Giá thấp đến cao
                                    </option>
                                    <option value="{{ route('categories.show', ['category' => $category->id, 'filter' => request('filter', 'all'), 'sort' => 'price_desc']) }}"
                                        {{ $sort === 'price_desc' ? 'selected' : '' }}>
                                        Giá cao đến thấp
                                    </option>
                                </select>
                            </div>
                            <button class="refresh-btn" onclick="window.location.reload()">Làm mới</button>
                        </div>

                        <!-- Products Grid -->
                        @if ($products->count() > 0)
                            <div class="products-grid">
                                @foreach ($products as $product)
                                    @php
                                        $image = optional($product->images->first())->image_url;
                                        if (empty($image)) {
                                            $image = asset('images/product_1.png');
                                        }
                                        $hasDiscount = $product->original_price && $product->original_price > $product->price;
                                        $discountAmount = $hasDiscount ? $product->original_price - $product->price : 0;
                                    @endphp

                                    @php
                                        $isOutOfStock = isset($product->quantity) && $product->quantity <= 0;
                                        $isExpired = isset($product->expires_at) && $product->expires_at < now();
                                        $isNotAvailable = $isOutOfStock || $isExpired || $product->status !== 'published';
                                    @endphp

                                    <div class="product-card">
                                        <div class="product-image-wrapper">
                                            <img src="{{ $image }}" alt="{{ $product->name }}" class="product-image">
                                            
                                            @if ($isOutOfStock)
                                                <div class="product-status out-of-stock">Hết hàng</div>
                                            @elseif ($isExpired)
                                                <div class="product-status out-of-stock">Đã hết hạn</div>
                                            @elseif ($hasDiscount)
                                                <div class="product-badge">
                                                    -{{ number_format($discountAmount, 0, ',', '.') }}₫
                                                </div>
                                            @endif
                                            
                                            <div class="favorite-icon">
                                                <i class="fa fa-heart-o" style="color: #dc3545;"></i>
                                            </div>
                                        </div>
                                        <div class="product-info">
                                            <h3 class="product-name">
                                                <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                                            </h3>
                                            <div class="product-price">{{ number_format($product->price, 0, ',', '.') }}₫</div>
                                            @if ($hasDiscount && !$isNotAvailable)
                                                <div class="product-original-price">
                                                    Giá gốc: {{ number_format($product->original_price, 0, ',', '.') }}₫
                                                </div>
                                            @endif
                                            <div class="product-location">
                                                <i class="fa fa-map-marker" style="margin-right: 4px;"></i>
                                                {{ $product->location ?? 'TP.HCM' }}
                                            </div>
                                            <div class="product-actions">
                                                <a href="{{ route('products.show', $product) }}" class="btn-product btn-detail">Xem chi tiết</a>
                                                @if ($isOutOfStock)
                                                    <button class="btn-product btn-buy" disabled title="Sản phẩm tạm thời hết hàng">Hết hàng</button>
                                                @elseif ($isExpired)
                                                    <button class="btn-product btn-buy" disabled title="Sản phẩm đã hết hạn">Hết hạn</button>
                                                @else
                                                    <form method="POST" action="{{ route('cart.add') }}" style="display: inline;">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                        <input type="hidden" name="quantity" value="1">
                                                        <button type="submit" class="btn-product btn-buy">Mua ngay</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            <div class="pagination-wrapper">
                                {{ $products->links('pagination::bootstrap-4') }}
                            </div>
                        @else
                            {{-- Empty State: Không có sản phẩm --}}
                            <div class="empty-state">
                                <div class="empty-state-icon">📦</div>
                                <p class="empty-state-text">Chưa có sản phẩm nào</p>
                                <p class="empty-state-subtext">
                                    @if (request('filter') === 'discount')
                                        Hiện tại không có sản phẩm nào đang giảm giá trong danh mục này.<br>
                                        Hãy thử xem tất cả sản phẩm hoặc quay lại sau.
                                    @else
                                        Danh mục này chưa có sản phẩm nào.<br>
                                        Hãy khám phá các danh mục khác hoặc quay lại sau!
                                    @endif
                                </p>
                                <div class="empty-cta">
                                    @if (request('filter') === 'discount')
                                        <a href="{{ route('categories.show', ['category' => $category->id, 'filter' => 'all']) }}" class="btn-explore">Xem tất cả sản phẩm</a>
                                    @else
                                        <a href="{{ route('categories.index') }}" class="btn-explore">Xem danh mục khác</a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Toast Notification --}}
    @if (session('success'))
        <div id="toast-notification" class="toast-notification">
            <div class="toast-icon">✓</div>
            <div class="toast-message">{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div id="toast-notification" class="toast-notification toast-error">
            <div class="toast-icon">✗</div>
            <div class="toast-message">{{ session('error') }}</div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .toast-notification {
            position: fixed;
            top: 100px;
            right: 24px;
            background: #28a745;
            color: #fff;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.7s;
            min-width: 300px;
        }

        .toast-notification.toast-error {
            background: #dc3545;
        }

        .toast-icon {
            font-size: 24px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .toast-message {
            font-size: 15px;
            font-weight: 500;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast-notification');
            if (toast) {
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }
        });
    </script>
@endpush
