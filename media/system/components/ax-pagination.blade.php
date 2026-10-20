@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pagination-nav">
        <div class="pagination-desktop">
            <div class="pagination-info">
                <p>
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span>{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span>{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span>{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div class="pagination-links">
            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <span class="ax-btn color-info disabled">
                    @icon('angle-double-left') {!! __('tmp::atum.ax_pagination_previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="ax-btn color-info">
                    @icon('angle-double-left') {!! __('tmp::atum.ax_pagination_previous') !!}
                </a>
            @endif

            {{-- Números --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $start = max(1, $currentPage - 2);
                $end = min($lastPage, $currentPage + 2);
            @endphp

            @for ($page = $start; $page <= $end; $page++)
                <div class="cont-countpage">   
                    @if ($page == $paginator->currentPage())
                        <span class="ax-btn color-info active">{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="ax-btn color-info">{{ $page }}</a>
                    @endif
                </div>
            @endfor

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="ax-btn color-info">
                    {!! __('tmp::atum.ax_pagination_next') !!} @icon('angle-double-right')
                </a>
            @else
                <span class="ax-btn color-info disabled">
                    {!! __('tmp::atum.ax_pagination_next') !!} @icon('angle-double-right')
                </span>
            @endif

        </div>
    </nav>
@endif