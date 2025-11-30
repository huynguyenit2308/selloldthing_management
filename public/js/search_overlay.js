(function () {
    const overlay = document.getElementById('search-overlay');
    if (!overlay) {
        return;
    }

    window.searchOverlayInitialState = window.searchOverlayInitialState || {
        keyword: '',
        sort: 'relevance',
        payload: null,
        errors: null,
    };

    const dataset = overlay.dataset;
    const input = overlay.querySelector('#search-overlay-input');
    const submitBtn = overlay.querySelector('[data-action="submit"]');
    const clearBtn = overlay.querySelector('[data-action="clear"]');
    const errorBox = overlay.querySelector('.search-overlay__error');
    const loader = overlay.querySelector('.search-overlay__loader');
    const emptyMessage = overlay.querySelector('[data-role="empty"]');
    const resultsContainer = overlay.querySelector('[data-role="results"]');
    const paginationContainer = overlay.querySelector('[data-role="pagination"]');
    const tagsContainer = overlay.querySelector('[data-role="suggestion-tags"]');
    const historyList = overlay.querySelector('[data-role="history-list"]');
    const historyClearBtn = overlay.querySelector('[data-action="history-clear"]');
    const autocompleteWrapper = overlay.querySelector('.search-overlay__autocomplete');
    const autocompleteList = overlay.querySelector('.search-overlay__autocomplete-list');
    const closeBtn = overlay.querySelector('.search-overlay__close');
    const backdrop = overlay.querySelector('.search-overlay__backdrop');
    const csrfToken = dataset.csrfToken;
    const cartCounter = document.getElementById('checkout_items');

    const state = {
        isOpen: false,
        bootstrapLoaded: false,
        keyword: '',
        sort: 'relevance',
        suggestions: [],
        history: [],
        results: [],
        pagination: null,
        loading: false,
        error: null,
        autocomplete: [],
        autocompleteVisible: false,
    };

    let suggestionAbortController = null;
    let searchAbortController = null;
    let historyAbortController = null;
    let autocompleteTimer = null;

    function lockScroll() {
        document.body.style.overflow = 'hidden';
    }

    function unlockScroll() {
        document.body.style.overflow = '';
    }

    function setError(message) {
        state.error = message;
        if (!message) {
            errorBox.setAttribute('hidden', 'hidden');
            errorBox.textContent = '';
        } else {
            errorBox.removeAttribute('hidden');
            errorBox.textContent = message;
        }
    }

    function setLoading(isLoading) {
        state.loading = isLoading;
        if (isLoading) {
            loader.removeAttribute('hidden');
        } else {
            loader.setAttribute('hidden', 'hidden');
        }
    }

    function clearResults() {
        resultsContainer.innerHTML = '';
        paginationContainer.innerHTML = '';
        emptyMessage.setAttribute('hidden', 'hidden');
    }

    function renderSuggestionTags() {
        if (!tagsContainer) return;
        tagsContainer.innerHTML = '';
        if (!state.suggestions || state.suggestions.length === 0) {
            const placeholder = document.createElement('span');
            placeholder.textContent = 'Không có gợi ý.';
            placeholder.className = 'text-muted';
            tagsContainer.appendChild(placeholder);
            return;
        }

        state.suggestions.forEach((keyword) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'search-overlay__tag';
            button.textContent = keyword;
            button.addEventListener('click', () => {
                input.value = keyword;
                performSearch(1, true);
            });
            tagsContainer.appendChild(button);
        });
    }

    function renderHistory() {
        if (!historyList) return;
        historyList.innerHTML = '';

        if (!state.history || state.history.length === 0) {
            const emptyItem = document.createElement('li');
            emptyItem.className = 'text-muted';
            emptyItem.textContent = 'Chưa có lịch sử tìm kiếm.';
            historyList.appendChild(emptyItem);
            return;
        }

        state.history.forEach((entry) => {
            const item = document.createElement('li');
            item.className = 'search-overlay__history-item';

            const keywordButton = document.createElement('button');
            keywordButton.type = 'button';
            keywordButton.className = 'search-overlay__history-keyword';
            keywordButton.textContent = entry.keyword;
            keywordButton.addEventListener('click', () => {
                input.value = entry.keyword;
                performSearch(1, true);
            });

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'search-overlay__history-remove';
            removeBtn.innerHTML = '&times;';
            removeBtn.setAttribute('aria-label', 'Xóa');
            removeBtn.addEventListener('click', () => {
                removeHistoryEntry(entry.id);
            });

            item.appendChild(keywordButton);
            item.appendChild(removeBtn);
            historyList.appendChild(item);
        });
    }

    function renderAutocomplete(items) {
        state.autocomplete = items || [];
        autocompleteList.innerHTML = '';

        if (!items || items.length === 0) {
            autocompleteWrapper.setAttribute('hidden', 'hidden');
            state.autocompleteVisible = false;
            return;
        }

        items.forEach((keyword) => {
            const li = document.createElement('li');
            li.className = 'search-overlay__autocomplete-item';

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'search-overlay__autocomplete-button';
            button.textContent = keyword;
            button.addEventListener('click', () => {
                input.value = keyword;
                renderAutocomplete([]);
                performSearch(1, true);
            });

            li.appendChild(button);
            autocompleteList.appendChild(li);
        });

        autocompleteWrapper.removeAttribute('hidden');
        state.autocompleteVisible = true;
    }

    function renderResults() {
        clearResults();

        if (!state.results || state.results.length === 0) {
            emptyMessage.removeAttribute('hidden');
            return;
        }

        state.results.forEach((product) => {
            const card = document.createElement('div');
            card.className = 'search-overlay__result-card';

            const imageWrapper = document.createElement('div');
            imageWrapper.className = 'search-overlay__result-image';
            const img = document.createElement('img');
            img.src = product.image;
            img.alt = product.name;
            imageWrapper.appendChild(img);

            const body = document.createElement('div');
            body.className = 'search-overlay__result-body';

            const title = document.createElement('a');
            title.href = product.url;
            title.className = 'search-overlay__result-title';
            title.textContent = product.name;

            const price = document.createElement('div');
            price.className = 'search-overlay__result-price';
            price.textContent = formatCurrency(product.price);

            body.appendChild(title);
            body.appendChild(price);

            if (product.original_price && Number(product.original_price) > Number(product.price)) {
                const original = document.createElement('div');
                original.className = 'search-overlay__result-original-price';
                original.textContent = 'Giá gốc ' + formatCurrency(product.original_price);
                body.appendChild(original);
            }

            const metaInfo = [];
            if (product.condition) {
                metaInfo.push(product.condition);
            }
            if (product.location) {
                metaInfo.push(product.location);
            }
            if (metaInfo.length > 0) {
                const meta = document.createElement('div');
                meta.className = 'search-overlay__result-meta';
                meta.textContent = metaInfo.join(' • ');
                body.appendChild(meta);
            }

            const actions = document.createElement('div');
            actions.className = 'search-overlay__result-actions';

            const cartBtn = document.createElement('button');
            cartBtn.type = 'button';
            cartBtn.className = 'primary';
            cartBtn.textContent = 'Thêm vào giỏ hàng';
            cartBtn.addEventListener('click', () => handleAddToCart(product.id, cartBtn));

            const favoriteBtn = document.createElement('button');
            favoriteBtn.type = 'button';
            favoriteBtn.className = 'favorite';
            favoriteBtn.textContent = 'Yêu thích';
            if (product.is_favorited) {
                favoriteBtn.classList.add('active');
            }
            favoriteBtn.addEventListener('click', () => handleToggleFavorite(product.id, favoriteBtn));

            actions.appendChild(cartBtn);
            actions.appendChild(favoriteBtn);

            body.appendChild(actions);
            card.appendChild(imageWrapper);
            card.appendChild(body);
            resultsContainer.appendChild(card);
        });

        renderPagination();
    }

    function renderPagination() {
        paginationContainer.innerHTML = '';
        const pagination = state.pagination;
        if (!pagination || pagination.last_page <= 1) {
            return;
        }

        const createButton = (label, page, disabled, active) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            if (disabled) button.setAttribute('disabled', 'disabled');
            if (active) button.classList.add('active');
            if (!disabled) {
                button.addEventListener('click', () => performSearch(page, false));
            }
            return button;
        };

        paginationContainer.appendChild(createButton('<', pagination.current_page - 1, pagination.current_page <= 1, false));

        const start = Math.max(1, pagination.current_page - 2);
        const end = Math.min(pagination.last_page, pagination.current_page + 2);

        for (let page = start; page <= end; page++) {
            paginationContainer.appendChild(createButton(page.toString(), page, false, page === pagination.current_page));
        }

        paginationContainer.appendChild(createButton('>', pagination.current_page + 1, pagination.current_page >= pagination.last_page, false));
    }

    function formatCurrency(value) {
        const number = Number(value);
        if (Number.isNaN(number)) {
            return value;
        }
        return number.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    }

    function applyPayload(payload) {
        if (!payload) {
            return;
        }
        state.keyword = payload.keyword || '';
        state.sort = payload.sort || 'relevance';
        state.results = payload.results || [];
        state.pagination = payload.pagination || null;
        input.value = state.keyword;
        renderResults();
    }

    function fetchBootstrapIfNeeded() {
        if (state.bootstrapLoaded) {
            return;
        }

        state.bootstrapLoaded = true;
        fetch(dataset.bootstrapUrl, {
            headers: {
                'Accept': 'application/json',
            },
        })
            .then((response) => response.json())
            .then((data) => {
                state.suggestions = data.suggestions || [];
                state.history = data.history || [];
                renderSuggestionTags();
                renderHistory();
            })
            .catch(() => {
                state.suggestions = [];
                renderSuggestionTags();
            });
    }

    function fetchHistory() {
        if (historyAbortController) {
            historyAbortController.abort();
        }
        historyAbortController = new AbortController();
        fetch(dataset.historyUrl, {
            headers: {
                'Accept': 'application/json',
            },
            signal: historyAbortController.signal,
        })
            .then((response) => response.ok ? response.json() : Promise.reject())
            .then((data) => {
                state.history = data.history || [];
                renderHistory();
            })
            .catch(() => {
                // ignore history update errors
            });
    }

    function removeHistoryEntry(id) {
        fetch(dataset.historyUrl + '/' + encodeURIComponent(id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        })
            .then((response) => response.ok ? response.json() : Promise.reject())
            .then(() => {
                state.history = state.history.filter((item) => item.id !== id);
                renderHistory();
            })
            .catch(() => {
                setError('Không thể xóa lịch sử tìm kiếm. Vui lòng thử lại.');
            });
    }

    function clearHistory() {
        fetch(dataset.historyClearUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        })
            .then((response) => response.ok ? response.json() : Promise.reject())
            .then(() => {
                state.history = [];
                renderHistory();
            })
            .catch(() => {
                setError('Không thể xóa toàn bộ lịch sử tìm kiếm.');
            });
    }

    function fetchSuggestions(keyword) {
        if (!dataset.suggestUrl) return;
        if (keyword.length < 2) {
            renderAutocomplete([]);
            return;
        }

        if (suggestionAbortController) {
            suggestionAbortController.abort();
        }
        suggestionAbortController = new AbortController();
        const url = new URL(dataset.suggestUrl, window.location.origin);
        url.searchParams.set('q', keyword);

        fetch(url.toString(), {
            headers: { 'Accept': 'application/json' },
            signal: suggestionAbortController.signal,
        })
            .then((response) => response.ok ? response.json() : Promise.reject())
            .then((data) => {
                const items = (data.items || [])
                    .filter((item) => typeof item === 'string' && item.trim() !== '')
                    .map((item) => item.trim())
                    .filter((item) => item.toLowerCase().includes(keyword.toLowerCase()));
                renderAutocomplete(items);
            })
            .catch(() => {
                // ignore suggestion errors
                renderAutocomplete([]);
            });
    }

    function performSearch(page = 1, showValidationErrors = true) {
        const keyword = input.value.trim();
        const sort = state.sort || 'relevance';
        state.sort = sort;

        if (keyword.length < 2) {
            if (showValidationErrors) {
                setError('Vui lòng nhập ít nhất 2 ký tự');
            }
            return Promise.resolve();
        }

        if (keyword.length > 100) {
            if (showValidationErrors) {
                setError('Từ khóa tìm kiếm không được vượt quá 100 ký tự');
            }
            return Promise.resolve();
        }

        // Check for special characters - đơn giản hóa
        if (/[<>|!@#$%^&*()+=\[\]{};:"\\\/?]/.test(keyword)) {
            if (showValidationErrors) {
                setError('Từ khóa chứa ký tự không hợp lệ. Vui lòng chỉ sử dụng chữ cái, số và các ký tự cơ bản');
            }
            return Promise.resolve();
        }

        setError(null);
        setLoading(true);
        clearResults();

        if (searchAbortController) {
            searchAbortController.abort();
        }
        searchAbortController = new AbortController();

        const url = new URL(dataset.resultsUrl, window.location.origin);
        url.searchParams.set('q', keyword);
        url.searchParams.set('sort', sort);
        url.searchParams.set('page', page);

        return fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
            },
            signal: searchAbortController.signal,
        })
            .then(async (response) => {
                if (!response.ok) {
                    let message = 'Tìm kiếm quá lâu. Vui lòng thử lại.';
                    if (response.status === 422) {
                        const data = await response.json().catch(() => null);
                        message = extractValidationMessage(data) || message;
                    } else if (response.status >= 500) {
                        message = 'Dịch vụ tìm kiếm tạm gián đoạn.';
                    }
                    throw new Error(message);
                }
                return response.json();
            })
            .then((payload) => {
                state.keyword = keyword;
                state.sort = sort;
                applyPayload(payload);
                fetchHistory();
            })
            .catch((error) => {
                if (error.name === 'AbortError') {
                    return;
                }
                setError(error.message || 'Không thể tìm kiếm. Vui lòng thử lại.');
                clearResults();
            })
            .finally(() => {
                setLoading(false);
            });
    }

    function extractValidationMessage(data) {
        if (!data) return null;
        if (Array.isArray(data)) {
            return data[0];
        }
        if (typeof data === 'object' && data !== null) {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0];
                if (Array.isArray(firstError)) {
                    return firstError[0];
                }
                if (typeof firstError === 'string') {
                    return firstError;
                }
            }
            if (data.message) {
                return data.message;
            }
        }
        return null;
    }

    function handleAddToCart(productId, button) {
        const url = dataset.cartUrlTemplate.replace('__ID__', encodeURIComponent(productId));
        button.disabled = true;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ quantity: 1 }),
        })
            .then(async (response) => {
                if (!response.ok) {
                    let message = 'Không thể thêm vào giỏ hàng.';
                    if (response.status === 401) {
                        message = 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.';
                    } else if (response.status >= 400) {
                        const data = await response.json().catch(() => null);
                        message = (data && (data.message || extractValidationMessage(data))) || message;
                    }
                    throw new Error(message);
                }
                return response.json();
            })
            .then((data) => {
                if (cartCounter && data && typeof data.cart_count !== 'undefined') {
                    cartCounter.textContent = data.cart_count;
                }
                const originalText = button.textContent;
                button.textContent = 'Đã thêm!';
                setTimeout(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                }, 1500);
            })
            .catch((error) => {
                setError(error.message || 'Không thể thêm vào giỏ hàng.');
                button.disabled = false;
            });
    }

    function handleToggleFavorite(productId, button) {
        const url = dataset.favoriteUrlTemplate.replace('__ID__', encodeURIComponent(productId));
        button.disabled = true;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: '{}',
        })
            .then(async (response) => {
                if (!response.ok) {
                    let message = 'Không thể cập nhật trạng thái yêu thích.';
                    if (response.status === 401) {
                        message = 'Vui lòng đăng nhập để lưu yêu thích.';
                    } else {
                        const data = await response.json().catch(() => null);
                        message = (data && (data.message || extractValidationMessage(data))) || message;
                    }
                    throw new Error(message);
                }
                return response.json();
            })
            .then((data) => {
                if (data.status === 'added') {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            })
            .catch((error) => {
                setError(error.message || 'Không thể cập nhật yêu thích.');
            })
            .finally(() => {
                button.disabled = false;
            });
    }

    function openOverlay(initialKeyword, payload, errors) {
        if (state.isOpen) {
            if (typeof initialKeyword === 'string') {
                input.value = initialKeyword;
            }
            if (payload) {
                applyPayload(payload);
            }
            if (errors) {
                const message = extractValidationMessage(errors) || 'Vui lòng nhập từ khóa hợp lệ';
                setError(message);
            }
            focusInput();
            return;
        }
        state.isOpen = true;
        overlay.classList.add('is-open');
        lockScroll();
        fetchBootstrapIfNeeded();

        if (typeof initialKeyword === 'string') {
            input.value = initialKeyword;
            state.keyword = initialKeyword;
        } else if (state.keyword) {
            input.value = state.keyword;
        }

        if (payload) {
            applyPayload(payload);
        } else {
            clearResults();
        }

        if (errors) {
            const message = extractValidationMessage(errors) || 'Vui lòng nhập từ khóa hợp lệ';
            setError(message);
        } else {
            setError(null);
        }

        focusInput();
        document.addEventListener('keydown', handleKeyDown);
    }

    function focusInput() {
        setTimeout(() => input.focus(), 50);
    }

    function closeOverlay() {
        if (!state.isOpen) return;
        state.isOpen = false;
        overlay.classList.remove('is-open');
        unlockScroll();
        autocompleteWrapper.setAttribute('hidden', 'hidden');
        state.autocompleteVisible = false;
        document.removeEventListener('keydown', handleKeyDown);
    }

    function handleKeyDown(event) {
        if (event.key === 'Escape') {
            closeOverlay();
        }
        if (event.key === 'Enter' && document.activeElement === input) {
            event.preventDefault();
            performSearch(1, true);
        }
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => closeOverlay());
    }

    if (backdrop) {
        backdrop.addEventListener('click', () => closeOverlay());
    }

    submitBtn.addEventListener('click', () => performSearch(1, true));

    clearBtn.addEventListener('click', () => {
        input.value = '';
        state.keyword = '';
        state.results = [];
        state.pagination = null;
        clearResults();
        setError(null);
        renderAutocomplete([]);
    });

    input.addEventListener('input', () => {
        setError(null);
        if (autocompleteTimer) {
            clearTimeout(autocompleteTimer);
        }
        const value = input.value.trim();
        if (value.length === 0) {
            renderAutocomplete([]);
            return;
        }
        autocompleteTimer = setTimeout(() => fetchSuggestions(value), 200);
    });

    input.addEventListener('blur', () => {
        if (!state.autocompleteVisible) return;
        setTimeout(() => {
            renderAutocomplete([]);
        }, 200);
    });

    if (historyClearBtn) {
        historyClearBtn.addEventListener('click', () => clearHistory());
    }

    const navTriggers = document.querySelectorAll('[data-search-overlay-trigger="true"]');
    navTriggers.forEach((trigger) => {
        trigger.setAttribute('readonly', 'readonly');
        trigger.addEventListener('focus', (event) => {
            event.preventDefault();
            event.target.blur();
            openOverlay(event.target.value || '', null, null);
        });
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openOverlay(event.target.value || '', null, null);
        });
        if (trigger.closest('form')) {
            trigger.closest('form').addEventListener('submit', (event) => {
                event.preventDefault();
                openOverlay(trigger.value || '', null, null);
            });
        }
    });

    window.searchOverlayController = {
        open: openOverlay,
        close: closeOverlay,
        isOpen: () => state.isOpen,
    };

    // Auto-open if initial state provided (e.g., from search page)
    const initial = window.searchOverlayInitialState;
    if (initial && (initial.keyword || initial.payload || initial.errors)) {
        openOverlay(initial.keyword, initial.payload, initial.errors);
    }
})();
