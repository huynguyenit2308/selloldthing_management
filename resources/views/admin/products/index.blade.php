@extends('dashboard')

@section('content')
<!-- Thêm Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .product-admin main {
        margin-top: 120px;
        background: #f3f4f6;
        min-height: calc(100vh - 120px);
        padding-bottom: 48px;
    }

    .product-admin .product-admin-container {
        width: 100%;
        max-width: none;
        padding: 0 36px;
        margin: 0 auto;
    }

    @media (max-width: 992px) {
        .product-admin .product-admin-container {
            padding: 0 18px;
        }
    }

    .product-admin .page-header,
    .product-admin .filter-card,
    .product-admin .data-card {
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 16px 32px -20px rgba(15, 23, 42, 0.35);
    }

    .product-admin .page-header {
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .product-admin .page-header h5 {
        margin-bottom: 6px;
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
    }

    .product-admin .page-header .pending-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .product-admin .page-header .pending-indicator i {
        color: #f59e0b;
    }

    .product-admin .filter-card {
        padding: 22px 24px;
        margin-bottom: 28px;
    }

    .product-admin .filter-card .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin: 0;
    }

    .product-admin .filter-card .form-row > * {
        flex: 1;
        min-width: 180px;
    }

    .product-admin .filter-card .form-control,
    .product-admin .filter-card .btn {
        border-radius: 12px;
        height: 44px;
        border: 1px solid #e5e7eb;
        box-shadow: none;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .product-admin .filter-card .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .product-admin .filter-card .btn {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        border: none;
        font-weight: 600;
    }

    .product-admin .filter-card .btn:hover {
        background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    }

    .product-admin .data-card {
        overflow-x: auto;
        overflow-y: hidden;
    }

    .product-admin .table-responsive {
        min-width: 1000px;
    }

    .product-admin .table {
        margin: 0;
        background: transparent;
    }

    .product-admin .table thead th {
        background: #111827;
        color: #f9fafb;
        border: none;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 14px 16px;
    }

    .product-admin .table tbody tr {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .product-admin .table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px -24px rgba(15, 23, 42, 0.45);
    }

    .product-admin .table td {
        vertical-align: middle;
        padding: 16px 18px;
        border-top: none;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.95rem;
        color: #1f2937;
    }

    .product-image-thumb {
        width: 62px;
        height: 62px;
        border-radius: 18px;
        background: linear-gradient(145deg, #eef2ff, #e0e7ff);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid rgba(99, 102, 241, 0.15);
        box-shadow: inset 0 6px 12px rgba(99, 102, 241, 0.08);
    }

    .product-image-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: inherit;
    }

    .product-admin .product-name {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 4px;
        color: #111827;
    }

    .product-admin .seller-meta {
        font-size: 0.82rem;
        color: #6b7280;
    }

    .product-admin .price-display {
        font-weight: 700;
        color: #dc2626;
        font-size: 1rem;
    }

    .product-admin .original-price {
        text-decoration: line-through;
        color: #9ca3af;
        font-size: 0.8rem;
    }

    .product-admin .badge {
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.78rem;
    }

    .badge-pending {
        background: rgba(245, 158, 11, 0.18);
        color: #b45309;
    }

    .badge-published {
        background: rgba(16, 185, 129, 0.18);
        color: #047857;
    }

    .badge-hidden {
        background: rgba(107, 114, 128, 0.18);
        color: #374151;
    }

    .badge-approved {
        background: rgba(59, 130, 246, 0.18);
        color: #1d4ed8;
    }

    .badge-featured {
        background: rgba(139, 92, 246, 0.18);
        color: #5b21b6;
    }

    .badge-secondary {
        background: rgba(107, 114, 128, 0.18);
        color: #374151;
    }

    .badge-danger {
        background: rgba(248, 113, 113, 0.18);
        color: #b91c1c;
    }

    .badge-success {
        background: rgba(34, 197, 94, 0.18);
        color: #166534;
    }

    .actions-cell {
        min-width: 320px;
    }

    .action-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 8px;
    }

    .action-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 9px 14px;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        font-size: 0.85rem;
        letter-spacing: 0.01em;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        color: #111827;
        background: #f3f4f6;
        box-shadow: inset 0 -2px 0 rgba(17, 24, 39, 0.08);
    }

    .action-chip i {
        font-size: 0.9rem;
    }

    .action-chip:hover,
    .action-chip:focus {
        transform: translateY(-1px);
        box-shadow: 0 10px 18px -12px rgba(17, 24, 39, 0.3);
        text-decoration: none;
        color: inherit;
    }

    .action-chip--primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #ffffff;
        box-shadow: inset 0 -2px 0 rgba(17, 24, 39, 0.15);
    }

    .action-chip--success {
        background: linear-gradient(135deg, #059669, #047857);
        color: #ffffff;
    }

    .action-chip--warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #ffffff;
    }

    .action-chip--danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #ffffff;
    }

    .action-chip--info {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: #ffffff;
    }

    .action-chip--neutral {
        background: #e5e7eb;
        color: #111827;
    }

    .action-chip--outline {
        border: 1px solid #d1d5db;
        background: #ffffff;
    }

    .action-chip.dropdown-toggle::after {
        margin-left: auto;
    }

    .action-chip-group .dropdown-menu {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 16px 32px -20px rgba(15, 23, 42, 0.35);
        padding: 8px;
    }

    .action-chip-group .dropdown-item {
        border-radius: 10px;
        padding: 8px 12px;
        font-weight: 500;
    }

    .action-chip-group .dropdown-item:hover {
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
    }

    .pagination-wrapper {
        display: flex;
        justify-content: center;
        padding: 20px 16px 28px;
        background: #ffffff;
    }

    .pagination .page-link {
        border-radius: 10px !important;
        margin: 0 4px;
        color: #1f2937;
        font-weight: 600;
        border: none;
    }

    .pagination .page-link:focus {
        box-shadow: none;
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
    }

    .pagination .page-item.disabled .page-link {
        background: #f3f4f6;
        color: #9ca3af;
    }

    .delete-modal .modal-content {
        border: none;
        border-radius: 18px;
        box-shadow: 0 22px 44px -28px rgba(15, 23, 42, 0.45);
    }

    .delete-modal .modal-header,
    .delete-modal .modal-body,
    .delete-modal .modal-footer {
        padding: 1.75rem;
    }

    .delete-modal .modal-header {
        border-bottom: none;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .delete-modal .modal-footer {
        border-top: none;
    }

    .delete-modal .btn-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: none;
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 600;
    }

    .delete-modal .btn-outline-secondary {
        border-radius: 12px;
        padding: 10px 18px;
        font-weight: 600;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    @media (max-width: 1200px) {
        .product-admin .table td {
            padding: 14px 12px;
        }

        .actions-cell {
            min-width: 260px;
        }

        .action-list {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }

        .action-chip {
            min-width: auto;
        }
    }

    @media (max-width: 992px) {
        .product-admin .filter-card .form-row > * {
            min-width: 100%;
        }

        .product-admin .table thead {
            display: none;
        }

        .product-admin .table tbody tr {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            padding: 18px;
            border-radius: 16px;
            margin-bottom: 16px;
        }

        .product-admin .table tbody td {
            border-bottom: none !important;
            padding: 0;
        }

        .product-admin .table tbody td::before {
            content: attr(data-label);
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 6px;
            letter-spacing: 0.05em;
        }

        .action-list {
            grid-template-columns: 1fr;
        }

        .product-admin .table tbody tr:hover {
            transform: none;
            box-shadow: none;
        }
    }
</style>

<div class="product-admin">
<main class="py-5">
    <div class="product-admin-container">
        <div class="page-header mb-4">
            <div>
                <h5>Quản lý sản phẩm</h5>
                @if($pendingCount > 0)
                    <div class="pending-indicator">
                        <i class="fas fa-hourglass-half"></i>
                        {{ $pendingCount }} sản phẩm chờ duyệt
                    </div>
                @endif
            </div>
            <a href="{{ route('admin.products.create') }}" class="action-chip action-chip--primary" style="max-width: 160px;">
                <i class="fas fa-plus"></i> Thêm mới
            </a>
        </div>

        <form method="GET" action="{{ route('admin.products.index') }}" class="filter-card mb-4">
            <div class="form-row">
                <div>
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Tìm theo tên sản phẩm...">
                </div>
                <div>
                    <select name="category_id" class="form-control">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status" class="form-control">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ $status==='pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="published" {{ $status==='published' ? 'selected' : '' }}>Đang bán</option>
                        <option value="hidden" {{ $status==='hidden' ? 'selected' : '' }}>Đã ẩn</option>
                        <option value="sold" {{ $status==='sold' ? 'selected' : '' }}>Đã bán</option>
                    </select>
                </div>
                <div>
                    <select name="approval_status" class="form-control">
                        <option value="">Tất cả duyệt</option>
                        <option value="pending" {{ $approval_status==='pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ $approval_status==='approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="featured" {{ $approval_status==='featured' ? 'selected' : '' }}>Nổi bật</option>
                    </select>
                </div>
                <div style="max-width: 120px;">
                    <button class="btn btn-block">Lọc</button>
                </div>
            </div>
        </form>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if($products->total() === 0)
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <p class="mb-3">Không có sản phẩm nào</p>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Thêm mới</a>
                </div>
            </div>
        @else
            <div class="data-card">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center" style="width:4%">STT</th>
                                <th class="text-center" style="width:5%">ID</th>
                                <th style="width:7%">Hình</th>
                                <th style="width:15%">Tên sản phẩm</th>
                                <th style="width:10%">Danh mục</th>
                                <th class="text-right" style="width:8%">Giá</th>
                                <th class="text-center" style="width:5%">SL</th>
                                <th class="text-center" style="width:8%">Trạng thái</th>
                                <th class="text-center" style="width:8%">Duyệt</th>
                                <th class="text-center" style="width:6%">NB</th>
                                <th class="text-right" style="width:24%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $index => $product)
                                <tr>
                                    <td class="text-center">{{ ($products->currentPage() - 1) * $products->perPage() + $index + 1 }}</td>
                                    <td class="text-center" data-label="ID">{{ $product->id }}</td>
                                    <td data-label="Hình">
                                        <div class="product-image-thumb">
                                            @php
                                                $firstImage = $product->images->first();
                                                $imageUrl = $firstImage ? $firstImage->image_url : asset('images/product_1.png');
                                            @endphp
                                            <img src="{{ $imageUrl }}" alt="thumb">
                                        </div>
                                    </td>
                                    <td data-label="Tên sản phẩm">
                                        <div class="product-name" title="{{ $product->name }}">
                                            {{ $product->name ?? 'N/A' }}
                                        </div>
                                        <div class="seller-meta">
                                            <i class="fas fa-user"></i> {{ $product->user->name ?? 'N/A' }}
                                        </div>
                                        <div class="seller-meta">
                                            <i class="fas fa-envelope"></i> {{ $product->user->email ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td data-label="Danh mục">
                                        <div class="text-truncate" style="max-width: 140px">
                                            {{ $product->category->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="text-right" data-label="Giá">
                                        <div class="price-display">{{ number_format($product->price, 0, ',', '.') }}đ</div>
                                        @if($product->original_price && $product->original_price > $product->price)
                                            <div class="original-price">{{ number_format($product->original_price, 0, ',', '.') }}đ</div>
                                        @endif
                                    </td>
                                    <td class="text-center" data-label="SL">
                                        <span class="badge {{ $product->quantity > 0 ? 'badge-success' : 'badge-danger' }}">
                                            {{ $product->quantity }}
                                        </span>
                                    </td>
                                    <td class="text-center" data-label="Trạng thái">
                                        @if($product->status === 'pending')
                                            <span class="badge badge-pending">Chờ duyệt</span>
                                        @elseif($product->status === 'published')
                                            <span class="badge badge-published">Đang bán</span>
                                        @elseif($product->status === 'sold')
                                            <span class="badge badge-secondary">Đã bán</span>
                                        @else
                                            <span class="badge badge-hidden">Đã ẩn</span>
                                        @endif
                                    </td>
                                    <td class="text-center" data-label="Duyệt">
                                        @if($product->is_approved)
                                            <span class="badge badge-approved">Đã duyệt</span>
                                            @if($product->approver)
                                                <br><small class="text-muted">{{ $product->approver->name }}</small>
                                            @endif
                                        @else
                                            <span class="badge badge-pending">Chờ duyệt</span>
                                        @endif
                                    </td>
                                    <td class="text-center" data-label="Nổi bật">
                                        @if($product->is_featured)
                                            <span class="badge badge-featured">Nổi bật</span>
                                            @if($product->featured_until)
                                                <br><small class="text-muted">{{ $product->featured_until->format('d/m/Y') }}</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="Thao tác">
                                        <div class="action-list">
                                            @if(!$product->is_approved && $product->status === 'pending')
                                                <button type="button"
                                                        class="action-chip action-chip--success approve-btn"
                                                        data-product-id="{{ $product->id }}"
                                                        data-product-name="{{ $product->name }}">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>
                                                <button type="button"
                                                        class="action-chip action-chip--danger reject-btn"
                                                        data-product-id="{{ $product->id }}"
                                                        data-product-name="{{ $product->name }}">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            @endif

                                            <button type="button"
                                                    class="action-chip action-chip--warning feature-btn {{ $product->is_featured ? 'active' : '' }}"
                                                    data-product-id="{{ $product->id }}"
                                                    data-is-featured="{{ $product->is_featured ? '1' : '0' }}">
                                                <i class="fas fa-star"></i> {{ $product->is_featured ? 'Bỏ ghim' : 'Ghim nổi bật' }}
                                            </button>

                                            <div class="action-chip-group dropdown">
                                                <button class="action-chip action-chip--neutral dropdown-toggle" type="button" data-toggle="dropdown">
                                                    <i class="fas fa-cog"></i> Trạng thái
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item status-change" href="#" data-product-id="{{ $product->id }}" data-status="published">Đang bán</a>
                                                    <a class="dropdown-item status-change" href="#" data-product-id="{{ $product->id }}" data-status="hidden">Ẩn</a>
                                                    <a class="dropdown-item status-change" href="#" data-product-id="{{ $product->id }}" data-status="sold">Đã bán</a>
                                                </div>
                                            </div>

                                            <button type="button"
                                                    class="action-chip action-chip--info extend-btn"
                                                    data-product-id="{{ $product->id }}">
                                                <i class="fas fa-clock"></i> Gia hạn
                                            </button>

                                            <a href="{{ route('admin.products.edit', $product->id) }}"
                                               class="action-chip action-chip--primary">
                                                <i class="fas fa-edit"></i> Sửa
                                            </a>

                                            <button type="button"
                                                    class="action-chip action-chip--danger delete-product-btn"
                                                    data-product-id="{{ $product->id }}"
                                                    data-product-name="{{ $product->name }}">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white">
                    <div class="pagination-wrapper">
                        {{ $products->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</main>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade delete-modal" id="deleteProductModal" tabindex="-1" role="dialog" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-danger" id="deleteProductModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Xác nhận xóa
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deleteModalContent">
                    <p>Bạn có chắc chắn muốn xóa sản phẩm <strong id="productNameToDelete"></strong>?</p>
                    <p class="text-muted mb-0">Hành động này không thể hoàn tác.</p>
                </div>
                
                <div id="deleteModalError" class="alert alert-danger d-none mt-3">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="deleteErrorText"></span>
                </div>

                <div id="deleteModalSuccess" class="alert alert-success d-none mt-3">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="deleteSuccessText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Hủy</button>
                <form id="deleteProductForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                        <span id="deleteBtnText">Có, xóa ngay</span>
                        <div id="deleteSpinner" class="spinner-border spinner-border-sm d-none" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteProductModal');
    const deleteProductForm = document.getElementById('deleteProductForm');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteBtnText = document.getElementById('deleteBtnText');
    const deleteSpinner = document.getElementById('deleteSpinner');
    const productNameToDelete = document.getElementById('productNameToDelete');
    const deleteModalError = document.getElementById('deleteModalError');
    const deleteErrorText = document.getElementById('deleteErrorText');
    const deleteModalSuccess = document.getElementById('deleteModalSuccess');
    const deleteSuccessText = document.getElementById('deleteSuccessText');
    const deleteModalContent = document.getElementById('deleteModalContent');

    let currentProductId = null;
    let isDeleting = false;

    document.querySelectorAll('.delete-product-btn').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            
            currentProductId = productId;
            productNameToDelete.textContent = productName;
            
            resetDeleteModal();
            deleteProductForm.action = `/admin/products/${productId}`;
            $(deleteModal).modal('show');
        });
    });

    function resetDeleteModal() {
        deleteModalError.classList.add('d-none');
        deleteModalSuccess.classList.add('d-none');
        deleteModalContent.classList.remove('d-none');
        confirmDeleteBtn.disabled = false;
        deleteBtnText.textContent = 'Có, xóa ngay';
        deleteSpinner.classList.add('d-none');
        isDeleting = false;
    }

    function showDeleteError(message) {
        deleteErrorText.textContent = message;
        deleteModalError.classList.remove('d-none');
        deleteModalContent.classList.add('d-none');
        deleteModalSuccess.classList.add('d-none');
    }

    function showDeleteSuccess(message) {
        deleteSuccessText.textContent = message;
        deleteModalSuccess.classList.remove('d-none');
        deleteModalContent.classList.add('d-none');
        deleteModalError.classList.add('d-none');
    }

    function setDeletingState(deleting) {
        isDeleting = deleting;
        confirmDeleteBtn.disabled = deleting;
        deleteBtnText.classList.toggle('d-none', deleting);
        deleteSpinner.classList.toggle('d-none', !deleting);
    }

    deleteProductForm.addEventListener('submit', function (event) {
        event.preventDefault();
        
        if (isDeleting) return;
        
        setDeletingState(true);
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                _method: 'DELETE'
            })
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                
                if (response.ok) {
                    if (data.success) {
                        handleDeleteSuccess(data.message || 'Xóa sản phẩm thành công!');
                    } else {
                        handleDeleteError(data.error_code, data.message, data.data);
                    }
                } else {
                    handleDeleteError(data.error_code, data.message, data.data);
                }
            } else {
                if (response.ok) {
                    handleDeleteSuccess('Xóa sản phẩm thành công!');
                } else {
                    throw new Error('Không thể xử lý phản hồi từ server');
                }
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showDeleteError('Không thể kết nối đến server. Vui lòng thử lại.');
            setDeletingState(false);
        });
    });

    function handleDeleteSuccess(message) {
        showDeleteSuccess(message || 'Xóa sản phẩm thành công!');
        setTimeout(() => {
            $(deleteModal).modal('hide');
            window.location.reload();
        }, 1500);
    }

    function handleDeleteError(errorCode, message, additionalData = null) {
        switch (errorCode) {
            case 'PRODUCT_IN_USE':
                const orderCount = additionalData?.order_count || 0;
                showDeleteError(`Không thể xóa sản phẩm đang có trong ${orderCount} đơn hàng.`);
                setDeletingState(false);
                break;
                
            case 'PERMISSION_DENIED':
                showDeleteError('Bạn không có quyền xóa sản phẩm này.');
                setTimeout(() => $(deleteModal).modal('hide'), 3000);
                break;
                
            case 'SERVER_ERROR':
                showDeleteError('Đã có lỗi xảy ra. Vui lòng thử lại sau.');
                setTimeout(() => $(deleteModal).modal('hide'), 3000);
                break;
                
            case 'PRODUCT_NOT_FOUND':
                showDeleteError('Sản phẩm không tồn tại hoặc đã bị xóa.');
                setTimeout(() => {
                    $(deleteModal).modal('hide');
                    window.location.reload();
                }, 2000);
                break;
                
            default:
                showDeleteError(message || 'Đã có lỗi xảy ra. Vui lòng thử lại.');
                setDeletingState(false);
        }
    }

    $(deleteModal).on('hidden.bs.modal', function () {
        resetDeleteModal();
    });

    // Approve product
    document.querySelectorAll('.approve-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            
            if (confirm(`Bạn có chắc chắn muốn duyệt sản phẩm "${productName}"?`)) {
                fetch(`/admin/products/${productId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Không thể kết nối đến server');
                });
            }
        });
    });

    // Reject product
    document.querySelectorAll('.reject-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            
            const reason = prompt(`Nhập lý do từ chối sản phẩm "${productName}":`);
            if (reason && reason.trim()) {
                fetch(`/admin/products/${productId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ rejection_reason: reason })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Không thể kết nối đến server');
                });
            }
        });
    });

    // Toggle featured
    document.querySelectorAll('.feature-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const isFeatured = this.getAttribute('data-is-featured') === '1';
            
            let days = 7;
            if (!isFeatured) {
                const input = prompt('Ghim sản phẩm nổi bật trong bao nhiêu ngày?', '7');
                if (!input) return;
                days = parseInt(input);
            }
            
            fetch(`/admin/products/${productId}/toggle-featured`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ days: days })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Không thể kết nối đến server');
            });
        });
    });

    // Change status
    document.querySelectorAll('.status-change').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const status = this.getAttribute('data-status');
            
            fetch(`/admin/products/${productId}/update-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Không thể kết nối đến server');
            });
        });
    });

    // Extend expiration
    document.querySelectorAll('.extend-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const days = prompt('Gia hạn bao nhiêu ngày?', '30');
            
            if (days && parseInt(days) > 0) {
                fetch(`/admin/products/${productId}/extend`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ days: parseInt(days) })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Không thể kết nối đến server');
                });
            }
        });
    });
});
</script>
@endsection
