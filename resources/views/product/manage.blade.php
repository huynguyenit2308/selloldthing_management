@extends('dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/my_products.css') }}">
@endpush

@section('body-class', 'account-products-page')

@section('content')
    @php
        $statusLabels = [
            'all' => 'Tất cả',
            'published' => 'Đang hiển thị',
            'pending' => 'Đang chờ duyệt',
            'hidden' => 'Đang ẩn',
            'sold' => 'Đã bán',
        ];

        $sortLabels = [
            'newest' => 'Mới nhất',
            'oldest' => 'Cũ nhất',
            'price_desc' => 'Giá cao đến thấp',
            'price_asc' => 'Giá thấp đến cao',
            'views_desc' => 'Lượt xem nhiều nhất',
            'views_asc' => 'Lượt xem ít nhất',
        ];
    @endphp

    <div class="account-products-wrapper">
        <div class="container">
            <nav class="account-breadcrumb" aria-label="breadcrumb">
                <ol>
                    <li><a href="{{ url('/') }}">Trang chủ</a></li>
                    <li><span>Tài khoản</span></li>
                    <li aria-current="page"><span>Sản phẩm của tôi</span></li>
                </ol>
            </nav>

            <div class="account-products-header">
                <h1>Sản phẩm của tôi</h1>
                <a href="#" class="btn-primary">Đăng sản phẩm mới</a>
            </div>

            <div class="account-products-content">
                <aside class="account-products-summary" aria-label="Thống kê sản phẩm">
                    <h2>Thống kê</h2>
                    <ul>
                        <li>
                            <span class="label">Tổng sản phẩm</span>
                            <span class="value">{{ number_format($statistics['total'] ?? 0) }}</span>
                        </li>
                        <li>
                            <span class="label">Đang hiển thị</span>
                            <span class="value">{{ number_format($statistics['published'] ?? 0) }}</span>
                        </li>
                        <li>
                            <span class="label">Đang chờ duyệt</span>
                            <span class="value">{{ number_format($statistics['pending'] ?? 0) }}</span>
                        </li>
                        <li>
                            <span class="label">Đang ẩn</span>
                            <span class="value">{{ number_format($statistics['hidden'] ?? 0) }}</span>
                        </li>
                        <li>
                            <span class="label">Đã bán</span>
                            <span class="value">{{ number_format($statistics['sold'] ?? 0) }}</span>
                        </li>
                    </ul>
                </aside>

                <section class="account-products-panel">
                    <header class="account-products-toolbar">
                        <form method="GET" action="{{ route('products.manage') }}" class="toolbar-form">
                            <div class="toolbar-group">
                                <label for="sort">Sắp xếp theo:</label>
                                <select id="sort" name="sort">
                                    @foreach ($sortLabels as $value => $label)
                                        <option value="{{ $value }}" @selected($sortOption === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="toolbar-status">
                                @foreach ($statusLabels as $status => $label)
                                    @php
                                        $count = match ($status) {
                                            'all' => $statistics['total'] ?? 0,
                                            'published' => $statistics['published'] ?? 0,
                                            'pending' => $statistics['pending'] ?? 0,
                                            'hidden' => $statistics['hidden'] ?? 0,
                                            'sold' => $statistics['sold'] ?? 0,
                                            default => 0,
                                        };
                                    @endphp
                                    <button type="submit" name="status" value="{{ $status }}"
                                        class="status-pill {{ $statusFilter === $status ? 'is-active' : '' }}"
                                        aria-pressed="{{ $statusFilter === $status ? 'true' : 'false' }}">
                                        <span>{{ $label }}</span>
                                        <span class="badge">{{ $count }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </form>
                    </header>

                    <div class="account-products-table" role="table">
                        <div class="table-header" role="row">
                            <div class="cell image" role="columnheader">Hình ảnh</div>
                            <div class="cell info" role="columnheader">Tên sản phẩm</div>
                            <div class="cell price" role="columnheader">Giá</div>
                            <div class="cell views" role="columnheader">Lượt xem</div>
                            <div class="cell status" role="columnheader">Trạng thái</div>
                            <div class="cell actions" role="columnheader">Thao tác</div>
                        </div>

                        @forelse ($products as $product)
                            @php
                                $primaryImage = optional($product->images->first())->image_url ?? asset('images/product_1.png');
                                $statusKey = $product->status ?? 'pending';
                                $statusText = $statusLabels[$statusKey] ?? ucfirst($statusKey);
                            @endphp
                            <div class="table-row" role="row">
                                <div class="cell image" role="cell">
                                    <img src="{{ $primaryImage }}" alt="{{ $product->name }}">
                                </div>
                                <div class="cell info" role="cell">
                                    <h3>{{ $product->name }}</h3>
                                    <p>{{ $product->short_description ?? \Illuminate\Support\Str::limit($product->description, 80) ?? 'Chưa có mô tả.' }}</p>
                                </div>
                                <div class="cell price" role="cell">
                                    <span>{{ number_format($product->price, 0, ',', '.') }} VND</span>
                                </div>
                                <div class="cell views" role="cell">
                                    <span>{{ number_format($product->view_count ?? 0) }}</span>
                                </div>
                                <div class="cell status" role="cell">
                                    <span class="status-tag status-{{ $statusKey }}">{{ $statusText }}</span>
                                </div>
                                <div class="cell actions" role="cell">
                                    <button type="button">Ẩn/hiển thị</button>
                                    <button type="button">Sửa</button>
                                    <button type="button">Xóa</button>
                                </div>
                            </div>
                        @empty
                            <div class="table-empty" role="row">
                                <div class="cell" role="cell">
                                    <p>Chưa có sản phẩm nào. Hãy đăng sản phẩm đầu tiên của bạn!</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="account-products-pagination">
                        {{ $products->withQueryString()->links() }}
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
