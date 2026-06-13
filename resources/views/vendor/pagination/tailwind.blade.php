@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="mt-2">
        <div class="pagination-shell sm:hidden">
            <p class="pagination-summary">
                @if ($paginator->firstItem())
                    <strong>{{ $paginator->firstItem() }}</strong> - <strong>{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong>
                @else
                    <strong>{{ $paginator->count() }}</strong> hasil
                @endif
            </p>

            <div class="grid grid-cols-2 gap-2">
                @if ($paginator->onFirstPage())
                    <span class="pager-link pager-link-disabled" aria-disabled="true">
                        {!! __('pagination.previous') !!}
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pager-link">
                        {!! __('pagination.previous') !!}
                    </a>
                @endif

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pager-link">
                        {!! __('pagination.next') !!}
                    </a>
                @else
                    <span class="pager-link pager-link-disabled" aria-disabled="true">
                        {!! __('pagination.next') !!}
                    </span>
                @endif
            </div>
        </div>

        <div class="hidden sm:flex pagination-shell">
            <p class="pagination-summary">
                @if ($paginator->firstItem())
                    Menampilkan <strong>{{ $paginator->firstItem() }}</strong> - <strong>{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong> hasil
                @else
                    Menampilkan <strong>{{ $paginator->count() }}</strong> hasil
                @endif
            </p>

            <div class="pager-group">
                @if ($paginator->onFirstPage())
                    <span class="pager-link pager-link-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15 6-6 6 6 6" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pager-link" aria-label="{{ __('pagination.previous') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15 6-6 6 6 6" />
                        </svg>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="pager-link pager-link-disabled" aria-disabled="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pager-link pager-link-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pager-link" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pager-link" aria-label="{{ __('pagination.next') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 6 6 6-6 6" />
                        </svg>
                    </a>
                @else
                    <span class="pager-link pager-link-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 6 6 6-6 6" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
