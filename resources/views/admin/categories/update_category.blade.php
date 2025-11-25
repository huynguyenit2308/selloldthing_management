@extends('dashboard')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    $initialStatus = (string) old('status', (string) ($category->status ? 1 : 0));
    $initialImagePath = $category->image;
    $initialImageUrl = asset('images/product_1.png');
    $hasInitialImage = false;

    if ($initialImagePath) {
        if (filter_var($initialImagePath, FILTER_VALIDATE_URL)) {
            $initialImageUrl = $initialImagePath;
            $hasInitialImage = true;
        } elseif (Storage::disk('public')->exists($initialImagePath)) {
            $initialImageUrl = Storage::url($initialImagePath);
            $hasInitialImage = true;
        } elseif (file_exists(public_path($initialImagePath))) {
            $initialImageUrl = asset(ltrim($initialImagePath, '/'));
            $hasInitialImage = true;
        } elseif (file_exists(public_path('images/' . ltrim($initialImagePath, '/')))) {
            $initialImageUrl = asset('images/' . ltrim($initialImagePath, '/'));
            $hasInitialImage = true;
        }
    }
@endphp

<div class="category-admin">
<main class="py-5">
    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-body d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
                <div>
                    <h5 class="mb-1 font-weight-bold">Sửa Danh Mục</h5>
                    <p class="mb-0 text-muted">Chỉnh sửa thông tin một danh mục đã tồn tại trong hệ thống</p>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary mt-3 mt-sm-0">Quay lại danh sách</a>
            </div>
        </div>

        <!-- Hiển thị lỗi DATA_OUTDATED (optimistic locking) -->
        @if($errors->has('system') && str_contains($errors->first('system'), 'DATA_OUTDATED'))
            <div class="alert alert-warning">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong class="d-block">Dữ liệu đã thay đổi</strong>
                        <small class="d-block mt-1">Dữ liệu danh mục đã được thay đổi bởi người khác. Vui lòng tải lại trang để xem dữ liệu mới nhất trước khi cập nhật.</small>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">Tải lại trang</button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại danh sách</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Hiển thị lỗi backend quan trọng -->
        @if(session('error_code') && in_array(session('error_code'), ['CATEGORY_NOT_FOUND', 'PERMISSION_DENIED', 'CATEGORY_IN_USE']))
            <div class="alert alert-danger">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong class="d-block">
                            @if(session('error_code') === 'CATEGORY_NOT_FOUND')
                                Danh mục không tồn tại hoặc đã bị xóa
                            @elseif(session('error_code') === 'PERMISSION_DENIED')
                                Bạn không có quyền chỉnh sửa danh mục này
                            @elseif(session('error_code') === 'CATEGORY_IN_USE')
                                Không thể chỉnh sửa danh mục đang được sử dụng
                            @else
                                {{ session('error') }}
                            @endif
                        </strong>
                        @if(session('error_code') === 'CATEGORY_NOT_FOUND')
                            <div class="mt-2">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-primary btn-sm">Quay lại danh sách danh mục</a>
                            </div>
                        @elseif(session('error_code') === 'PERMISSION_DENIED')
                            <div class="mt-2">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-primary btn-sm">Quay lại danh sách</a>
                            </div>
                        @elseif(session('error_code') === 'CATEGORY_IN_USE')
                            <small class="d-block mt-1">Vui lòng kiểm tra lại các sản phẩm đang sử dụng danh mục này trước khi chỉnh sửa.</small>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Hiển thị lỗi hệ thống -->
        @if(session('error_code') && in_array(session('error_code'), ['SERVER_ERROR', 'SYSTEM_ERROR']))
            <div class="alert alert-warning">
                <div class="d-flex align-items-center">
                    <i class="fas fa-server me-2"></i>
                    <div>
                        <strong class="d-block">
                            @if(session('error_code') === 'SERVER_ERROR')
                                Không thể kết nối đến server
                            @elseif(session('error_code') === 'SYSTEM_ERROR')
                                Đã có lỗi xảy ra, vui lòng thử lại sau
                            @else
                                {{ session('error') }}
                            @endif
                        </strong>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">Thử lại</button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại danh sách</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Hiển thị lỗi upload hình ảnh -->
        @if(session('error_code') === 'IMAGE_UPLOAD_FAILED')
            <div class="alert alert-warning">
                <div class="d-flex align-items-center">
                    <i class="fas fa-image me-2"></i>
                    <div>
                        <strong class="d-block">Không thể upload hình ảnh</strong>
                        <small class="d-block mt-1">Vui lòng thử lại với hình ảnh khác hoặc để trống nếu không cần thay đổi.</small>
                    </div>
                </div>
            </div>
        @endif

        <!-- Hiển thị lỗi system khác -->
        @if($errors->has('system') && !str_contains($errors->first('system'), 'DATA_OUTDATED'))
            <div class="alert alert-danger">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong class="d-block">Lỗi hệ thống</strong>
                        <small class="d-block mt-1">{{ $errors->first('system') }}</small>
                        <div class="mt-2">
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại danh sách</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Hiển thị lỗi validation thông thường -->
        @if($errors->any() && !session('error_code') && !$errors->has('system'))
            <div class="alert alert-danger">
                <h6 class="alert-heading">Vui lòng sửa các lỗi sau:</h6>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="categoryEditForm" class="card shadow-sm" action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $category->updated_at->toString() }}">
            <div class="card-body">
                <!-- Trường Tên danh mục -->
                <div class="form-group">
                    <label for="name" class="font-weight-semibold">Tên danh mục</label>
                    <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $category->name) }}" required maxlength="255" autocomplete="off" 
                           placeholder="Nhập tên danh mục">
                    
                    <!-- Lỗi validation frontend -->
                    <div id="nameErrorRequired" class="mt-1 text-danger d-none">
                        <i class="fas fa-exclamation-circle me-1"></i>Tên danh mục không được để trống
                    </div>
                    <div id="nameErrorTooLong" class="mt-1 text-danger d-none">
                        <i class="fas fa-exclamation-circle me-1"></i>Tên danh mục không được vượt quá 255 ký tự
                    </div>
                    <div id="nameErrorInvalid" class="mt-1 text-danger d-none">
                        <i class="fas fa-exclamation-circle me-1"></i>Tên danh mục chứa ký tự không hợp lệ
                    </div>
                    
                    <!-- Lỗi backend -->
                    @error('name')
                        <div class="mt-1 text-danger">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            @if($message === 'CATEGORY_NAME_EXISTS')
                                Tên danh mục đã tồn tại trong hệ thống
                            @else
                                {{ $message }}
                            @endif
                        </div>
                    @enderror
                </div>

                <!-- Trường Hình ảnh danh mục -->
                <div class="form-group">
                    <label for="image" class="font-weight-semibold">Hình ảnh danh mục</label>
                    <div
                        class="border rounded text-center @error('image') border-danger @enderror"
                        id="imageDropZone"
                        data-initial-image-url="{{ $hasInitialImage ? $initialImageUrl : '' }}"
                        style="position: relative; overflow: hidden; min-height: 240px; display: flex; flex-direction: column; justify-content: center; align-items: center; background-size: cover; background-position: center; background-repeat: no-repeat; background-color: #f8fafc; transition: all 0.3s ease;"
                    >
                        <div id="imagePlaceholder" class="text-muted">
                            <p class="mb-1">Kéo thả file icon vào hoặc click để chọn.</p>
                            <p class="mb-0">Định dạng: PNG, SVG, JPG,... (tối đa 2MB).</p>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="imagePicker">Chọn hình ảnh</button>
                        </div>
                        <div id="imageOverlay" style="position:absolute; inset:0; background: rgba(15, 23, 42, 0.45); display:flex; flex-direction:column; justify-content:center; align-items:center; gap:12px; opacity:0; pointer-events:none; transition: opacity .2s ease;">
                            <button type="button" class="btn btn-light btn-sm" id="changeImageButton">Chọn hình ảnh khác</button>
                        </div>
                        <input id="image" name="image" type="file" class="d-none" accept="image/*">
                    </div>
                    
                    <!-- Lỗi validation frontend -->
                    <div id="imageErrorFormat" class="mt-1 text-danger d-none">
                        <i class="fas fa-exclamation-circle me-1"></i>Định dạng file không được hỗ trợ
                    </div>
                    <div id="imageErrorSize" class="mt-1 text-danger d-none">
                        <i class="fas fa-exclamation-circle me-1"></i>Kích thước file không được vượt quá 2MB
                    </div>
                    
                    <!-- Lỗi backend -->
                    @error('image')
                        <div class="mt-1 text-danger">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            @if($message === 'IMAGE_UPLOAD_FAILED')
                                Không thể upload hình ảnh
                            @else
                                {{ $message }}
                            @endif
                        </div>
                    @enderror
                </div>

                <!-- Trường Mô tả -->
                <div class="form-group w-100" >
                    <label for="description" class="font-weight-semibold">Mô tả</label>
                    <textarea id="description" name="description" class="form-control" rows="4" placeholder="Nhập mô tả ngắn cho danh mục" maxlength="500">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="mt-1 text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Trường Trạng thái -->
                <div class="form-group">
                    <label class="font-weight-semibold d-block">Ẩn/Hiện</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="statusToggle" {{ $initialStatus === '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="statusToggle">
                            <span id="statusToggleText">{{ $initialStatus === '1' ? 'Đang hiển thị' : 'Đang ẩn' }}</span>
                        </label>
                    </div>
                    <input type="hidden" name="status" id="statusInput" value="{{ $initialStatus }}">
                    @error('status')
                        <div class="mt-1 text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
                <button type="button" class="btn btn-outline-info order-1" id="toggleStatusBtn">Ẩn/Hiện</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary order-3 order-md-2">Hủy bỏ</a>
                <button id="submitBtn" type="submit" class="btn btn-primary order-2 order-md-3" disabled>
                    <span id="submitText">Xác nhận</span>
                    <div id="submitSpinner" class="spinner-border spinner-border-sm d-none" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </button>
            </div>
        </form>
    </div>
</main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('categoryEditForm');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const imageInput = document.getElementById('image');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    const dropZone = document.getElementById('imageDropZone');
    const imagePicker = document.getElementById('imagePicker');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    const imageOverlay = document.getElementById('imageOverlay');
    const changeImageButton = document.getElementById('changeImageButton');
    const statusToggle = document.getElementById('statusToggle');
    const statusInput = document.getElementById('statusInput');
    const statusToggleText = document.getElementById('statusToggleText');
    const toggleStatusBtn = document.getElementById('toggleStatusBtn');

    const nameErrors = {
        required: document.getElementById('nameErrorRequired'),
        tooLong: document.getElementById('nameErrorTooLong'),
        invalid: document.getElementById('nameErrorInvalid')
    };

    const imageErrors = {
        format: document.getElementById('imageErrorFormat'),
        size: document.getElementById('imageErrorSize')
    };

    let previewUrl = null;
    let imageChanged = false;
    let isSubmitting = false;

    const initialData = {
        name: nameInput.value.trim(),
        description: descriptionInput.value.trim(),
        status: statusInput.value,
        imageUrl: dropZone.dataset.initialImageUrl || ''
    };

    if (initialData.imageUrl) {
        dropZone.style.backgroundImage = `url('${initialData.imageUrl}')`;
        dropZone.classList.add('has-image');
        imagePlaceholder.classList.add('d-none');
    }

    function hideErrors(errorMap) {
        Object.values(errorMap).forEach(function (el) {
            if (el) {
                el.classList.add('d-none');
            }
        });
    }

    function showError(el) {
        if (el) {
            el.classList.remove('d-none');
        }
    }

    function validateName() {
        hideErrors(nameErrors);
        const value = nameInput.value.trim();
        if (value.length === 0) {
            showError(nameErrors.required);
            return false;
        }
        if (value.length > 255) {
            showError(nameErrors.tooLong);
            return false;
        }
        const pattern = /^[\p{L}\d\s.,\-_/()]+$/u;
        if (!pattern.test(value)) {
            showError(nameErrors.invalid);
            return false;
        }
        return true;
    }

    function validateImage() {
        hideErrors(imageErrors);
        const file = imageInput.files[0];
        if (!file) {
            return true;
        }
        if (!file.type.startsWith('image/')) {
            showError(imageErrors.format);
            return false;
        }
        if (file.size > 2 * 1024 * 1024) {
            showError(imageErrors.size);
            return false;
        }
        return true;
    }

    function revokePreview() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
    }

    function showPreview(file) {
        revokePreview();
        previewUrl = URL.createObjectURL(file);
        dropZone.style.backgroundImage = `url('${previewUrl}')`;
        dropZone.classList.add('has-image');
        imagePlaceholder.classList.add('d-none');
        imageChanged = true;
    }

    function resetPreview() {
        revokePreview();
        if (initialData.imageUrl && !imageChanged) {
            dropZone.style.backgroundImage = `url('${initialData.imageUrl}')`;
            dropZone.classList.add('has-image');
            imagePlaceholder.classList.add('d-none');
        } else {
            dropZone.style.backgroundImage = '';
            dropZone.classList.remove('has-image');
            imagePlaceholder.classList.remove('d-none');
        }
    }

    function updateStatusText() {
        const isActive = statusToggle.checked;
        statusInput.value = isActive ? '1' : '0';
        statusToggleText.textContent = isActive ? 'Đang hiển thị' : 'Đang ẩn';
    }

    function hasChanges() {
        const nameChanged = nameInput.value.trim() !== initialData.name;
        const descriptionChanged = descriptionInput.value.trim() !== initialData.description;
        const statusChanged = statusInput.value !== initialData.status;
        return nameChanged || descriptionChanged || statusChanged || imageChanged;
    }

    function updateSubmitState() {
        const isValid = validateName() && validateImage();
        submitBtn.disabled = !(isValid && hasChanges()) || isSubmitting;
    }

    function setSubmitting(state) {
        isSubmitting = state;
        submitBtn.disabled = state;
        submitText.classList.toggle('d-none', state);
        submitSpinner.classList.toggle('d-none', !state);
    }

    function triggerFilePick() {
        imageInput.click();
    }

    imagePicker.addEventListener('click', triggerFilePick);
    changeImageButton.addEventListener('click', function (event) {
        event.stopPropagation();
        triggerFilePick();
    });
    dropZone.addEventListener('click', function (event) {
        if (event.target === imagePicker || event.target === changeImageButton) {
            return;
        }
        triggerFilePick();
    });

    imageInput.addEventListener('change', function () {
        if (!validateImage()) {
            imageInput.value = '';
            imageChanged = false;
            resetPreview();
            updateSubmitState();
            return;
        }
        const file = imageInput.files[0];
        if (!file) {
            imageChanged = false;
            resetPreview();
            updateSubmitState();
            return;
        }
        showPreview(file);
        updateSubmitState();
    });

    dropZone.addEventListener('dragover', function (event) {
        event.preventDefault();
        dropZone.classList.add('border-primary');
    });
    dropZone.addEventListener('dragleave', function () {
        dropZone.classList.remove('border-primary');
    });
    dropZone.addEventListener('drop', function (event) {
        event.preventDefault();
        dropZone.classList.remove('border-primary');
        if (event.dataTransfer.files && event.dataTransfer.files[0]) {
            imageInput.files = event.dataTransfer.files;
            imageInput.dispatchEvent(new Event('change'));
        }
    });

    dropZone.addEventListener('mouseenter', function () {
        if (dropZone.classList.contains('has-image')) {
            imageOverlay.style.opacity = '1';
            imageOverlay.style.pointerEvents = 'auto';
        }
    });
    dropZone.addEventListener('mouseleave', function () {
        imageOverlay.style.opacity = '0';
        imageOverlay.style.pointerEvents = 'none';
    });

    nameInput.addEventListener('input', updateSubmitState);
    descriptionInput.addEventListener('input', updateSubmitState);

    statusToggle.addEventListener('change', function () {
        updateStatusText();
        updateSubmitState();
    });
    toggleStatusBtn.addEventListener('click', function () {
        statusToggle.checked = !statusToggle.checked;
        statusToggle.dispatchEvent(new Event('change'));
    });

    form.addEventListener('submit', function (event) {
        if (!validateName() || !validateImage()) {
            event.preventDefault();
            return;
        }

        setSubmitting(true);
        
        // Timeout để tránh submit nhiều lần
        setTimeout(() => {
            setSubmitting(false);
        }, 10000);
    });

    updateStatusText();
    updateSubmitState();
});
</script>

<style>
.category-admin .card {
    border: none;
    border-radius: 12px;
}

.category-admin .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.category-admin .btn:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.category-admin #imageDropZone {
    border: 2px dashed #dee2e6;
    transition: all 0.3s ease;
}

.category-admin #imageDropZone:hover {
    border-color: #007bff;
}

.category-admin #imageDropZone.has-image {
    border-style: solid;
}

.category-admin .text-danger {
    font-size: 0.875rem;
}
</style>
@endsection