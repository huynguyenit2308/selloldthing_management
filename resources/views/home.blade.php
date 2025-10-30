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
                    <li class="menu_item"><a href="#">Trang chủ</a></li>
                    <li class="menu_item"><a href="#">Danh mục</a></li>
                    <li class="menu_item"><a href="#">Sản phẩm</a></li>
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

        <!-- Banner -->

        <div class="banner">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <div class="banner_item align-items-center" style="background-image:url(images/banner_1.jpg)">
                            <div class="banner_category">
                                <a href="categories.html">Tên danh mục</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="banner_item align-items-center" style="background-image:url(images/banner_2.jpg)">
                            <div class="banner_category">
                                <a href="categories.html">Tên danh mục</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="banner_item align-items-center" style="background-image:url(images/banner_3.jpg)">
                            <div class="banner_category">
                                <a href="categories.html">Tên danh mục</a>
                            </div>
                        </div>
                    </div>
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

                                <!-- Slide 1 -->

                                <div class="owl-item product_slider_item">
                                    <div class="product-item">
                                        <div class="product discount">
                                            <div class="product_image">
                                                <img src="images/product_1.png" alt="">
                                            </div>
                                            <div class="favorite favorite_left"></div>
                                            <div
                                                class="product_bubble product_bubble_right product_bubble_red d-flex flex-column align-items-center">
                                                <span>-$20</span>
                                            </div>
                                            <div class="product_info">
                                                <h6 class="product_name"><a href="single.html">Fujifilm X100T 16
                                                        MP Digital Camera (Silver)</a></h6>
                                                <div class="product_price">$520.00<span>$590.00</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 2 -->

                                <div class="owl-item product_slider_item">
                                    <div class="product-item women">
                                        <div class="product">
                                            <div class="product_image">
                                                <img src="images/product_2.png" alt="">
                                            </div>
                                            <div class="favorite"></div>
                                            <div
                                                class="product_bubble product_bubble_left product_bubble_green d-flex flex-column align-items-center">
                                                <span>new</span>
                                            </div>
                                            <div class="product_info">
                                                <h6 class="product_name"><a href="single.html">Samsung CF591
                                                        Series Curved 27-Inch FHD Monitor</a></h6>
                                                <div class="product_price">$610.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 3 -->

                                <div class="owl-item product_slider_item">
                                    <div class="product-item women">
                                        <div class="product">
                                            <div class="product_image">
                                                <img src="images/product_3.png" alt="">
                                            </div>
                                            <div class="favorite"></div>
                                            <div class="product_info">
                                                <h6 class="product_name"><a href="single.html">Blue Yeti USB
                                                        Microphone Blackout Edition</a></h6>
                                                <div class="product_price">$120.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 4 -->

                                <div class="owl-item product_slider_item">
                                    <div class="product-item accessories">
                                        <div class="product">
                                            <div class="product_image">
                                                <img src="images/product_4.png" alt="">
                                            </div>
                                            <div
                                                class="product_bubble product_bubble_right product_bubble_red d-flex flex-column align-items-center">
                                                <span>sale</span>
                                            </div>
                                            <div class="favorite favorite_left"></div>
                                            <div class="product_info">
                                                <h6 class="product_name"><a href="single.html">DYMO LabelWriter
                                                        450 Turbo Thermal Label Printer</a></h6>
                                                <div class="product_price">$410.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 5 -->

                                <div class="owl-item product_slider_item">
                                    <div class="product-item women men">
                                        <div class="product">
                                            <div class="product_image">
                                                <img src="images/product_5.png" alt="">
                                            </div>
                                            <div class="favorite"></div>
                                            <div class="product_info">
                                                <h6 class="product_name"><a href="single.html">Pryma Headphones,
                                                        Rose Gold & Grey</a></h6>
                                                <div class="product_price">$180.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 6 -->

                                <div class="owl-item product_slider_item">
                                    <div class="product-item accessories">
                                        <div class="product discount">
                                            <div class="product_image">
                                                <img src="images/product_6.png" alt="">
                                            </div>
                                            <div class="favorite favorite_left"></div>
                                            <div
                                                class="product_bubble product_bubble_right product_bubble_red d-flex flex-column align-items-center">
                                                <span>-$20</span>
                                            </div>
                                            <div class="product_info">
                                                <h6 class="product_name"><a href="single.html">Fujifilm X100T 16
                                                        MP Digital Camera (Silver)</a></h6>
                                                <div class="product_price">$520.00<span>$590.00</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
