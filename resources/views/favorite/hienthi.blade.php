{{-- BẮT ĐẦU FILE BLADE ĐÃ HỢP NHẤT --}}

@extends('dashboard')

@section('body-class', 'favorites-page')

@push('styles')
{{-- THAY ĐỔI 1: Giữ nguyên CSS của Giao diện 1 (lưới) mà bạn muốn --}}
<link rel="stylesheet" href="{{ asset('styles/favorite.css') }}">
{{-- Nếu cần, bạn có thể nạp thêm file favorite.css của Giao diện 2 --}}
@endpush

@push('scripts')
{{-- Để trống, chúng ta sẽ push script ở dưới cùng --}}
@endpush

@section('content')
<div class="favorites-page-wrapper">
    <div class="container">
        <nav class="favorites-breadcrumb" aria-label="breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('account.info') }}">Tài khoản</a>
            <span>/</span>
            <span>Sản phẩm yêu thích</span>
        </nav>



        <div class="favorites-layout">
            <aside class="favorites-sidebar">
                <section class="favorites-panel">
                    <h3 class="favorites-panel-title">Bộ lọc</h3>
                    <ul class="favorites-filter-list">
                        <li>
                            <a class="favorites-filter-link {{ !request('filter_type') ? 'is-active' : '' }}" href="{{ route('favorite.hienthi', request()->except(['filter_type', 'page'])) }}">
                                Tất cả sản phẩm
                            </a>
                        </li>
                        <li>
                            <a class="favorites-filter-link {{ request('filter_type') === 'recently_viewed' ? 'is-active' : '' }}" href="{{ route('favorite.hienthi', array_merge(request()->except('page'), ['filter_type' => 'recently_viewed'])) }}">
                                Đã xem gần đây
                            </a>
                        </li>
                        <li>
                            <a class="favorites-filter-link {{ request('filter_type') === 'on_sale' ? 'is-active' : '' }}" href="{{ route('favorite.hienthi', array_merge(request()->except('page'), ['filter_type' => 'on_sale'])) }}">
                                Đang giảm giá
                            </a>
                        </li>
                    </ul>
                </section>

                <section class="favorites-panel">
                    <h3 class="favorites-panel-title">Danh mục</h3>
                    <ul class="favorites-category-list">
                        {{-- THAY ĐỔI 2: Đảm bảo Controller của bạn truyền biến $categories --}}
                        {{-- Logic này yêu cầu $categories từ Controller, giống như Giao diện 1 --}}
                        @forelse ($categories as $category)
                        <li class="favorites-category-item {{ (string) request('category') === (string) $category->id ? 'is-selected' : '' }}" data-category-id="{{ $category->id }}">
                            <span>{{ $category->name }}</span>
                            <i class="fa fa-chevron-right"></i>
                        </li>
                        @empty
                        <li class="favorites-category-empty">Chưa có danh mục khả dụng</li>
                        @endforelse
                    </ul>
                </section>

                <section class="favorites-panel">
                    <h3 class="favorites-panel-title">Thao tác</h3>
                    <button type="button" class="favorites-panel-btn" id="addAllToCartBtn">Thêm tất cả vào giỏ hàng</button>
                    <button type="button" class="favorites-panel-btn outline" id="sidebarClearAll">Xóa tất cả yêu thích</button>
                </section>
            </aside>

            <section class="favorites-main">
                <header class="favorites-header">
                    <h1>Sản phẩm yêu thích</h1>
                    @php

                    $favoritesCount = $products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                    ? $products->total()
                    : $products->count();
                    @endphp
                    <span class="favorites-count"><span class="favorites-count-number">{{ $favoritesCount }}</span> sản phẩm</span>
                </header>

                <div class="favorites-controls">
                    <div class="favorites-sort">
                        {{-- THAY ĐỔI 4: Dùng logic Sort của Giao diện 2 --}}
                        <form method="GET" action="{{ route('favorite.hienthi') }}" class="toolbar-form" id="sortForm">
                            @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            {{-- Giữ lại các filter khác nếu có --}}
                            @if(request('filter_type'))
                            <input type="hidden" name="filter_type" value="{{ request('filter_type') }}">
                            @endif
                            @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif

                            <label for="sortSelect">Sắp xếp theo:</label>
                            {{-- Dùng ID 'sortSelect' của Giao diện 1, nhưng lặp mảng $sortLabels của Giao diện 2 --}}
                            <select id="sortSelect" name="sort">
                                @foreach ($sortLabels as $value => $label)
                                <option value="{{ $value }}" @selected($sortOption===$value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="favorites-control-buttons">
                        <button type="button" id="refreshBtn" class="favorites-control-btn">Làm mới</button>
                        <button type="button" id="clearAllBtn" class="favorites-control-btn danger">Xóa tất cả</button>
                    </div>
                </div>

                {{-- THAY ĐỔI 5: Dùng @forelse và biến $products (từ Giao diện 2) --}}
                @if ($products->count() > 0)
                <div class="favorites-grid">
                    @foreach ($products as $product)
                    {{-- Không cần @php $product = $favorite->product; @endphp nữa --}}
                    @if ($product)
                    {{-- Thêm ID cho card để JS có thể xóa --}}
                    <article class="favorite-card" id="favorite-card-{{ $product->id }}">
                        {{-- THAY ĐỔI 6: Thêm class 'favorite-button' để JS của Giao diện 2 bắt sự kiện --}}
                        <button type="button"
                            class="favorite-remove remove-favorite favorite-button favorited"
                            data-id="{{ $product->id }}"
                            data-url="{{ route('favorites.toggle') }}"
                            aria-label="Bỏ khỏi yêu thích">
                            <i class="fa fa-heart" aria-hidden="true"></i>
                        </button>

                        <div class="favorite-image">
                            <a href="{{ route('products.show', $product->id) }}">
                                {{-- THAY ĐỔI 7: Dùng logic ảnh tốt hơn của Giao diện 2 --}}
                                @php
                                $firstImage = $product->images->first();
                                $primaryImage = $firstImage ? $firstImage->image_url : null;
                                @endphp
                                @if ($primaryImage)
                                <img src="{{ $primaryImage }}" alt="{{ $product->name }}" onerror="window.handleProductImageError && window.handleProductImageError(this);">
                                @else
                                <img src="{{ asset('images/product_1.png') }}" alt="{{ $product->name }}">
                                @endif
                            </a>
                        </div>
                        <div class="favorite-info">
                            <div class="favorite-price">
                                <span class="current">{{ number_format($product->price, 0, ',', '.') }} VND</span>
                                @if ($product->original_price && $product->original_price > $product->price)
                                <span class="original">{{ number_format($product->original_price, 0, ',', '.') }} VND</span>
                                @endif
                            </div>
                            <ul class="favorite-details">
                                <li>{{ $product->name }}</li>
                                <li>Giá gốc: {{ $product->original_price ? number_format($product->original_price, 0, ',', '.') . ' VND' : 'Đang cập nhật' }}</li>
                                <li>Tình trạng: {{ $product->condition ? ucfirst($product->condition) : 'Đang cập nhật' }}</li>
                                <li>Địa điểm: {{ $product->location ?? 'Đang cập nhật' }}</li>
                            </ul>
                            <div class="favorite-actions">
                                <a href="{{ route('products.show', $product->id) }}" class="favorite-btn outline">Xem chi tiết</a>
                                <button type="button" class="favorite-btn solid add-to-cart" data-product-id="{{ $product->id }}">Mua ngay</button>
                            </div>
                        </div>
                    </article>
                    @endif
                    @endforeach
                </div>

                <div class="favorites-pagination">
                    {{-- THAY ĐỔI 8: Dùng $products cho phân trang --}}
                    {{ $products->appends(request()->query())->links() }}
                </div>
                @else
                {{-- Giữ nguyên Empty State của Giao diện 1 --}}
                <div class="favorites-empty-state">
                    <div class="icon"><i class="fa fa-heart-o"></i></div>
                    <h3>Chưa có sản phẩm yêu thích</h3>
                    <p>Hãy thêm những sản phẩm bạn yêu thích và quay lại sau.</p>
                    <a href="{{ route('products.index') }}" class="favorite-btn solid">
                        <i class="fa fa-search"></i> Khám phá sản phẩm
                    </a>
                </div>
                @endif
            </section>
        </div>
    </div>

    {{-- Giữ nguyên các Modal của Giao diện 1 --}}
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        {{-- ... nội dung modal ... --}}
    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        {{-- ... nội dung modal ... --}}
    </div>
</div>
@endsection




@push('scripts')
<script>
    (function() {
        // ----- HÀM XỬ LÝ LỖI ẢNH -----
        window.handleProductImageError = function(img) {
            if (!img) return;
            img.src = "{{ asset('images/product_1.png') }}"; // Thay bằng ảnh mặc định
            img.onerror = null; // Ngăn lặp vô hạn
        };

        document.addEventListener('DOMContentLoaded', function() {
            // ----- HÀM TỰ SUBMIT SORT -----
            const sortSelect = document.getElementById('sortSelect'); // Dùng ID của giao diện lưới
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            }

            // ----- JAVASCRIPT CHO NÚT YÊU THÍCH -----
            document.querySelectorAll('.favorite-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const buttonElement = this;
                    const productId = buttonElement.dataset.id;
                    const toggleUrl = buttonElement.dataset.url;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(toggleUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                product_id: productId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'removed') {

                                // [SỬA LỖI] Tìm đúng .favorite-card thay vì .table-row
                                const card = buttonElement.closest('article.favorite-card');

                                if (card) {
                                    // Thêm hiệu ứng fade-out và co lại
                                    card.style.transition = 'all 0.4s ease';
                                    card.style.opacity = '0';
                                    card.style.transform = 'scale(0.9)';
                                    card.style.maxHeight = '0px';
                                    card.style.padding = '0';
                                    card.style.margin = '0';

                                    setTimeout(() => {
                                        card.remove();

                                        // Cập nhật lại số lượng
                                        const countEl = document.querySelector('.favorites-count-number');
                                        if (countEl) {
                                            let currentCount = parseInt(countEl.textContent) || 0;
                                            if (currentCount > 0) {
                                                countEl.textContent = currentCount - 1;
                                            }
                                        }

                                        // Kiểm tra nếu hết sản phẩm thì reload
                                        const grid = document.querySelector('.favorites-grid');
                                        if (grid && !grid.querySelector('article.favorite-card')) {
                                            window.location.reload();
                                        }
                                    }, 400);
                                }
                            }
                        })
                        .catch(error => console.error('Có lỗi xảy ra:', error));
                });
            });

        });
    })();
</script>
@endpush