{{--
    /account/profile — nastavení účtu, které patří každému (jméno, e-mail,
    telefon, heslo, smazání účtu; u účtu bez pohlaví i jeho doplnění).

    Dřív to byla anglická stránka mimo design. Je to ale jediné místo, kde jde
    smazat účet, a jediná stránka účtu pro uživatele bez vyplněného pohlaví
    (odkazuje sem předplatné a menu účtu), takže zůstává a přebírá rozvržení
    „Základní nastavení“ z Figmy (home-8): nadpis, linka, sloupec 400 px,
    šedé „Uložit změny“, sekce oddělené linkou.

    Muž dostane rozvržení svého účtu (postranní menu člena), ostatní rozvržení
    účtu poskytovatelky. Obsah je v obou stejný.
--}}
@extends(auth()->user()->isMale() ? 'layouts.member' : 'layouts.account')

@section('title', __('front.account.member.basic_settings'))

@php
    $activeItem = 'settings';
@endphp

@section('member-heading')
    <h1 class="member-title">{{ __('front.account.member.basic_settings') }}</h1>
@endsection

@section('member-content')
    <div class="pt-[50px]">
        @include('account.profile.partials.settings', ['user' => $user])
    </div>
@endsection

@section('account-content')
    @include('account.partials.page-header', ['title' => __('front.account.member.basic_settings'), 'ruleGap' => 'mb-[80px]'])
    @include('account.profile.partials.settings', ['user' => $user])
@endsection
