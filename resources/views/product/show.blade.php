        @extends('dashboard')

        @section('body-class', 'product-detail-page')

        @push('styles')
        <link rel="stylesheet" href="{{ asset('styles/product-detail.css') }}">
        <link rel="stylesheet" href="{{ asset('styles/review.css') }}">
        @endpush
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        @section('content')
        <div class="product-detail-wrapper">
            <div class="container">
                <div class="product-detail-breadcrumb">
                    <span><a href="{{ url('/') }}">Trang chủ</a></span>
                    <span>&gt;</span>
                    <span><a href="{{ route('products.index') }}">Sản phẩm</a></span>
                    <span>&gt;</span>
                    <span>{{ $product->name }}</span>
                </div>

                <div class="product-detail-content">
                    <section class="product-gallery">
                        @php
                        $galleryImages = $product->images->take(4);
                        if ($galleryImages->isEmpty()) {
                        $galleryImages = collect([null]);
                        }
                        $primaryImageUrl = optional($galleryImages->first())->image_url ?? asset('images/product_1.png');
                        @endphp
                        <div class="product-gallery-preview">
                            <img src="{{ $primaryImageUrl }}" alt="{{ $product->name }}" data-active-image>
                        </div>

                        <div class="product-gallery-thumbnails">
                            @foreach ($galleryImages as $index => $image)
                            @php
                            $imageUrl = $image ? $image->image_url : asset('images/product_1.png');
                            $isActive = $index === 0;
                            @endphp
                            <button type="button" class="product-gallery-thumb {{ $isActive ? 'active' : '' }}" data-image="{{ $imageUrl }}" aria-label="Ảnh phụ {{ $index + 1 }} của {{ $product->name }}" aria-pressed="{{ $isActive ? 'true' : 'false' }}">
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }} thumbnail {{ $index + 1 }}">
                            </button>
                            @endforeach

                            @for ($i = $galleryImages->count(); $i < 4; $i++) <button type="button" class="product-gallery-thumb placeholder" aria-hidden="true" data-image="{{ asset('images/product_1.png') }}" disabled>
                                <img src="{{ asset('images/product_1.png') }}" alt="Ảnh sản phẩm dự phòng">
                                </button>
                                @endfor
                        </div>
                    </section>

                    <section class="product-summary">
                        <header class="product-summary-header">
                            <h1 class="product-title">{{ $product->name }}</h1>
                            <div class="product-price-group">
                                <span class="product-price-current">{{ number_format($product->price, 0, ',', '.') }} VND</span>
                                <span class="product-price-original">
                                    {{ $product->original_price ? number_format($product->original_price, 0, ',', '.') . ' VND' : 'Giá gốc: Đang cập nhật' }}
                                </span>
                            </div>
                            <div class="product-meta-grid">
                                <div>
                                    <span class="product-meta-label">Địa chỉ</span>
                                    <span class="product-meta-value">{{ $product->location ?? 'Đang cập nhật' }}</span>
                                </div>
                                <div>
                                    <span class="product-meta-label">Thời gian đăng</span>
                                    <span class="product-meta-value">{{ optional($product->created_at)->format('d/m/Y') }}</span>
                                </div>
                                <div>
                                    <span class="product-meta-label">Tình trạng</span>
                                    <span class="product-meta-value">{{ $product->condition ? ucfirst($product->condition) : 'Đang cập nhật' }}</span>
                                </div>
                                <div>
                                    <span class="product-meta-label">Kho hàng</span>
                                    <span class="product-meta-value">{{ $product->quantity > 0 ? 'Còn ' . $product->quantity . ' sản phẩm' : 'Hết hàng' }}</span>
                                </div>
                            </div>
                            <p class="product-summary-description">{{ $product->short_description ?? \Illuminate\Support\Str::limit($product->description, 180) ?? 'Chưa có mô tả.' }}</p>
                        </header>

                        <div class="product-summary-actions">
                            <button type="button" class="btn-primary btn-icon-only" aria-label="Thêm vào giỏ hàng">
                                <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="btn-outline btn-icon-only" aria-label="Yêu thích">
                                <i class="fa fa-heart" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="product-summary-rating">
                            <div class="rating-total">
                                <strong>{{ $averageRating > 0 ? $averageRating : 'Chưa có' }}</strong>
                                <span>/ 5</span>
                            </div>
                            <div>
                                <span>{{ $reviewsCount }} đánh giá</span>
                            </div>
                        </div>
                    </section>

                    <aside class="product-seller-card">
                        <div class="product-seller-avatar" aria-hidden="true">Ảnh</div>
                        <div class="product-seller-info">
                            <div class="product-seller-name">Tên người bán: {{ data_get($product, 'seller_name', 'Đang cập nhật') }}</div>
                            <div class="product-seller-meta">Số sao đánh giá: {{ $averageRating > 0 ? $averageRating : 'Chưa có' }}</div>
                            <div class="product-seller-meta">Ngày gia nhập: {{ optional($product->created_at)->format('d/m/Y') }}</div>
                        </div>
                        <div class="product-seller-actions">
                            <button type="button" class="btn-outline btn-with-icon">
                                <i class="fa fa-phone" aria-hidden="true"></i>
                                <span>Gọi điện</span>
                            </button>
                            <button type="button" class="btn-outline btn-with-icon">
                                <i class="fa fa-comments" aria-hidden="true"></i>
                                <span>Nhắn tin</span>
                            </button>
                        </div>
                    </aside>
                </div>

                <section class="product-tabs">
                    <div class="product-tabs-header" role="tablist">
                        <button type="button" class="product-tab active" id="tab-button-description" data-tab-target="tab-description" role="tab" aria-selected="true">Mô tả chi tiết</button>
                        <button type="button" class="product-tab" id="tab-button-specs" data-tab-target="tab-specs" role="tab" aria-selected="false">Thông số kỹ thuật</button>
                        <button type="button" class="product-tab" id="tab-button-reviews" data-tab-target="tab-reviews" role="tab" aria-selected="false">Đánh giá ({{ $reviewsCount }})</button>
                        <button type="button" class="product-tab" id="tab-button-shipping" data-tab-target="tab-shipping" role="tab" aria-selected="false">Vận chuyển &amp; Thanh toán</button>
                    </div>

                    <div class="product-tabs-body">
                        <div class="product-tab-section is-active" id="tab-description" data-tab-panel role="tabpanel" aria-labelledby="tab-button-description">
                            <h2>Mô tả sản phẩm</h2>
                            <p>{{ $product->description ?? 'Thông tin sản phẩm đang được cập nhật.' }}</p>
                            <h3>Đặc điểm nổi bật</h3>
                            <ul>
                                <li>Thông tin chi tiết sẽ được bổ sung sau.</li>
                                <li>Liên hệ người bán để biết thêm chi tiết.</li>
                            </ul>
                            <h3>Tình trạng sản phẩm</h3>
                            <p>{{ $product->condition ? ucfirst($product->condition) : 'Đang cập nhật' }}</p>
                            <h3>Phụ kiện đi kèm</h3>
                            <p>{{ $product->attachments ?? 'Đang cập nhật' }}</p>
                        </div>

                        <div class="product-tab-section" id="tab-specs" data-tab-panel role="tabpanel" aria-labelledby="tab-button-specs" hidden>
                            <h2>Thông số kỹ thuật</h2>
                            <dl class="product-spec-list">
                                <div>
                                    <dt>Danh mục</dt>
                                    <dd>{{ optional($product->category)->name ?? 'Đang cập nhật' }}</dd>
                                </div>
                                <div>
                                    <dt>SKU</dt>
                                    <dd>{{ $product->sku ?? 'Chưa có' }}</dd>
                                </div>
                                <div>
                                    <dt>Xuất xứ</dt>
                                    <dd>{{ $product->origin ?? 'Đang cập nhật' }}</dd>
                                </div>
                                <div>
                                    <dt>Bảo hành</dt>
                                    <dd>{{ $product->warranty ?? 'Đang cập nhật' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="product-tab-section" id="tab-reviews" data-tab-panel role="tabpanel" aria-labelledby="tab-button-reviews" hidden>
                            <div class="container mt-5">
                                <h2>Đánh giá sản phẩm: {{ $product->name }}</h2>
                                <form id="review-form" action="{{ route('reviews.store', $product->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="rating">Số sao (1-5):</label>
                                        <div class="form-group">
                                            <label for="rating">Số sao:</label>
                                            <div class="rating-stars">
                                                @for($i = 5; $i >= 1; $i--)
                                                <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" />
                                                <label for="star{{ $i }}" title="{{ $i }} sao">&#9733;</label>
                                                @endfor
                                            </div>
                                        </div>

                                    </div>
                                    <div class="form-group">
                                        <label for="comment">Nhận xét:</label>
                                        <textarea name="comment" id="comment" class="form-control" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                                </form>
                                <hr>

                                <h3>Danh sách đánh giá</h3>
                                <div id="review-list">
                                    @if($product->reviews->count() > 0)
                                    @foreach($product->reviews as $review)
                                    <div class="card mb-3 position-relative" id="review-{{ $review->id }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1 fw-bold">{{ $review->user->name ?? 'Người dùng ẩn danh' }}</h6>

                                                    <!-- Hiển thị số sao -->
                                                    <div class="review-stars mb-1">
                                                        @for ($i = 1; $i <= 5; $i++) @if ($i <=$review->rating)
                                                            <i class="bi bi-star-fill text-warning"></i>
                                                            @else
                                                            <i class="bi bi-star text-secondary"></i>
                                                            @endif
                                                            @endfor
                                                    </div>

                                                    <!-- Nội dung bình luận -->
                                                    <p class="mb-0">{{ $review->comment }}</p>

                                                    <!-- Thời gian -->
                                                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                                    <!-- Form phan hoi ẩn -->
                                                    @auth
                                                    <button class="btn btn-link btn-sm text-decoration-none p-0 ms-1" onclick="toggleReplyForm('review-{{ $review->id }}')">💬 Phản hồi</button>


                                                    {{-- Form Phản hồi cho Review (có thể dùng route('comments.store', $review->id)) --}}
                                                    <form id="reply-form-review-{{ $review->id }}" class="reply-form d-none mt-2" action="{{ route('comments.store', $review->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="parent_id" value=""> {{-- parent_id sẽ được điền bằng JS khi reply từ nút 'Phản hồi' --}}
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required>
                                                            <button class="btn btn-outline-primary" type="submit">Gửi</button>
                                                        </div>
                                                    </form>
                                                    @endauth

                                                    {{-- **BƯỚC QUAN TRỌNG NHẤT:** Tạo container .replies --}}
                                                    <div class="replies-root mt-4">
                                                        @foreach($review->comments as $comment)
                                                        <div class="comment-item mb-2" id="comment-{{ $comment->id }}" data-parent-id="{{ $comment->parent_id }}" style="margin-left: 20px; display: block;">
                                                            <div class="d-flex align-items-start">
                                                                <div>
                                                                    <strong>{{ $comment->user->name ?? 'Người dùng' }}:</strong>
                                                                    <p class="mb-1" data-comment-content>{{ $comment->content }}</p>
                                                                    <small class="text-muted">
                                                                        {{ $comment->created_at->diffForHumans() }}

                                                                        @auth
                                                                        · <button class="btn btn-link btn-sm text-decoration-none p-0" onclick="toggleReplyForm('comment-{{ $comment->id }}')">Phản hồi</button>
                                                                        {{-- Thêm nút Sửa/Xóa cho comment nếu cần --}}
                                                                        @endauth
                                                                    </small>

                                                                    {{-- Form phản hồi cho comment CẤP 1 --}}
                                                                    @auth
                                                                    <form id="reply-form-comment-{{ $comment->id }}" class="reply-form d-none mt-1" action="{{ route('comments.reply', $review->id) }}" {{-- Giữ nguyên route store --}} method="POST">
                                                                        @csrf
                                                                        {{-- Quan trọng: parent_id là của comment này --}}
                                                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                                                        <div class="input-group input-group-sm">
                                                                            <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required>
                                                                            <button class="btn btn-outline-primary" type="submit">Gửi</button>
                                                                        </div>
                                                                    </form>
                                                                    @endauth

                                                                    <div class="replies mt-2">
                                                                        @include('phanhoi.phanhoi', [
                                                                        'comments' => $comment->replies,
                                                                        'level' => 2
                                                                        ])
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>

                                                    <!-- Form sửa ẩn -->
                                                    <div class="edit-form d-none mt-2">
                                                        <div class="mb-2">
                                                            <label>Số sao:</label>
                                                            <select class="form-control edit-rating">
                                                                @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}" {{ $i == $review->rating ? 'selected' : '' }}>{{ $i }}</option>
                                                                    @endfor
                                                            </select>
                                                        </div>
                                                        <div class="mb-2">
                                                            <textarea class="form-control edit-comment">{{ $review->comment }}</textarea>
                                                        </div>
                                                        <button class="btn btn-sm btn-primary" onclick="saveEdit({{ $review->id }})">💾 Lưu</button>
                                                        <button class="btn btn-sm btn-secondary" onclick="cancelEdit({{ $review->id }})">❌ Hủy</button>
                                                    </div>
                                                </div>

                                                @if(auth()->check() && auth()->id() === $review->user_id)
                                                <!-- Dropdown -->
                                                <div class="dropdown">
                                                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <button class="dropdown-item" onclick="editReview({{ $review->id }}, {{ $review->rating }}, '{{ addslashes($review->comment) }}')">
                                                                ✏️ Sửa
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item text-danger" onclick="deleteReview({{ $review->id }})">
                                                                🗑️ Xóa
                                                            </button>
                                                        </li>
                                                    </ul>

                                                </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

                                    @else
                                    <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <div class="product-tab-section" id="tab-shipping" data-tab-panel role="tabpanel" aria-labelledby="tab-button-shipping" hidden>
                            <h2>Vận chuyển &amp; Thanh toán</h2>
                            <p>Hỗ trợ giao hàng toàn quốc. Vui lòng liên hệ người bán để thống nhất phí vận chuyển.</p>
                            <p>Phương thức thanh toán linh hoạt: tiền mặt, chuyển khoản hoặc ví điện tử (nếu người bán hỗ trợ).</p>
                        </div>
                    </div>
                </section>

                <section class="product-similar">
                    <header class="product-similar-header">
                        <h2>Sản phẩm tương tự</h2>
                    </header>

                    <div class="product-similar-grid">
                        @forelse ($similarProducts as $similar)
                        @php
                        $similarImage = $similar->images->first();
                        @endphp
                        <article class="product-similar-card">
                            <div class="product-similar-image">
                                @if ($similarImage)
                                <img src="{{ $similarImage->image_url }}" alt="{{ $similar->name }}">
                                @else
                                <img src="{{ asset('images/product_1.png') }}" alt="{{ $similar->name }}">
                                @endif
                            </div>
                            <div class="product-similar-body">
                                <h3><a href="{{ route('products.show', $similar) }}">{{ $similar->name }}</a></h3>
                                <div class="product-similar-prices">
                                    <span class="current">{{ number_format($similar->price, 0, ',', '.') }} VND</span>
                                    <span class="original">
                                        {{ $similar->original_price ? number_format($similar->original_price, 0, ',', '.') . ' VND' : '' }}
                                    </span>
                                </div>
                                <div class="product-similar-meta">
                                    <span>{{ $similar->condition ? ucfirst($similar->condition) : 'Tình trạng: cập nhật' }}</span>
                                    <span>{{ $similar->location ?? 'Địa điểm: cập nhật' }}</span>
                                </div>
                                <div class="product-similar-actions">
                                    <a class="btn-primary btn-icon-only" href="{{ route('products.show', $similar) }}" aria-label="Thêm vào giỏ hàng">
                                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                    </a>
                                    <button type="button" class="btn-outline btn-icon-only" aria-label="Yêu thích">
                                        <i class="fa fa-heart" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </article>
                        @empty
                        <p>Chưa có sản phẩm tương tự để gợi ý.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
        <!-- Bootstrap 5 JS Bundle (có PopperJS để dropdown hoạt động) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        @endsection

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var tabButtons = document.querySelectorAll('.product-tab[data-tab-target]');
                var tabPanels = document.querySelectorAll('.product-tab-section[data-tab-panel]');
                var previewImage = document.querySelector('.product-gallery-preview img[data-active-image]');
                var galleryButtons = document.querySelectorAll('.product-gallery-thumb[data-image]');


                tabButtons.forEach(function(button) {
                    button.addEventListener('click', function(e) {
                        //e.preventDefault(); // Ngăn reload trang
                        e.preventDefault();
                        var targetId = button.getAttribute('data-tab-target');
                        if (!targetId) return;

                        tabButtons.forEach(function(btn) {
                            btn.classList.toggle('active', btn === button);
                            btn.setAttribute('aria-selected', btn === button ? 'true' : 'false');
                        });

                        tabPanels.forEach(function(panel) {
                            var isActive = panel.id === targetId;
                            panel.classList.toggle('is-active', isActive);
                            panel.toggleAttribute('hidden', !isActive);
                        });
                    });
                });

                galleryButtons.forEach(function(button) {
                    if (button.disabled) {
                        return;
                    }

                    button.addEventListener('click', function() {
                        var imageSrc = button.getAttribute('data-image');
                        if (!imageSrc || !previewImage) {
                            return;
                        }

                        previewImage.src = imageSrc;

                        galleryButtons.forEach(function(btn) {
                            var isActive = btn === button;
                            btn.classList.toggle('active', isActive);
                            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                        });
                    });
                });
            });
            document.addEventListener('DOMContentLoaded', function() {
                const reviewForm = document.getElementById('review-form');
                const reviewList = document.getElementById('review-list');

                reviewForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // Ngăn reload trang

                    const formData = new FormData(reviewForm);
                    const actionUrl = reviewForm.getAttribute('action');

                    fetch(actionUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': formData.get('_token'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                const review = data.review;
                                const userName = review.user?.name || 'Người dùng ẩn danh'; // Dùng tên user

                                // 1. [MỚI] Helper để tạo HTML cho các ngôi sao
                                let starsHtml = '';
                                for (let i = 1; i <= 5; i++) {
                                    if (i <= review.rating) {
                                        starsHtml += '<i class="bi bi-star-fill text-warning"></i>';
                                    } else {
                                        starsHtml += '<i class="bi bi-star text-secondary"></i>';
                                    }
                                }

                                // 2. [MỚI] Lấy CSRF token và URL cho form phản hồi Cấp 1
                                const csrfToken = formData.get('_token');
                                const commentStoreUrl = `/reviews/${review.id}/comments`; // Trỏ đến route 'comments.store'

                                // 3. [MỚI] Đây là toàn bộ HTML chính xác
                                const reviewHtml = `
                            <div class="card mb-3 position-relative" id="review-${review.id}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-bold">${userName}</h6>
                                            
                                            <div class="review-stars mb-1">
                                                ${starsHtml}
                                            </div>

                                            <p class="mb-0">${review.comment}</p>

                                            <small class="text-muted">Vừa xong</small>

                                            <button class="btn btn-link btn-sm text-decoration-none p-0 ms-1" onclick="toggleReplyForm('review-${review.id}')">💬 Phản hồi</button>

                                            <form id="reply-form-review-${review.id}" class="reply-form d-none mt-2" action="${commentStoreUrl}" method="POST">
                                                <input type="hidden" name="_token" value="${csrfToken}">
                                                <input type="hidden" name="parent_id" value="">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required>
                                                    <button class="btn btn-outline-primary" type="submit">Gửi</button>
                                                </div>
                                            </form>

                                            <div class="replies-root mt-4"></div> 
                                        </div>

                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button class="dropdown-item" onclick="editReview(${review.id}, ${review.rating}, '${review.comment.replace(/'/g, "\\'")}')">
                                                        ✏️ Sửa
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger" onclick="deleteReview(${review.id})">
                                                        🗑️ Xóa
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                                reviewList.insertAdjacentHTML('afterbegin', reviewHtml);
                                reviewForm.reset();

                            } else {
                                alert('❌ Có lỗi xảy ra: ' + (data.message || 'Vui lòng thử lại.'));
                            }
                        })
                        .catch(err => {
                            console.error('Lỗi:', err);
                            alert('⚠️ Gửi đánh giá thất bại, thử lại sau.');
                        });
                });
            });

            function editReview(id, rating, comment) {
                const reviewDiv = document.getElementById(`review-${id}`);

                // Lưu HTML gốc để có thể khôi phục nếu hủy
                reviewDiv.dataset.original = reviewDiv.innerHTML;

                // Hiển thị form sửa tại chỗ
                reviewDiv.innerHTML = `
                <form onsubmit="return saveReview(${id})">
                    <div class="rating-stars mb-2">
                        ${[1,2,3,4,5].map(i => `
                            <i class="${i <= rating ? 'fas' : 'far'} fa-star text-warning" 
                            data-value="${i}" 
                            style="cursor:pointer; font-size:20px;" 
                            onclick="setStar(${id}, ${i})"></i>
                        `).join('')}
                        <input type="hidden" id="edit-rating-${id}" value="${rating}">
                    </div>
                    <textarea id="edit-comment-${id}" class="form-control mb-2">${comment}</textarea>
                    <button type="submit" class="btn btn-primary btn-sm">💾 Lưu</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit(${id})">❌ Hủy</button>
                </form>
            `;
            }

            // Chọn sao
            function setStar(id, value) {
                const container = document.querySelector(`#review-${id} .rating-stars`);
                const stars = container.querySelectorAll('.fa-star');
                document.getElementById(`edit-rating-${id}`).value = value;

                stars.forEach((star, i) => {
                    if (i < value) {
                        star.classList.remove('far');
                        star.classList.add('fas', 'text-warning');
                    } else {
                        star.classList.remove('fas', 'text-warning');
                        star.classList.add('far');
                    }
                });
            }

            // Hủy sửa
            function cancelEdit(id) {
                const reviewDiv = document.getElementById(`review-${id}`);
                reviewDiv.innerHTML = reviewDiv.dataset.original;
            }

            // Lưu sửa
            function saveReview(id) {
                const rating = document.getElementById(`edit-rating-${id}`).value;
                const comment = document.getElementById(`edit-comment-${id}`).value;

                fetch(`/reviews/${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            rating,
                            comment
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const review = data.review;
                            const userName = review.user?.name || 'Người dùng ẩn danh'; // Dùng tên user

                            // 1. [MỚI] Helper để tạo HTML cho các ngôi sao
                            let starsHtml = '';
                            for (let i = 1; i <= 5; i++) {
                                if (i <= review.rating) {
                                    starsHtml += '<i class="bi bi-star-fill text-warning"></i>';
                                } else {
                                    starsHtml += '<i class="bi bi-star text-secondary"></i>';
                                }
                            }

                            // 2. [MỚI] Lấy CSRF token và URL cho form phản hồi Cấp 1
                            const csrfToken = formData.get('_token');
                            const commentStoreUrl = `/reviews/${review.id}/comments`; // Trỏ đến route 'comments.store'

                            // 3. [MỚI] Đây là toàn bộ HTML chính xác
                            const reviewHtml = `
                            <div class="card mb-3 position-relative" id="review-${review.id}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-bold">${userName}</h6>
                                            
                                            <div class="review-stars mb-1">
                                                ${starsHtml}
                                            </div>

                                            <p class="mb-0">${review.comment}</p>

                                            <small class="text-muted">Vừa xong</small>

                                            <button class="btn btn-link btn-sm text-decoration-none p-0 ms-1" onclick="toggleReplyForm('review-${review.id}')">💬 Phản hồi</button>

                                            <form id="reply-form-review-${review.id}" class="reply-form d-none mt-2" action="${commentStoreUrl}" method="POST">
                                                <input type="hidden" name="_token" value="${csrfToken}">
                                                <input type="hidden" name="parent_id" value="">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required>
                                                    <button class="btn btn-outline-primary" type="submit">Gửi</button>
                                                </div>
                                            </form>

                                            <div class="replies-root mt-4"></div> 
                                        </div>

                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button class="dropdown-item" onclick="editReview(${review.id}, ${review.rating}, '${review.comment.replace(/'/g, "\\'")}')">
                                                        ✏️ Sửa
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger" onclick="deleteReview(${review.id})">
                                                        🗑️ Xóa
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                            reviewList.insertAdjacentHTML('afterbegin', reviewHtml);
                            reviewForm.reset();

                        } else {
                            alert('❌ Có lỗi xảy ra: ' + (data.message || 'Vui lòng thử lại.'));
                        }
                    })
                    .catch(() => alert('⚠️ Có lỗi khi gửi dữ liệu lên server.'));

                return false;
            }

            function deleteReview(id) {
                if (!confirm('Bạn có chắc muốn xóa đánh giá này không?')) return;

                fetch(`/reviews/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const reviewDiv = document.getElementById(`review-${id}`);
                            if (reviewDiv) reviewDiv.remove();
                            alert('✅ Đã xóa đánh giá thành công!');
                        } else {
                            alert('❌ Có lỗi xảy ra khi xóa.');
                        }
                    })
                    .catch(err => console.error(err));
            }

            function toggleReplyForm(id) {
                const form = document.getElementById(`reply-form-${id}`);
                form.classList.toggle('d-none');
            }


            // ================= COMMENT REPLY (AJAX) =================
            // Toggle form reply
            window.toggleReplyForm = function(id) { // id truyền vào là 'review-123' hoặc 'comment-456'
                const form = document.getElementById(`reply-form-${id}`);
                if (!form) return;

                form.classList.toggle('d-none');

                // --- ĐÂY LÀ PHẦN SỬA LỖI QUAN TRỌNG ---
                const parentIdInput = form.querySelector('input[name="parent_id"]');

                if (id.startsWith('review-')) {
                    // 1. Nếu là phản hồi cho REVIEW (tạo Cấp 1)
                    // Chúng ta phải set parent_id = rỗng (NULL)
                    parentIdInput.value = '';
                } else if (id.startsWith('comment-')) {
                    // 2. Nếu là phản hồi cho COMMENT (tạo Cấp 2+)
                    // Chúng ta set parent_id = ID của comment cha
                    const parentId = id.split('-').pop();
                    parentIdInput.value = parentId;
                }
            };

            // Hàm tính toán cấp độ mới
            function calculateNewLevel(parentId) {
                const MARGIN_STEP = 20; // Thụt lề mỗi cấp là 20px

                // 1. Nếu là phản hồi cho REVIEW (cha là Review ID, không phải Comment ID)
                // Review ID có thể là số, nhưng ta chỉ tìm margin từ element cha đã tồn tại.
                const parentReview = document.getElementById(`review-${parentId}`);
                if (parentReview) {
                    // Đây là cấp độ 1 của comments (con của review), bắt đầu margin từ 0.
                    return MARGIN_STEP; // Cấp 1 có margin là 20px
                }

                // 2. Nếu là phản hồi cho COMMENT (cha là Comment ID)
                const parentComment = document.getElementById(`comment-${parentId}`);
                if (parentComment) {
                    const currentMargin = parseInt(parentComment.style.marginLeft) || 0;
                    return currentMargin + MARGIN_STEP;
                }

                // Fallback (chưa xác định cha, có thể là lỗi hoặc comment gốc)
                return 0;
            }

            /**
             * Chèn Comment (PHẢN HỒI) mới vào DOM
             * @param {Object} data - Dữ liệu comment mới (id, parent_id, user, content, v.v.)
             */

            // Xử lý sự kiện Submit Form (AJAX) - Chỉ dành cho các form phản hồi con
            document.addEventListener('submit', async function(e) {
                const form = e.target.closest('.reply-form');
                if (!form) return;

                e.preventDefault();

                const submitButton = form.querySelector('[type="submit"]');
                submitButton.disabled = true;
                submitButton.textContent = 'Đang gửi...';

                const formData = new FormData(form);
                const action = form.getAttribute('action');

                try {
                    const res = await fetch(action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await res.json();

                    if (data.success) {
                        // ------------ LOGIC CHÈN MỚI THAY CHO insertNewComment ------------

                        const newComment = document.createElement('div');
                        newComment.classList.add('comment-item', 'mb-2');
                        newComment.id = `comment-${data.id}`;
                        newComment.dataset.parentId = data.parent_id_db; // Dùng parent_id từ CSDL (nếu có)

                        // 1. Tính toán thụt lề (Margin)
                        const MARGIN_STEP = 20;
                        let newMarginLeft = MARGIN_STEP; // Mặc định là Cấp 1

                        // Lấy ID của form cha (ví dụ: 'reply-form-comment-456')
                        const parentFormId = form.id;
                        if (parentFormId.startsWith('reply-form-comment-')) {
                            const parentCommentId = parentFormId.split('-').pop();
                            const parentComment = document.getElementById(`comment-${parentCommentId}`);
                            if (parentComment) {
                                const currentMargin = parseInt(parentComment.style.marginLeft) || 0;
                                newMarginLeft = currentMargin + MARGIN_STEP;
                            }
                        }
                        newComment.style.cssText = `margin-left: ${newMarginLeft}px; display: block;`;

                        // 2. Tạo HTML cho comment mới
                        newComment.innerHTML = `
                    <div class="d-flex align-items-start">
                        <div>
                            <strong>${data.user || 'Người dùng ẩn danh'}:</strong>
                            <p class="mb-1">${data.content}</p>
                            <small class="text-muted">
                                Vừa xong 
                                · <button class="btn btn-link btn-sm text-decoration-none p-0" 
                                    onclick="toggleReplyForm('comment-${data.id}')">Phản hồi</button>
                            </small>

                            <form id="reply-form-comment-${data.id}" class="reply-form d-none mt-1" 
                            action="/comments/${data.id}/reply" method="POST">
                                <input type="hidden" name="_token" value="${formData.get('_token')}">
                                <input type="hidden" name="parent_id" value="${data.id}">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required>
                                    <button class="btn btn-outline-primary" type="submit">Gửi</button>
                                </div>
                            </form>
                            
                            <div class="replies mt-2"></div> 
                        </div>
                    </div>
                    `;

                        // 3. Tìm đúng container để chèn
                        let container = null;
                        if (parentFormId.startsWith('reply-form-comment-')) {
                            // Đây là Cấp 2+, chèn vào '.replies' của comment cha
                            const parentCommentId = parentFormId.split('-').pop();
                            container = document.getElementById(`comment-${parentCommentId}`).querySelector('.replies');
                        } else if (parentFormId.startsWith('reply-form-review-')) {
                            // Đây là Cấp 1, chèn vào '.replies-root' của review cha
                            const parentReviewId = parentFormId.split('-').pop();
                            container = document.getElementById(`review-${parentReviewId}`).querySelector('.replies-root');
                        }

                        // 4. Chèn vào DOM
                        if (container) {
                            container.appendChild(newComment);
                        } else {
                            console.error('Không tìm thấy container để chèn comment mới.');
                        }

                        // 5. Dọn dẹp
                        form.reset();
                        form.classList.add('d-none');

                    } else {
                        alert('❌ Gửi phản hồi thất bại: ' + (data.message || 'Lỗi không xác định.'));
                    }
                } catch (err) {
                    console.error('Lỗi Fetch/AJAX:', err);
                    alert('⚠️ Lỗi kết nối máy chủ khi gửi phản hồi.');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Gửi';
                }
            });
        </script>
        @endpush