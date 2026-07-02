@props([
    'name' => '',
    'id' => '',
    'checked' => false,
    'label' => '',
    'wireModel' => null,
    'disabled' => false,
])

@php
    $switchId = $id ?: $name;
    $isChecked = old($name, $checked);
    // Check if wire:model is passed as an attribute
    $wireModelAttr = $attributes->get('wire-model') ?? $wireModel;
@endphp

<div class="flex items-center">
    @if($wireModelAttr)
        <!-- Livewire Version -->
        <div class="relative inline-flex items-center cursor-pointer {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
            <input 
                type="checkbox" 
                id="{{ $switchId }}"
                name="{{ $name }}"
                wire:model="{{ $wireModelAttr }}"
                {{ $disabled ? 'disabled' : '' }}
                class="sr-only peer">
            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600" onclick="document.getElementById('{{ $switchId }}').click()"></div>
        </div>
    @else
        <!-- Regular Version -->
        <div class="relative inline-flex items-center cursor-pointer {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
            <input 
                type="checkbox" 
                id="{{ $switchId }}"
                name="{{ $name }}"
                value="1"
                {{ $isChecked ? 'checked' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $attributes->whereStartsWith('wire:') }}
                class="sr-only peer">
            <label for="{{ $switchId }}" class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600 cursor-pointer"></label>
        </div>
    @endif

    @if($label)
        <label for="{{ $switchId }}" class="ml-3 text-sm text-gray-700 cursor-pointer {{ $disabled ? 'cursor-not-allowed' : '' }}">
            {{ $label }}
        </label>
    @endif
</div>