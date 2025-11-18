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
            position: relative;
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

        .watchlist-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            color: #6b7280;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .watchlist-btn:hover {
            transform: scale(1.1);
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .watchlist-btn.watched {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }

        .watchlist-btn.watched:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        .watchlist-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .watchlist-btn .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: currentColor;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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

                        <div class="category-card">
                            @auth
                                <button 
                                    class="watchlist-btn {{ in_array($category->id, $watchedCategoryIds ?? []) ? 'watched' : '' }}" 
                                    onclick="toggleWatchlist(event, {{ $category->id }})"
                                    data-category-id="{{ $category->id }}"
                                    title="{{ in_array($category->id, $watchedCategoryIds ?? []) ? 'Bỏ theo dõi' : 'Theo dõi danh mục' }}">
                                    <i class="fas fa-heart"></i>
                                </button>
                            @endauth
                            <a href="{{ route('categories.show', $category->id) }}" style="text-decoration: none; color: inherit;">
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
                        </div>
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

@push('scripts')
<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Toggle watchlist
    async function toggleWatchlist(event, categoryId) {
        event.preventDefault();
        event.stopPropagation();

        const button = event.currentTarget;
        const icon = button.querySelector('i');
        const isWatched = button.classList.contains('watched');
        
        // Kiểm tra authentication
        @guest
            window.location.href = '{{ route("login") }}';
            return;
        @endguest

        // Disable button trong khi xử lý
        button.disabled = true;
        const originalIcon = icon.className;
        icon.className = 'spinner';

        try {
            const url = `/watchlist/${categoryId}/toggle`;
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Toggle trạng thái
                button.classList.toggle('watched');
                icon.className = originalIcon;
                
                // Cập nhật title
                button.title = button.classList.contains('watched') 
                    ? 'Bỏ theo dõi' 
                    : 'Theo dõi danh mục';

                // Hiển thị thông báo
                showToast(data.message, 'success');
            } else {
                // Xử lý lỗi
                handleWatchlistError(data, button);
                icon.className = originalIcon;
            }
        } catch (error) {
            console.error('Watchlist error:', error);
            showToast('Không thể thực hiện thao tác. Vui lòng thử lại.', 'error');
            icon.className = originalIcon;
        } finally {
            button.disabled = false;
        }
    }

    // Xử lý lỗi watchlist
    function handleWatchlistError(data, button) {
        let message = data.message || 'Có lỗi xảy ra';
        
        switch(data.error) {
            case 'UNAUTHORIZED':
                window.location.href = '{{ route("login") }}';
                break;
            case 'WATCHLIST_LIMIT_REACHED':
                message = 'Bạn đã đạt giới hạn theo dõi. Vui lòng xóa bớt để thêm mới.';
                break;
            case 'RATE_LIMIT_EXCEEDED':
                message = 'Quá nhiều yêu cầu. Vui lòng đợi một chút.';
                break;
            case 'CATEGORY_NOT_FOUND':
                if (data.auto_remove) {
                    // Reload trang nếu category không tồn tại
                    setTimeout(() => window.location.reload(), 1500);
                }
                break;
        }
        
        showToast(message, 'error');
    }

    // Hiển thị toast notification
    function showToast(message, type = 'info') {
        // Xóa toast cũ nếu có
        const oldToast = document.querySelector('.toast-notification');
        if (oldToast) {
            oldToast.remove();
        }

        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        // Hiển thị toast
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Tự động ẩn sau 3 giây
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Thêm CSS cho toast
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            .toast-notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                padding: 16px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 9999;
                opacity: 0;
                transform: translateX(400px);
                transition: all 0.3s ease;
            }
            
            .toast-notification.show {
                opacity: 1;
                transform: translateX(0);
            }
            
            .toast-notification i {
                font-size: 20px;
            }
            
            .toast-success {
                border-left: 4px solid #10b981;
            }
            
            .toast-success i {
                color: #10b981;
            }
            
            .toast-error {
                border-left: 4px solid #ef4444;
            }
            
            .toast-error i {
                color: #ef4444;
            }
            
            .toast-notification span {
                font-size: 14px;
                font-weight: 500;
                color: #1f2937;
            }
        `;
        document.head.appendChild(style);
    }
</script>
@endpush
