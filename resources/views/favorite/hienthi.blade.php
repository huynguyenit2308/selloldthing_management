@extends('dashboard')

@push('styles')
<!-- Sử dụng chung CSS với trang quản lý sản phẩm -->
<link rel="stylesheet" href="{{ asset('styles/my_products.css') }}">
<link rel="stylesheet" href="{{ asset('styles/favorite.css') }}">
@endpush

@section('body-class', 'account-favorites-page') <!-- Thay đổi class -->

@section('content')
@php
// Biến $sortLabels và $sortOption sẽ được truyền từ FavoriteController
@endphp

<div class="account-products-wrapper">
    <div class="container">
        <nav class="account-breadcrumb" aria-label="breadcrumb">
            <ol>
                <li><a href="{{ url('/') }}">Trang chủ</a></li>
                <li><span>Tài khoản</span></li>
                <li aria-current="page"><span>Sản phẩm yêu thích</span></li> <!-- Thay đổi breadcrumb -->
            </ol>
        </nav>

        <div class="account-products-header">
            <h1>Sản phẩm yêu thích</h1> <!-- Thay đổi tiêu đề -->
            <!-- Không cần nút "Đăng sản phẩm mới" ở đây -->
        </div>

        <div class="account-products-content">
            <!-- XÓA BỎ THANH THỐNG KÊ (ASIDE) VÌ KHÔNG ÁP DỤNG CHO TRANG YÊU THÍCH -->

            <!-- Panel chính, thêm style để nó chiếm toàn bộ chiều rộng -->
            <section class="account-products-panel" style="grid-column: 1 / -1;">
                <header class="account-products-toolbar">
                    <form method="GET" action="{{ route('favorite.hienthi') }}" class="toolbar-form">
                        <!-- Preserve other parameters -->
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        <div class="toolbar-filters">
                            <div class="toolbar-group">
                                <label for="sort">Sắp xếp theo</label>
                                <select id="sort" name="sort">
                                    @foreach ($sortLabels as $value => $label)
                                    <option value="{{ $value }}" @selected($sortOption===$value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </header>

                <!-- Hiển thị các thông báo (nếu có) -->
                @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                <div class="alert alert-error" role="alert">{{ session('error') }}</div>
                @endif


                <div class="products-table-wrapper" role="region" aria-label="Danh sách sản phẩm yêu thích">
                    <div class="products-table" role="table">
                        <div class="table-header" role="row">
                            <div class="cell stt" role="columnheader">STT</div>
                            <div class="cell image" role="columnheader">Hình ảnh</div>
                            <div class="cell info" role="columnheader">Tên sản phẩm</div>
                            <div class="cell price" role="columnheader">Giá</div>
                            <div class="cell views" role="columnheader">Lượt xem</div>
                            <!-- Xóa cột Kho và Trạng thái -->
                            <div class="cell actions" role="columnheader">Thao tác</div>
                        </div>

                        <!-- Sửa: Lặp qua $products (được truyền từ controller, chính là $favorites) -->
                        @forelse ($products as $product)
                        @php
                        $firstImage = $product->images->first();
                        $primaryImage = $firstImage ? $firstImage->image_url : null;
                        $hasPrimaryImage = $firstImage && !empty($firstImage->url);
                        $priceText = $product->price ? number_format($product->price, 0, ',', '.') . ' VND' : 'Chưa đặt giá';
                        $placeholderText = \Illuminate\Support\Str::limit($product->name ?? 'Sản phẩm', 15, '');
                        @endphp
                        <div class="table-row" role="row" id="product-row-{{ $product->id }}">
                            <div class="cell stt" role="cell">
                                <span class="row-number">{{ ($products->firstItem() ?? 0) + $loop->index }}</span>
                            </div>
                            <div class="cell image" role="cell">
                                <div class="product-thumbnail {{ $hasPrimaryImage ? 'has-image' : 'is-placeholder' }}" data-product-name="{{ $product->name }}">
                                    @if ($hasPrimaryImage && $primaryImage)
                                    <img src="{{ $primaryImage }}" alt="{{ $product->name }}" onerror="window.handleProductImageError && window.handleProductImageError(this);">
                                    @endif
                                    <span class="product-placeholder" aria-hidden="{{ $hasPrimaryImage ? 'true' : 'false' }}">{{ $placeholderText }}</span>
                                </div>
                            </div>
                            <div class="cell info" role="cell">
                                <div class="product-info">
                                    <!-- Sửa: link đến products.show -->
                                    <h3 class="product-title"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
                                </div>
                            </div>
                            <div class="cell price" role="cell">
                                <span class="price-value">{{ $priceText }}</span>
                            </div>
                            <div class="cell views" role="cell">
                                <span class="views-value">{{ number_format($product->view_count ?? 0) }}</span>
                            </div>
                            <!-- Xóa cell stock và status -->
                            <div class="cell actions" role="cell">
                                <div class="action-buttons">

                                    <!-- [THAY THẾ TOÀN BỘ NÚT BẰNG NÚT YÊU THÍCH] -->
                                    @php
                                    $isFavorited = isset($userFavoriteIds) && in_array($product->id, $userFavoriteIds);
                                    @endphp
                                    <button type="button"
                                        class="action-btn btn-delete favorite-button {{ $isFavorited ? 'favorited' : '' }}"
                                        aria-label="Bỏ yêu thích"
                                        title="Bỏ yêu thích"
                                        data-id="{{ $product->id }}"
                                        data-url="{{ route('favorites.toggle') }}"
                                        id="fav-btn-{{ $product->id }}">
                                        <!-- Sử dụng icon trái tim đầy (vì đây là danh sách yêu thích) -->
                                        <i class="fa {{ $isFavorited ? 'fa-heart' : 'fa-heart-o' }}" aria-hidden="true"></i>
                                    </button>

                                    <!-- Thêm nút xem chi tiết nếu muốn -->
                                    <a href="{{ route('products.show', $product) }}" class="action-btn btn-edit" title="Xem chi tiết" aria-label="Xem chi tiết">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <path d="M1.458 12C2.732 7.943 6.796 5 12 5s9.268 2.943 10.542 7c-1.274 4.057-5.338 7-10.542 7S2.732 16.057 1.458 12Z" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M15 12a3 3 0 1 1-6 0a3 3 0 0 1 6 0Z" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </a>

                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="table-empty" role="row">
                            <div class="empty-content">
                                <div class="empty-icon">❤️</div>
                                <h3>Bạn chưa có sản phẩm yêu thích nào</h3>
                                <p>Hãy khám phá thêm các sản phẩm và nhấn trái tim để lưu lại nhé</p>
                                <a class="btn-primary" href="{{ route('products.index') }}">Xem tất cả sản phẩm</a>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        @if ($products->count() > 0)
        <!-- [SỬA LỖI 1] THAY THẾ PHÂN TRANG BẰNG HTML CHI TIẾT -->
        <div class="account-products-pagination">
            <nav aria-label="Pagination">
                <ul class="pagination">
                    <li class="pagination-item {{ $products->onFirstPage() ? 'is-disabled' : '' }}">
                        <a href="{{ $products->previousPageUrl() ?? '#' }}" aria-disabled="{{ $products->onFirstPage() ? 'true' : 'false' }}">&lt;</a>
                    </li>

                    @if ($products->hasPages())
                    @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                    <li class="pagination-item {{ $page === $products->currentPage() ? 'is-active' : '' }}">
                        <a href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    @endif

                    <li class="pagination-item {{ $products->hasMorePages() ? '' : 'is-disabled' }}">
                        <a href="{{ $products->nextPageUrl() ?? '#' }}" aria-disabled="{{ $products->hasMorePages() ? 'false' : 'true' }}">&gt;</a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif

    </div>
</div>

<!-- Xóa bỏ toàn bộ popup Overlay (Delete, Warning, Error) -->
@endsection

@push('scripts')
<script>
    (function() {
        // ----- HÀM XỬ LÝ LỖI ẢNH (GIỮ NGUYÊN) -----
        const updateThumbnailState = (container) => {
            if (!container) return;
            const hasImage = !!container.querySelector('img');
            const placeholder = container.querySelector('.product-placeholder');
            container.classList.toggle('has-image', hasImage);
            container.classList.toggle('is-placeholder', !hasImage);
            if (placeholder) {
                placeholder.setAttribute('aria-hidden', hasImage ? 'true' : 'false');
            }
        };

        window.handleProductImageError = function(img) {
            if (!img) return;
            const container = img.closest('.product-thumbnail');
            if (container && img.parentNode === container) {
                container.removeChild(img);
            }
            updateThumbnailState(container);
        };

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.product-thumbnail').forEach(function(container) {
                const img = container.querySelector('img');
                if (!img) {
                    updateThumbnailState(container);
                    return;
                }
                const handleErrorOnce = () => {
                    window.handleProductImageError(img);
                };
                if (!img.complete) {
                    img.addEventListener('load', () => updateThumbnailState(container), {
                        once: true
                    });
                    img.addEventListener('error', handleErrorOnce, {
                        once: true
                    });
                    return;
                }
                if (img.naturalWidth === 0 || img.naturalHeight === 0) {
                    handleErrorOnce();
                    return;
                }
                updateThumbnailState(container);
            });

            // ----- HÀM TỰ SUBMIT SORT (GIỮ NGUYÊN) -----
            const sortSelect = document.getElementById('sort');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            }

            // ----- [THÊM MỚI] JAVASCRIPT CHO NÚT YÊU THÍCH -----
            document.querySelectorAll('.favorite-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const buttonElement = this;
                    const productId = buttonElement.dataset.id;
                    const toggleUrl = buttonElement.dataset.url;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const icon = buttonElement.querySelector('i.fa');

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
                        .then(response => {
                            if (!response.ok) {
                                if (response.status === 401) {
                                    alert('Bạn cần đăng nhập để thực hiện việc này.');
                                    window.location.href = '/login';
                                }
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.status === 'added') {
                                // Thêm lại
                                buttonElement.classList.add('favorited');
                                icon.classList.remove('fa-heart-o');
                                icon.classList.add('fa-heart');
                            } else if (data.status === 'removed') {
                                // Xóa khỏi danh sách
                                buttonElement.classList.remove('favorited');
                                icon.classList.remove('fa-heart');
                                icon.classList.add('fa-heart-o');

                                // TÌM DÒNG VÀ XÓA NÓ
                                const row = buttonElement.closest('.table-row');
                                if (row) {
                                    row.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                                    row.style.opacity = '0';
                                    row.style.transform = 'translateX(50px)';
                                    setTimeout(() => {
                                        row.remove();
                                        // Cập nhật lại STT nếu cần (bỏ qua để đơn giản)
                                    }, 300);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Có lỗi xảy ra:', error);
                        });
                });
            });

        });
    })();
</script>
@endpush