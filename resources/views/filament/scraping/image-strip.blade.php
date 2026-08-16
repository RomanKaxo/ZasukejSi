@php /** @var array<int, string> $urls */ @endphp

@if(empty($urls))
    <p style="font-size:0.875rem;color:#6b7280;">—</p>
@else
    {{-- Remote thumbnails straight from the source: nothing is downloaded until
         the item is imported, so this is the only way to see what will arrive. --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;">
        @foreach($urls as $index => $url)
            <a href="{{ $url }}" target="_blank" rel="noopener"
               style="display:block;position:relative;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;aspect-ratio:3/4;background:#f3f4f6;">
                <img src="{{ $url }}" alt="Fotografie {{ $index + 1 }}" loading="lazy"
                     style="width:100%;height:100%;object-fit:cover;display:block;"
                     onerror="this.style.display='none';this.parentElement.setAttribute('data-failed','1');" />
                <span style="position:absolute;left:6px;top:6px;padding:1px 6px;border-radius:999px;background:rgba(0,0,0,.6);color:#fff;font-size:11px;">
                    {{ $index + 1 }}
                </span>
            </a>
        @endforeach
    </div>

    <p style="margin-top:8px;font-size:0.75rem;color:#6b7280;">
        Náhledy se načítají přímo ze zdroje. Prázdné dlaždice znamenají, že fotka už na původní adrese není.
    </p>
@endif
