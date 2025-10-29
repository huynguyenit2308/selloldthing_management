/**
 * Favorites Page JavaScript
 * Handles interactive features for the favorites product list
 */

class FavoritesManager {
    constructor() {
        this.init();
        this.bindEvents();
    }

    init() {
        this.loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        this.confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        this.currentAction = null;
        this.currentProductId = null;
    }

    bindEvents() {
        // Sort dropdown
        document.getElementById('sortSelect')?.addEventListener('change', this.handleSort.bind(this));
        
        // Clear all button
        document.getElementById('clearAllBtn')?.addEventListener('click', this.clearAllFavorites.bind(this));
        
        // Add all to cart button
        document.getElementById('addAllToCartBtn')?.addEventListener('click', this.addAllToCart.bind(this));
        
        // Category filter items
        document.querySelectorAll('.category_item').forEach(item => {
            item.addEventListener('click', this.handleCategoryFilter.bind(this));
        });
        
        // Product actions
        document.querySelectorAll('.remove-favorite').forEach(btn => {
            btn.addEventListener('click', this.removeFavorite.bind(this));
        });
        
        document.querySelectorAll('.add-to-cart, .product_cart_button').forEach(btn => {
            btn.addEventListener('click', this.addToCart.bind(this));
        });
        
        // Confirm modal action
        document.getElementById('confirmAction')?.addEventListener('click', this.executeConfirmedAction.bind(this));
    }

    toggleFilter() {
        const sidebar = document.getElementById('sidebarFilter');
        if (window.innerWidth <= 991.98) {
            sidebar.classList.toggle('show');
        }
    }

    handleSort() {
        const sortValue = document.getElementById('sortSelect').value;
        const url = new URL(window.location);
        url.searchParams.set('sort', sortValue);
        window.location.href = url.toString();
    }

    refreshPage() {
        const btn = document.getElementById('refreshBtn');
        this.setButtonLoading(btn, true);
        window.location.reload();
    }

    clearAllFavorites() {
        this.showConfirmation(
            'Bạn có chắc chắn muốn xóa tất cả sản phẩm yêu thích?',
            () => this.executeClearAll()
        );
    }

    executeClearAll() {
        this.showLoading();
        
        fetch('/favorites', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            this.hideLoading();
            if (data.success) {
                this.showToast('success', data.message);
                setTimeout(() => window.location.reload(), 1000);
            } else {
                this.showToast('error', data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            this.hideLoading();
            console.error('Error:', error);
            this.showToast('error', 'Có lỗi xảy ra khi xóa danh sách yêu thích');
        });
    }

    addAllToCart() {
        const btn = document.getElementById('addAllToCartBtn');
        this.setButtonLoading(btn, true);
        
        fetch('/favorites/add-all-to-cart', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            this.setButtonLoading(btn, false);
            if (data.success) {
                this.showToast('success', data.message);
                if (data.errors && data.errors.length > 0) {
                    setTimeout(() => {
                        this.showToast('warning', 'Một số sản phẩm không thể thêm: ' + data.errors.join(', '));
                    }, 2000);
                }
            } else {
                this.showToast('error', data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            this.setButtonLoading(btn, false);
            console.error('Error:', error);
            this.showToast('error', 'Có lỗi xảy ra khi thêm vào giỏ hàng');
        });
    }

    handleCategoryFilter(event) {
        const item = event.currentTarget;
        const categoryId = item.dataset.categoryId;
        
        // Toggle selection
        document.querySelectorAll('.category_item').forEach(el => el.classList.remove('selected'));
        
        const url = new URL(window.location);
        
        if (item.classList.contains('selected')) {
            // Deselect
            item.classList.remove('selected');
            url.searchParams.delete('category');
        } else {
            // Select
            item.classList.add('selected');
            url.searchParams.set('category', categoryId);
        }
        
        window.location.href = url.toString();
    }

    removeFavorite(event) {
        const btn = event.currentTarget;
        const productId = btn.dataset.productId;
        
        this.showConfirmation(
            'Bạn có chắc chắn muốn xóa sản phẩm này khỏi danh sách yêu thích?',
            () => this.executeRemoveFavorite(productId, btn)
        );
    }

    executeRemoveFavorite(productId, btn) {
        this.setButtonLoading(btn, true);
        
        fetch(`/favorites/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            this.setButtonLoading(btn, false);
            if (data.success) {
                this.showToast('success', data.message);
                // Remove the product card with animation
                const productCard = btn.closest('.product_item');
                productCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                productCard.style.opacity = '0';
                productCard.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    productCard.remove();
                    this.updateProductCount();
                }, 300);
            } else {
                this.showToast('error', data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            this.setButtonLoading(btn, false);
            console.error('Error:', error);
            this.showToast('error', 'Có lỗi xảy ra khi xóa sản phẩm');
        });
    }

    addToCart(event) {
        const btn = event.currentTarget;
        const productId = btn.dataset.productId;
        
        this.setButtonLoading(btn, true);
        
        // Simulate cart addition (replace with actual cart API)
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            this.setButtonLoading(btn, false);
            if (data.success) {
                this.showToast('success', 'Đã thêm vào giỏ hàng');
                // If it's "Mua ngay" button, redirect to checkout
                if (btn.textContent.trim() === 'Mua ngay') {
                    setTimeout(() => {
                        window.location.href = '/payment';
                    }, 1000);
                }
            } else {
                this.showToast('error', data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            this.setButtonLoading(btn, false);
            console.error('Error:', error);
            this.showToast('error', 'Có lỗi xảy ra khi thêm vào giỏ hàng');
        });
    }

    updateProductCount() {
        const productCards = document.querySelectorAll('.product_item').length;
        const countElement = document.querySelector('.shop_product_count span');
        if (countElement) {
            countElement.textContent = productCards;
        }
        
        // Update header count
        const headerCount = document.querySelector('.favorites_count');
        if (headerCount) {
            headerCount.textContent = `${productCards} sản phẩm`;
        }
        
        // Show empty state if no products left
        if (productCards === 0) {
            const shopContent = document.querySelector('.shop_content');
            if (shopContent) {
                shopContent.innerHTML = `
                    <div class="favorites_empty">
                        <div class="favorites_empty_icon">
                            <i class="fa fa-heart-o"></i>
                        </div>
                        <h3 class="favorites_empty_title">Chưa có sản phẩm yêu thích</h3>
                        <p class="favorites_empty_text">Hãy thêm sản phẩm bạn yêu thích để xem lại sau</p>
                        <div class="red_button shop_now_button">
                            <a href="/products">Khám phá sản phẩm</a>
                        </div>
                    </div>
                `;
            }
        }
    }

    showConfirmation(message, callback) {
        document.getElementById('confirmMessage').textContent = message;
        this.currentAction = callback;
        this.confirmModal.show();
    }

    executeConfirmedAction() {
        if (this.currentAction) {
            this.confirmModal.hide();
            this.currentAction();
            this.currentAction = null;
        }
    }

    showLoading() {
        this.loadingModal.show();
    }

    hideLoading() {
        this.loadingModal.hide();
    }

    setButtonLoading(btn, loading) {
        if (loading) {
            btn.classList.add('loading');
            btn.disabled = true;
        } else {
            btn.classList.remove('loading');
            btn.disabled = false;
        }
    }

    showToast(type, message) {
        // Create toast element
        const toastContainer = this.getOrCreateToastContainer();
        const toastId = 'toast-' + Date.now();
        
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'exclamation-triangle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: type === 'error' ? 5000 : 3000
        });
        
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    getOrCreateToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1055';
            document.body.appendChild(container);
        }
        return container;
    }

    handleOutsideClick(event) {
        const sidebar = document.getElementById('sidebarFilter');
        const toggleBtn = document.getElementById('toggleFilter');
        
        if (sidebar && sidebar.classList.contains('show') && 
            !sidebar.contains(event.target) && 
            !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new FavoritesManager();
});

// Handle browser back/forward navigation
window.addEventListener('popstate', function() {
    window.location.reload();
});
