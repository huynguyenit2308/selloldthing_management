@if ($paginator->hasPages())
    <nav class="products-pagination" role="navigation" aria-label="Pagination Navigation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="page-item disabled" aria-disabled="true" aria-label="Previous">
                <span class="page-link" aria-hidden="true">&#10094;</span>
            </span>
        @else
            <a class="page-item" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                <span class="page-link">&#10094;</span>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></span>
                    @else
                        <a class="page-item" href="{{ $url }}"><span class="page-link">{{ $page }}</span></a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a class="page-item" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                <span class="page-link">&#10095;</span>
            </a>
        @else
            <span class="page-item disabled" aria-disabled="true" aria-label="Next">
                <span class="page-link" aria-hidden="true">&#10095;</span>
            </span>
        @endif
    </nav>
@endif
