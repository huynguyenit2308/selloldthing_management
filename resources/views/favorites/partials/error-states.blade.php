{{-- Error States for Favorites Page --}}

{{-- Connection Error --}}
@if(isset($connectionError) && $connectionError)
<div class="error-state connection-error">
    <div class="error-icon">
        <i class="fas fa-wifi-slash"></i>
    </div>
    <h3>Không thể tải danh sách yêu thích</h3>
    <p>Vui lòng kiểm tra kết nối mạng và thử lại</p>
    <button class="btn btn-primary" onclick="window.location.reload()">
        <i class="fas fa-sync-alt"></i> Thử lại
    </button>
</div>
@endif

{{-- Authentication Error --}}
@if(isset($authError) && $authError)
<div class="error-state auth-error">
    <div class="error-icon">
        <i class="fas fa-user-lock"></i>
    </div>
    <h3>Vui lòng đăng nhập để sử dụng tính năng yêu thích</h3>
    <p>Bạn cần đăng nhập để xem và quản lý danh sách sản phẩm yêu thích</p>
    <a href="{{ route('login.form') }}" class="btn btn-primary">
        <i class="fas fa-sign-in-alt"></i> Đăng nhập
    </a>
</div>
@endif

{{-- Session Expired --}}
@if(isset($sessionExpired) && $sessionExpired)
<div class="error-state session-error">
    <div class="error-icon">
        <i class="fas fa-clock"></i>
    </div>
    <h3>Phiên đăng nhập đã hết hạn</h3>
    <p>Vui lòng đăng nhập lại để tiếp tục sử dụng</p>
    <a href="{{ route('login.form') }}" class="btn btn-primary">
        <i class="fas fa-sign-in-alt"></i> Đăng nhập lại
    </a>
</div>
@endif

{{-- Data Sync Error --}}
@if(isset($syncError) && $syncError)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Cảnh báo:</strong> Danh sách có thể chưa được cập nhật. 
    <button type="button" class="btn btn-sm btn-outline-warning ms-2" onclick="syncData()">
        Đồng bộ ngay
    </button>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Performance Warning --}}
@if(isset($performanceWarning) && $performanceWarning)
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Thông báo:</strong> Danh sách yêu thích quá dài có thể ảnh hưởng hiệu năng. 
    Bạn có thể xem xét việc phân loại hoặc xóa bớt một số sản phẩm.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Product Unavailable Notice --}}
@if(isset($unavailableProducts) && count($unavailableProducts) > 0)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Một số sản phẩm không còn khả dụng:</strong>
    <ul class="mb-0 mt-2">
        @foreach($unavailableProducts as $product)
        <li>{{ $product['name'] }} - {{ $product['reason'] }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<style>
.error-state {
    text-align: center;
    padding: 4rem 2rem;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 2rem 0;
}

.error-icon {
    font-size: 4rem;
    color: #dc3545;
    margin-bottom: 1.5rem;
}

.connection-error .error-icon {
    color: #ffc107;
}

.auth-error .error-icon {
    color: #007bff;
}

.session-error .error-icon {
    color: #6c757d;
}

.error-state h3 {
    color: #495057;
    margin-bottom: 1rem;
}

.error-state p {
    color: #6c757d;
    margin-bottom: 2rem;
}

@media (max-width: 575.98px) {
    .error-state {
        padding: 2rem 1rem;
    }
    
    .error-icon {
        font-size: 3rem;
    }
}
</style>

<script>
function syncData() {
    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đồng bộ...';
    btn.disabled = true;
    
    fetch('/favorites?sync=1', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            window.location.reload();
        } else {
            throw new Error('Sync failed');
        }
    })
    .catch(error => {
        btn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Thử lại';
        btn.disabled = false;
        console.error('Sync error:', error);
    });
}
</script>
