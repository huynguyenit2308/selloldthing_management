@extends('dashboard')
<!-- Thêm Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@push('styles')
<style>
    .watchlist-page {
        background: linear-gradient(135deg, #f5f7fb 0%, #eef2f8 100%);
        padding: 32px 0 48px;
        min-height: calc(100vh - 120px);
        margin-top: 120px;
    }

    .watchlist-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .main-content {
        display: flex;
        gap: 24px;
        align-items: flex-start;
    }

    .sidebar {
        width: 260px;
        background: #ffffff;
        border-radius: 20px;
        padding: 28px 24px;
        box-shadow: 0 18px 40px rgba(18, 38, 63, 0.08);
        position: sticky;
        top: 110px;
        height: fit-content;
        flex-shrink: 0;
    }

    .sidebar h3 {
        font-size: 14px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0 0 28px 0;
        display: grid;
        gap: 10px;
    }

    .sidebar ul li a,
    .sidebar ul li button {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        color: #1f2937;
        padding: 12px 16px;
        border-radius: 14px;
        background: #f9fafb;
        transition: all 0.25s ease;
        width: 100%;
        text-align: left;
        border: none;
        cursor: pointer;
    }

    .sidebar ul li a i,
    .sidebar ul li button i {
        color: #6366f1;
    }

    .sidebar ul li a:hover,
    .sidebar ul li a.active,
    .sidebar ul li button:hover {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #ffffff;
        box-shadow: 0 12px 25px rgba(79, 70, 229, 0.25);
    }

    .sidebar ul li a:hover i,
    .sidebar ul li a.active i,
    .sidebar ul li button:hover i {
        color: #ffffff;
    }

    .content {
        flex: 1;
        background: #ffffff;
        border-radius: 24px;
        padding: 32px 36px;
        box-shadow: 0 24px 60px rgba(16, 24, 40, 0.08);
    }

    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .content-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .search-box input {
        width: 100%;
        padding: 10px 40px 10px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .search-box input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .search-box button {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 6px;
    }

    .watchlist-stats {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        padding: 16px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(168, 85, 247, 0.05));
        border-radius: 16px;
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stat-item i {
        color: #6366f1;
        font-size: 20px;
    }

    .stat-item .stat-value {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }

    .stat-item .stat-label {
        font-size: 13px;
        color: #6b7280;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .category-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .category-card:hover {
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
        transform: translateY(-4px);
    }

    .category-card:hover::before {
        transform: scaleX(1);
    }

    .category-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .category-info h3 {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }

    .category-info p {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }

    .category-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        font-size: 20px;
        flex-shrink: 0;
    }

    .category-stats {
        display: flex;
        gap: 16px;
        margin-bottom: 16px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .category-stat {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #6b7280;
    }

    .category-stat i {
        color: #6366f1;
    }

    .category-actions {
        display: flex;
        gap: 10px;
    }

    .btn-view {
        flex: 1;
        padding: 10px 16px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
    }

    .btn-remove {
        padding: 10px 16px;
        border: 1px solid #ef4444;
        border-radius: 10px;
        background: white;
        color: #ef4444;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-remove:hover {
        background: #ef4444;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 10px;
    }

    .empty-state p {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 24px;
    }

    .btn-explore {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-explore:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(99, 102, 241, 0.3);
    }

    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
    }

    .alert-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert-success {
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .alert-info {
        background: #dbeafe;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 32px;
    }

    .pagination a,
    .pagination span {
        padding: 8px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        color: #6b7280;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .pagination a:hover {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }

    .pagination .active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }

    @media (max-width: 1100px) {
        .main-content {
            flex-direction: column;
        }

        .sidebar {
            position: relative;
            top: 0;
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .categories-grid {
            grid-template-columns: 1fr;
        }

        .content-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-box {
            max-width: 100%;
        }
    }

    /* Loading spinner */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="watchlist-page">
    <div class="watchlist-container">
        <div class="main-content">
            <!-- Sidebar -->
            <div class="sidebar">
                <h3><i class="fas fa-heart"></i> Quản lý theo dõi</h3>
                <ul>
                    <li><a href="{{ route('watchlist.index') }}" class="active"><i class="fas fa-list"></i> Danh mục đang theo dõi</a></li>
                    <li><a href="{{ route('categories.index') }}"><i class="fas fa-search"></i> Tìm kiếm danh mục</a></li>
                </ul>

                <h3><i class="fas fa-info-circle"></i> Thông tin</h3>
                <ul>
                    <li>
                        <button type="button" onclick="showInfoModal()">
                            <i class="fas fa-question-circle"></i> Hướng dẫn sử dụng
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="content-header">
                    <h2><i class="fas fa-heart"></i> Danh mục đang theo dõi</h2>
                    <form action="{{ route('watchlist.index') }}" method="GET" class="search-box">
                        <input type="text" name="search" value="{{ $search }}" 
                               placeholder="Tìm kiếm danh mục..." 
                               aria-label="Tìm kiếm danh mục">
                        <button type="submit" aria-label="Tìm kiếm">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Thống kê -->
                <div class="watchlist-stats">
                    <div class="stat-item">
                        <i class="fas fa-heart"></i>
                        <div>
                            <div class="stat-value">{{ $currentCount }}/{{ $maxItems }}</div>
                            <div class="stat-label">Đang theo dõi</div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-box"></i>
                        <div>
                            <div class="stat-value">{{ $watchlist->sum(function($item) { return $item->category->products_count ?? 0; }) }}</div>
                            <div class="stat-label">Tổng sản phẩm</div>
                        </div>
                    </div>
                </div>

                <!-- Hiển thị thông báo -->
                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if($currentCount >= $maxItems)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Bạn đã đạt giới hạn {{ $maxItems }} danh mục theo dõi. Vui lòng xóa bớt để theo dõi danh mục mới.
                    </div>
                @endif

                <!-- Danh sách danh mục -->
                @if($watchlist->count() > 0)
                    <div class="categories-grid">
                        @foreach($watchlist as $item)
                            @if($item->category)
                                <div class="category-card" data-category-id="{{ $item->category->id }}">
                                    <div class="category-header">
                                        <div class="category-info">
                                            <h3>{{ $item->category->name }}</h3>
                                            <p>{{ Str::limit($item->category->description ?? 'Chưa có mô tả', 50) }}</p>
                                        </div>
                                        <div class="category-icon">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="category-stats">
                                        <div class="category-stat">
                                            <i class="fas fa-box"></i>
                                            <span>{{ $item->category->products_count ?? 0 }} sản phẩm</span>
                                        </div>
                                        <div class="category-stat">
                                            <i class="fas fa-clock"></i>
                                            <span>{{ $item->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="category-actions">
                                        <a href="{{ route('categories.show', $item->category->id) }}" class="btn-view">
                                            <i class="fas fa-eye"></i> Xem sản phẩm
                                        </a>
                                        <button type="button" class="btn-remove" onclick="removeFromWatchlist({{ $item->category->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($watchlist->hasPages())
                        <div class="pagination">
                            {{ $watchlist->appends(['search' => $search])->links() }}
                        </div>
                    @endif
                @elseif(!empty($search))
                    <!-- Không tìm thấy kết quả -->
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>Không tìm thấy danh mục phù hợp</h3>
                        <p>Không có danh mục nào khớp với từ khóa "{{ $search }}"</p>
                        <a href="{{ route('watchlist.index') }}" class="btn-explore">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                @else
                    <!-- Empty state -->
                    <div class="empty-state">
                        <i class="fas fa-heart-broken"></i>
                        <h3>Bạn chưa theo dõi danh mục nào</h3>
                        <p>Hãy khám phá và theo dõi các danh mục yêu thích của bạn</p>
                        <a href="{{ route('categories.index') }}" class="btn-explore">
                            <i class="fas fa-compass"></i> Khám phá danh mục
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal hướng dẫn -->
<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb;">
                <h5 class="modal-title" id="infoModalLabel">
                    <i class="fas fa-info-circle" style="color: #6366f1;"></i> Hướng dẫn sử dụng
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <h6 style="font-weight: 700; margin-bottom: 12px;">Theo dõi danh mục</h6>
                <p style="font-size: 14px; color: #6b7280; margin-bottom: 16px;">
                    Chức năng này giúp bạn theo dõi các danh mục yêu thích để dễ dàng truy cập sau này.
                </p>
                
                <h6 style="font-weight: 700; margin-bottom: 12px;">Giới hạn</h6>
                <ul style="font-size: 14px; color: #6b7280; margin-bottom: 16px;">
                    <li>Tối đa {{ $maxItems }} danh mục theo dõi</li>
                    <li>Có thể xóa và thêm mới bất cứ lúc nào</li>
                </ul>
                
                <h6 style="font-weight: 700; margin-bottom: 12px;">Lưu ý</h6>
                <ul style="font-size: 14px; color: #6b7280;">
                    <li>Cần đăng nhập để sử dụng chức năng này</li>
                    <li>Danh mục đã xóa sẽ tự động bị xóa khỏi danh sách theo dõi</li>
                </ul>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb;">
                <button type="button" class="btn btn-primary" data-dismiss="modal" style="border-radius: 8px;">
                    Đã hiểu
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Show info modal
    function showInfoModal() {
        $('#infoModal').modal('show');
    }

    // Xóa danh mục khỏi watchlist
    async function removeFromWatchlist(categoryId) {
        if (!confirm('Bạn có chắc muốn bỏ theo dõi danh mục này?')) {
            return;
        }

        const button = event.target.closest('.btn-remove');
        const card = button.closest('.category-card');
        const originalHTML = button.innerHTML;

        try {
            button.disabled = true;
            button.innerHTML = '<span class="loading-spinner"></span>';

            const response = await fetch(`/watchlist/${categoryId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Xóa card với animation
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    card.remove();
                    
                    // Kiểm tra nếu không còn card nào
                    const grid = document.querySelector('.categories-grid');
                    if (grid && grid.children.length === 0) {
                        location.reload();
                    }
                    
                    // Hiển thị thông báo thành công
                    showNotification('success', data.message || 'Đã bỏ theo dõi danh mục');
                }, 300);
            } else {
                // Xử lý lỗi
                handleRemoveError(data, card);
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', 'Không thể bỏ theo dõi danh mục. Vui lòng thử lại.');
            button.disabled = false;
            button.innerHTML = originalHTML;
        }
    }

    // Xử lý lỗi khi xóa
    function handleRemoveError(data, card) {
        if (data.auto_remove) {
            // Tự động xóa card nếu category không tồn tại
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 300);
        }
        
        showNotification('error', data.message || 'Có lỗi xảy ra');
    }

    // Hiển thị thông báo
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass}`;
        alert.innerHTML = `
            <i class="fas ${icon}"></i>
            ${message}
        `;
        
        const content = document.querySelector('.content');
        const firstChild = content.querySelector('.content-header').nextElementSibling;
        content.insertBefore(alert, firstChild);
        
        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }

    // Xử lý rate limiting
    let lastRequestTime = 0;
    const REQUEST_COOLDOWN = 1000; // 1 giây

    function canMakeRequest() {
        const now = Date.now();
        if (now - lastRequestTime < REQUEST_COOLDOWN) {
            showNotification('error', 'Vui lòng đợi một chút trước khi thực hiện hành động tiếp theo');
            return false;
        }
        lastRequestTime = now;
        return true;
    }

    // Cải thiện xử lý lỗi network
    window.addEventListener('online', () => {
        showNotification('success', 'Đã kết nối lại internet');
    });

    window.addEventListener('offline', () => {
        showNotification('error', 'Mất kết nối internet');
    });
</script>
@endpush
@endsection
