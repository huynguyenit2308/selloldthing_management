@extends('dashboard')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .product-create main {
        margin-top: 96px;
    }

    .form-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .form-section h6 {
        font-weight: 700;
        margin-bottom: 20px;
        color: #1f2937;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 10px;
    }

    .image-preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 12px;
    }

    .image-preview {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-preview .remove-image {
        position: absolute;
        top: 4px;
        right: 4px;
        background: #dc2626;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
    }

    .image-preview .remove-image:hover {
        background: #b91c1c;
    }

    .custom-file-upload {
        display: inline-block;
        padding: 10px 20px;
        cursor: pointer;
        background: #2563eb;
        color: white;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .custom-file-upload:hover {
        background: #1d4ed8;
    }

    .custom-file-upload input[type="file"] {
        display: none;
    }
</style>

<div class="product-create">
<main class="py-5">
    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-body d-flex align-items-center justify-content-between">
                <h5 class="mb-0 font-weight-bold">THÊM SẢN PHẨM MỚI</h5>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Quay lại
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Có lỗi xảy ra:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Thông tin cơ bản -->
            <div class="form-section">
                <h6><i class="fas fa-info-circle mr-2"></i>Thông tin cơ bản</h6>
                
                <div class="form-group">
                    <label for="name">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category_id">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                <option value="hidden" {{ old('status') == 'hidden' ? 'selected' : '' }}>Đã ẩn</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="short_description">Mô tả ngắn</label>
                    <textarea class="form-control" id="short_description" name="short_description" rows="2" maxlength="500">{{ old('short_description') }}</textarea>
                    <small class="form-text text-muted">Tối đa 500 ký tự</small>
                </div>

                <div class="form-group">
                    <label for="description">Mô tả chi tiết</label>
                    <textarea class="form-control" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Giá và số lượng -->
            <div class="form-section">
                <h6><i class="fas fa-dollar-sign mr-2"></i>Giá và số lượng</h6>
                
                <div class="form-row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="price">Giá bán <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" min="0" step="1000" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="original_price">Giá gốc</label>
                            <input type="number" class="form-control" id="original_price" name="original_price" value="{{ old('original_price') }}" min="0" step="1000">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="quantity">Số lượng <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin bổ sung -->
            <div class="form-section">
                <h6><i class="fas fa-plus-circle mr-2"></i>Thông tin bổ sung</h6>
                
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="condition">Tình trạng</label>
                            <select class="form-control" id="condition" name="condition">
                                <option value="">-- Chọn tình trạng --</option>
                                <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>Mới</option>
                                <option value="like_new" {{ old('condition') == 'like_new' ? 'selected' : '' }}>Như mới</option>
                                <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Tốt</option>
                                <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Khá</option>
                                <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Cũ</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="location">Địa điểm</label>
                            <input type="text" class="form-control" id="location" name="location" value="{{ old('location') }}">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="seller_name">Tên người bán</label>
                            <input type="text" class="form-control" id="seller_name" name="seller_name" value="{{ old('seller_name') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="contact_phone">Số điện thoại</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="contact_email">Email liên hệ</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="{{ old('contact_email') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hình ảnh -->
            <div class="form-section">
                <h6><i class="fas fa-images mr-2"></i>Hình ảnh sản phẩm</h6>
                
                <div class="form-group">
                    <label class="custom-file-upload">
                        <input type="file" name="images[]" id="images" accept="image/*" multiple onchange="previewImages(event)">
                        <i class="fas fa-upload mr-2"></i>Chọn hình ảnh
                    </label>
                    <small class="form-text text-muted d-block mt-2">Chọn nhiều hình ảnh (JPEG, PNG, JPG, GIF - tối đa 2MB mỗi ảnh)</small>
                </div>

                <div id="imagePreviewContainer" class="image-preview-container"></div>
            </div>

            <!-- Nút hành động -->
            <div class="form-section">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times mr-1"></i> Hủy
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Lưu sản phẩm
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>
</div>

<script>
let selectedFiles = [];

function previewImages(event) {
    const files = Array.from(event.target.files);
    selectedFiles = files;
    
    const container = document.getElementById('imagePreviewContainer');
    container.innerHTML = '';
    
    files.forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.createElement('div');
            preview.className = 'image-preview';
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${index + 1}">
                <button type="button" class="remove-image" onclick="removeImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(preview);
        };
        
        reader.readAsDataURL(file);
    });
}

function removeImage(index) {
    selectedFiles.splice(index, 1);
    
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    
    document.getElementById('images').files = dataTransfer.files;
    
    const event = new Event('change');
    document.getElementById('images').dispatchEvent(event);
}
</script>
@endsection
