/**
 * Favorites Helper - Global functionality for managing favorites
 * Can be included on any page that needs favorite functionality
 */

class FavoritesHelper {
    constructor() {
        this.init();
    }

    init() {
        // Bind events for favorite buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.favorite-btn') || e.target.closest('[data-favorite-toggle]')) {
                e.preventDefault();
                this.handleFavoriteToggle(e.target.closest('.favorite-btn') || e.target.closest('[data-favorite-toggle]'));
            }
        });

        // Initialize favorite states on page load
        this.initializeFavoriteStates();
    }

    async initializeFavoriteStates() {
        const favoriteButtons = document.querySelectorAll('.favorite-btn, [data-favorite-toggle]');
        
        for (const btn of favoriteButtons) {
            const productId = btn.dataset.productId;
            if (productId) {
                try {
                    const response = await fetch(`/favorites/check/${productId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        this.updateFavoriteButton(btn, data.is_favorite);
                    }
                } catch (error) {
                    console.warn('Could not check favorite status for product', productId);
                }
            }
        }
    }

    async handleFavoriteToggle(btn) {
        const productId = btn.dataset.productId;
        if (!productId) return;

        // Check if user is logged in
        if (!this.isUserLoggedIn()) {
            this.showLoginPrompt();
            return;
        }

        const isFavorite = btn.classList.contains('is-favorite') || btn.dataset.isFavorite === 'true';
        
        this.setButtonLoading(btn, true);

        try {
            let response;
            if (isFavorite) {
                // Remove from favorites
                response = await fetch(`/favorites/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
            } else {
                // Add to favorites
                response = await fetch('/favorites', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                });
            }

            const data = await response.json();
            
            if (data.success) {
                this.updateFavoriteButton(btn, !isFavorite);
                this.showToast('success', data.message);
                
                // Trigger custom event for other components to listen to
                document.dispatchEvent(new CustomEvent('favoriteToggled', {
                    detail: { productId, isFavorite: !isFavorite }
                }));
            } else {
                this.showToast('error', data.message || 'Có lỗi xảy ra');
            }
        } catch (error) {
            console.error('Favorite toggle error:', error);
            this.showToast('error', 'Có lỗi xảy ra khi cập nhật danh sách yêu thích');
        } finally {
            this.setButtonLoading(btn, false);
        }
    }

    updateFavoriteButton(btn, isFavorite) {
        if (isFavorite) {
            btn.classList.add('is-favorite');
            btn.dataset.isFavorite = 'true';
            
            // Update icon
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-heart';
            }
            
            // Update text if present
            const text = btn.querySelector('.btn-text');
            if (text) {
                text.textContent = 'Đã yêu thích';
            }
            
            // Update title
            btn.title = 'Bỏ yêu thích';
            
            // Add visual feedback
            btn.style.color = '#dc3545';
        } else {
            btn.classList.remove('is-favorite');
            btn.dataset.isFavorite = 'false';
            
            // Update icon
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = 'far fa-heart';
            }
            
            // Update text if present
            const text = btn.querySelector('.btn-text');
            if (text) {
                text.textContent = 'Yêu thích';
            }
            
            // Update title
            btn.title = 'Thêm vào yêu thích';
            
            // Reset color
            btn.style.color = '';
        }
    }

    setButtonLoading(btn, loading) {
        if (loading) {
            btn.classList.add('loading');
            btn.disabled = true;
            
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-spinner fa-spin';
            }
        } else {
            btn.classList.remove('loading');
            btn.disabled = false;
        }
    }

    isUserLoggedIn() {
        // Check if user is logged in (you can customize this based on your auth system)
        return document.querySelector('meta[name="user-authenticated"]') !== null ||
               document.body.classList.contains('authenticated') ||
               document.querySelector('.account .dropdown-item[href*="account"]') !== null;
    }

    showLoginPrompt() {
        if (confirm('Bạn cần đăng nhập để sử dụng tính năng yêu thích. Chuyển đến trang đăng nhập?')) {
            window.location.href = '/login';
        }
    }

    showToast(type, message) {
        // Create toast element
        const toastContainer = this.getOrCreateToastContainer();
        const toastId = 'toast-' + Date.now();
        
        const bgClass = type === 'success' ? 'bg-success' : 
                       type === 'error' ? 'bg-danger' : 'bg-warning';
        
        const iconClass = type === 'success' ? 'fa-check-circle' : 
                         type === 'error' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle';
        
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${iconClass} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        
        // Check if Bootstrap is available
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: type === 'error' ? 5000 : 3000
            });
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        } else {
            // Fallback for when Bootstrap is not available
            toastElement.style.display = 'block';
            setTimeout(() => {
                toastElement.style.opacity = '0';
                setTimeout(() => toastElement.remove(), 300);
            }, type === 'error' ? 5000 : 3000);
        }
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

    // Static method to create favorite button HTML
    static createFavoriteButton(productId, options = {}) {
        const {
            className = 'btn btn-outline-danger favorite-btn',
            iconOnly = false,
            size = 'sm'
        } = options;
        
        const sizeClass = size ? `btn-${size}` : '';
        const buttonClass = `${className} ${sizeClass}`.trim();
        
        return `
            <button type="button" 
                    class="${buttonClass}" 
                    data-product-id="${productId}"
                    data-favorite-toggle="true"
                    title="Thêm vào yêu thích">
                <i class="far fa-heart"></i>
                ${iconOnly ? '' : '<span class="btn-text ms-1">Yêu thích</span>'}
            </button>
        `;
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.favoritesHelper = new FavoritesHelper();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FavoritesHelper;
}
