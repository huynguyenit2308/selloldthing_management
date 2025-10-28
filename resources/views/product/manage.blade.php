@extends('dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/my_products.css') }}">
@endpush

@section('body-class', 'account-products-page')

@section('content')
    @php
        $statusLabels = [
            'all' => 'Tất cả',
            'published' => 'Đang hiển thị',
            'pending' => 'Đang chờ duyệt',
            'hidden' => 'Đã ẩn',
            'sold' => 'Đã bán',
        ];

        $sortLabels = [
            'newest' => 'Mới nhất',
            'oldest' => 'Cũ nhất',
            'name_asc' => 'A-Z',
            'name_desc' => 'Z-A',
            'price_desc' => 'Giá cao nhất',
            'price_asc' => 'Giá thấp nhất',
            'views_desc' => 'Lượt xem nhiều nhất',
            'views_asc' => 'Lượt xem ít nhất',
        ];

        $statCards = [
            [
                'key' => 'total',
                'label' => 'Tổng sản phẩm',
                'value' => number_format($statistics['total'] ?? 0),
                'status' => 'all',
            ],
            [
                'key' => 'published',
                'label' => 'Đang hiển thị',
                'value' => number_format($statistics['published'] ?? 0),
                'status' => 'published',
            ],
            [
                'key' => 'pending',
                'label' => 'Đang chờ duyệt',
                'value' => number_format($statistics['pending'] ?? 0),
                'status' => 'pending',
            ],
            [
                'key' => 'sold',
                'label' => 'Đã bán',
                'value' => number_format($statistics['sold'] ?? 0),
                'status' => 'sold',
            ],
        ];
    @endphp

    <div class="account-products-wrapper">
        <div class="container">
            <nav class="account-breadcrumb" aria-label="breadcrumb">
                <ol>
                    <li><a href="{{ url('/') }}">Trang chủ</a></li>
                    <li><span>Tài khoản</span></li>
                    <li aria-current="page"><span>Sản phẩm của tôi</span></li>
                </ol>
            </nav>

            <div class="account-products-header">
                <h1>Sản phẩm của tôi</h1>
                <div class="header-actions">
                    @if ($isLimitReached)
                        <div class="alert alert-warning" role="status">Bạn đã đạt giới hạn {{ number_format($productLimit) }} sản phẩm</div>
                    @endif
                    @if ($accountRestricted)
                        <div class="alert alert-danger" role="status">Tài khoản của bạn đang bị hạn chế tính năng đăng bán</div>
                    @endif
                    <a href="{{ route('products.create') }}" class="btn-primary {{ ($isLimitReached || $accountRestricted) ? 'is-disabled' : '' }}" aria-disabled="{{ ($isLimitReached || $accountRestricted) ? 'true' : 'false' }}">Đăng sản phẩm mới</a>
                </div>
            </div>

            <div class="account-products-content">
                <aside class="account-products-summary" aria-label="Thống kê sản phẩm">
                    <h2>Thống kê</h2>
                    <div class="summary-grid">
                        @foreach ($statCards as $card)
                            <a href="{{ route('products.manage', array_filter(['status' => $card['status'] !== 'all' ? $card['status'] : null] + request()->except('page'))) }}"
                                class="summary-card {{ $statusFilter === $card['status'] ? 'is-active' : '' }}"
                                data-status="{{ $card['status'] }}">
                                <span class="summary-label">{{ $card['label'] }}</span>
                                <span class="summary-value">{{ $card['value'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </aside>

                <section class="account-products-panel">
                    <header class="account-products-toolbar">
                        <form method="GET" action="{{ route('products.manage') }}" class="toolbar-form">
                            <div class="toolbar-filters">
                                <div class="toolbar-group">
                                    <label for="sort">Sắp xếp theo</label>
                                    <select id="sort" name="sort">
                                        @foreach ($sortLabels as $value => $label)
                                            <option value="{{ $value }}" @selected($sortOption === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                    </header>

                    @if ($loadError)
                        <div class="alert alert-error" role="alert">
                            <p>{{ $loadError }}</p>
                            <form method="GET" action="{{ route('products.manage') }}">
                                <button type="submit" class="btn-secondary">Thử lại</button>
                            </form>
                        </div>
                        <div class="table-skeleton">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="skeleton-row"></div>
                            @endfor
                        </div>
                    @elseif ($syncWarning)
                        <div class="alert alert-warning" role="status">
                            <p>{{ $syncWarning }}</p>
                            <button type="button" class="btn-secondary">Đồng bộ ngay</button>
                        </div>
                    @endif

                    @if ($productActionBlocked)
                        <div class="alert alert-error" role="alert">{{ $productActionBlocked }}</div>
                    @endif
                    @if ($productStatusSuccess)
                        <div class="alert alert-success" role="status">{{ $productStatusSuccess }}</div>
                    @endif
                    @if ($productStatusError)
                        <div class="alert alert-error" role="alert">{{ $productStatusError }}</div>
                    @endif
                    @if ($productDeleteSuccess)
                        <div class="alert alert-success" role="status">{{ $productDeleteSuccess }}</div>
                    @endif
                    @if ($productDeleteError)
                        <div class="alert alert-error" role="alert">{{ $productDeleteError }}</div>
                        @if ($productDeleteReason)
                            <p class="alert-detail">{{ $productDeleteReason }}</p>
                        @endif
                    @endif
                    @if ($productBulkSuccess)
                        <div class="alert alert-success" role="status">{{ $productBulkSuccess }}</div>
                    @endif
                    @if ($productBulkError)
                        <div class="alert alert-error" role="alert">{{ $productBulkError }}</div>
                    @endif
                    @if ($productBulkErrors && is_array($productBulkErrors) && count($productBulkErrors) > 0)
                        <ul class="alert-detail">
                            @foreach ($productBulkErrors as $bulkMessage)
                                <li>{{ $bulkMessage }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($conflictPayload)
                        <div class="alert alert-warning" role="status">
                            <p>Dữ liệu mới nhất đã thay đổi. Vui lòng kiểm tra và chọn phiên bản phù hợp.</p>
                        </div>
                    @endif

                    <div class="account-products-table" role="table">
                        <div class="table-header" role="row">
                            <div class="cell index" role="columnheader">STT</div>
                            <div class="cell image" role="columnheader">Hình ảnh</div>
                            <div class="cell info" role="columnheader">Tên sản phẩm</div>
                            <div class="cell price" role="columnheader">Giá</div>
                            <div class="cell views" role="columnheader">Lượt xem</div>
                            <div class="cell status" role="columnheader">Trạng thái</div>
                            <div class="cell actions" role="columnheader">Thao tác</div>
                        </div>

                        @forelse ($products as $product)
                            @php
                                $firstImage = $product->images->first();
                                $primaryImage = $firstImage ? $firstImage->image_url : null;
                                $hasPrimaryImage = $firstImage && !empty($firstImage->url);
                                $statusKey = $product->status ?? 'pending';
                                $statusText = $statusLabels[$statusKey] ?? ucfirst($statusKey);
                                $priceText = $product->price ? number_format($product->price, 0, ',', '.') . ' VND' : 'Chưa đặt giá';
                                $shortDescription = $product->short_description
                                    ?? ($product->description ? \Illuminate\Support\Str::limit($product->description, 80) : null);
                                $placeholderText = \Illuminate\Support\Str::limit($product->name ?? 'Sản phẩm', 36, '');
                            @endphp
                            <div class="table-row" role="row">
                                <div class="cell index" role="cell">
                                    {{ ($products->firstItem() ?? 0) + $loop->index }}
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
                                    <h3>{{ $product->name }}</h3>
                                    @if ($shortDescription)
                                        <p class="product-description">{{ $shortDescription }}</p>
                                    @endif
                                </div>
                                <div class="cell price" role="cell">
                                    <span>{{ $priceText }}</span>
                                </div>
                                <div class="cell views" role="cell">
                                    <span class="views-value" aria-label="{{ number_format($product->view_count ?? 0) }} lượt xem">
                                        <span class="views-icon" aria-hidden="true">👁️</span>
                                        {{ number_format($product->view_count ?? 0) }}
                                    </span>
                                </div>
                                <div class="cell status" role="cell">
                                    <span class="status-tag status-{{ $statusKey }}">{{ $statusText }}</span>
                                </div>
                                <div class="cell actions" role="cell">
                                    <div class="action-buttons">
                                        <form method="POST" action="{{ route('products.toggle', $product) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="action-btn" title="Ẩn/hiển thị" aria-label="Ẩn/hiển thị" {{ ($accountRestricted) ? 'disabled' : '' }}>
                                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M1.458 12C2.732 7.943 6.796 5 12 5s9.268 2.943 10.542 7c-1.274 4.057-5.338 7-10.542 7S2.732 16.057 1.458 12Z" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12a3 3 0 1 1-6 0a3 3 0 0 1 6 0Z" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                <span class="sr-only">Ẩn/hiển thị</span>
                                            </button>
                                        </form>
                                        <button type="button" class="action-btn js-edit-product" data-edit-url="{{ route('products.edit', $product) }}" data-product-id="{{ $product->id }}" title="Chỉnh sửa" aria-label="Chỉnh sửa">
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 15.5 15.5 4a2.121 2.121 0 1 1 3 3L7 18.5 3 19.5Z" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="m14.5 5.5 3 3" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            <span class="sr-only">Chỉnh sửa</span>
                                        </button>
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn action-delete" title="Xóa" aria-label="Xóa" {{ ($accountRestricted) ? 'disabled' : '' }}>
                                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 7L18.132 19.142A2 2 0 0 1 16.137 21H7.863a2 2 0 0 1-1.995-1.858L5 7" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 11v6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 7h18" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                <span class="sr-only">Xóa</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="table-empty" role="row">
                                <div class="cell" role="cell">
                                    <p>Bạn chưa có sản phẩm nào</p>
                                    <a class="btn-primary" href="{{ route('products.create') }}">Đăng sản phẩm đầu tiên</a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if ($products->count() > 0)
                        <div class="account-products-pagination">
                            <nav aria-label="Pagination">
                                <ul class="pagination">
                                    <li class="pagination-item {{ $products->onFirstPage() ? 'is-disabled' : '' }}">
                                        <a href="{{ $products->previousPageUrl() ?? '#' }}" aria-disabled="{{ $products->onFirstPage() ? 'true' : 'false' }}">&lt;</a>
                                    </li>
                                    @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                        <li class="pagination-item {{ $page === $products->currentPage() ? 'is-active' : '' }}">
                                            <a href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endforeach
                                    <li class="pagination-item {{ $products->hasMorePages() ? '' : 'is-disabled' }}">
                                        <a href="{{ $products->nextPageUrl() ?? '#' }}" aria-disabled="{{ $products->hasMorePages() ? 'false' : 'true' }}">&gt;</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const updateThumbnailState = (container) => {
                if (!container) {
                    return;
                }

                const hasImage = !!container.querySelector('img');
                const placeholder = container.querySelector('.product-placeholder');

                container.classList.toggle('has-image', hasImage);
                container.classList.toggle('is-placeholder', !hasImage);

                if (placeholder) {
                    placeholder.setAttribute('aria-hidden', hasImage ? 'true' : 'false');
                }
            };

            window.handleProductImageError = function (img) {
                if (!img) {
                    return;
                }

                const container = img.closest('.product-thumbnail');

                if (container && img.parentNode === container) {
                    container.removeChild(img);
                }

                updateThumbnailState(container);
            };

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.product-thumbnail').forEach(function (container) {
                    const img = container.querySelector('img');

                    if (!img) {
                        updateThumbnailState(container);
                        return;
                    }

                    const handleErrorOnce = () => {
                        window.handleProductImageError(img);
                    };

                    if (!img.complete) {
                        img.addEventListener('load', function () {
                            updateThumbnailState(container);
                        }, { once: true });

                        img.addEventListener('error', handleErrorOnce, { once: true });

                        return;
                    }

                    if (img.naturalWidth === 0 || img.naturalHeight === 0) {
                        handleErrorOnce();
                        return;
                    }

                    updateThumbnailState(container);
                });

                // AJAX for toggle visibility
                document.querySelectorAll('form[action*="toggle"]').forEach(function (form) {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const row = form.closest('.table-row');
                        const statusTag = row.querySelector('.status-tag');
                        const isHidden = statusTag.classList.contains('status-hidden');
                        
                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: new FormData(form)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                statusTag.textContent = isHidden ? 'Đang hiển thị' : 'Đã ẩn';
                                statusTag.className = 'status-tag ' + (isHidden ? 'status-published' : 'status-hidden');
                                
                                const alert = document.createElement('div');
                                alert.className = 'alert alert-success';
                                alert.textContent = data.message;
                                document.querySelector('.account-products-panel').prepend(alert);
                                setTimeout(() => alert.remove(), 3000);
                            } else {
                                const alert = document.createElement('div');
                                alert.className = 'alert alert-error';
                                alert.textContent = data.message || 'Có lỗi xảy ra';
                                document.querySelector('.account-products-panel').prepend(alert);
                                setTimeout(() => alert.remove(), 3000);
                            }
                        })
                        .catch(error => {
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-error';
                            alert.textContent = 'Lỗi kết nối. Vui lòng thử lại.';
                            document.querySelector('.account-products-panel').prepend(alert);
                            setTimeout(() => alert.remove(), 3000);
                        });
                    });
                });

                // AJAX for delete product
                document.querySelectorAll('form[action*="destroy"]').forEach(function (form) {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                            const row = form.closest('.table-row');
                            
                            fetch(form.action, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    row.style.opacity = '0.5';
                                    row.style.pointerEvents = 'none';
                                    setTimeout(() => {
                                        row.remove();
                                        const alert = document.createElement('div');
                                        alert.className = 'alert alert-success';
                                        alert.textContent = data.message;
                                        document.querySelector('.account-products-panel').prepend(alert);
                                        setTimeout(() => alert.remove(), 3000);
                                    }, 500);
                                } else {
                                    const alert = document.createElement('div');
                                    alert.className = 'alert alert-error';
                                    alert.textContent = data.message || 'Có lỗi xảy ra';
                                    document.querySelector('.account-products-panel').prepend(alert);
                                    setTimeout(() => alert.remove(), 3000);
                                }
                            })
                            .catch(error => {
                                const alert = document.createElement('div');
                                alert.className = 'alert alert-error';
                                alert.textContent = 'Lỗi kết nối. Vui lòng thử lại.';
                                document.querySelector('.account-products-panel').prepend(alert);
                                setTimeout(() => alert.remove(), 3000);
                            });
                        }
                    });
                });

                const editButtons = document.querySelectorAll('.js-edit-product');

                editButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const url = button.getAttribute('data-edit-url');

                        if (!url) {
                            return;
                        }

                        window.location.href = url;
                    });
                });
            });
        })();
    </script>
@endpush
