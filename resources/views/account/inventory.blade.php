@extends('dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/account_inventory.css') }}">
@endpush

@section('body-class', 'account-inventory-page')

@section('content')
    <div class="inventory-page"
         data-max-stock="{{ $maxStock }}"
         data-low-stock="{{ $lowStockThreshold }}"
         data-bulk-url="{{ route('account.inventory.bulk') }}">
        <div class="container">
            <nav class="inventory-breadcrumb" aria-label="breadcrumb">
                <ol>
                    <li>
                        <a href="{{ url('/') }}">Trang chủ</a>
                    </li>
                    <li>
                        <span>Tài khoản</span>
                    </li>
                    <li aria-current="page">
                        <span class="is-active">Quản lý kho</span>
                    </li>
                </ol>
            </nav>

            <header class="inventory-header">
                <div class="header-title">
                    <h1>Quản lý số lượng tồn kho</h1>
                    @if (session('inventory_export_error'))
                        <div class="alert alert-error" role="alert">{{ session('inventory_export_error') }}</div>
                    @endif
                </div>
                <div class="header-actions">
                    <button type="button" class="btn-secondary js-refresh" data-action="refresh">Làm mới</button>
                    <a href="{{ route('account.inventory.export', ['format' => 'xlsx']) }}" class="btn-secondary">Xuất báo cáo</a>
                    <button type="button" class="btn-primary js-bulk-save" data-action="bulk-save">Lưu tất cả</button>
                </div>
            </header>

            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <strong>Không thể tải danh sách sản phẩm.</strong>
                    @foreach ($errors->all() as $error)
                        <p class="mb-0">- {{ $error }}</p>
                    @endforeach
                    <p class="mt-2">Vui lòng quay lại trang đầu hoặc nhập lại số trang hợp lệ.</p>
                    <a class="btn-outline" href="{{ route('account.inventory') }}">Quay về trang 1</a>
                </div>
            @endif

            <section class="inventory-overview" aria-label="Tổng quan tồn kho">
                <div class="overview-grid">
                    @foreach ($statCards as $card)
                        <button type="button"
                                class="overview-card {{ $statusFilter === $card['status'] ? 'is-active' : '' }}"
                                data-status="{{ $card['status'] }}"
                                data-value="{{ $card['value'] }}">
                            <span class="label">{{ $card['label'] }}</span>
                            <span class="value">{{ $card['display_value'] }}</span>
                        </button>
                    @endforeach
                </div>
            </section>

            <section class="inventory-panel">
                <header class="panel-toolbar">
                    <form method="GET" action="{{ route('account.inventory') }}" class="toolbar-form" id="inventoryFilterForm">
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                        <div class="form-group">
                            <label for="inventorySort">Sắp xếp theo</label>
                            <select id="inventorySort" name="sort">
                                @foreach ($sortOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedSort === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="toolbar-actions">
                            <button type="submit" class="btn-outline">Lọc</button>
                            <button type="button" class="btn-primary js-bulk-save" data-action="bulk-save">Lưu tất cả</button>
                        </div>
                    </form>
                </header>

                <div class="panel-body">
                    <div class="table-wrapper" role="region" aria-live="polite">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th scope="col">Hình ảnh</th>
                                    <th scope="col">Tên sản phẩm</th>
                                    <th scope="col">Giá</th>
                                    <th scope="col">Tồn kho</th>
                                    <th scope="col">Đã bán</th>
                                    <th scope="col">Trạng thái</th>
                                    <th scope="col">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr class="inventory-row"
                                        data-product-id="{{ $product['id'] }}"
                                        data-update-url="{{ route('account.inventory.update', $product['id']) }}"
                                        data-original-quantity="{{ $product['quantity'] }}">
                                        <td data-title="Hình ảnh">
                                            @php
                                                $imageUrl = $product['image_url'] ?? null;
                                            @endphp
                                            <div class="product-thumb {{ $imageUrl ? 'has-image' : 'is-placeholder' }}">
                                                @if ($imageUrl)
                                                    <img src="{{ $imageUrl }}"
                                                         alt="{{ $product['name'] }}"
                                                         onerror="this.closest('.product-thumb').classList.replace('has-image', 'is-placeholder'); this.remove();">
                                                @endif
                                                <span class="placeholder">Ảnh</span>
                                            </div>
                                        </td>
                                        <td data-title="Tên sản phẩm">
                                            <div class="product-info">
                                                <strong class="name">{{ $product['name'] }}</strong>
                                                @if ($product['description'])
                                                    <p class="description">{{ $product['description'] }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td data-title="Giá">
                                            <span class="price">{{ $product['price_formatted'] }}</span>
                                        </td>
                                        <td data-title="Tồn kho">
                                            <div class="stock-editor" data-quantity="{{ $product['quantity'] }}">
                                                <button type="button" class="qty-btn" data-change="-1" aria-label="Giảm tồn kho">&minus;</button>
                                                <input type="number" class="qty-input" min="0" value="{{ $product['quantity'] }}">
                                                <button type="button" class="qty-btn" data-change="1" aria-label="Tăng tồn kho">+</button>
                                            </div>
                                            <p class="input-feedback" role="alert"></p>
                                        </td>
                                        <td data-title="Đã bán"><span class="sold">{{ number_format($product['sold']) }}</span></td>
                                        <td data-title="Trạng thái">
                                            <span class="status-badge status-{{ $product['inventory_status']['key'] }}">
                                                {{ $product['inventory_status']['label'] }}
                                            </span>
                                        </td>
                                        <td data-title="Thao tác">
                                            <div class="row-actions">
                                                <button type="button"
                                                        class="btn-secondary btn-icon js-save-row"
                                                        data-action="save-row"
                                                        aria-label="Lưu thay đổi">
                                                    <i class="fa fa-floppy-o" aria-hidden="true"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn-outline btn-icon js-reset-row"
                                                        data-action="reset-row"
                                                        aria-label="Đặt lại">
                                                    <i class="fa fa-undo" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            <div class="empty-card">
                                                <span class="icon" aria-hidden="true">📦</span>
                                                <p>Chưa có sản phẩm nào trong kho.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($paginator->hasPages())
                        <nav class="inventory-pagination" aria-label="Pagination">
                            <ul>
                                <li class="{{ $paginator->onFirstPage() ? 'is-disabled' : '' }}">
                                    <a href="{{ $paginator->previousPageUrl() ?? '#' }}" aria-label="Trang trước">&lt;</a>
                                </li>
                                @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                                    <li class="{{ $page === $paginator->currentPage() ? 'is-active' : '' }}">
                                        <a href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endforeach
                                <li class="{{ $paginator->hasMorePages() ? '' : 'is-disabled' }}">
                                    <a href="{{ $paginator->nextPageUrl() ?? '#' }}" aria-label="Trang sau">&gt;</a>
                                </li>
                            </ul>
                        </nav>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <div class="toast" role="status" aria-live="assertive" aria-atomic="true" hidden>
        <div class="toast-content">
            <span class="toast-message"></span>
            <button type="button" class="toast-close" aria-label="Đóng">×</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/account_inventory.js') }}" defer></script>
@endpush
