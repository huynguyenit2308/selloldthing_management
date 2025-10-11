@extends('dashboard')

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
                                    data-filter="*">all</li>
                                <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center"
                                    data-filter=".women">Tên danh mục</li>
                                <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center"
                                    data-filter=".accessories">Tên danh mục</li>
                                <li class="grid_sorting_button button d-flex flex-column justify-content-center align-items-center"
                                    data-filter=".men">Tên danh mục</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="product-grid"
                            data-isotope='{ "itemSelector": ".product-item", "layoutMode": "fitRows" }'>

                            <!-- Product 1 -->

                            <div class="product-item men">
                                <div class="product discount product_filter">
                                    <div class="product_image">
                                        <img src="images/product_1.png" alt="">
                                    </div>
                                    <div class="favorite favorite_left"></div>
                                    <div
                                        class="product_bubble product_bubble_right product_bubble_red d-flex flex-column align-items-center">
                                        <span>-$20</span>
                                    </div>
                                    <div class="product_info">
                                        <h6 class="product_name"><a href="single.html">Fujifilm X100T 16 MP
                                                Digital Camera (Silver)</a></h6>
                                        <div class="product_price">$520.00<span>$590.00</span></div>
                                    </div>
                                </div>
                                <div class="red_button add_to_cart_button"><a href="#">add to cart</a></div>
                            </div>

                            <!-- Product 2 -->

                            <div class="product-item women">
                                <div class="product product_filter">
                                    <div class="product_image">
                                        <img src="images/product_2.png" alt="">
                                    </div>
                                    <div class="favorite"></div>
                                    <div
                                        class="product_bubble product_bubble_left product_bubble_green d-flex flex-column align-items-center">
                                        <span>new</span>
                                    </div>
                                    <div class="product_info">
                                        <h6 class="product_name"><a href="single.html">Samsung CF591 Series Curved
                                                27-Inch FHD Monitor</a></h6>
                                        <div class="product_price">$610.00</div>
                                    </div>
                                </div>
                                <div class="red_button add_to_cart_button"><a href="#">add to cart</a></div>
                            </div>

                            <!-- Product 3 -->

                            <div class="product-item accessories">
                                <div class="product product_filter">
                                    <div class="product_image">
                                        <img src="images/product_4.png" alt="">
                                    </div>
                                    <div
                                        class="product_bubble product_bubble_right product_bubble_red d-flex flex-column align-items-center">
                                        <span>sale</span>
                                    </div>
                                    <div class="favorite favorite_left"></div>
                                    <div class="product_info">
                                        <h6 class="product_name"><a href="single.html">DYMO LabelWriter 450 Turbo
                                                Thermal Label Printer</a></h6>
                                        <div class="product_price">$410.00</div>
                                    </div>
                                </div>
                                <div class="red_button add_to_cart_button"><a href="#">add to cart</a></div>
                            </div>

                            <!-- Product 4 -->

                            <div class="product-item accessories">
                                <div class="product discount product_filter">
                                    <div class="product_image">
                                        <img src="images/product_6.png" alt="">
                                    </div>
                                    <div class="favorite favorite_left"></div>
                                    <div
                                        class="product_bubble product_bubble_right product_bubble_red d-flex flex-column align-items-center">
                                        <span>-$20</span>
                                    </div>
                                    <div class="product_info">
                                        <h6 class="product_name"><a href="#single.html">Fujifilm X100T 16 MP
                                                Digital Camera (Silver)</a></h6>
                                        <div class="product_price">$520.00<span>$590.00</span></div>
                                    </div>
                                </div>
                                <div class="red_button add_to_cart_button"><a href="#">add to cart</a></div>
                            </div>

                            <!-- Product 5 -->

                            <div class="product-item men">
                                <div class="product product_filter">
                                    <div class="product_image">
                                        <img src="images/product_9.png" alt="">
                                    </div>
                                    <div
                                        class="product_bubble product_bubble_right product_bubble_red d-flex flex-column align-items-center">
                                        <span>sale</span>
                                    </div>
                                    <div class="favorite favorite_left"></div>
                                    <div class="product_info">
                                        <h6 class="product_name"><a href="single.html">DYMO LabelWriter 450 Turbo
                                                Thermal Label Printer</a></h6>
                                        <div class="product_price">$410.00</div>
                                    </div>
                                </div>
                                <div class="red_button add_to_cart_button"><a href="#">add to cart</a></div>
                            </div>
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
