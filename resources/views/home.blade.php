@extends('dashboard')

@php
    use Illuminate\Support\Str;
@endphp

@push('styles')
    <style>
        .product {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: none;
            min-height: 380px;
            background: #fff;
        }

        .product_filter {
            border: none !important;
            padding: 20px 20px 70px;
        }

        .product_image {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 20px;
            height: 220px;
        }

        .product_image img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            width: auto;
        }

        .product_info {
            padding: 0;
            text-align: center;
        }

        .product_name {
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .product_name a {
            display: block;
            width: 100%;
        }

        .product_price {
            display: block;
        }

        .add_to_cart_button {
            position: absolute;
            left: 50%;
            bottom: 20px;
            width: calc(100% - 48px);
            max-width: 220px;
            transform: translate(-50%, 12px);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.28s ease, transform 0.28s ease;
        }

        .add_to_cart_button a {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 11px 20px;
            border-radius: 12px;
            box-shadow: none;
            font-size: 13px;
            letter-spacing: 0.04em;
        }

        .product-item:hover .add_to_cart_button {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, 0);
        }

        .product_bubble {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            gap: 2px;
            width: auto;
            min-width: 72px;
            height: auto;
            border-radius: 6px;
            text-transform: none;
            white-space: nowrap;
            color: #fff;
        }

        .product_bubble .discount-text {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .product_bubble .discount-value {
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
        }

        .product_slider_container {
            position: relative;
            height: auto;
            padding-bottom: 56px;
        }

        .product_slider_item .product {
            min-height: 340px;
            border-right: none;
            padding: 20px 24px 70px;
        }

        .product_slider_item .product_image {
            height: 190px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
        }

        .product_slider_item .product_image img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .product_slider_item .product_info {
            padding: 0;
        }

        .product_slider_item .product_price {
            margin-bottom: 12px;
        }

        .product_rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            color: #6c6f80;
        }

        .product_rating .stars {
            color: #ffcc00;
            display: flex;
            gap: 2px;
        }

        .product_rating .rating_count {
            color: #9ea2b6;
        }

        /* Banner Category Product Count */
        .banner_product_count {
            display: block;
            font-size: 13px;
            font-weight: 400;
            margin-top: 6px;
            opacity: 0.9;
            letter-spacing: 0.3px;
        }

        .banner_category a {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
    </style>
@endpush

@section('content')
    <main>
        <div class="fs_menu_overlay"></div>
        <div class="hamburger_menu">
            <div class="hamburger_close"><i class="fa fa-times" aria-hidden="true"></i></div>
            <div class="hamburger_menu_content text-right">
                <ul class="menu_top_nav">
                    <li class="menu_item has-children">
                        <a href="#">
                            Tài khoản của tôi
                            <i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="menu_selection">
                            <li><a href="#"><i class="fa fa-sign-in" aria-hidden="true"></i>Đăng nhập</a>
                            </li>
                            <li><a href="#"><i class="fa fa-user-plus" aria-hidden="true"></i>Đăng ký</a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu_item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="menu_item"><a href="{{ route('categories.index') }}">Danh mục</a></li>
                    <li class="menu_item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Admin
                        </a>
                        <div class="dropdown-menu" aria-labelledby="adminDropdown">
                            <a class="dropdown-item" href="#">Quản lý sản phẩm</a>
                            <a class="dropdown-item" href="{{ route('admin.categories.index') }}">Quản lý danh mục</a>
                            <a class="dropdown-item" href="#">Quản lý hóa đơn</a>
                            <a class="dropdown-item" href="{{ route('voucher.list') }}">Quản lys voucher</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Slider -->

        <div class="main_slider" style="background-image:url(images/slider_1.jpg)">
            <div class="container fill_height">
                <div class="row align-items-center fill_height">
                    <div class="col">
                        <div class="main_slider_content">
                            <h1>Chào mừng đến với cửa hàng bán đồ cũ.</h1>
                            <div class="red_button shop_now_button"><a href="#">Mua sắm ngay</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner - Top 3 danh mục có nhiều sản phẩm nhất -->

        <div class="banner">
            <div class="container">
                <div class="row">
                    @php
                        $defaultBanners = [
                            'images/banner_1.jpg',
                            'images/banner_2.jpg',
                            'images/banner_3.jpg'
                        ];
                    @endphp

                    @forelse ($topCategories as $index => $topCategory)
                        @php
                            // Lấy hình ảnh danh mục hoặc dùng banner mặc định
                            $categoryImage = $defaultBanners[$index] ?? 'images/banner_1.jpg';
                            
                            if (!empty($topCategory->image)) {
                                if (filter_var($topCategory->image, FILTER_VALIDATE_URL)) {
                                    $categoryImage = $topCategory->image;
                                } else {
                                    // Sử dụng helper asset() để tạo URL
                                    $categoryImage = asset('storage/' . $topCategory->image);
                                }
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="banner_item align-items-center" 
                                 style="background-image:url({{ $categoryImage }})">
                                <div class="banner_category">
                                    <a href="{{ route('categories.show', $topCategory->id) }}">
                                        {{ $topCategory->name }}
                                        <span class="banner_product_count">({{ $topCategory->products_count }} sản phẩm)</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Hiển thị banner mặc định nếu không có danh mục --}}
                        @foreach ($defaultBanners as $banner)
                            <div class="col-md-4">
                                <div class="banner_item align-items-center" style="background-image:url({{ $banner }})">
                                    <div class="banner_category">
                                        <a href="{{ route('categories.index') }}">Khám phá danh mục</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </div>

        <!-- New Arrivals -->

        <div class="new_arrivals">
            <div class="container">
                <div class="row">
                    <div class="col text-center">
                        <div class="section_title new_arrivals_title">
                            <h2>Sản phẩm</h2>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col text-center">
                        <div class="new_arrivals_sorting">
                            <ul class="arrivals_grid_sorting clearfix button-group filters-button-group">
                                <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center active is-checked"
                                    data-filter="*">Tất cả</li>
                                @foreach ($categories as $category)
                                    @php
                                        $categorySlug = Str::slug($category->name);
                                    @endphp
                                    <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center"
                                        data-filter=".{{ $categorySlug }}">{{ $category->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="product-grid"
                            data-isotope='{ "itemSelector": ".product-item", "layoutMode": "fitRows" }'>
                            @forelse ($products as $product)
                                @php
                                    $categorySlug = $product->category ? Str::slug($product->category->name) : 'khac';
                                    $image = optional($product->images->first())->image_url;
                                    if (empty($image)) {
                                        $image = asset('images/product_1.png');
                                    }
                                    $hasDiscount = $product->original_price && $product->original_price > $product->price;
                                    $discountAmount = $hasDiscount ? $product->original_price - $product->price : 0;
                                @endphp

                                <div class="product-item {{ $categorySlug }}">
                                    <div class="product product_filter d-flex flex-column {{ $hasDiscount ? 'discount' : '' }}">
                                        <div class="product_image">
                                            <img src="{{ $image }}" alt="{{ $product->name }}">
                                        </div>
                                        <div class="favorite {{ $hasDiscount ? 'favorite_left' : '' }}"></div>
                                        @if ($hasDiscount)
                                            <div
                                                class="product_bubble product_bubble_right product_bubble_red">
                                                <span class="discount-value">-{{ number_format($discountAmount, 0, ',', '.') }} VND</span>
                                            </div>
                                        @endif
                                        <div class="product_info">
                                            <h6 class="product_name">
                                                <a href="{{ route('products.show', $product) }}">{{ Str::limit($product->name, 48) }}</a>
                                            </h6>
                                            <div class="product_price">
                                                {{ number_format($product->price, 0, ',', '.') }}₫
                                                @if ($hasDiscount)
                                                    <span>{{ number_format($product->original_price, 0, ',', '.') }}₫</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="red_button add_to_cart_button"><a href="{{ route('products.show', $product) }}">Xem chi tiết</a>
                                    </div>
                                </div>
                            @empty
                                <div class="w-100 text-center py-5">
                                    <p class="mb-0">Hiện chưa có sản phẩm để hiển thị.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best Sellers -->

        <div class="best_sellers">
            <div class="container">
                <div class="row">
                    <div class="col text-center">
                        <div class="section_title new_arrivals_title">
                            <h2>Nhiều đánh giá nhất</h2>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="product_slider_container">
                            <div class="owl-carousel owl-theme product_slider">
                                @forelse ($topReviewedProducts as $product)
                                    @php
                                        $image = optional($product->images->first())->image_url;
                                        if (empty($image)) {
                                            $image = asset('images/product_1.png');
                                        }
                                        $hasDiscount = $product->original_price && $product->original_price > $product->price;
                                        $discountAmount = $hasDiscount ? $product->original_price - $product->price : 0;
                                        $rating = round($product->reviews_avg_rating ?? 0, 1);
                                    @endphp

                                    <div class="owl-item product_slider_item">
                                        <div class="product-item">
                                            <div class="product {{ $hasDiscount ? 'discount' : '' }}">
                                                <div class="product_image">
                                                    <img src="{{ $image }}" alt="{{ $product->name }}">
                                                </div>
                                                <div class="favorite {{ $hasDiscount ? 'favorite_left' : '' }}"></div>
                                                @if ($hasDiscount)
                                                    <div
                                                        class="product_bubble product_bubble_right product_bubble_red d-flex flex-column align-items-center">
                                                        <span class="discount-value">-{{ number_format($discountAmount, 0, ',', '.') }}₫</span>
                                                    </div>
                                                @endif
                                                <div class="product_info">
                                                    <h6 class="product_name">
                                                        <a href="{{ route('products.show', $product) }}">{{ Str::limit($product->name, 48) }}</a>
                                                    </h6>
                                                    <div class="product_price">
                                                        {{ number_format($product->price, 0, ',', '.') }}₫
                                                        @if ($hasDiscount)
                                                            <span>{{ number_format($product->original_price, 0, ',', '.') }}₫</span>
                                                        @endif
                                                    </div>
                                                    <div class="product_rating mt-2">
                                                        <span class="rating_value">{{ $rating }}/5</span>
                                                        <span class="rating_count">({{ $product->reviews_count }} đánh giá)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="w-100 text-center py-4">
                                        <p class="mb-0">Chưa có sản phẩm nổi bật theo đánh giá.</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Slider Navigation -->

                            <div
                                class="product_slider_nav_left product_slider_nav d-flex align-items-center justify-content-center flex-column">
                                <i class="fa fa-chevron-left" aria-hidden="true"></i>
                            </div>
                            <div
                                class="product_slider_nav_right product_slider_nav d-flex align-items-center justify-content-center flex-column">
                                <i class="fa fa-chevron-right" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
