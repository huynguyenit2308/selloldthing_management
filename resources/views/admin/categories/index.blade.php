@extends('dashboard')

@section('content')
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

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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
                                        <a href="#" class="action-btn edit mr-1" title="Sửa">
                                            <i class="fa fa-pencil"></i>
                                            <span class="d-none d-sm-inline">Sửa</span>
                                        </a>
                                        <button type="button" class="action-btn delete" title="Xóa">
                                            <i class="fa fa-trash"></i>
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
@endsection
