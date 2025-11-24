@extends('dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/product_create.css') }}">
@endpush

@section('body-class', 'account-products-page product-create-page')

@section('content')
    <div class="product-create-wrapper">
        <div class="container">
            <nav class="account-breadcrumb" aria-label="breadcrumb">
                <ol>
                    <li><a href="{{ url('/') }}">Trang chủ</a></li>
                    <li><span>Tài khoản</span></li>
                    <li><a href="{{ route('products.manage') }}">Sản phẩm của tôi</a></li>
                    <li aria-current="page"><span>Thêm sản phẩm mới</span></li>
                </ol>
            </nav>

            <header class="product-create-header">
                <div class="title-block">
                    <h1>Thêm sản phẩm mới</h1>
                    <p>Đăng bán sản phẩm của bạn một cách dễ dàng và chuyên nghiệp</p>
                </div>
                <div class="tips-block" aria-label="Mẹo dễ bán được giá tốt">
                    <h2>Mẹo dễ bán được giá tốt</h2>
                    <ul>
                        <li>Chụp ảnh sản phẩm trong ánh sáng tự nhiên</li>
                        <li>Mô tả chi tiết tình trạng và khuyết điểm (nếu có)</li>
                        <li>Đặt giá hợp lý so với thị trường</li>
                        <li>Phản hồi nhanh chóng với người mua</li>
                    </ul>
                </div>
            </header>

            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <p>Vui lòng kiểm tra lại các trường được đánh dấu.</p>
                    <ul>
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($errors->has('general'))
                <div class="alert alert-error" role="alert">{{ $errors->first('general') }}</div>
            @endif

            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="product-create-form">
                @csrf

                <section class="form-section" aria-labelledby="basic-info-heading">
                    <div class="section-heading">
                        <h2 id="basic-info-heading">Thông tin cơ bản</h2>
                    </div>

                    <div class="form-grid two-columns">
                        <div class="form-field">
                            <label for="name">Tên sản phẩm <span class="required">*</span></label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                            @error('name')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-field">
                            <label for="category_id">Danh mục <span class="required">*</span></label>
                            <select id="category_id" name="category_id" required>
                                <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Chọn danh mục</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-field wide">
                            <label for="description">Mô tả sản phẩm <span class="required">*</span></label>
                            <textarea id="description" name="description" rows="6" required>{{ old('description') }}</textarea>
                            @error('description')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-field wide">
                            <label for="short_description">Thông tin bổ sung</label>
                            <textarea id="short_description" name="short_description" rows="3" maxlength="255" placeholder="Thông tin thêm (tùy chọn)">{{ old('short_description') }}</textarea>
                            @error('short_description')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-field wide media-field">
                        <label for="images">Hình ảnh sản phẩm <span class="required">*</span></label>
                        <div class="upload-dropzone">
                            <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required>
                            <div class="upload-hint">
                                <span class="upload-icon">📷</span>
                                <p>Tải sản phẩm lên</p>
                                <small>(tối đa 5 ảnh, kích thước tối thiểu 300x300px)</small>
                            </div>
                        </div>
                        <div id="image-preview" class="image-preview-grid" aria-live="polite"></div>
                        <p class="field-note">Chỉ hỗ trợ JPG, PNG.</p>
                        @error('images')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                        @error('images.*')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                <section class="form-section" aria-labelledby="condition-heading">
                    <div class="section-heading">
                        <h2 id="condition-heading">Tình trạng & giá cả</h2>
                    </div>

                    <div class="form-field">
                        <span class="label">Tình trạng sản phẩm <span class="required">*</span></span>
                        <div class="condition-options" role="radiogroup" aria-labelledby="condition-heading">
                            @foreach ($conditions as $key => $label)
                                <label class="condition-pill">
                                    <input type="radio" name="condition" value="{{ $key }}" @checked(old('condition') === $key) required>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('condition')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-grid two-columns">
                        <div class="form-field">
                            <label for="price">Giá bán (VND) <span class="required">*</span></label>
                            <div class="input-addon">
                                <input id="price" name="price" type="text" inputmode="numeric" value="{{ old('price') }}" required>
                                <span class="addon">đ</span>
                            </div>
                            @error('price')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-field">
                            <label for="quantity">Số lượng trong kho <span class="required">*</span></label>
                            <input id="quantity" name="quantity" type="number" inputmode="numeric" min="0" value="{{ old('quantity', 1) }}" required>
                            <p class="field-note">Nhập số lượng hiện có. Đặt 0 nếu đã hết hàng.</p>
                            @error('quantity')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-field">
                            <label for="original_price">Giá gốc (Tham khảo)</label>
                            <div class="input-addon">
                                <input id="original_price" name="original_price" type="text" inputmode="numeric" value="{{ old('original_price') }}">
                                <span class="addon">đ</span>
                            </div>
                            @error('original_price')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="form-section" aria-labelledby="contact-heading">
                    <div class="section-heading">
                        <h2 id="contact-heading">Thông tin liên hệ</h2>
                    </div>

                    <div class="form-grid two-columns">
                        <div class="form-field">
                            <label for="location_city">Tỉnh/Thành phố <span class="required">*</span></label>
                            <input id="location_city" name="location_city" type="text" value="{{ old('location_city') }}" placeholder="Ví dụ: Hà Nội" required maxlength="100">
                            @error('location_city')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-field">
                            <label for="location_district">Quận/Huyện <span class="required">*</span></label>
                            <input id="location_district" name="location_district" type="text" value="{{ old('location_district') }}" placeholder="Ví dụ: Cầu Giấy" required maxlength="100">
                            @error('location_district')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-field">
                        <span class="label">Hình thức liên hệ <span class="required">*</span></span>
                        <div class="contact-methods">
                            @foreach ($contactMethods as $key => $label)
                                @php
                                    $disabled = $key === 'phone' && empty($user->phone);
                                @endphp
                                <label class="contact-option {{ $disabled ? 'is-disabled' : '' }}">
                                    <input type="checkbox" name="contact_methods[]" value="{{ $key }}" {{ $disabled ? 'disabled' : '' }}
                                        @checked(in_array($key, (array) old('contact_methods', []), true))>
                                    <span>{{ $label }}</span>
                                    @if ($disabled)
                                        <small>(Cần xác thực số điện thoại)</small>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        @error('contact_methods')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Đăng bán ngay</button>
                    <a href="{{ route('products.manage') }}" class="btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const input = document.getElementById('images');
            const preview = document.getElementById('image-preview');
            const maxImages = 5;
            let currentFiles = [];

            if (!input || !preview) {
                return;
            }

            const syncInputFiles = () => {
                const dataTransfer = new DataTransfer();
                currentFiles.slice(0, maxImages).forEach(file => dataTransfer.items.add(file));
                input.files = dataTransfer.files;
            };

            const renderPreview = () => {
                preview.innerHTML = '';

                if (currentFiles.length === 0) {
                    preview.innerHTML = '<p class="field-note">Chưa có ảnh nào được chọn.</p>';
                    return;
                }

                currentFiles.slice(0, maxImages).forEach((file, index) => {
                    const reader = new FileReader();

                    reader.onload = event => {
                        const item = document.createElement('div');
                        item.className = 'preview-item';
                        item.innerHTML = `
                            <button type="button" class="preview-remove" data-index="${index}" aria-label="Xóa ảnh ${file.name}">×</button>
                            <img src="${event.target?.result}" alt="${file.name}">
                            <span class="preview-name" title="${file.name}">${file.name}</span>
                        `;
                        preview.appendChild(item);
                    };

                    reader.onerror = () => {
                        alert('Không thể đọc file ảnh. Vui lòng thử lại.');
                    };

                    reader.readAsDataURL(file);
                });
            };

            input.addEventListener('change', function () {
                const selected = Array.from(input.files || []);

                if (selected.length + currentFiles.length > maxImages) {
                    alert(`Bạn chỉ có thể chọn tối đa ${maxImages} ảnh. Chỉ giữ ${maxImages} ảnh đầu tiên.`);
                }

                currentFiles = currentFiles.concat(selected);

                if (currentFiles.length > maxImages) {
                    currentFiles = currentFiles.slice(0, maxImages);
                }
                syncInputFiles();
                renderPreview();
            });

            preview.addEventListener('click', event => {
                const button = event.target;

                if (!(button instanceof HTMLButtonElement) || !button.classList.contains('preview-remove')) {
                    return;
                }

                const index = Number(button.dataset.index);

                if (Number.isNaN(index)) {
                    return;
                }

                currentFiles.splice(index, 1);
                syncInputFiles();
                renderPreview();
            });

            renderPreview();
        })();
    </script>
@endpush
