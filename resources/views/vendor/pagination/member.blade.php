@if ($paginator->hasPages())
    <div class="flex items-center justify-center gap-3">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#F2F2F2;">
                <span class="inline-block text-[#5C2D62]">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#5C2D62;">
                <span class="inline-block text-white">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="flex items-center justify-center font-semibold" style="width:45px;height:45px;color:#505050;">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="flex items-center justify-center font-semibold" style="width:45px;height:45px;border-radius:8px;background:#DD3888;color:#FFFFFF;font-family: 'Poppins', sans-serif; font-size:14px;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="flex items-center justify-center font-semibold" style="width:45px;height:45px;border-radius:8px;background:transparent;color:#505050;font-family: 'Poppins', sans-serif; font-size:14px;">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#5C2D62;">
                <span class="inline-block transform rotate-180 text-white">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </a>
        @else
            <span class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#F2F2F2;">
                <span class="inline-block transform rotate-180 text-[#5C2D62]">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </span>
        @endif
    </div>
@endif
