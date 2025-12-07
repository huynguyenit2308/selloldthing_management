@extends('dashboard')

@section('body-class', 'products-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('styles/products.css') }}">
<link rel="stylesheet" href="{{ asset('styles/favorite.css') }}">
@endpush

@section('content')
<div class="products-wrapper">
    <div class="container">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        <div class="products-breadcrumb">
            <span><a href="{{ url('/') }}">Trang chủ</a></span>
            <span> &gt; </span>
            <span>Danh mục &gt; Tất cả sản phẩm</span>
        </div>

        <div class="products-grid-area">
            <aside class="products-filters">
                <form method="GET" action="{{ route('products.index') }}">
                    <input type="hidden" name="page" value="1">

                    <div class="filter-section">
                        <div class="filter-title">Danh mục</div>
                        <div class="filter-option">
                            <select name="category">
                                <option value="">Tất cả</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($filters['category']==$category->id)>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-title">Lọc theo giá</div>
                        <div class="filter-price-row">
                            <div class="filter-option">
                                <input type="number" name="price_min" placeholder="Từ"
                                    min="0" value="{{ $filters['price_min'] }}">
                            </div>
                            <div class="filter-option">
                                <input type="number" name="price_max" placeholder="Đến"
                                    max="{{ $priceBounds->max_price ?? '' }}" value="{{ $filters['price_max'] }}">
                            </div>
                        </div>
                        <button type="submit" class="filter-apply-button">Áp dụng</button>
                    </div>

                    @if ($conditionOptions->isNotEmpty())
                    <div class="filter-section">
                        <div class="filter-title">Tình trạng</div>
                        @foreach ($conditionOptions as $condition)
                        <label class="filter-option">
                            <input type="radio" name="condition" value="{{ $condition }}"
                                @checked($filters['condition']===$condition)>
                            <span>{{ ucfirst($condition) }}</span>
                        </label>
                        @endforeach
                        <label class="filter-option">
                            <input type="radio" name="condition" value="" @checked(!$filters['condition'])>
                            <span>Tất cả</span>
                        </label>
                    </div>
                    @endif

                    @if ($locationOptions->isNotEmpty())
                    <div class="filter-section">
                        <div class="filter-title">Địa điểm</div>
                        <div class="filter-option">
                            <select name="location">
                                <option value="">Tất cả</option>
                                @foreach ($locationOptions as $location)
                                <option value="{{ $location }}" @selected($filters['location']===$location)>
                                    {{ $location }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                </form>
            </aside>

            <section class="products-list">
                <div class="products-header">
                    <h1 class="products-title">Tất cả sản phẩm</h1>
                    <span class="products-total">{{ $products->total() }} sản phẩm</span>
                </div>

                @if ($products->count() === 0)
                <div class="products-empty">
                    Không tìm thấy sản phẩm phù hợp với bộ lọc hiện tại.
                </div>
                @else
                <div class="product-cards">
                    @foreach ($products as $product)
                    <article class="product-card">
                        <div class="product-card-image">
                            <a href="{{ route('products.show', $product) }}">
                                @php
                                $image = $product->images->first();
                                @endphp
                                @if ($image)
                                <img src="{{ $image->image_url }}" alt="{{ $product->name }}">
                                @else
                                <img src="{{ asset('images/product_1.png') }}" alt="{{ $product->name }}">
                                @endif
                            </a>
                        </div>

                        <div class="product-card-body">
                            <a href="{{ route('products.show', $product) }}"
                                class="product-name">{{ $product->name }}</a>
                            <div class="product-price">
                                <span
                                    class="product-price-current">{{ number_format($product->price, 0, ',', '.') }}
                                    VND</span>
                                @if ($product->original_price && $product->original_price > $product->price)
                                <span
                                    class="product-price-original">{{ number_format($product->original_price, 0, ',', '.') }}
                                    VND</span>
                                @endif
                            </div>
                            <div class="product-meta">
                                <span>Giá gốc:
                                    {{ $product->original_price ? number_format($product->original_price, 0, ',', '.') . ' VND' : 'Đang cập nhật' }}</span>
                                <span>Tình trạng:
                                    {{ $product->condition ? ucfirst($product->condition) : 'Đang cập nhật' }}</span>
                                <span>Địa điểm: {{ $product->location ?? 'Đang cập nhật' }}</span>
                                <span>Tình trạng kho:
                                    {{ $product->quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}</span>
                            </div>
                            <div class="product-actions">
                                <form method="POST" action="{{ route('cart.add') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="product-action-button product-action-primary"
                                        aria-label="Thêm vào giỏ hàng">
                                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                    </button>
                                </form>

                                @php
                                // Sửa lỗi: Dùng $product->id, không phải $similar->id
                                $isFavorited = isset($userFavoriteIds) && in_array($product->id, $userFavoriteIds);
                                @endphp

                                <button type="button"
                                    class="btn-outline btn-icon-only favorite-button {{ $isFavorited ? 'favorited' : '' }}"
                                    aria-label="Yêu thích"
                                    data-id="{{ $product->id }}"
                                    data-url="{{ route('favorites.toggle') }}"
                                    id="fav-btn-{{ $product->id }}"> <!-- Sửa: $product->id -->
                                    <i class="fa {{ $isFavorited ? 'fa-heart' : 'fa-heart-o' }}" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>

                @if ($products->hasPages())
                {{ $products->onEachSide(1)->links('vendor.pagination.products') }}
                @endif
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    // Bọc tất cả code trong DOMContentLoaded để đảm bảo HTML đã tải xong
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.favorite-button').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Ngăn hành vi mặc định của button

                const buttonElement = this;
                const productId = buttonElement.dataset.id;
                const toggleUrl = buttonElement.dataset.url;

                // Lấy CSRF token từ thẻ meta
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Lấy icon bên trong nút
                const icon = buttonElement.querySelector('i.fa');

                // Gửi request đến server bằng Fetch API
                fetch(toggleUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken // Gửi kèm CSRF token
                        },
                        body: JSON.stringify({
                            product_id: productId
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            // [THÊM MỚI] Xử lý lỗi 401 (Chưa đăng nhập)
                            if (response.status === 401) {
                                alert('Bạn cần đăng nhập để thực hiện việc này.');
                                window.location.href = '/login'; // Chuyển hướng đến trang đăng nhập
                            }
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Server sẽ trả về trạng thái mới ('added' hoặc 'removed')
                        if (data.status === 'added') {
                            // Thêm class 'favorited' vào nút
                            buttonElement.classList.add('favorited');
                            // Đổi icon thành 'fa-heart' (đầy)
                            icon.classList.remove('fa-heart-o');
                            icon.classList.add('fa-heart');
                            console.log('Đã thêm vào yêu thích');
                        } else if (data.status === 'removed') {
                            // Xóa class 'favorited' khỏi nút
                            buttonElement.classList.remove('favorited');
                            // Đổi icon thành 'fa-heart-o' (viền)
                            icon.classList.remove('fa-heart');
                            icon.classList.add('fa-heart-o');
                            console.log('Đã xóa khỏi yêu thích');
                        }
                    })
                    .catch(error => {
                        console.error('Có lỗi xảy ra:', error);
                        // Tắt alert để không làm phiền khi user chưa đăng nhập
                        // alert('Đã xảy ra lỗi. Vui lòng thử lại.'); 
                    });
            });
        });

    // Price filter validation
        const filterForm = document.querySelector('.products-filters form');
        const priceMinInput = document.querySelector('input[name="price_min"]');
        const priceMaxInput = document.querySelector('input[name="price_max"]');

        if (filterForm && priceMinInput && priceMaxInput) {
            filterForm.addEventListener('submit', function(e) {
                const priceMin = priceMinInput.value.trim();
                const priceMax = priceMaxInput.value.trim();

                // Check if both fields are filled or both are empty
                if ((priceMin === '' && priceMax !== '') || (priceMin !== '' && priceMax === '')) {
                    e.preventDefault();
                    alert('Vui lòng nhập đầy đủ khoảng giá');
                    return;
                }

                // If both fields are filled, validate them
                if (priceMin !== '' && priceMax !== '') {
                    // Check if inputs are valid numbers
                    if (isNaN(priceMin) || isNaN(priceMax)) {
                        e.preventDefault();
                        alert('Vui lòng nhập giá hợp lệ');
                        return;
                    }

                    const minPrice = parseFloat(priceMin);
                    const maxPrice = parseFloat(priceMax);

                    // Check for negative values
                    if (minPrice < 0 || maxPrice < 0) {
                        e.preventDefault();
                        alert('Giá không được nhỏ hơn 0');
                        return;
                    }

                    // Check if "from" price is greater than "to" price
                    if (minPrice > maxPrice) {
                        e.preventDefault();
                        alert('Giá \'Từ\' không được lớn hơn giá \'Đến\'');
                        return;
                    }
                }
            });
        }

    }); // <-- Kết thúc DOMContentLoaded
</script>
@endpush