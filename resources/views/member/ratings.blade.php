@extends('layouts.member')

@section('member-heading')
    <h1 class="member-title">
        {{-- Čárka je součástí překladu, ne šablony — jazyk, který ji nepoužívá,
             ji tak může vynechat. Byla tu obojí, takže vycházelo „Vítejte,,". --}}
        <span style="color:#D4D4D4;">{{ __('front.account.member.welcome') }}</span>
        <span style="color:#5C2D62;">{{ $user->name }}</span>
    </h1>
@endsection

@section('member-content')
<h2 class="member-subtitle">
    {{ __('front.account.member.ratings_heading') }}
</h2>

<!-- Ratings Component -->
<livewire:member-ratings />
@endsection
