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

    // Null rather than a stand-in value: the modal renders a neutral placeholder
    // for whatever is missing. A reported-profile case file is the last place
    // that should carry an invented height or a stranger's photograph.
    $cardContent = (isset($profile->content) && is_array($profile->content)) ? $profile->content : [];
    $location = $cardContent['card_location'] ?? ($profile->city ?? null);
    $heightCm = $cardContent['card_height_cm'] ?? null;
    $imageUrl = $profile->getFirstImageThumbUrl();
@endphp

<div x-data style="width:230px;height:510px;background:#F2F2F2;border-top-right-radius:15px;border-bottom-right-radius:15px;border-top-left-radius:0;border-bottom-left-radius:0;box-shadow:0 15px 15px 0 rgba(92,45,98,0.1);box-sizing:border-box;position:relative;z-index:1;" class="p-5 flex flex-col reported-info-card">
    <x-icons name="TriangleAlert" class="mx-auto md:mx-0" style="width:32px;height:32px;color:#DD3888;" />

    <h4 class="mt-3 text-center md:text-left whitespace-nowrap reported-info-card-title" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:18px;color:#505050;">
        {{ __('front.account.member.block_reason') }}
    </h4>

    <p class="mt-2 line-clamp-6 hidden md:block" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;">
        {{ $report->reason }}
    </p>

    <div class="mt-auto reported-info-card-badges flex flex-col items-center">
        @foreach($visibleAllegations as $key)
            <div class="reported-info-card-badge" style="width:171px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:11px;color:#505050;">
                    {{ __('front.account.member.allegations.' . $key) }}
                </span>
            </div>
        @endforeach

        @if($hasMore)
            <div class="reported-info-card-badge reported-info-card-more hidden md:flex" style="width:171px;height:30px;border-radius:8px;background:#FFFFFF;align-items:center;justify-content:center;">
                <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:11px;color:#505050;">…</span>
            </div>
        @endif

        <div class="block md:hidden" style="width:1px;height:61px;background:#E6E6E6;"></div>

        <button type="button"
            class="reported-info-card-button"
            @click="$store.reportedCase.open({
                name: @js($profile->display_name),
                reason: @js($report->reason),
                allegations: @js($allegationLabels),
                height: @js($heightCm),
                age: @js($profile->age),
                location: @js($location),
                image: @js($imageUrl),
            })"
            style="width:171px;height:40px;border-radius:8px;background:#DD3888;">
            <span class="hidden md:inline" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:14px;color:#FFFFFF;">
                {{ __('front.account.member.read_full_case') }}
            </span>
            <span class="inline md:hidden" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:14px;color:#FFFFFF;">
                {{ __('front.account.member.read_case') }}
            </span>
        </button>
    </div>
</div>
