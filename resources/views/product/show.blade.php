@extends('dashboard')

@section('body-class', 'product-detail-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/product-detail.css') }}">
@endpush

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
                            <button type="button"
                                class="product-gallery-thumb {{ $isActive ? 'active' : '' }}"
                                data-image="{{ $imageUrl }}"
                                aria-label="Ảnh phụ {{ $index + 1 }} của {{ $product->name }}"
                                aria-pressed="{{ $isActive ? 'true' : 'false' }}">
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }} thumbnail {{ $index + 1 }}">
                            </button>
                        @endforeach

                        @for ($i = $galleryImages->count(); $i < 4; $i++)
                            <button type="button" class="product-gallery-thumb placeholder" aria-hidden="true"
                                data-image="{{ asset('images/product_1.png') }}" disabled>
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
                        <button type="button" class="btn-primary" aria-label="Mua ngay">Mua ngay</button>
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
                        <h2>Đánh giá</h2>
                        @forelse ($product->reviews as $review)
                            <article class="product-review">
                                <header>
                                    <strong>{{ optional($review->user)->name ?? 'Người mua ẩn danh' }}</strong>
                                    <span class="product-review-rating">{{ $review->rating }}/5</span>
                                    <span class="product-review-date">{{ optional($review->created_at)->format('d/m/Y') }}</span>
                                </header>
                                <p>{{ $review->comment ?? 'Người dùng không để lại nhận xét.' }}</p>
                            </article>
                        @empty
                            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                        @endforelse
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tabButtons = document.querySelectorAll('.product-tab[data-tab-target]');
            var tabPanels = document.querySelectorAll('.product-tab-section[data-tab-panel]');
            var previewImage = document.querySelector('.product-gallery-preview img[data-active-image]');
            var galleryButtons = document.querySelectorAll('.product-gallery-thumb[data-image]');

            tabButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var targetId = button.getAttribute('data-tab-target');
                    if (!targetId) {
                        return;
                    }

                    tabButtons.forEach(function (btn) {
                        btn.classList.toggle('active', btn === button);
                        btn.setAttribute('aria-selected', btn === button ? 'true' : 'false');
                    });

                    tabPanels.forEach(function (panel) {
                        var isActive = panel.id === targetId;
                        panel.classList.toggle('is-active', isActive);
                        panel.toggleAttribute('hidden', !isActive);
                    });
                });
            });

            galleryButtons.forEach(function (button) {
                if (button.disabled) {
                    return;
                }

                button.addEventListener('click', function () {
                    var imageSrc = button.getAttribute('data-image');
                    if (!imageSrc || !previewImage) {
                        return;
                    }

                    previewImage.src = imageSrc;

                    galleryButtons.forEach(function (btn) {
                        var isActive = btn === button;
                        btn.classList.toggle('active', isActive);
                        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });
                });
            });
        });
    </script>
@endpush
