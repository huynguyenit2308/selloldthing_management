(function () {
    const pageEl = document.querySelector('.inventory-page');

    if (!pageEl) {
        return;
    }

    const maxStock = Number(pageEl.dataset.maxStock || 10000);
    const lowStockThreshold = Number(pageEl.dataset.lowStock || 5);
    const toastEl = document.querySelector('.toast');
    const overviewButtons = Array.from(document.querySelectorAll('.overview-card'));
    const refreshButtons = Array.from(document.querySelectorAll('[data-action="refresh"]'));
    const bulkSaveButtons = Array.from(document.querySelectorAll('[data-action="bulk-save"]'));
    const rows = Array.from(document.querySelectorAll('.inventory-row'));

    const fetchOptions = () => ({
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    });

    const showToast = (message, type = 'success') => {
        if (!toastEl) return;
        const msgEl = toastEl.querySelector('.toast-message');
        msgEl.textContent = message;
        toastEl.classList.toggle('is-error', type === 'error');
        toastEl.classList.add('is-visible');
        toastEl.removeAttribute('hidden');
        clearTimeout(showToast._timeoutId);
        showToast._timeoutId = setTimeout(() => {
            toastEl.classList.remove('is-visible');
        }, 4000);
    };

    const hideToast = () => {
        if (!toastEl) return;
        toastEl.classList.remove('is-visible');
        showToast._timeoutId && clearTimeout(showToast._timeoutId);
    };

    toastEl?.querySelector('.toast-close')?.addEventListener('click', hideToast);

    const updateStatusBadge = (row, status) => {
        const badge = row.querySelector('.status-badge');
        if (!badge) return;
        badge.className = `status-badge status-${status.key}`;
        badge.textContent = status.label;
    };

    const markRowState = (row, state) => {
        row.classList.toggle('is-dirty', state === 'dirty');
        row.classList.toggle('has-error', state === 'error');
    };

    const validateQuantity = (value, sold) => {
        if (Number.isNaN(value)) {
            return 'Vui lòng nhập số nguyên dương';
        }

        if (value < 0) {
            return 'Số lượng tồn kho không được âm';
        }

        if (value < sold) {
            return `Số lượng không thể nhỏ hơn số lượng đã bán (${sold})`;
        }

        if (value > maxStock) {
            return 'Số lượng vượt quá giới hạn cho phép';
        }

        return '';
    };

    const renderStats = (stats) => {
        if (!stats) return;

        overviewButtons.forEach((button) => {
            const status = button.dataset.status;
            const value = statsValueForStatus(stats, status);

            if (typeof value === 'number') {
                button.dataset.value = value;
                button.querySelector('.value').textContent = formatNumber(status === 'value' ? stats.inventory_value : value, status === 'value');
            }
        });
    };

    const statsValueForStatus = (stats, status) => {
        switch (status) {
            case 'all':
                return stats.total_products;
            case 'in_stock':
                return stats.in_stock;
            case 'low_stock':
                return stats.low_stock;
            case 'value':
                return stats.inventory_value;
            default:
                return null;
        }
    };

    const formatNumber = (value, isCurrency = false) => {
        if (isCurrency) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(value);
        }

        return new Intl.NumberFormat('vi-VN').format(value);
    };

    const saveRow = async (row) => {
        const input = row.querySelector('.qty-input');
        const feedback = row.querySelector('.input-feedback');
        const sold = Number(row.querySelector('.sold')?.textContent.replace(/[^0-9]/g, '') || 0);
        const quantity = Number(input.value);
        const error = validateQuantity(quantity, sold);

        if (error) {
            feedback.textContent = error;
            markRowState(row, 'error');
            return false;
        }

        markRowState(row, '');
        feedback.textContent = '';

        const url = row.dataset.updateUrl;

        try {
            const response = await fetch(url, {
                method: 'PATCH',
                ...fetchOptions(),
                body: JSON.stringify({ quantity })
            });

            const payload = await response.json();

            if (!response.ok || !payload.success) {
                // Handle 404 error - product was deleted in another tab
                if (response.status === 404) {
                    showToast(payload.message || 'Sản phẩm không còn tồn tại. Vui lòng tải lại trang.', 'error');
                    // Reload page after a short delay to show the message
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    return false;
                }
                throw new Error(payload.message || 'Không thể cập nhật tồn kho');
            }

            const status = payload.product.inventory_status;
            updateStatusBadge(row, status);
            row.dataset.originalQuantity = String(quantity);
            markRowState(row, '');
            renderStats(payload.stats);
            showToast(payload.message || 'Đã cập nhật tồn kho thành công');
            return true;
        } catch (error) {
            feedback.textContent = error.message;
            markRowState(row, 'error');
            showToast(error.message, 'error');
            return false;
        }
    };

    const bulkSave = async () => {
        const dirtyRows = rows.filter((row) => row.classList.contains('is-dirty'));

        if (dirtyRows.length === 0) {
            showToast('Không có thay đổi nào để lưu');
            return;
        }

        const items = dirtyRows.map((row) => ({
            id: Number(row.dataset.productId),
            quantity: Number(row.querySelector('.qty-input').value)
        }));

        bulkSaveButtons.forEach((button) => button.classList.add('is-loading'));

        try {
            const response = await fetch(pageEl.dataset.bulkUrl, {
                method: 'POST',
                ...fetchOptions(),
                body: JSON.stringify({ items })
            });

            const payload = await response.json();

            if (!response.ok || !payload.success) {
                // Handle 404 error - some products were deleted in another tab
                if (response.status === 404) {
                    showToast(payload.message || 'Một số sản phẩm không còn tồn tại. Vui lòng tải lại trang.', 'error');
                    // Reload page after a short delay to show the message
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    return;
                }
                throw new Error(payload.errors?.join(', ') || payload.message || 'Không thể lưu tất cả thay đổi');
            }

            payload.products?.forEach((product) => {
                const row = rows.find((el) => Number(el.dataset.productId) === product.id);
                if (!row) return;
                row.dataset.originalQuantity = String(product.quantity);
                row.querySelector('.qty-input').value = product.quantity;
                updateStatusBadge(row, product.inventory_status);
                markRowState(row, '');
            });

            renderStats(payload.stats);
            showToast(payload.message || 'Đã lưu tất cả thay đổi');
        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            bulkSaveButtons.forEach((button) => button.classList.remove('is-loading'));
        }
    };

    const refreshPage = () => {
        window.location.reload();
    };

    const resetRow = (row) => {
        const originalQuantity = Number(row.dataset.originalQuantity || 0);
        const input = row.querySelector('.qty-input');
        const feedback = row.querySelector('.input-feedback');
        input.value = originalQuantity;
        feedback.textContent = '';
        markRowState(row, '');
    };

    const handleInputChange = (row) => {
        const input = row.querySelector('.qty-input');
        const current = Number(input.value);
        const original = Number(row.dataset.originalQuantity || 0);
        markRowState(row, current !== original ? 'dirty' : '');
    };

    const handleStepChange = (row, delta) => {
        const input = row.querySelector('.qty-input');
        const current = Number(input.value);
        const next = Math.max(0, current + delta);
        input.value = next;
        handleInputChange(row);
    };

    rows.forEach((row) => {
        const input = row.querySelector('.qty-input');
        const feedback = row.querySelector('.input-feedback');
        const buttons = row.querySelectorAll('.qty-btn');

        input.addEventListener('input', () => {
            const sold = Number(row.querySelector('.sold')?.textContent.replace(/[^0-9]/g, '') || 0);
            const value = Number(input.value);
            const message = validateQuantity(value, sold);
            feedback.textContent = message;
            handleInputChange(row);
        });

        buttons.forEach((button) => {
            const delta = Number(button.dataset.change);
            button.addEventListener('click', () => {
                handleStepChange(row, delta);
                input.dispatchEvent(new Event('input'));
            });
        });

        row.querySelector('[data-action="save-row"]').addEventListener('click', () => saveRow(row));
        row.querySelector('[data-action="reset-row"]').addEventListener('click', () => resetRow(row));
    });

    overviewButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.getElementById('inventoryFilterForm');
            if (!form) return;
            form.querySelector('input[name="status"]').value = button.dataset.status;
            form.submit();
        });
    });

    bulkSaveButtons.forEach((button) => button.addEventListener('click', bulkSave));
    refreshButtons.forEach((button) => button.addEventListener('click', refreshPage));
})();
