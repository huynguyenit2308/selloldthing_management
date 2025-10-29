@extends('dashboard')

@section('content')
<!-- Thêm Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .category-admin main {
        margin-top: 96px;
    }

    .category-admin .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 14px;
        font-weight: 600;
        transition: all .2s ease;
    }

    .category-admin .action-btn i {
        font-size: .85rem;
    }

    .category-admin .action-btn.edit {
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.35);
        background: rgba(37, 99, 235, 0.08);
    }

    .category-admin .action-btn.edit:hover {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .category-admin .action-btn.delete {
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.35);
        background: rgba(220, 38, 38, 0.08);
    }

    .category-admin .action-btn.delete:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
    }

    /* căn giữa phân trang */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 24px;
    }

    /* chỉnh kích thước nút trang */
    .pagination .page-link {
        border-radius: 8px !important;
        margin: 0 3px;
    }

    .pagination .page-item.active .page-link {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #f8f9fa;
    }

    /* Modal xóa */
    .delete-modal .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .delete-modal .modal-header {
        border-bottom: 1px solid #e9ecef;
        padding: 1.5rem;
    }

    .delete-modal .modal-body {
        padding: 1.5rem;
    }

    .delete-modal .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1.5rem;
    }

    .delete-modal .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .delete-modal .btn-danger:disabled {
        background-color: #6c757d;
        border-color: #6c757d;
        cursor: not-allowed;
    }

    /* Loading spinner */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* Đảm bảo icon hiển thị đúng */
    .fas, .far, .fab {
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900;
    }

    .fa-exclamation-triangle:before {
        content: "\f071";
    }

    .fa-check-circle:before {
        content: "\f058";
    }

    .fa-exclamation-circle:before {
        content: "\f06a";
    }

    .fa-pencil:before {
        content: "\f303";
    }

    .fa-trash:before {
        content: "\f1f8";
    }
</style>

<div class="category-admin">
<main class="py-5">
    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-body d-flex align-items-center justify-content-between">
                <h5 class="mb-0 font-weight-bold">QUẢN LÝ DANH MỤC</h5>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <span class="mr-1">&#x2795;</span> Thêm mới
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.categories.index') }}" class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="form-row">
                    <div class="col-md-6 mb-2">
                        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Tìm theo tên danh mục...">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="status" class="form-control">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active" {{ $status==='active' ? 'selected' : '' }}>Hiện</option>
                            <option value="inactive" {{ $status==='inactive' ? 'selected' : '' }}>Ẩn</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 text-right">
                        <button class="btn btn-outline-secondary">Lọc</button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Hiển thị thông báo thành công -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <!-- Hiển thị thông báo lỗi từ xóa -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if($categories->total() === 0)
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <p class="mb-3">Không có danh mục nào</p>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Thêm mới</a>
                </div>
            </div>
        @else
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center" style="width:7%">STT</th>
                                <th class="text-center" style="width:8%">ID</th>
                                <th style="width:12%">Hình ảnh</th>
                                <th style="width:25%">Tên danh mục</th>
                                <th style="width:30%">Mô tả</th>
                                <th class="text-center" style="width:10%">Trạng thái</th>
                                <th class="text-right" style="width:15%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $index => $cat)
                                <tr>
                                    <td class="text-center">{{ ($categories->currentPage() - 1) * $categories->perPage() + $index + 1 }}</td>
                                    <td class="text-center">{{ $cat->id }}</td>
                                    <td>
                                        <div style="width:56px;height:56px;border-radius:12px;background:#eef2ff;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid #e5e7eb;">
                                            @php
                                                $imageUrl = asset('images/product_1.png');
                                                if (!empty($cat->image)) {
                                                    if (filter_var($cat->image, FILTER_VALIDATE_URL)) {
                                                        $imageUrl = $cat->image;
                                                    } elseif (Storage::disk('public')->exists($cat->image)) {
                                                        $imageUrl = Storage::url($cat->image);
                                                    } elseif (file_exists(public_path($cat->image))) {
                                                        $imageUrl = asset($cat->image);
                                                    }
                                                }
                                            @endphp
                                            <img src="{{ $imageUrl }}" alt="thumb" style="width:100%;height:100%;object-fit:cover;">
                                        </div>
                                    </td>
                                    <td><div class="text-truncate" style="max-width: 220px">{{ $cat->name ?? 'N/A' }}</div></td>
                                    <td><div class="text-truncate" style="max-width: 320px" title="{{ $cat->description ?? 'N/A' }}">{{ $cat->description ?? 'N/A' }}</div></td>
                                    <td class="text-center">
                                        @if(in_array($cat->status, [1, '1', true, 'active'], true))
                                            <span class="badge badge-success">Đang hoạt động</span>
                                        @else
                                            <span class="badge badge-secondary">Ngừng hoạt động</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.categories.edit', $cat->id) }}" class="action-btn edit mr-1" title="Sửa">
                                            <i class="fas fa-pencil-alt"></i>
                                            <span class="d-none d-sm-inline">Sửa</span>
                                        </a>
                                        <button type="button" class="action-btn delete delete-category-btn" 
                                                data-category-id="{{ $cat->id }}" 
                                                data-category-name="{{ $cat->name }}"
                                                title="Xóa">
                                            <i class="fas fa-trash"></i>
                                            <span class="d-none d-sm-inline">Xóa</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Phân trang Bootstrap --}}
                <div class="card-footer bg-white">
                    <div class="pagination-wrapper">
                        {{ $categories->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</main>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade delete-modal" id="deleteCategoryModal" tabindex="-1" role="dialog" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-danger" id="deleteCategoryModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Xác nhận xóa
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deleteModalContent">
                    <p>Bạn có chắc chắn muốn xóa danh mục <strong id="categoryNameToDelete"></strong>?</p>
                    <p class="text-muted mb-0">Hành động này không thể hoàn tác.</p>
                </div>
                
                <!-- Hiển thị lỗi trong modal -->
                <div id="deleteModalError" class="alert alert-danger d-none mt-3">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="deleteErrorText"></span>
                </div>

                <!-- Hiển thị thành công trong modal -->
                <div id="deleteModalSuccess" class="alert alert-success d-none mt-3">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="deleteSuccessText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Hủy</button>
                <form id="deleteCategoryForm" method="POST" style="display: inline;">
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
    const deleteModal = document.getElementById('deleteCategoryModal');
    const deleteCategoryForm = document.getElementById('deleteCategoryForm');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteBtnText = document.getElementById('deleteBtnText');
    const deleteSpinner = document.getElementById('deleteSpinner');
    const categoryNameToDelete = document.getElementById('categoryNameToDelete');
    const deleteModalError = document.getElementById('deleteModalError');
    const deleteErrorText = document.getElementById('deleteErrorText');
    const deleteModalSuccess = document.getElementById('deleteModalSuccess');
    const deleteSuccessText = document.getElementById('deleteSuccessText');
    const deleteModalContent = document.getElementById('deleteModalContent');

    let currentCategoryId = null;
    let isDeleting = false;

    // Mở modal xóa
    document.querySelectorAll('.delete-category-btn').forEach(button => {
        button.addEventListener('click', function () {
            const categoryId = this.getAttribute('data-category-id');
            const categoryName = this.getAttribute('data-category-name');
            
            currentCategoryId = categoryId;
            categoryNameToDelete.textContent = categoryName;
            
            // Reset modal state
            resetDeleteModal();
            
            // Cập nhật action form
            deleteCategoryForm.action = `/admin/categories/${categoryId}`;
            
            // Hiển thị modal
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

    // Xử lý submit form xóa
    deleteCategoryForm.addEventListener('submit', function (event) {
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
            
            // Kiểm tra nếu response là JSON
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                
                if (response.ok) {
                    // THÀNH CÔNG: Response 200 với JSON
                    if (data.success) {
                        handleDeleteSuccess(data.message || 'Xóa danh mục thành công!');
                    } else {
                        // Server trả về success: false
                        handleDeleteError(data.error_code, data.message, data.data);
                    }
                } else {
                    // LỖI: Response không ok (4xx, 5xx) nhưng có JSON
                    handleDeleteError(data.error_code, data.message, data.data);
                }
            } else {
                // Response không phải JSON (có thể là redirect hoặc HTML)
                if (response.ok) {
                    // THÀNH CÔNG: Response 200 nhưng không phải JSON (redirect)
                    handleDeleteSuccess('Xóa danh mục thành công!');
                } else {
                    // LỖI: Response không ok và không phải JSON
                    throw new Error('Không thể xử lý phản hồi từ server');
                }
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            // NETWORK_ERROR - Lỗi kết nối server
            showDeleteError('Không thể kết nối đến server. Vui lòng thử lại.');
            setDeletingState(false);
        });
    });

    // Xử lý khi xóa thành công
    function handleDeleteSuccess(message) {
        showDeleteSuccess(message || 'Xóa danh mục thành công!');
        
        // Đóng modal sau 1.5 giây và reload trang
        setTimeout(() => {
            $(deleteModal).modal('hide');
            window.location.reload();
        }, 1500);
    }

    // Xử lý các loại lỗi từ server
    function handleDeleteError(errorCode, message, additionalData = null) {
        switch (errorCode) {
            case 'CATEGORY_ALREADY_DELETED':
                // Danh mục đã bị xóa trước đó
                showDeleteError('Danh mục này đã bị xóa trước đó.');
                setTimeout(() => {
                    $(deleteModal).modal('hide');
                    window.location.reload();
                }, 2000);
                break;
                
            case 'CATEGORY_IN_USE':
                // Danh mục đang được sử dụng
                const productCount = additionalData?.product_count || 0;
                showDeleteError(`Không thể xóa danh mục đang được sử dụng bởi ${productCount} sản phẩm.`);
                setDeletingState(false);
                break;
                
            case 'PERMISSION_DENIED':
                // Không có quyền xóa
                showDeleteError('Bạn không có quyền xóa danh mục này.');
                setTimeout(() => {
                    $(deleteModal).modal('hide');
                }, 3000);
                break;
                
            case 'SERVER_ERROR':
                // Lỗi hệ thống
                showDeleteError('Đã có lỗi xảy ra. Vui lòng thử lại sau.');
                setTimeout(() => {
                    $(deleteModal).modal('hide');
                }, 3000);
                break;
                
            case 'CATEGORY_NOT_FOUND':
                // Danh mục không tồn tại
                showDeleteError('Danh mục không tồn tại hoặc đã bị xóa.');
                setTimeout(() => {
                    $(deleteModal).modal('hide');
                    window.location.reload();
                }, 2000);
                break;
                
            default:
                // Lỗi không xác định
                showDeleteError(message || 'Đã có lỗi xảy ra. Vui lòng thử lại.');
                setDeletingState(false);
        }
    }

    // Reset modal khi đóng
    $(deleteModal).on('hidden.bs.modal', function () {
        resetDeleteModal();
    });
});
</script>
@endsection