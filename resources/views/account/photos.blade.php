@extends('layouts.account')

@section('title', __('Fotografie a video'))

@section('account-content')
    <div class="flex flex-col max-[426px]:flex-col-reverse items-center max-[426px]:w-full">
        <!-- Title & Basic Info -->
        <div class="flex flex-col items-end w-[1134px] mx-auto max-[426px]:items-center max-[426px]:w-full max-[426px]:my-8">
            <div class="w-[843px] max-[426px]:w-[310px] flex items-center justify-center relative">
                <h2 class="font-bold text-[36px] text-[#5C2D62]" style="font-family: 'Poppins', sans-serif;">Fotografie a video</h2>
                
                <div class="absolute right-0 flex flex-col items-end gap-0.5 max-[426px]:hidden">
                    <div class="flex items-center gap-1.5">
                        <div class="w-[14px] h-[14px] rounded-full bg-[#00B80F]"></div>
                        <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">Nyní si online</span>
                    </div>
                    <span class="text-[13px] text-[#505050] underline" style="font-family: 'Poppins', sans-serif; color: #DD3888;">nastavení</span>
                </div>
            </div>
            <x-dashboard.section-divider />
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex justify-end mb-8 gap-x-12 w-[1134px] mx-auto max-[426px]:!w-[310px] max-[426px]:mx-auto">
        <div class="max-[426px]:hidden">
            <x-dashboard.sidebar />
        </div>
        
        <div class="w-[843px] max-[426px]:w-[310px] max-[426px]:mx-auto flex flex-col items-center mt-12 max-[426px]:items-center">
            <!-- Zde bude obsah pro Fotografie a video -->
            <p class="text-gray-500">Obsah pro fotografie a video bude brzy následovat.</p>
        </div>
    </div>
@endsection
