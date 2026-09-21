@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between text-sm">
        <div class="flex-1 flex justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 rounded border border-ink-200 text-ink-300">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded border border-ink-200 text-ink-700 hover:bg-ink-50">Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded border border-ink-200 text-ink-700 hover:bg-ink-50">Next</a>
            @else
                <span class="px-3 py-1.5 rounded border border-ink-200 text-ink-300">Next</span>
            @endif
        </div>

        <div class="hidden sm:flex sm:items-center sm:justify-between w-full">
            <p class="text-ink-500">
                Showing <span class="font-medium text-ink-800">{{ $paginator->firstItem() }}</span>
                to <span class="font-medium text-ink-800">{{ $paginator->lastItem() }}</span>
                of <span class="font-medium text-ink-800">{{ $paginator->total() }}</span> results
            </p>

            <div class="flex gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-3 py-1.5 text-ink-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="px-3 py-1.5 rounded bg-ink-900 text-white font-medium">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-1.5 rounded text-ink-600 hover:bg-ink-50">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
    </nav>
@endif
