@extends('dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/product_edit.css') }}">
@endpush

@section('body-class', 'account-products-page product-edit-page')

@section('content')
    <div class="product-edit-wrapper">
        <div class="container">
            @php
                $removedImageIds = collect(old('remove_image_ids', []))->map(static fn ($id) => (int) $id)->filter()->unique();
                $existingOrder = collect(old('existing_images', $product->images->pluck('id')->all()))
                    ->map(static fn ($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->all();
                
                // Get existing images, ordered by sort_order and created_at
                $displayImages = $product->images->reject(function ($image) use ($removedImageIds) {
                    return $removedImageIds->contains($image->id);
                })->sortBy([
                    ['sort_order', 'asc'],
                    ['created_at', 'asc']
                ])->values();
                
                $currentImageCount = $displayImages->count();
            @endphp
            <nav class="account-breadcrumb" aria-label="breadcrumb">
                <ol>
                    <li><a href="{{ url('/') }}">Trang chủ</a></li>
                    <li><a href="{{ route('products.manage') }}">Sản phẩm của tôi</a></li>
                    <li aria-current="page"><span>Cập nhật sản phẩm</span></li>
                </ol>
            </nav>

            <!-- Notification Container -->
            <div id="image-notification-container" class="image-notification-container"></div>

            <header class="product-edit-header">
                <div class="title-block">
                    <h1>Cập nhật thông tin sản phẩm</h1>
                    <p>Chỉnh sửa sản phẩm của bạn một cách dễ dàng và chuyên nghiệp</p>
                </div>
                <div class="tips-block" aria-label="Mẹo để bán được giá tốt">
                    <h2>Mẹo để bán được giá tốt</h2>
                    <ul>
                        <li>Chụp ảnh sản phẩm trong ánh sáng tự nhiên</li>
                        <li>Mô tả chi tiết tình trạng và khuyết điểm (nếu có)</li>
                        <li>Đặt giá hợp lý so với thị trường</li>
                        <li>Phản hồi nhanh chóng với người mua</li>
                    </ul>
                </div>
            </header>

            @if (session('product_sync_warning'))
                <div class="alert alert-warning" role="alert">
                    <p>{{ session('product_sync_warning') }}</p>
                </div>
            @endif

            @if (session('product_conflict_payload'))
                <div class="alert alert-warning" role="alert">
                    <p>Dữ liệu sản phẩm đã thay đổi kể từ lần chỉnh sửa trước. Vui lòng kiểm tra trước khi lưu.</p>
                </div>
            @endif

            @if (session('product_action_blocked'))
                <div class="alert alert-error" role="alert">
                    <p>{{ session('product_action_blocked') }}</p>
                </div>
            @endif

            @if ($isSold)
                <div class="alert alert-error" role="alert">
                    <p>Sản phẩm đã có người mua. Bạn chỉ có thể xem lại thông tin.</p>
                </div>
            @endif

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

            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" id="product-edit-form"
                class="product-edit-form" data-max-images="{{ $maxImages }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="version" value="{{ optional($product->updated_at)->getTimestamp() }}">

                <fieldset {{ $isSold ? 'disabled' : '' }}>
                    <section class="form-section" aria-labelledby="basic-info-heading">
                        <div class="section-heading">
                            <h2 id="basic-info-heading">Thông tin cơ bản</h2>
                        </div>

                        <div class="form-grid two-columns">
                            <div class="form-field wide">
                                <label for="name">Tên sản phẩm <span class="required">*</span></label>
                                <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required>
                                <p class="field-note">Tên sản phẩm giới hạn từ 1 - 30 ký tự.</p>
                                @error('name')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-field">
                                <label for="category_id">Danh mục <span class="required">*</span></label>
                                <select id="category_id" name="category_id" required>
                                    <option value="" disabled {{ old('category_id', $product->category_id) ? '' : 'selected' }}>Chọn danh mục</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
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
                                <textarea id="description" name="description" rows="8" required>{{ old('description', $product->description) }}</textarea>
                                <p class="field-note">Hãy mô tả rõ tình trạng, khuyết điểm (nếu có) và phụ kiện kèm theo.</p>
                                @error('description')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="form-section" aria-labelledby="media-heading">
                        <div class="section-heading">
                            <h2 id="media-heading">Hình ảnh sản phẩm</h2>
                            <div class="image-actions">
                                <button type="button" class="btn-upload" id="trigger-upload">Tải sản phẩm lên (tối đa {{ $maxImages }} ảnh)</button>
                                <span class="field-note" id="image-count-note" data-initial-count="{{ $currentImageCount }}">{{ $currentImageCount }} / {{ $maxImages }} ảnh</span>
                            </div>
                        </div>

                        <div class="media-field">
                            <div class="image-list">
                                <div class="image-grid js-existing-images" data-max="{{ $maxImages }}">
                                    @forelse ($displayImages as $index => $image)
                                    <div class="image-item" draggable="true" data-image-id="{{ $image->id }}">
                                        <span class="drag-handle" aria-label="Kéo để sắp xếp">⠿</span>
                                        <div class="image-thumb">
                                            <img src="{{ $image->image_url }}" alt="Ảnh {{ $index + 1 }} của {{ $product->name }}">
                                            @if ($loop->first)
                                                <span class="image-badge" aria-label="Ảnh đại diện">Ảnh chính</span>
                                            @endif
                                        </div>
                                        <div class="image-actions">
                                            <button type="button" class="btn-danger js-remove-existing" data-image-id="{{ $image->id }}">Xóa</button>
                                        </div>
                                        <input type="hidden" name="existing_images[]" value="{{ $image->id }}">
                                    </div>
                                    @empty
                                        <p class="field-note">Chưa có ảnh nào. Vui lòng tải lên tối thiểu 1 ảnh.</p>
                                    @endforelse
                                </div>

                                <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple hidden>

                                <div class="new-image-preview" id="new-image-preview"></div>
                                <div id="remove-image-container"></div>
                                <p class="field-note">Chỉ chấp nhận JPG, PNG. Kéo thả để thay đổi thứ tự.</p>
                                @error('images')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                                @error('images.*')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="form-section" aria-labelledby="condition-heading">
                        <div class="section-heading">
                            <h2 id="condition-heading">Tình trạng &amp; giá cả</h2>
                        </div>

                        <div class="form-field">
                            <span class="label">Tình trạng sản phẩm <span class="required">*</span></span>
                            <div class="condition-options" role="radiogroup" aria-labelledby="condition-heading">
                                @foreach ($conditions as $key => $label)
                                    <label class="condition-pill">
                                        <input type="radio" name="condition" value="{{ $key }}" @checked(old('condition', $product->condition) === $key) required>
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
                                    <input id="price" name="price" type="text" inputmode="numeric" value="{{ old('price', $product->price) }}" required>
                                    <span class="addon">đ</span>
                                </div>
                                <p class="field-note">Giá tối thiểu 1,000 VND.</p>
                                @error('price')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-field">
                                <label for="quantity">Số lượng trong kho <span class="required">*</span></label>
                                <input id="quantity" name="quantity" type="number" inputmode="numeric" min="0" value="{{ old('quantity', $product->quantity) }}" required>
                                <p class="field-note">Cập nhật số lượng hiện có. Đặt 0 nếu đã hết hàng.</p>
                                @error('quantity')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-field">
                                <label for="original_price">Giá gốc (Tham khảo)</label>
                                <div class="input-addon">
                                    <input id="original_price" name="original_price" type="text" inputmode="numeric" value="{{ old('original_price', $product->original_price) }}">
                                    <span class="addon">đ</span>
                                </div>
                                <p class="field-note">Không bắt buộc. Giúp người mua thấy mức giảm giá.</p>
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
                                <label for="location_city">Khu vực (Tỉnh/Thành phố) <span class="required">*</span></label>
                                <input id="location_city" name="location_city" type="text" value="{{ old('location_city', $locationCity) }}" placeholder="Ví dụ: Hà Nội" required maxlength="255">
                                @error('location_city')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-field">
                                <label for="location_district">Quận/Huyện <span class="required">*</span></label>
                                <input id="location_district" name="location_district" type="text" value="{{ old('location_district', $locationDistrict) }}" placeholder="Ví dụ: Cầu Giấy" required maxlength="255">
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
                                        $disabled = $key === 'phone' && !$phoneVerified;
                                        $checked = in_array($key, (array) $selectedContactMethods, true);
                                    @endphp
                                    <label class="contact-option {{ $disabled ? 'is-disabled' : '' }}">
                                        <input type="checkbox" name="contact_methods[]" value="{{ $key }}" {{ $disabled ? 'disabled' : '' }} @checked($checked)>
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
                </fieldset>

                <div class="form-actions">
                    <a href="{{ route('products.manage') }}" class="btn-secondary">Hủy</a>
                    <button type="submit" class="btn-primary" {{ $isSold ? 'disabled' : '' }}>Lưu thay đổi</button>
                </div>

                </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('product-edit-form');
            if (!form) {
                return;
            }

            const maxImages = Number(form.dataset.maxImages || 6);
            const existingGrid = document.querySelector('.js-existing-images');
            const newPreview = document.getElementById('new-image-preview');
            const removeContainer = document.getElementById('remove-image-container');
            const imageCountNote = document.getElementById('image-count-note');
            const fileInput = document.getElementById('images');
            const triggerUpload = document.getElementById('trigger-upload');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const isSold = {{ $isSold ? 'true' : 'false' }};

            const numberFormatter = new Intl.NumberFormat('vi-VN');

            const conditionLabels = @json($conditions);
            const categoryOptions = @json($categories->pluck('name', 'id')->toArray());

            const state = {
                removedImageIds: new Set(),
                newFiles: [],
                totalImages() {
                    const existingCount = existingGrid ? existingGrid.querySelectorAll('.image-item').length : 0;
                    return existingCount + this.newFiles.length;
                }
            };

            // Notification System
            const showNotification = (message, type = 'error') => {
                const container = document.getElementById('image-notification-container');
                if (!container) return;

                // Check if same notification already exists to prevent duplicates
                const existingNotifications = container.querySelectorAll('.image-notification');
                for (let existing of existingNotifications) {
                    const messageEl = existing.querySelector('.image-notification-message');
                    if (messageEl && messageEl.textContent === message) {
                        return; // Don't show duplicate notification
                    }
                }

                const notification = document.createElement('div');
                notification.className = 'image-notification';
                
                const icon = document.createElement('div');
                icon.className = 'image-notification-icon';
                icon.innerHTML = type === 'error' ? '⚠️' : '✓';
                
                const messageEl = document.createElement('div');
                messageEl.className = 'image-notification-message';
                messageEl.textContent = message;
                
                notification.appendChild(icon);
                notification.appendChild(messageEl);
                
                container.appendChild(notification);
                
                // Auto hide after 3 seconds
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.classList.add('hiding');
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.parentNode.removeChild(notification);
                            }
                        }, 300);
                    }
                }, 3000);
            };

            const updateImageCountNote = () => {
                if (!imageCountNote) {
                    return;
                }
                imageCountNote.textContent = `${state.totalImages()} / ${maxImages} ảnh`;
            };

            function updatePrimaryBadges() {
                if (!existingGrid) {
                    return;
                }
                const items = existingGrid.querySelectorAll('.image-item');
                items.forEach((item, index) => {
                    const badge = item.querySelector('.image-badge');
                    if (index === 0) {
                        if (!badge) {
                            const thumb = item.querySelector('.image-thumb');
                            if (thumb) {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'image-badge';
                                newBadge.textContent = 'Ảnh chính';
                                newBadge.setAttribute('aria-label', 'Ảnh đại diện');
                                thumb.appendChild(newBadge);
                            }
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                });
            }

            const syncExistingOrderInputs = () => {
                if (!existingGrid) {
                    return;
                }
                const inputs = existingGrid.querySelectorAll('input[name="existing_images[]"]');
                inputs.forEach((input, index) => {
                    input.setAttribute('data-order', String(index));
                });
                updatePrimaryBadges();
            };

            const removeExistingImage = (imageId) => {
                if (!existingGrid) {
                    return;
                }
                const item = existingGrid.querySelector(`.image-item[data-image-id="${imageId}"]`);
                if (!item) {
                    return;
                }
                
                // Kiểm tra số lượng ảnh còn lại sau khi xóa
                const currentCount = state.totalImages();
                if (currentCount <= 1) {
                    showNotification('Vui lòng giữ lại ít nhất 1 ảnh sản phẩm', 'error');
                    return;
                }
                
                state.removedImageIds.add(Number(imageId));
                item.remove();
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'remove_image_ids[]';
                hiddenInput.value = imageId;
                removeContainer.appendChild(hiddenInput);
                updateImageCountNote();
                syncExistingOrderInputs();
            };

            // Drag & drop sorting for existing images
            if (existingGrid) {
                let dragItem = null;

                existingGrid.addEventListener('dragstart', (event) => {
                    const target = event.target.closest('.image-item');
                    if (!target) {
                        return;
                    }
                    dragItem = target;
                    target.classList.add('dragging');
                    event.dataTransfer.effectAllowed = 'move';
                });

                existingGrid.addEventListener('dragend', (event) => {
                    const target = event.target.closest('.image-item');
                    if (target) {
                        target.classList.remove('dragging');
                    }
                    dragItem = null;
                });

                existingGrid.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    const target = event.target.closest('.image-item');
                    if (!target || target === dragItem) {
                        return;
                    }
                    const bounding = target.getBoundingClientRect();
                    const offset = event.clientY - bounding.top;
                    const shouldInsertAfter = offset > bounding.height / 2;
                    if (shouldInsertAfter) {
                        target.after(dragItem);
                    } else {
                        target.before(dragItem);
                    }
                });

                existingGrid.addEventListener('drop', (event) => {
                    event.preventDefault();
                    syncExistingOrderInputs();
                });

                existingGrid.addEventListener('click', (event) => {
                    const removeButton = event.target.closest('.js-remove-existing');
                    if (removeButton) {
                        const imageId = removeButton.dataset.imageId;
                        removeExistingImage(imageId);
                        return;
                    }
                });
            }

            if (triggerUpload && fileInput) {
                triggerUpload.addEventListener('click', () => {
                    fileInput.click();
                });
            }

            const renderNewPreviews = () => {
                newPreview.innerHTML = '';
                state.newFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const card = document.createElement('div');
                        card.className = 'preview-card';
                        card.innerHTML = `
                            <button type="button" class="preview-remove" data-index="${index}" aria-label="Xóa ảnh mới">×</button>
                            <img src="${event.target?.result}" alt="${file.name}">
                            <div>${file.name}</div>
                        `;
                        newPreview.appendChild(card);
                    };
                    reader.readAsDataURL(file);
                });
            };

            const syncNewFileInput = () => {
                const dataTransfer = new DataTransfer();
                state.newFiles.slice(0, Math.max(0, maxImages - (existingGrid ? existingGrid.querySelectorAll('.image-item').length : 0))).forEach(file => dataTransfer.items.add(file));
                fileInput.files = dataTransfer.files;
            };

            if (fileInput) {
                fileInput.addEventListener('change', () => {
                    const selected = Array.from(fileInput.files || []);
                    const existingCount = existingGrid ? existingGrid.querySelectorAll('.image-item').length : 0;
                    const maxNewAllowed = Math.max(0, maxImages - existingCount);

                    if (maxNewAllowed <= 0) {
                        showNotification(`Bạn chỉ có thể có tối đa ${maxImages} ảnh. Vui lòng xóa bớt ảnh hiện tại trước khi thêm ảnh mới.`, 'error');
                        fileInput.value = '';
                        return;
                    }

                    if (selected.length === 0) {
                        return;
                    }

                    let combined = state.newFiles.concat(selected);

                    if (combined.length > maxNewAllowed) {
                        showNotification(`Bạn chỉ có thể thêm tối đa ${maxNewAllowed} ảnh mới. Chỉ giữ ${maxNewAllowed} ảnh đầu tiên.`, 'error');
                        combined = combined.slice(0, maxNewAllowed);
                    }

                    state.newFiles = combined;
                    syncNewFileInput();
                    renderNewPreviews();
                    updateImageCountNote();
                });
            }

            if (newPreview) {
                newPreview.addEventListener('click', (event) => {
                    const removeButton = event.target.closest('.preview-remove');
                    if (!removeButton) {
                        return;
                    }
                    const index = Number(removeButton.dataset.index);
                    if (Number.isNaN(index)) {
                        return;
                    }
                    
                    // Kiểm tra số lượng ảnh còn lại sau khi xóa
                    const currentCount = state.totalImages();
                    if (currentCount <= 1) {
                        showNotification('Vui lòng giữ lại ít nhất 1 ảnh sản phẩm', 'error');
                        return;
                    }
                    
                    state.newFiles.splice(index, 1);
                    syncNewFileInput();
                    renderNewPreviews();
                    updateImageCountNote();
                });
            }

            updateImageCountNote();
            syncExistingOrderInputs();

            const validators = {
                name() {
                    const input = form.name;
                    if (!input) {
                        return true;
                    }
                    const value = input.value.trim();
                    clearFieldError(input);
                    if (value.length < 1) {
                        showFieldError(input, 'Tên sản phẩm phải có ít nhất 1 ký tự');
                        return false;
                    }
                    if (value.length > 30) {
                        showFieldError(input, 'Tên sản phẩm không được vượt quá 30 ký tự');
                        return false;
                    }
                    return true;
                },
                category() {
                    const select = form.category_id;
                    if (!select) {
                        return true;
                    }
                    clearFieldError(select);
                    if (!select.value) {
                        showFieldError(select, 'Vui lòng chọn danh mục sản phẩm');
                        return false;
                    }
                    return true;
                },
                price() {
                    const input = form.price;
                    if (!input) {
                        return true;
                    }
                    clearFieldError(input);
                    const numeric = parseInt(input.value.replace(/[^0-9]/g, ''), 10);
                    if (Number.isNaN(numeric) || numeric < 1000) {
                        showFieldError(input, 'Giá bán phải từ 1,000 VND trở lên');
                        return false;
                    }
                    return true;
                },
                locationCity() {
                    const input = form.location_city;
                    if (!input) {
                        return true;
                    }
                    const value = input.value.trim();
                    clearFieldError(input);
                    if (value.length < 1) {
                        showFieldError(input, 'Tỉnh/Thành phố phải có ít nhất 1 ký tự');
                        return false;
                    }
                    if (value.length > 30) {
                        showFieldError(input, 'Tỉnh/Thành phố không được vượt quá 30 ký tự');
                        return false;
                    }
                    // Check for special characters
                    if (/[<>|!@#$%^&*()_+=\[\]{};:"\\|<>\/?]/.test(value)) {
                        showFieldError(input, 'Tỉnh/Thành phố không được chứa ký tự đặc biệt');
                        return false;
                    }
                    return true;
                },
                locationDistrict() {
                    const input = form.location_district;
                    if (!input) {
                        return true;
                    }
                    const value = input.value.trim();
                    clearFieldError(input);
                    if (value.length < 1) {
                        showFieldError(input, 'Quận/Huyện phải có ít nhất 1 ký tự');
                        return false;
                    }
                    if (value.length > 30) {
                        showFieldError(input, 'Quận/Huyện không được vượt quá 30 ký tự');
                        return false;
                    }
                    // Check for special characters
                    if (/[<>|!@#$%^&*()_+=\[\]{};:"\\|<>\/?]/.test(value)) {
                        showFieldError(input, 'Quận/Huyện không được chứa ký tự đặc biệt');
                        return false;
                    }
                    return true;
                }
            };

            const showFieldError = (input, message) => {
                const field = input.closest('.form-field');
                if (!field) {
                    return;
                }
                let error = field.querySelector('.field-error');
                if (!error) {
                    error = document.createElement('p');
                    error.className = 'field-error';
                    field.appendChild(error);
                }
                error.textContent = message;
                input.classList.add('has-error');
            };

            const clearFieldError = (input) => {
                const field = input.closest('.form-field');
                if (!field) {
                    return;
                }
                const error = field.querySelector('.field-error');
                if (error && !error.hasAttribute('data-persist')) {
                    error.remove();
                }
                input.classList.remove('has-error');
            };

            if (form.name) {
                form.name.addEventListener('input', validators.name);
                form.name.addEventListener('blur', validators.name);
            }

            if (form.category_id) {
                form.category_id.addEventListener('change', validators.category);
            }

            if (form.price) {
                form.price.addEventListener('input', () => {
                    validators.price();
                    formatPriceInput(form.price);
                });
                form.price.addEventListener('blur', validators.price);
            }

            if (form.location_city) {
                form.location_city.addEventListener('input', validators.locationCity);
                form.location_city.addEventListener('blur', validators.locationCity);
            }

            if (form.location_district) {
                form.location_district.addEventListener('input', validators.locationDistrict);
                form.location_district.addEventListener('blur', validators.locationDistrict);
            }

            if (form.original_price) {
                form.original_price.addEventListener('input', () => formatPriceInput(form.original_price));
            }

            const formatPriceInput = (input) => {
                const raw = input.value.replace(/[^0-9]/g, '');
                if (raw === '') {
                    input.value = '';
                    return;
                }
                const numeric = parseInt(raw, 10);
                input.value = numberFormatter.format(numeric);
            };

            form.addEventListener('submit', async (e) => {
                // Kiểm tra xem sản phẩm còn tồn tại không trước khi submit
                try {
                    const response = await fetch(`{{ route('products.checkDelete', $product) }}`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        }
                    });
                    
                    if (response.status === 404) {
                        e.preventDefault();
                        window.location.href = '{{ route('products.notFound') }}';
                        return;
                    }
                    
                    const data = await response.json();
                    if (!data || !data.exists) {
                        e.preventDefault();
                        window.location.href = '{{ route('products.notFound') }}';
                        return;
                    }
                } catch (error) {
                    console.error('Lỗi khi kiểm tra sản phẩm:', error);
                    // Vẫn cho phép submit nếu có lỗi mạng
                }
            });
        })();
    </script>
@endpush
