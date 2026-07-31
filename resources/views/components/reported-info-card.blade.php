@props(['report'])

@php
    $profile = $report->profile;
    $allegations = $report->allegations ?? [];
    $visibleAllegations = array_slice($allegations, 0, 3);
    $hasMore = count($allegations) > 3;

    $allegationLabels = collect($allegations)
        ->map(fn ($key) => __('front.account.member.allegations.' . $key))
        ->values()
        ->all();

    $cardContent = (isset($profile->content) && is_array($profile->content)) ? $profile->content : [];
    $location = $cardContent['card_location'] ?? ($profile->city ?? '');
    $heightCm = $cardContent['card_height_cm'] ?? 168;
    $imageUrl = $profile->getFirstImageThumbUrl() ?? asset('images/models/model6.png');
@endphp

<div x-data style="width:285px;height:510px;background:#F2F2F2;border-radius:15px;box-shadow:0 15px 15px 0 rgba(92,45,98,0.1);box-sizing:border-box;" class="p-5 flex flex-col">
    <x-icons name="TriangleAlert" style="width:32px;height:32px;color:#DD3888;" />

    <h4 class="mt-3" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:18px;color:#505050;">
        {{ __('front.account.member.block_reason') }}
    </h4>

    <p class="mt-2 line-clamp-6" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;">
        {{ $report->reason }}
    </p>

    <div class="mt-auto space-y-2">
        @foreach($visibleAllegations as $index => $key)
            <div style="width:171px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:11px;color:#505050;">
                    {{ __('front.account.member.allegations.' . $key) }}
                </span>
            </div>
        @endforeach

        @if($hasMore)
            <div style="width:171px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:11px;color:#505050;">…</span>
            </div>
        @endif

        <button type="button"
            @click="Alpine.store('reportedCase').open({
                name: @js($profile->display_name),
                reason: @js($report->reason),
                allegations: @js($allegationLabels),
                height: @js($heightCm),
                age: @js($profile->age),
                location: @js($location),
                image: @js($imageUrl),
            })"
            style="width:171px;height:40px;border-radius:8px;background:#DD3888;">
            <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:14px;color:#FFFFFF;">
                {{ __('front.account.member.read_full_case') }}
            </span>
        </button>
    </div>
</div>
