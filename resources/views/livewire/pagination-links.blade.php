@if ($paginator->hasPages())
    <div class="flex items-center justify-center gap-3 mt-16 md:mt-20 lg:mt-24">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button type="button" disabled aria-disabled="true" class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#F2F2F2;">
                <span class="inline-block text-[#5C2D62]">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </button>
        @else
            <button type="button" wire:click="previousPage" wire:loading.attr="disabled" class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#5C2D62;">
                <span class="inline-block text-white">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </button>
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
                        <a href="{{ $url }}" aria-current="page" class="flex items-center justify-center font-semibold" style="width:45px;height:45px;border-radius:8px;background:#DD3888;color:#FFFFFF;font-family: 'Poppins', sans-serif; font-size:14px;">{{ $page }}</a>
                    @else
                        <a href="{{ $url }}" wire:click.prevent="gotoPage({{ $page }})" class="flex items-center justify-center font-semibold" style="width:45px;height:45px;border-radius:8px;background:transparent;color:#505050;font-family: 'Poppins', sans-serif; font-size:14px;">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <button type="button" wire:click="nextPage" wire:loading.attr="disabled" class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#5C2D62;">
                <span class="inline-block transform rotate-180 text-white">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </button>
        @else
            <button type="button" disabled aria-disabled="true" class="flex items-center justify-center" style="width:45px;height:45px;border-radius:8px;background:#F2F2F2;">
                <span class="inline-block transform rotate-180 text-[#5C2D62]">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 12.6668V3.3335L3.33335 8.00016L10.6667 12.6668Z"/></svg>
                </span>
            </button>
        @endif
    </div>
@endif
