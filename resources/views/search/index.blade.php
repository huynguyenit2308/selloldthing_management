@extends('dashboard')

@section('content')
    <section class="search-page container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="search-page__title mb-3">Tìm kiếm sản phẩm</h1>
                <p class="search-page__subtitle mb-4">
                    Sử dụng thanh tìm kiếm phía trên để khám phá sản phẩm, xem gợi ý và lịch sử tìm kiếm gần đây.
                </p>
                <button class="btn btn-primary" id="search-page-open-overlay">Mở bảng tìm kiếm nâng cao</button>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.searchOverlayInitialState = {
            keyword: @json($initialKeyword),
            sort: @json($initialSort),
            payload: @json($initialPayload),
            errors: @json($initialErrors),
        };

        document.addEventListener('DOMContentLoaded', function () {
            const openBtn = document.getElementById('search-page-open-overlay');
            if (openBtn) {
                openBtn.addEventListener('click', function () {
                    if (window.searchOverlayController) {
                        window.searchOverlayController.open();
                    }
                });
            }
            if (window.searchOverlayController && window.searchOverlayInitialState &&
                (window.searchOverlayInitialState.keyword || window.searchOverlayInitialState.payload)) {
                window.searchOverlayController.open(
                    window.searchOverlayInitialState.keyword,
                    window.searchOverlayInitialState.payload,
                    window.searchOverlayInitialState.errors
                );
            }
        });
    </script>
@endpush
