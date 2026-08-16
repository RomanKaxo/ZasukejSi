@php /** @var \App\Models\ScrapeRun $run */ @endphp

<div>
    <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:12px;font-size:0.875rem;color:#4b5563;">
        <span><strong>Zdroj:</strong> {{ $run->source?->name ?? '—' }}</span>
        <span><strong>Spuštěno:</strong> {{ $run->started_at?->format('d.m.Y H:i:s') ?? '—' }}</span>
        <span><strong>Trvání:</strong> {{ $run->durationSeconds() === null ? '—' : $run->durationSeconds() . ' s' }}</span>
        <span><strong>Stránek:</strong> {{ $run->pages_fetched }}</span>
        <span><strong>Nalezeno:</strong> {{ $run->items_found }}</span>
    </div>

    @if(filled($run->error))
        <p style="margin-bottom:12px;padding:10px 12px;border-radius:8px;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;font-size:0.875rem;">
            {{ $run->error }}
        </p>
    @endif

    {{-- Kept verbatim: this is what the run reported line by line, including
         the values a dry run extracted. --}}
    <pre style="max-height:420px;overflow:auto;padding:12px;border-radius:8px;background:#0f172a;color:#e2e8f0;font-size:12px;line-height:1.55;white-space:pre-wrap;word-break:break-word;">{{ $run->log }}</pre>
</div>
