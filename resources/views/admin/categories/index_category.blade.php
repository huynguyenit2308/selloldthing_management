@extends('dashboard')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@push('styles')
    <style>
        /* Categories Index Page */
        .categories-page {
            margin-top: 140px;
            padding: 40px 0 60px;
            background-color: #f8f9fa;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 12px;
        }

        .page-subtitle {
            font-size: 16px;
            color: #6c757d;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .category-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            text-decoration: none;
            color: inherit;
        }

        .category-image-wrapper {
            position: relative;
            padding-top: 60%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
        }

        .category-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .category-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.4) 100%);
            display: flex;
            align-items: flex-end;
            padding: 20px;
        }

        .category-name-overlay {
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .category-info {
            padding: 20px;
        }

        .category-name {
            font-size: 18px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 8px;
        }

        .category-description {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 40px;
        }

        .category-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }

        .category-count {
            font-size: 14px;
            color: #0066cc;
            font-weight: 600;
        }

        .category-arrow {
            color: #0066cc;
            font-size: 18px;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .empty-icon {
            font-size: 72px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-title {
            font-size: 24px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 12px;
        }

        .empty-text {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .empty-cta {
            margin-top: 24px;
        }

        .btn-explore {
            display: inline-block;
            padding: 12px 32px;
            background: #0066cc;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-explore:hover {
            background: #0052a3;
            color: #fff;
            text-decoration: none;
            transform: translateY(-2px);
        }

        /* Error States */
        .error-alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .error-alert.error {
            background: #f8d7da;
            border-color: #dc3545;
        }

        .error-alert.warning {
            background: #fff3cd;
            border-color: #ffc107;
        }

        .error-alert.info {
            background: #d1ecf1;
            border-color: #17a2b8;
        }

        .error-icon {
            font-size: 32px;
            flex-shrink: 0;
        }

        .error-content {
            flex: 1;
        }

        .error-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .error-message {
            font-size: 14px;
            color: #6c757d;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            margin-top: 12px;
        }

        .btn-retry, .btn-sync {
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-retry {
            background: #0066cc;
            color: #fff;
        }

        .btn-retry:hover {
            background: #0052a3;
        }

        .btn-sync {
            background: #28a745;
            color: #fff;
        }

        .btn-sync:hover {
            background: #218838;
        }

        @media (max-width: 768px) {
            .categories-page {
                margin-top: 120px;
                padding: 24px 0 40px;
            }

            .page-title {
                font-size: 24px;
            }

            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 16px;
            }

            .category-info {
                padding: 16px;
            }

            .category-name {
                font-size: 16px;
            }
        }
    </style>
@endpush

@section('content')
    <main class="categories-page">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Danh mục sản phẩm</h1>
                <p class="page-subtitle">Khám phá các danh mục sản phẩm đa dạng của chúng tôi</p>
            </div>

            {{-- Hiển thị lỗi kết nối --}}
            @if (isset($error) && $error)
                <div class="error-alert error">
                    <div class="error-icon">⚠️</div>
                    <div class="error-content">
                        <div class="error-title">{{ $error['message'] }}</div>
                        <div class="error-message">Vui lòng kiểm tra kết nối mạng và thử lại</div>
                        @if ($error['action'] === 'retry')
                            <div class="error-actions">
                                <button class="btn-retry" onclick="window.location.reload()">Thử lại</button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($categories->count() > 0)
                <!-- Categories Grid -->
                <div class="categories-grid">
                    @foreach ($categories as $category)
                        @php
                            $imageUrl = asset('images/product_1.png');
                            if (!empty($category->image)) {
                                if (filter_var($category->image, FILTER_VALIDATE_URL)) {
                                    $imageUrl = $category->image;
                                } elseif (Storage::disk('public')->exists($category->image)) {
                                    $imageUrl = Storage::url($category->image);
                                } elseif (file_exists(public_path($category->image))) {
                                    $imageUrl = asset($category->image);
                                }
                            }
                        @endphp

                        <a href="{{ route('categories.show', $category->id) }}" class="category-card">
                            <div class="category-image-wrapper">
                                <img src="{{ $imageUrl }}" alt="{{ $category->name }}" class="category-image">
                                <div class="category-overlay">
                                    <h3 class="category-name-overlay">{{ $category->name }}</h3>
                                </div>
                            </div>
                            <div class="category-info">
                                <h3 class="category-name">{{ $category->name }}</h3>
                                <p class="category-description">
                                    {{ $category->description ?: 'Khám phá các sản phẩm chất lượng trong danh mục này' }}
                                </p>
                                <div class="category-meta">
                                    <span class="category-count">
                                        {{ $category->products_count }} sản phẩm
                                    </span>
                                    <span class="category-arrow">→</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <!-- Empty State: Không có danh mục -->
                <div class="empty-state">
                    <div class="empty-icon">📂</div>
                    <h2 class="empty-title">Chưa có danh mục nào</h2>
                    <p class="empty-text">Hiện tại chưa có danh mục sản phẩm nào.<br>Hãy quay lại sau để khám phá các sản phẩm mới!</p>
                    <div class="empty-cta">
                        <a href="{{ route('home') }}" class="btn-explore">Về trang chủ</a>
                    </div>
                </div>
            @endif
        </div>
    </main>
@endsection
