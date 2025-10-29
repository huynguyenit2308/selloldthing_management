@extends('dashboard')

@section('body-class', 'favorites-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/favorites.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/favorites.js') }}"></script>
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

            @include('favorites.partials.error-states')

            <div class="favorites-layout">
                <aside class="favorites-sidebar">
                    <section class="favorites-panel">
                        <h3 class="favorites-panel-title">Bộ lọc</h3>
                        <ul class="favorites-filter-list">
                            <li>
                                <a class="favorites-filter-link {{ !request('filter_type') ? 'is-active' : '' }}" href="{{ route('favorites.index', request()->except(['filter_type', 'page'])) }}">
                                    Tất cả sản phẩm
                                </a>
                            </li>
                            <li>
                                <a class="favorites-filter-link {{ request('filter_type') === 'recently_viewed' ? 'is-active' : '' }}" href="{{ route('favorites.index', array_merge(request()->except('page'), ['filter_type' => 'recently_viewed'])) }}">
                                    Đã xem gần đây
                                </a>
                            </li>
                            <li>
                                <a class="favorites-filter-link {{ request('filter_type') === 'on_sale' ? 'is-active' : '' }}" href="{{ route('favorites.index', array_merge(request()->except('page'), ['filter_type' => 'on_sale'])) }}">
                                    Đang giảm giá
                                </a>
                            </li>
                        </ul>
                    </section>

                    <section class="favorites-panel">
                        <h3 class="favorites-panel-title">Danh mục</h3>
                        <ul class="favorites-category-list">
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
                            $favoritesCount = $favorites instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                                ? $favorites->total()
                                : $favorites->count();
                        @endphp
                        <span class="favorites-count"><span class="favorites-count-number">{{ $favoritesCount }}</span> sản phẩm</span>
                    </header>

                    <div class="favorites-controls">
                        <div class="favorites-sort">
                            <label for="sortSelect">Sắp xếp theo:</label>
                            <select id="sortSelect">
                                <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Tên A-Z</option>
                            </select>
                        </div>
                        <div class="favorites-control-buttons">
                            <button type="button" id="refreshBtn" class="favorites-control-btn">Làm mới</button>
                            <button type="button" id="clearAllBtn" class="favorites-control-btn danger">Xóa tất cả</button>
                        </div>
                    </div>

                    @if ($favorites->count() > 0)
                        <div class="favorites-grid">
                            @foreach ($favorites as $favorite)
                                @php $product = $favorite->product; @endphp
                                @if ($product)
                                    <article class="favorite-card">
                                        <button type="button" class="favorite-remove remove-favorite" data-product-id="{{ $product->id }}" aria-label="Bỏ khỏi yêu thích">×</button>
                                        <div class="favorite-image">
                                            <a href="{{ route('products.show', $product->id) }}">
                                                @if ($product->images->count())
                                                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}">
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
                            {{ $favorites->appends(request()->query())->links() }}
                        </div>
                    @else
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

        <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0">Đang xử lý...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmMessage">Bạn có chắc chắn muốn thực hiện hành động này?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary" id="confirmAction">Xác nhận</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
