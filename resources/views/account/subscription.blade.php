@extends('layouts.account')

@section('title', __('front.subscription.page_title'))

@php
    $activeItem = 'subscription';
@endphp

@section('account-content')
    <!-- Mobile Header -->
    <div class="mb-4 md:hidden">
        <div class="relative flex items-center justify-center py-3">
            <h1 class="w-[400px] max-w-full text-[38px] text-center" style="font-family:'Poppins',sans-serif; font-weight:700; color:#5C2D62;">
                {{ __('front.subscription.page_title') }}
            </h1>
        </div>
        <hr class="w-[312px] mx-auto mb-4">
    </div>

    <!-- Desktop Header -->
    <div class="hidden md:block mb-8">
        <div class="relative flex items-center justify-center py-6">
            <h1 class="w-[400px] max-w-full text-4xl text-center" style="font-family:'Poppins',sans-serif; font-weight:700; color:#5C2D62;">
                {{ __('front.subscription.page_title') }}
            </h1>
        </div>
        <hr class="mb-[48px]">
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-700" style="font-family:'Poppins',sans-serif;">
            {{ session('status') }}
        </div>
    @endif

    <!-- Current plan -->
    <div class="mb-8 rounded-xl border-2 border-gray-200 p-5" style="font-family:'Poppins',sans-serif;">
        <h2 class="mb-2 text-lg font-semibold" style="color:#5C2D62;">{{ __('front.subscription.current_plan') }}</h2>
        @if ($activeSubscription)
            <p class="text-[15px] text-[#505050]">
                <span class="font-semibold" style="color:#DD3888;">{{ $activeSubscription->subscriptionType->getTranslation('name', app()->getLocale()) }}</span>
                &mdash;
                {{ __('front.subscription.active_until', ['date' => $activeSubscription->ends_at->format('d.m.Y')]) }}
            </p>
        @else
            <p class="text-[15px] text-[#505050]">{{ __('front.subscription.no_active_plan') }}</p>
        @endif
    </div>

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

    <!-- Plans -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($types as $type)
            <div class="flex flex-col rounded-xl border-2 border-gray-200 p-6 transition-all duration-200 hover:shadow-lg" style="font-family:'Poppins',sans-serif;">
                <h3 class="mb-1 text-xl font-bold" style="color:#5C2D62;">
                    {{ $type->getTranslation('name', app()->getLocale()) }}
                </h3>

                <p class="mb-4 text-sm text-[#505050]">
                    {{ __('front.subscription.days', ['count' => $type->duration_days]) }}
                </p>

                <p class="mb-4 text-3xl font-bold" style="color:#DD3888;">
                    {{ number_format($type->price, 0, ',', ' ') }} Kč
                </p>

                @if (is_array($type->features) && count($type->features))
                    <ul class="mb-6 flex-1 space-y-2 text-sm text-[#505050]">
                        @foreach ($type->features as $feature)
                            <li class="flex items-start gap-2">
                                <span style="color:#DD3888;">&#10003;</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="mb-6 flex-1"></div>
                @endif

                <form method="POST" action="{{ route('account.subscription.checkout', $type) }}" class="space-y-3">
                    @csrf

                    {{-- Výběr se ukáže, jen když je z čeho vybírat. Jedna
                         metoda znamená žádné rozhodování a rozbalovátko s
                         jedinou položkou je jen práce navíc. --}}
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
                        {{ __('front.subscription.buy_button') }}
                    </button>
                </form>
            </div>
        @empty
            <p class="text-[#505050]" style="font-family:'Poppins',sans-serif;">{{ __('front.subscription.no_active_plan') }}</p>
        @endforelse
    </div>
@endsection
