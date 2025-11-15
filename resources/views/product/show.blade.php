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
                                        <textarea name="comment" id="comment" class="form-control" rows="4" required minlength="10" maxlength="1000"></textarea>
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
                                                            <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required minlength="10" maxlength="1000"> <button class="btn btn-outline-primary" type="submit">Gửi</button>
                                                        </div>
                                                    </form>
                                                    @endauth

                                                    {{-- **BƯỚC QUAN TRỌNG NHẤT:** Tạo container .replies --}}
                                                    <div class="replies-root mt-4">
                                                        @foreach($review->comments as $comment)
                                                        {{-- Thẻ div .comment-item cho CẤP 1 --}}
                                                        <div class="comment-item mb-2" id="comment-{{ $comment->id }}" data-parent-id="{{ $comment->parent_id }}" style="margin-left: 20px; display: block;">
                                                            <div class="d-flex align-items-start">
                                                                <div>
                                                                    <strong>{{ $comment->user->name ?? 'Người dùng' }}:</strong>

                                                                    {{-- [MỚI] Thêm Wrapper cho Cấp 1 --}}
                                                                    <div class="comment-content-wrapper" id="comment-content-wrapper-{{ $comment->id }}">

                                                                        {{-- 1. Nội dung gốc (hiện mặc định) --}}
                                                                        <p class="mb-1" data-comment-content>{{ $comment->content }}</p>

                                                                        {{-- 2. [MỚI] Form sửa (ẩn mặc định) --}}
                                                                        @auth
                                                                        <form id="edit-form-comment-{{ $comment->id }}" class="comment-edit-form d-none mt-2" onsubmit="event.preventDefault(); saveCommentEdit({{ $comment->id }});">
                                                                            <textarea class="form-control form-control-sm" name="content" required>{{ $comment->content }}</textarea>
                                                                            <div class="mt-2">
                                                                                <button type="submit" class="btn btn-primary btn-sm">💾 Lưu</button>
                                                                                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleCommentEditForm({{ $comment->id }})">❌ Hủy</button>
                                                                            </div>
                                                                        </form>
                                                                        @endauth
                                                                    </div>
                                                                    {{-- Kết thúc wrapper --}}


                                                                    {{-- 3. Thanh công cụ (Phản hồi, Sửa, Xóa) --}}
                                                                    <small class="text-muted">
                                                                        {{ $comment->created_at->diffForHumans() }}

                                                                        @auth
                                                                        · <button class="btn btn-link btn-sm text-decoration-none p-0" onclick="toggleReplyForm('comment-{{ $comment->id }}')">💬 Phản hồi</button>

                                                                        {{-- [MỚI] Thêm Nút Sửa/Xóa cho Cấp 1 --}}
                                                                        @if(auth()->id() === $comment->user_id)
                                                                        · <button class="btn btn-link btn-sm text-decoration-none p-0 text-primary" onclick="toggleCommentEditForm({{ $comment->id }})">✏️ Sửa</button>
                                                                        · <button class="btn btn-link btn-sm text-decoration-none p-0 text-danger" onclick="deleteComment({{ $comment->id }})">🗑️ Xóa</button>
                                                                        @endif

                                                                        @endauth
                                                                    </small>

                                                                    {{-- 4. Form phản hồi (cho comment CẤP 1) --}}
                                                                    @auth
                                                                    <form id="reply-form-comment-{{ $comment->id }}" class="reply-form d-none mt-1" action="{{ route('comments.reply', $comment->id) }}" {{-- SỬA LẠI: Trỏ đến route 'reply' --}} method="POST">
                                                                        @csrf
                                                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                                                        <div class="input-group input-group-sm">
                                                                            <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required>
                                                                            <button class="btn btn-outline-primary" type="submit">Gửi</button>
                                                                        </div>
                                                                    </form>
                                                                    @endauth

                                                                    {{-- 5. Container gọi đệ quy Cấp 2+ (ĐÃ SỬA DẤU PHẨY) --}}
                                                                    <div class="replies mt-2">
                                                                        @include('phanhoi.phanhoi', [
                                                                        'comments' => $comment->replies,
                                                                        'level' => 2,
                                                                        'review_id' => $review->id // Truyền review_id xuống
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
            // =======================================================
            // 1. HÀM VALIDATE (ĐÃ TÁCH RA)
            // =======================================================
            /**
             * Kiểm tra nội dung bình luận theo yêu cầu
             * @param {string} content - Nội dung bình luận
             * @returns {string|null} - Trả về chuỗi lỗi nếu vi phạm, ngược lại trả về null
             */
            function validateComment(content) {
                if (!content) {
                    return "Nội dung bình luận không được để trống.";
                }
                const trimmedContent = content.trim();
                if (trimmedContent === "") {
                    return "Nội dung bình luận không được để trống.";
                }
                if (trimmedContent.length < 10 || trimmedContent.length > 1000) {
                    return "Bình luận phải từ 10 đến 1000 ký tự.";
                }
                return null; // Hợp lệ
            }

            /**
             * [MỚI] Kiểm tra số sao
             * @param {string|null} rating - Giá trị số sao (hoặc null)
             * @returns {string|null} - Trả về chuỗi lỗi nếu vi phạm, ngược lại trả về null
             */
            function validateRating(rating) {
                if (!rating) {
                    return "Vui lòng chọn số sao để đánh giá sản phẩm.";
                }
                return null; // Hợp lệ
            }


            document.addEventListener('DOMContentLoaded', function() {
                var tabButtons = document.querySelectorAll('.product-tab[data-tab-target]');
                var tabPanels = document.querySelectorAll('.product-tab-section[data-tab-panel]');
                var previewImage = document.querySelector('.product-gallery-preview img[data-active-image]');
                var galleryButtons = document.querySelectorAll('.product-gallery-thumb[data-image]');

                // Xử lý Tabs
                tabButtons.forEach(function(button) {
                    button.addEventListener('click', function(e) {
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

                // Xử lý Gallery
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

            // =======================================================
            // 2. XỬ LÝ FORM ĐÁNH GIÁ CHÍNH (ĐÃ THÊM VALIDATE SỐ SAO)
            // =======================================================
            document.addEventListener('DOMContentLoaded', function() {
                const reviewForm = document.getElementById('review-form');
                const reviewList = document.getElementById('review-list');

                if (!reviewForm) return;

                reviewForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(reviewForm);
                    const actionUrl = reviewForm.getAttribute('action');

                    // --- [ĐÃ CẬP NHẬT] THÊM VALIDATION SỐ SAO ---
                    const ratingValue = formData.get('rating');
                    const ratingError = validateRating(ratingValue);
                    if (ratingError) {
                        alert(ratingError); // Hiển thị thông báo lỗi
                        return; // Dừng gửi form
                    }
                    // --- KẾT THÚC VALIDATION SỐ SAO ---

                    // --- VALIDATION BÌNH LUẬN ---
                    const commentText = formData.get('comment');
                    const commentError = validateComment(commentText);
                    if (commentError) {
                        alert(commentError); // Hiển thị thông báo lỗi
                        return; // Dừng gửi form
                    }
                    // --- KẾT THÚC VALIDATION BÌNH LUẬN ---

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
                                const userName = review.user?.name || 'Người dùng ẩn danh';

                                let starsHtml = '';
                                for (let i = 1; i <= 5; i++) {
                                    if (i <= review.rating) {
                                        starsHtml += '<i class="bi bi-star-fill text-warning"></i>';
                                    } else {
                                        starsHtml += '<i class="bi bi-star text-secondary"></i>';
                                    }
                                }

                                const csrfToken = formData.get('_token');
                                const commentStoreUrl = `/reviews/${review.id}/comments`;

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
                                                <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required minlength="10" maxlength="1000">
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

                                if (reviewList) {
                                    reviewList.insertAdjacentHTML('afterbegin', reviewHtml);
                                }
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

            // =======================================================
            // 3. XỬ LÝ FORM PHẢN HỒI (ĐÃ THÊM VALIDATE)
            // =======================================================
            document.addEventListener('submit', async function(e) {
                const form = e.target.closest('.reply-form');
                if (!form) return;

                e.preventDefault();

                const formData = new FormData(form);
                const action = form.getAttribute('action');

                // --- VALIDATION BÌNH LUẬN PHẢN HỒI ---
                const contentText = formData.get('content');
                const validationError = validateComment(contentText);

                if (validationError) {
                    alert(validationError); // Hiển thị thông báo lỗi
                    return; // Dừng gửi form
                }
                // --- KẾT THÚC VALIDATION ---

                const submitButton = form.querySelector('[type="submit"]');
                submitButton.disabled = true;
                submitButton.textContent = 'Đang gửi...';

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
                        const newComment = document.createElement('div');
                        newComment.classList.add('comment-item', 'mb-2');
                        newComment.id = `comment-${data.id}`;
                        newComment.dataset.parentId = data.parent_id_db;

                        const MARGIN_STEP = 20;
                        let newMarginLeft = MARGIN_STEP;

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

                        newComment.innerHTML = `
                <div class="d-flex align-items-start">
                    <div>
                        <strong>${data.user || 'Người dùng ẩn danh'}:</strong>
                        <div class="comment-content-wrapper" id="comment-content-wrapper-${data.id}">
                            <p class="mb-1" data-comment-content>${data.content}</p>
                            <form id="edit-form-comment-${data.id}" class="comment-edit-form d-none mt-2" 
                                  onsubmit="event.preventDefault(); saveCommentEdit(${data.id});">
                                <textarea class="form-control form-control-sm" name="content" required>${data.content}</textarea>
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-primary btn-sm">💾 Lưu</button>
                                    <button type="button" class="btn btn-secondary btn-sm" 
                                            onclick="toggleCommentEditForm(${data.id})">❌ Hủy</button>
                                </div>
                            </form>
                        </div>
                        <small class="text-muted">
                            Vừa xong 
                            · <button class="btn btn-link btn-sm text-decoration-none p-0" 
                                onclick="toggleReplyForm('comment-${data.id}')">💬 Phản hồi</button>
                            · <button class="btn btn-link btn-sm text-decoration-none p-0 text-primary" 
                                    onclick="toggleCommentEditForm(${data.id})">✏️ Sửa</button>
                            · <button class="btn btn-link btn-sm text-decoration-none p-0 text-danger" 
                                    onclick="deleteComment(${data.id})">🗑️ Xóa</button>
                        </small>
                        <form id="reply-form-comment-${data.id}" class="reply-form d-none mt-1" 
                              action="/comments/${data.id}/reply" method="POST">
                            <input type="hidden" name="_token" value="${formData.get('_token')}">
                            <input type="hidden" name="parent_id" value="${data.id}">
                            <div class="input-group input-group-sm">
                                <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required minlength="10" maxlength="1000">
                                <button class="btn btn-outline-primary" type="submit">Gửi</button>
                            </div>
                        </form>
                        <div class="replies mt-2"></div> 
                    </div>
                </div>
                `;

                        let container = null;
                        if (parentFormId.startsWith('reply-form-comment-')) {
                            const parentCommentId = parentFormId.split('-').pop();
                            container = document.getElementById(`comment-${parentCommentId}`).querySelector('.replies');
                        } else if (parentFormId.startsWith('reply-form-review-')) {
                            const parentReviewId = parentFormId.split('-').pop();
                            container = document.getElementById(`review-${parentReviewId}`).querySelector('.replies-root');
                        }

                        if (container) {
                            container.appendChild(newComment);
                        } else {
                            console.error('Không tìm thấy container để chèn comment mới.');
                        }

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

            // =======================================================
            // 4. CÁC HÀM TIỆN ÍCH KHÁC (GIỮ NGUYÊN)
            // =======================================================

            function editReview(id, rating, comment) {
                const reviewDiv = document.getElementById(`review-${id}`);
                if (!reviewDiv) return;

                reviewDiv.dataset.original = reviewDiv.innerHTML;

                reviewDiv.innerHTML = `
        <form onsubmit="event.preventDefault(); saveReview(${id});">
            <div class="rating-stars mb-2">
                ${[1,2,3,4,5].map(i => `
                    <i class="${i <= rating ? 'fas' : 'far'} fa-star text-warning" 
                    data-value="${i}" 
                    style="cursor:pointer; font-size:20px;" 
                    onclick="setStar(${id}, ${i})"></i>
                `).join('')}
                <input type="hidden" id="edit-rating-${id}" value="${rating}">
            </div>
            <textarea id="edit-comment-${id}" class="form-control mb-2" required minlength="10" maxlength="1000">${comment}</textarea>
            <button type="submit" class="btn btn-primary btn-sm">💾 Lưu</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit(${id})">❌ Hủy</button>
        </form>
        `;
            }

            function setStar(id, value) {
                const container = document.querySelector(`#review-${id} .rating-stars`);
                if (!container) return;
                const stars = container.querySelectorAll('.fa-star');
                const ratingInput = document.getElementById(`edit-rating-${id}`);
                if (ratingInput) {
                    ratingInput.value = value;
                }

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

            function cancelEdit(id) {
                const reviewDiv = document.getElementById(`review-${id}`);
                if (reviewDiv && reviewDiv.dataset.original) {
                    reviewDiv.innerHTML = reviewDiv.dataset.original;
                    delete reviewDiv.dataset.original;
                }
            }

            function saveReview(id) {
                const rating = document.getElementById(`edit-rating-${id}`).value;
                const comment = document.getElementById(`edit-comment-${id}`).value;

                // VALIDATION CHO FORM SỬA
                const ratingError = validateRating(rating);
                if (ratingError) {
                    alert(ratingError);
                    return false;
                }

                const commentError = validateComment(comment);
                if (commentError) {
                    alert(commentError);
                    return false;
                }

                fetch(`/reviews/${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
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
                            const userName = review.user?.name || 'Người dùng ẩn danh';
                            let starsHtml = '';
                            for (let i = 1; i <= 5; i++) {
                                starsHtml += `<i class="bi ${i <= review.rating ? 'bi-star-fill text-warning' : 'bi-star text-secondary'}"></i>`;
                            }

                            cancelEdit(id);

                            const reviewCard = document.getElementById(`review-${id}`);
                            if (reviewCard) {
                                reviewCard.querySelector('.review-stars').innerHTML = starsHtml;
                                reviewCard.querySelector('p.mb-0').textContent = review.comment;
                                reviewCard.querySelector('.text-muted').textContent = 'Vừa cập nhật';

                                const editButton = reviewCard.querySelector(`button[onclick^="editReview"]`);
                                if (editButton) {
                                    editButton.setAttribute('onclick', `editReview(${review.id}, ${review.rating}, '${review.comment.replace(/'/g, "\\'")}')`);
                                }
                            }
                        } else {
                            alert('❌ Lưu thất bại: ' + (data.message || 'Lỗi.'));
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
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

            window.toggleReplyForm = function(id) {
                const form = document.getElementById(`reply-form-${id}`);
                if (!form) return;

                form.classList.toggle('d-none');

                const parentIdInput = form.querySelector('input[name="parent_id"]');
                if (!parentIdInput) return;

                if (id.startsWith('review-')) {
                    parentIdInput.value = '';
                } else if (id.startsWith('comment-')) {
                    const parentId = id.split('-').pop();
                    parentIdInput.value = parentId;
                }
            };

            window.toggleCommentEditForm = function(commentId) {
                const wrapper = document.getElementById(`comment-content-wrapper-${commentId}`);
                if (!wrapper) return;
                wrapper.querySelector('[data-comment-content]').classList.toggle('d-none');
                wrapper.querySelector('.comment-edit-form').classList.toggle('d-none');
            }

            window.saveCommentEdit = function(commentId) {
                const form = document.getElementById(`edit-form-comment-${commentId}`);
                if (!form) return;
                const content = form.querySelector('textarea[name="content"]').value;

                const validationError = validateComment(content);
                if (validationError) {
                    alert(validationError);
                    return;
                }

                fetch(`/comments/${commentId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            content: content
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const wrapper = document.getElementById(`comment-content-wrapper-${commentId}`);
                            wrapper.querySelector('[data-comment-content]').textContent = data.comment.content;
                            toggleCommentEditForm(commentId);
                        } else {
                            alert('Lỗi khi lưu chỉnh sửa.');
                        }
                    })
                    .catch(() => alert('Lỗi kết nối khi lưu comment.'));
            }

            window.deleteComment = function(commentId) {
                if (!confirm('Bạn có chắc muốn xóa bình luận này?')) return;

                fetch(`/comments/${commentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const commentEl = document.getElementById(`comment-${commentId}`);
                            if (commentEl) {
                                commentEl.remove();
                            }
                        } else {
                            alert('Lỗi khi xóa bình luận: ' + data.message);
                        }
                    })
                    .catch(() => alert('Lỗi kết nối khi xóa comment.'));
            }
        </script>
        @endpush