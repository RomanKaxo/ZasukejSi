@extends('layouts.app')

@section('content')
<!-- Add top padding to account for fixed navbar -->
<div class="container mx-auto pt-24 md:pt-38 min-h-screen px-4 md:px-0">
    <div class="flex flex-col md:flex-row md:justify-center md:gap-[81px]">
        <!-- Sidebar -->
        <x-member-sidebar :activeItem="$activeItem ?? 'dashboard'" />
        
        <!-- Main Content -->
        <main class="flex-1 md:flex-none md:w-[843px] pt-10 px-0 md:px-0 md:pt-0">
          
                @yield('member-content')
      
        </main>
    </div>
</div>
<x-reported-case-modal />
@endsection
