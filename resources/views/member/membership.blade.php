@extends('layouts.member')

@section('title', __('front.membership.page_title'))

@php
    $activeItem = 'membership';
@endphp

@section('member-content')
    {{-- Premium membership plans for members.

         The checkout, the webhook and the admin side existed already; this is
         the page that lets a member actually buy one. Until now the sidebar's
         "Začít PRÉMIUM" button had nowhere to go. --}}
    <div class="mb-4 md:mb-8 text-center">
        <h1 style="font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;color:#5C2D62;">
            {{ __('front.membership.page_title') }}
        </h1>
        <p style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;padding-top:16px;">
            {{ __('front.membership.page_description') }}
        </p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-700" style="font-family:'Poppins',sans-serif;">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" style="font-family:'Poppins',sans-serif;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Current state --}}
    {{-- Objednávka čekající na peníze. Dokud nedorazí, je jediné, co
         kupující potřebuje, číslo účtu a variabilní symbol. --}}
    @if (!empty($awaitingPayment) && !empty($transferInstructions))
        <div class="mb-8 rounded-xl border-2 p-5" style="border-color:#F0C36D;background:#FFF9EC;font-family:'Poppins',sans-serif;">
            <h2 class="mb-2 text-lg font-semibold" style="color:#8A6100;">{{ __('front.payments.awaiting_transfer') }}</h2>

            <p class="mb-4 text-[15px] text-[#5C5C5C]">{{ __('front.payments.awaiting_transfer_hint') }}</p>

            <dl class="grid gap-2 text-[15px] sm:grid-cols-2">
                @foreach ([
                    'holder' => __('front.payments.account_holder'),
                    'account_number' => __('front.payments.account_number'),
                    'iban' => 'IBAN',
                    'swift' => 'SWIFT / BIC',
                    'bank' => __('front.payments.bank'),
                    'amount' => __('front.payments.amount'),
                    'reference' => __('front.payments.reference'),
                ] as $key => $label)
                    @if (!empty($transferInstructions[$key]))
                        <div class="flex justify-between gap-3 border-b border-[#F0E2C0] py-1">
                            <dt class="text-[#7A6A45]">{{ $label }}</dt>
                            <dd class="font-semibold" style="color:#5C2D62;">{{ $transferInstructions[$key] }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>

            @if (!empty($transferInstructions['note']))
                <p class="mt-4 text-sm text-[#5C5C5C]">{{ $transferInstructions['note'] }}</p>
            @endif
        </div>
    @endif

    <div class="mb-8 rounded-xl border-2 border-gray-200 p-5" style="font-family:'Poppins',sans-serif;">
        <h2 class="mb-2 text-lg font-semibold" style="color:#5C2D62;">{{ __('front.membership.current') }}</h2>
        @if ($active)
            <p class="text-[15px] text-[#505050]">
                <span class="font-semibold" style="color:#DD3888;">{{ $active->subscriptionType->getTranslation('name', app()->getLocale()) }}</span>
                &mdash;
                {{ __('front.membership.valid_until', ['date' => $active->ends_at->translatedFormat('j. n. Y')]) }}
            </p>
        @else
            <p class="text-[15px] text-[#505050]">{{ __('front.membership.none') }}</p>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($types as $type)
            <div class="flex flex-col rounded-xl border-2 border-gray-200 p-6 transition-all duration-200 hover:shadow-lg" style="font-family:'Poppins',sans-serif;">
                <h3 class="mb-1 text-xl font-bold" style="color:#5C2D62;">
                    {{ $type->getTranslation('name', app()->getLocale()) }}
                </h3>

                <p class="mb-4 text-sm text-[#505050]">
                    {{ $type->periodLabel() }}
                </p>

                {{-- Priced in the currency of the visitor's language, taken
                     from its own column rather than converted, so the quoted
                     amount cannot drift with an exchange rate. --}}
                <p class="mb-4 text-3xl font-bold" style="color:#DD3888;">
                    @if ($type->formattedPrice())
                        {{ $type->formattedPrice() }}
                    @else
                        <x-empty-value />
                    @endif
                </p>

                @if (is_array($type->features) && count($type->features))
                    <ul class="mb-6 flex-1 space-y-2 text-sm text-[#505050]">
                        @foreach ($type->features as $feature)
                            <li class="flex items-start gap-2">
                                <span style="color:#DD3888;">&#10003;</span>
                                <span>{{ is_array($feature) ? reset($feature) : $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="mb-6 flex-1"></div>
                @endif

                <form method="POST" action="{{ route('account.member.membership.checkout', $type) }}" class="space-y-3">
                    @csrf

                    @if (!empty($paymentMethods) && count($paymentMethods) > 1)
                        <label class="block text-left text-sm text-[#505050]">
                            {{ __('front.payments.choose_method') }}
                            <select name="payment_method" class="mt-1 w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-sm">
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method->code }}">
                                        {{ \App\Services\Payments\PaymentMethods::label($method->code) }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    @elseif (!empty($paymentMethods) && count($paymentMethods) === 1)
                        <input type="hidden" name="payment_method" value="{{ $paymentMethods->first()->code }}">
                    @endif

                    <button type="submit" class="btn-gold w-full text-center">
                        {{ $active ? __('front.membership.extend_button') : __('front.membership.buy_button') }}
                    </button>
                </form>
            </div>
        @empty
            <p class="text-[#505050]" style="font-family:'Poppins',sans-serif;">{{ __('front.membership.no_plans') }}</p>
        @endforelse
    </div>
@endsection
