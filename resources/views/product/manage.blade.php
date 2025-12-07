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
                            {{-- Preserve other parameters --}}
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            
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
                        @if (isset($productBulkErrors) && is_array($productBulkErrors))
                            <ul class="alert-detail">
                                @foreach ($productBulkErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-error" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if ($accountRestricted)
                        <div class="alert alert-warning" role="status">
                            <p>Tài khoản của bạn đang bị hạn chế. Vui lòng liên hệ hỗ trợ để biết thêm chi tiết.</p>
                        </div>
                    @endif

                    <div class="products-table-wrapper" role="region" aria-label="Danh sách sản phẩm">
                        <div class="products-table" role="table">
                            <div class="table-header" role="row">
                                <div class="cell stt" role="columnheader">STT</div>
                                <div class="cell image" role="columnheader">Hình ảnh</div>
                                <div class="cell info" role="columnheader">Tên sản phẩm</div>
                                <div class="cell price" role="columnheader">Giá</div>
                                <div class="cell stock" role="columnheader">Kho</div>
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
                                    ?? ($product->description ? \Illuminate\Support\Str::limit($product->description, 50) : null);
                                $placeholderText = \Illuminate\Support\Str::limit($product->name ?? 'Sản phẩm', 15, '');
                                $quantity = $product->quantity ?? 0;
                                $stockLabel = $quantity > 0 ? 'Còn hàng' : 'Hết hàng';
                                $stockClass = $quantity > 0 ? 'stock-available' : 'stock-out';
                            @endphp
                            <div class="table-row" role="row">
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
                                        <h3 class="product-title">{{ $product->name }}</h3>
                                    </div>
                                </div>
                                <div class="cell price" role="cell">
                                    <span class="price-value">{{ $priceText }}</span>
                                </div>
                                <div class="cell stock" role="cell">
                                    <span class="stock-status {{ $stockClass }}">
                                        {{ $stockLabel }}
                                    </span>
                                </div>
                                <div class="cell views" role="cell">
                                    <span class="views-value">{{ number_format($product->view_count ?? 0) }}</span>
                                </div>
                                <div class="cell status" role="cell">
                                    <span class="status-tag status-{{ $statusKey }}">{{ $statusText }}</span>
                                </div>
                                <div class="cell actions" role="cell">
                                    <div class="action-buttons">
                                        <button type="button" class="action-btn btn-edit js-edit-product" data-edit-url="{{ route('products.edit', ['product' => $product, 'version' => optional($product->updated_at)->getTimestamp()]) }}" data-product-id="{{ $product->id }}" title="Chỉnh sửa" aria-label="Chỉnh sửa">
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 15.5 15.5 4a2.121 2.121 0 1 1 3 3L7 18.5 3 19.5Z" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="m14.5 5.5 3 3" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                        <button type="button" class="action-btn btn-delete js-delete-product" 
                                                data-product-id="{{ $product->id }}" 
                                                data-product-name="{{ $product->name }}"
                                                data-delete-url="{{ route('products.destroy', $product) }}"
                                                data-check-url="{{ route('products.checkDelete', $product) }}"
                                                title="Xóa" aria-label="Xóa" 
                                                {{ ($accountRestricted) ? 'disabled' : '' }}>
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 7L18.132 19.142A2 2 0 0 1 16.137 21H7.863a2 2 0 0 1-1.995-1.858L5 7" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 11v6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 7h18" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="table-empty" role="row">
                                <div class="empty-content">
                                    <div class="empty-icon">📦</div>
                                    <h3>Chưa có sản phẩm nào</h3>
                                    <p>Bắt đầu bán hàng bằng cách đăng sản phẩm đầu tiên của bạn</p>
                                    <a class="btn-primary" href="{{ route('products.create') }}">Đăng sản phẩm đầu tiên</a>
                                </div>
                            </div>
                        @endforelse
                        </div>
                    </div>
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

    <!-- Delete Confirmation Popups -->
    <div id="deletePopupOverlay" class="popup-overlay" style="display: none;">
        <!-- Standard Confirm Popup -->
        <div id="confirmDeletePopup" class="popup-modal confirm-popup" style="display: none;">
            <div class="popup-header">
                <h3>Xác nhận xóa sản phẩm</h3>
            </div>
            <div class="popup-content">
                <p>Bạn có chắc chắn muốn xóa sản phẩm '<span id="confirmProductName"></span>' không?</p>
            </div>
            <div class="popup-actions">
                <button type="button" class="btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn-danger" id="confirmDeleteBtn">Xóa</button>
            </div>
        </div>

        <!-- Warning Popup -->
        <div id="warningDeletePopup" class="popup-modal warning-popup" style="display: none;">
            <div class="popup-header">
                <span class="warning-icon">⚠️</span>
                <h3>Cảnh báo trước khi xóa</h3>
            </div>
            <div class="popup-content">
                <p>Sản phẩm '<span id="warningProductName"></span>' có các điều kiện cảnh báo:</p>
                <ul id="warningList"></ul>
                <p><strong>Bạn vẫn muốn tiếp tục xóa sản phẩm này?</strong></p>
            </div>
            <div class="popup-actions">
                <button type="button" class="btn-secondary" id="cancelWarningDelete">Hủy</button>
                <button type="button" class="btn-danger" id="proceedWarningDelete">Vẫn xóa</button>
            </div>
        </div>

        <!-- Error Popup -->
        <div id="errorDeletePopup" class="popup-modal error-popup" style="display: none;">
            <div class="popup-header">
                <span class="error-icon">❌</span>
                <h3>Không thể xóa sản phẩm</h3>
            </div>
            <div class="popup-content">
                <p>Sản phẩm '<span id="errorProductName"></span>' không thể xóa vì:</p>
                <ul id="errorList"></ul>
            </div>
            <div class="popup-actions">
                <button type="button" class="btn-secondary" id="closeErrorPopup">Đã hiểu</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toastNotification" class="toast-notification" style="display: none;">
        <div class="toast-content">
            <span class="toast-message"></span>
        </div>
        <button type="button" class="toast-close" id="closeToast">×</button>
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

                
                // Enhanced delete product functionality
                let currentDeleteData = null;

                // Check product existence before action
                async function checkProductExists(checkUrl) {
                    try {
                        const response = await fetch(checkUrl, {
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });
                        
                        if (response.status === 404) {
                            return false;
                        }
                        
                        const data = await response.json();
                        return data && data.exists;
                    } catch (error) {
                        console.error('Lỗi khi kiểm tra sản phẩm:', error);
                        return true; // Assume exists if network error
                    }
                }

                // Edit button click handler
                document.querySelectorAll('.js-edit-product').forEach(function (button) {
                    button.addEventListener('click', async function (e) {
                        e.preventDefault();
                        
                        if (button.disabled) return;
                        
                        const editUrl = button.dataset.editUrl;
                        const productId = button.dataset.productId;
                        const checkUrl = `{{ route('products.checkDelete', ['product' => ':id']) }}`.replace(':id', productId);
                        
                        // Check if product still exists
                        const exists = await checkProductExists(checkUrl);
                        if (!exists) {
                            window.location.href = '{{ route('products.notFound') }}';
                            return;
                        }
                        
                        // Product exists, proceed to edit
                        window.location.href = editUrl;
                    });
                });

                // Delete button click handler
                document.querySelectorAll('.js-delete-product').forEach(function (button) {
                    button.addEventListener('click', async function (e) {
                        e.preventDefault();
                        
                        if (button.disabled) return;
                        
                        const productId = button.dataset.productId;
                        const productName = button.dataset.productName;
                        const checkUrl = button.dataset.checkUrl;
                        const deleteUrl = button.dataset.deleteUrl;
                        
                        // Check if product still exists
                        const exists = await checkProductExists(checkUrl);
                        if (!exists) {
                            window.location.href = '{{ route('products.notFound') }}';
                            return;
                        }
                        
                        currentDeleteData = {
                            productId,
                            productName,
                            deleteUrl,
                            button,
                            row: button.closest('.table-row')
                        };
                        
                        // Show loading state
                        button.style.opacity = '0.6';
                        button.style.pointerEvents = 'none';
                        
                        // Check delete conditions
                        fetch(checkUrl, {
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => {
                            if (response.status === 404) {
                                // Sản phẩm đã bị xóa ở tab khác
                                button.style.opacity = '';
                                button.style.pointerEvents = '';
                                showAlert('error', 'Sản phẩm không còn tồn tại. Trang sẽ được tải lại.');
                                setTimeout(() => window.location.reload(), 1500);
                                return null;
                            }

                            return response.json();
                        })
                        .then(data => {
                            if (!data) {
                                return;
                            }

                            // Reset button state
                            button.style.opacity = '';
                            button.style.pointerEvents = '';

                            if (data.canDelete) {
                                if (data.warnings && data.warnings.length > 0) {
                                    showWarningPopup(productName, data.warnings);
                                } else {
                                    showConfirmPopup(productName);
                                }
                            } else {
                                showErrorPopup(productName, data.errors || ['Không thể xóa sản phẩm']);
                            }
                        })
                        .catch(error => {
                            // Reset button state
                            button.style.opacity = '';
                            button.style.pointerEvents = '';

                            showErrorPopup(productName, ['Không thể kiểm tra điều kiện xóa. Vui lòng thử lại sau.']);
                        });
                    });
                });

                // Popup functions
                function showConfirmPopup(productName) {
                    document.getElementById('confirmProductName').textContent = productName;
                    document.getElementById('deletePopupOverlay').style.display = 'flex';
                    document.getElementById('confirmDeletePopup').style.display = 'block';
                }

                function showWarningPopup(productName, warnings) {
                    document.getElementById('warningProductName').textContent = productName;
                    const warningList = document.getElementById('warningList');
                    warningList.innerHTML = '';
                    warnings.forEach(warning => {
                        const li = document.createElement('li');
                        li.textContent = warning;
                        warningList.appendChild(li);
                    });
                    document.getElementById('deletePopupOverlay').style.display = 'flex';
                    document.getElementById('warningDeletePopup').style.display = 'block';
                }

                function showErrorPopup(productName, errors) {
                    document.getElementById('errorProductName').textContent = productName;
                    const errorList = document.getElementById('errorList');
                    errorList.innerHTML = '';
                    errors.forEach(error => {
                        const li = document.createElement('li');
                        li.textContent = error;
                        errorList.appendChild(li);
                    });
                    document.getElementById('deletePopupOverlay').style.display = 'flex';
                    document.getElementById('errorDeletePopup').style.display = 'block';
                }

                function hideAllPopups() {
                    document.getElementById('deletePopupOverlay').style.display = 'none';
                    document.getElementById('confirmDeletePopup').style.display = 'none';
                    document.getElementById('warningDeletePopup').style.display = 'none';
                    document.getElementById('errorDeletePopup').style.display = 'none';
                }

                function performDelete() {
                    if (!currentDeleteData) return;

                    fetch(currentDeleteData.deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => {
                        if (response.status === 404) {
                            // Sản phẩm đã bị xóa ở tab khác
                            showAlert('error', 'Sản phẩm không còn tồn tại. Trang sẽ được tải lại.');
                            setTimeout(() => window.location.reload(), 1500);
                            return null;
                        }

                        return response.json();
                    })
                    .then(data => {
                        if (!data) {
                            return;
                        }

                        if (data.success) {
                            // Hide row with animation
                            currentDeleteData.row.style.opacity = '0.5';
                            currentDeleteData.row.style.pointerEvents = 'none';

                            setTimeout(() => {
                                currentDeleteData.row.style.display = 'none';
                                showSuccessToast(data.message);
                            }, 300);
                        } else {
                            showAlert('error', data.message || 'Có lỗi xảy ra khi xóa sản phẩm');
                        }
                    })
                    .catch(error => {
                        showAlert('error', 'Lỗi kết nối. Vui lòng thử lại.');
                    });
                }

                function showSuccessToast(message) {
                    const toast = document.getElementById('toastNotification');
                    const messageEl = toast.querySelector('.toast-message');
                    
                    messageEl.textContent = message;
                    toast.style.display = 'block';
                    
                    // Auto-hide toast after 10 seconds
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 10000);
                }

                function showAlert(type, message) {
                    const alert = document.createElement('div');
                    alert.className = `alert alert-${type}`;
                    alert.textContent = message;
                    document.querySelector('.account-products-panel').prepend(alert);
                    setTimeout(() => alert.remove(), 5000);
                }

                // Event listeners for popup buttons
                document.getElementById('cancelDelete').addEventListener('click', hideAllPopups);
                document.getElementById('cancelWarningDelete').addEventListener('click', hideAllPopups);
                document.getElementById('closeErrorPopup').addEventListener('click', hideAllPopups);
                
                document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
                    hideAllPopups();
                    performDelete();
                });
                
                document.getElementById('proceedWarningDelete').addEventListener('click', () => {
                    hideAllPopups();
                    performDelete();
                });

                // Close toast
                document.getElementById('closeToast').addEventListener('click', () => {
                    document.getElementById('toastNotification').style.display = 'none';
                    if (undoTimeout) {
                        clearTimeout(undoTimeout);
                        undoTimeout = null;
                    }
                });

                // Close popup when clicking overlay
                document.getElementById('deletePopupOverlay').addEventListener('click', (e) => {
                    if (e.target === e.currentTarget) {
                        hideAllPopups();
                    }
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

                // Auto-submit form when sort option changes
                const sortSelect = document.getElementById('sort');
                if (sortSelect) {
                    sortSelect.addEventListener('change', function() {
                        this.closest('form').submit();
                    });
                }
            });
        })();
    </script>
@endpush
