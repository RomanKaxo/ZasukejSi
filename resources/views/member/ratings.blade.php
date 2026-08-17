@extends('layouts.member')

@section('member-content')
<div class="w-[310px] md:w-auto mx-auto">
<!-- Welcome Title -->
<div class="mb-4 md:mb-8 text-center">
    <h1 style="font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;">
        {{-- Čárka je součástí překladu, ne šablony — jazyk, který ji nepoužívá,
             ji tak může vynechat. Byla tu obojí, takže vycházelo „Vítejte,,". --}}
        <span style="color:#D4D4D4;">{{ __('front.account.member.welcome') }}</span>
        <span style="color:#5C2D62;">{{ $user->name }}</span>
    </h1>
</div>
<hr class="mb-8 rounded-none">

<h2 class="text-center" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:24px;color:#5C2D62;padding-bottom:32px;">
    {{ __('front.account.member.ratings_heading') }}
</h2>

<!-- Ratings Component -->
<livewire:member-ratings />
</div>
@endsection
