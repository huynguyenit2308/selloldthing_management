@extends('dashboard')

@section('content')
<div class="category-admin">
<main class="py-5">
    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-body d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
                <div>
                    <h5 class="mb-1 font-weight-bold">Thêm Danh Mục</h5>
                    <p class="mb-0 text-muted">Tạo mới một danh mục trong hệ thống</p>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary mt-3 mt-sm-0">Quay lại danh sách</a>
            </div>
        </div>

        <form id="categoryCreateForm" class="card shadow-sm" action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="card-body">
                {{-- Tên danh mục --}}
                <div class="form-group">
                    <label for="name" class="font-weight-semibold">Tên danh mục</label>
                    <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255" autocomplete="off" placeholder="Nhập tên danh mục">
                    @error('name')
                        <div class="mt-1 text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Hình ảnh danh mục --}}
                <div class="form-group">
                    <label for="image" class="font-weight-semibold">Hình ảnh danh mục</label>
                    <div class="border rounded text-center" id="imageDropZone"
                         style="position: relative; overflow: hidden; min-height: 240px;
                                display: flex; flex-direction: column; justify-content: center;
                                align-items: center; background-size: cover; background-position: center;
                                background-repeat: no-repeat; background-color: #f8fafc;
                                transition: box-shadow .2s ease;">
                        <div id="imagePlaceholder" class="text-muted">
                            <p class="mb-1">Kéo thả file hoặc click để chọn hình ảnh.</p>
                            <p class="mb-0">Định dạng: PNG, JPG, JPEG, SVG (tối đa 2MB).</p>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="imagePicker">Chọn hình ảnh</button>
                        </div>
                        <div id="imageOverlay"
                             style="position:absolute; inset:0; background: rgba(15, 23, 42, 0.45);
                                    display:flex; flex-direction:column; justify-content:center;
                                    align-items:center; gap:12px; opacity:0; pointer-events:none;
                                    transition: opacity .2s ease;">
                            <button type="button" class="btn btn-light btn-sm" id="changeImageButton">Chọn hình ảnh khác</button>
                        </div>
                        <input id="image" name="image" type="file" class="d-none" accept="image/*">
                    </div>
                    @error('image')
                        <div class="mt-1 text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Mô tả --}}
                <div class="form-group w-100">
                    <label for="description" class="font-weight-semibold">Mô tả</label>
                    <textarea id="description" name="description" class="form-control"
                              rows="4" placeholder="Nhập mô tả ngắn cho danh mục"
                              maxlength="500">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="mt-1 text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                <button id="submitBtn" type="submit" class="btn btn-primary" disabled>Xác nhận</button>
            </div>
        </form>
    </div>
</main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('name');
    const imageInput = document.getElementById('image');
    const submitBtn = document.getElementById('submitBtn');
    const dropZone = document.getElementById('imageDropZone');
    const imagePicker = document.getElementById('imagePicker');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    const imageOverlay = document.getElementById('imageOverlay');
    const changeImageButton = document.getElementById('changeImageButton');

    let previewUrl = null;

    // --- Reset preview
    function resetPreview() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
        dropZone.style.backgroundImage = '';
        imagePlaceholder.classList.remove('d-none');
        imageOverlay.style.opacity = '0';
        imageOverlay.style.pointerEvents = 'none';
    }

    // --- Hiển thị ảnh xem trước
    function showPreview(file) {
        previewUrl = URL.createObjectURL(file);
        dropZone.style.backgroundImage = `url('${previewUrl}')`;
        imagePlaceholder.classList.add('d-none');
    }

    // --- Mở chọn file ảnh
    function openFilePicker(e) {
        e.stopPropagation(); // chặn click lan ra
        imageInput.click();
    }

    // --- Chỉ gán 1 listener duy nhất để tránh mở 2 lần
    imagePicker.addEventListener('click', openFilePicker);
    changeImageButton.addEventListener('click', openFilePicker);
    dropZone.addEventListener('click', function(e) {
        // nếu click vào nút bên trong, không mở thêm
        if (e.target.id === 'imagePicker' || e.target.id === 'changeImageButton') return;
        imageInput.click();
    });

    // --- Khi chọn ảnh
    imageInput.addEventListener('change', function () {
        const file = imageInput.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('Chỉ được chọn file hình ảnh!');
            imageInput.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('Kích thước ảnh không được vượt quá 2MB!');
            imageInput.value = '';
            return;
        }

        showPreview(file);
        submitBtn.disabled = nameInput.value.trim() === '';
    });

    // --- Hover overlay
    dropZone.addEventListener('mouseenter', function () {
        if (dropZone.style.backgroundImage) {
            imageOverlay.style.opacity = '1';
            imageOverlay.style.pointerEvents = 'auto';
        }
    });
    dropZone.addEventListener('mouseleave', function () {
        imageOverlay.style.opacity = '0';
        imageOverlay.style.pointerEvents = 'none';
    });

    // --- Bật nút submit khi có tên
    nameInput.addEventListener('input', function () {
        submitBtn.disabled = nameInput.value.trim() === '';
    });
});
</script>
@endsection
