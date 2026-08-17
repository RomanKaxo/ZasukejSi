@php
    use App\Models\SubscriptionType;
    use App\Support\Currencies;

    // Two audiences, two products: `profile` plans are what a provider buys to
    // be seen, `member` plans are what a visitor buys to see ratings. They were
    // only reachable from inside each account, so the public page describing
    // them had nothing behind it.
    $currency = Currencies::forLocale();

    $groups = [
        [
            'audience' => SubscriptionType::AUDIENCE_PROFILE ?? 'profile',
            'title' => __('front.plans.for_women'),
            'subtitle' => __('front.plans.for_women_subtitle'),
            'plans' => SubscriptionType::query()->where('audience', 'profile')->active()->ordered()->get(),
        ],
        [
            'audience' => 'member',
            'title' => __('front.plans.for_men'),
            'subtitle' => __('front.plans.for_men_subtitle'),
            'plans' => SubscriptionType::query()->where('audience', 'member')->active()->ordered()->get(),
        ],
    ];
@endphp

<section class="mx-auto w-full max-w-[1140px] px-4 py-12">
    @foreach ($groups as $group)
        @if ($group['plans']->isNotEmpty())
            <div class="mb-16 last:mb-0">
                <h2 class="mb-1 text-center text-3xl font-bold" style="color:#5C2D62;font-family:'Poppins',sans-serif;">
                    {{ $group['title'] }}
                </h2>
                <p class="mb-8 text-center text-sm" style="color:#5C5C5C;">{{ $group['subtitle'] }}</p>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($group['plans'] as $plan)
                        <div class="flex flex-col rounded-2xl border border-[#EDE7EE] bg-white p-6"
                             style="box-shadow:0 10px 25px 0 rgba(220,214,221,0.55);">
                            <h3 class="mb-1 text-xl font-bold" style="color:#5C2D62;">
                                {{ $plan->getTranslation('name', app()->getLocale()) }}
                            </h3>

                            <p class="mb-3 text-sm" style="color:#8C8C8C;">{{ $plan->periodLabel() }}</p>

                            {{-- Priced in the currency of the visitor's language,
                                 read from its own column rather than converted. --}}
                            <p class="mb-4 text-3xl font-bold" style="color:#DD3888;">
                                @if ($plan->formattedPrice($currency))
                                    {{ $plan->formattedPrice($currency) }}
                                @else
                                    <x-empty-value />
                                @endif
                            </p>

                            @if (filled($plan->getTranslation('description', app()->getLocale())))
                                <p class="mb-4 text-sm" style="color:#5C5C5C;">
                                    {{ $plan->getTranslation('description', app()->getLocale()) }}
                                </p>
                            @endif

                            @if (is_array($plan->features) && count($plan->features))
                                <ul class="mb-6 flex-1 space-y-2 text-sm" style="color:#505050;">
                                    @foreach ($plan->features as $feature)
                                        <li class="flex items-start gap-2">
                                            <span style="color:#00B80F;">&#10003;</span>
                                            <span>{{ is_array($feature) ? reset($feature) : $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="mb-6 flex-1"></div>
                            @endif

                            {{-- Where the buy button goes depends on who is
                                 looking: the two audiences buy in different
                                 places, and a guest has to sign in first. --}}
                            @php
                                $planUser = auth()->user();
                                $planIsMemberPlan = $plan->audience === 'member';
                                $planIsAdmin = $planUser?->hasRole('admin') ?? false;
                                $planIsMember = $planUser && $planUser->isMale() && ! $planIsAdmin;
                                $planIsProvider = $planUser && ! $planUser->isMale();

                                $planCanBuy = $planUser && (
                                    ($planIsMemberPlan && $planIsMember)
                                    || (! $planIsMemberPlan && $planIsProvider)
                                );

                                $planUrl = match (true) {
                                    ! $planUser => null,
                                    $planIsMemberPlan && $planIsMember => route('account.member.membership.index'),
                                    ! $planIsMemberPlan && $planIsProvider => route('account.subscription.index'),
                                    default => null,
                                };
                            @endphp

                            @if ($planUrl)
                                <a href="{{ $planUrl }}"
                                   class="flex h-[45px] items-center justify-center rounded-lg font-semibold text-white"
                                   style="background:#DD3888;">
                                    {{ __('front.plans.choose') }}
                                </a>
                            @elseif (! $planUser)
                                <button type="button" x-data @click="$dispatch('show-register-modal')"
                                        class="flex h-[45px] w-full items-center justify-center rounded-lg font-semibold text-white"
                                        style="background:#DD3888;">
                                    {{ __('front.plans.register_to_buy') }}
                                </button>
                            @else
                                {{-- Signed in, but this plan is for the other
                                     audience — say so rather than offering a
                                     button that cannot work. --}}
                                <p class="text-center text-sm" style="color:#8C8C8C;">
                                    {{ $planIsMemberPlan ? __('front.plans.men_only') : __('front.plans.women_only') }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    @if (collect($groups)->every(fn ($g) => $g['plans']->isEmpty()))
        <p class="text-center" style="color:#8C8C8C;">{{ __('front.membership.no_plans') }}</p>
    @endif
</section>
