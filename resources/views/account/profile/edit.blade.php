@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<!-- Add top padding to account for fixed navbar -->
<div class="pt-28 py-12 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('account.dashboard') }}" class="text-gray-700 hover:text-gray-900">
                        Account
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-gray-500 md:ml-2">Edit Profile</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Profile Information</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Update your account's profile information and email address.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form method="POST" action="{{ route('account.update') }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <div class="grid grid-cols-6 gap-6">
                                <!-- Name -->
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="name">{{ __('Name') }}</label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           required
                                           class="input-control mt-1 @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="email">{{ __('Email address') }}</label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           required
                                           class="input-control mt-1 @error('email') border-red-500 @enderror">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    
                                    @if($user->email_verified_at)
                                        <div class="mt-2 flex items-center text-sm text-green-600">
                                            <svg class="mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Email verified
                                        </div>
                                    @else
                                        <div class="mt-2 text-sm text-yellow-600">
                                            Your email address is unverified. 
                                            <form method="POST" action="{{ route('verification.send') }}" class="inline">
                                                @csrf
                                                <button type="submit" class="font-medium underline hover:text-yellow-500">
                                                    Click here to re-send the verification email.
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>

                                <!-- Phone -->
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="phone">{{ __('Phone number') }}</label>
                                    <input type="tel" 
                                           name="phone" 
                                           id="phone" 
                                           value="{{ old('phone', $user->phone) }}" 
                                           class="input-control mt-1 @error('phone') border-red-500 @enderror">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Optional. Include your country code if international.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <button type="submit" class="btn-primary">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profile Section (if exists) -->
        @if($user->profile)
        <div class="hidden sm:block" aria-hidden="true">
            <div class="py-5">
                <div class="border-t border-gray-200"></div>
            </div>
        </div>

        <div class="mt-10 sm:mt-0">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Public Profile</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            This information will be displayed publicly so be careful what you share.
                        </p>
                    </div>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6">
                                    <label class="block text-sm font-medium text-gray-700">Profile Information</label>
                                    <div class="mt-2 bg-gray-50 rounded-md p-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Display Name</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $user->profile->display_name ?? 'Not set' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">Gender</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $user->profile->gender ?? 'Not set' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">Age</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $user->profile->age ?? 'Not set' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">City</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $user->profile->city ?? 'Not set' }}
                                                </p>
                                            </div>
                                        </div>
                                        @if($user->profile->about)
                                            <div class="mt-4">
                                                <p class="text-sm text-gray-500">About</p>
                                                <p class="text-sm text-gray-900">{{ $user->profile->about }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">
                                        Profile information is managed separately. Contact support to update your public profile.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Password Section Link -->
        <div class="hidden sm:block" aria-hidden="true">
            <div class="py-5">
                <div class="border-t border-gray-200"></div>
            </div>
        </div>

        <div class="mt-10 sm:mt-0">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Security</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Keep your account secure with a strong password.
                        </p>
                    </div>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Password</h4>
                                    <p class="text-sm text-gray-500">Last changed {{ $user->updated_at->diffForHumans() }}</p>
                                </div>
                                <a href="{{ route('account.password.edit') }}" 
                                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Change Password
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="hidden sm:block" aria-hidden="true">
            <div class="py-5">
                <div class="border-t border-gray-200"></div>
            </div>
        </div>

        <div class="mt-10 sm:mt-0">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Account Actions</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage your account and data.
                        </p>
                    </div>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <div class="flex items-center justify-between py-3 border-b border-gray-200 last:border-b-0">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('front.profiles.form.delete_account') }}</h4>
                                    <p class="text-sm text-gray-500">{{ __('front.profiles.form.delete_account_desc') }}</p>
                                </div>
                                <button type="button" 
                                        x-data=""
                                        x-on:click="$dispatch('open-modal', 'confirm-user-deletion')"
                                        class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    {{ __('front.profiles.form.delete_account') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div x-data="{ show: false }" 
     x-on:open-modal.window="if ($event.detail === 'confirm-user-deletion') show = true"
     x-on:close-modal.window="if ($event.detail === 'confirm-user-deletion') show = false"
     x-show="show"
     class="fixed inset-0 overflow-y-auto z-50" 
     style="display: none;">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity" 
             aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75" x-on:click="show = false"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            
            <form method="POST" action="{{ route('account.destroy') }}">
                @csrf
                @method('DELETE')
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('front.profiles.form.delete_account') }}</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('front.profiles.form.delete_account_confirm') }}
                                </p>
                                <div class="mt-4">
                                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('front.auth.login.password') }}</label>
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           placeholder="{{ __('front.profiles.form.password_confirm') }}"
                                           required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('front.profiles.form.delete_account') }}
                    </button>
                    <button type="button" 
                            x-on:click="show = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('front.profiles.form.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection