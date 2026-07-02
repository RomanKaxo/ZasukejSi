@php
    $selectMethod = $attributes->get('select-method');
    $optionsJson = json_encode($options);
    $optionsHash = md5($optionsJson);
    $dropdownOpen = $attributes->get('dropdown-open');
@endphp
<div x-data="{ 
        open: {{ $dropdownOpen ? 'true' : 'false' }},
        selectedValue: @js($value),
        selectedLabel: @js($value && isset($options[$value]) ? $options[$value] : ($value ?: '')),
        isSearchable: {{ $searchable ? 'true' : 'false' }},
        selectOption(value, label) {
            this.selectedValue = value;
            this.selectedLabel = label;
            this.open = false;
            
            // For searchable inputs, also update the input element directly
            if (this.isSearchable) {
                const input = this.$refs.searchInput;
                if (input) {
                    input.value = label;
                }
            }
            
            // Sync with backend without blocking UI
            $wire.{{ $selectMethod }}(value);
        }
     }"
     x-init="
        // Preserve focus after Livewire updates
        let wasFocused = false;
        let cursorPos = 0;
        
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            const input = $refs.searchInput;
            if (input && document.activeElement === input) {
                wasFocused = true;
                cursorPos = input.selectionStart;
            }
            
            succeed(({ snapshot, effect }) => {
                if (wasFocused && input) {
                    $nextTick(() => {
                        input.focus();
                        input.setSelectionRange(cursorPos, cursorPos);
                        open = true;
                    });
                    wasFocused = false;
                }
            });
        });
     "
     x-on:click.outside="open = false"
     class="relative">
    <label for="{{ $name }}">{{ $label }}</label>
    <div class="relative">
        @if($searchable)
            <!-- Searchable Input -->
            <input
                type="text"
                id="{{ $name }}"
                x-ref="searchInput"
                wire:model.live.debounce.300ms="{{ $wireModel }}"
                @input="open = true"
                @click="open = true"
                @focus="open = true"
                placeholder="{{ $placeholder }}"
                autocomplete="off"
                class="input-control !pr-12">
        @else
            <!-- Non-searchable (readonly input) -->
            <input
                type="text"
                id="{{ $name }}"
                @click="open = !open"
                x-bind:value="selectedLabel || '{{ $placeholder }}'"
                placeholder="{{ $placeholder }}"
                readonly
                class="input-control !pr-12 cursor-pointer">
        @endif
        
        <!-- Dropdown Arrow -->
        <button 
            type="button"
            @click="open = !open"
            class="absolute flex items-center justify-center rounded-md inset-y-0 right-1.5 top-1.5 bottom-1.5 aspect-square bg-gray-100 hover:text-primary-600 transition-colors">
            <svg class="w-3 h-3 text-primary transition-transform duration-200" 
                 :class="{ 'rotate-180': open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-cloak
             class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
            @forelse($options as $optionValue => $optionLabel)
                <div 
                    @click="selectOption('{{ $optionValue }}', '{{ addslashes($optionLabel) }}')"
                    class="px-4 py-2 hover:bg-primary-50 cursor-pointer text-text-default hover:text-primary-600 transition-colors">
                    {{ $optionLabel }}
                </div>
            @empty
                <div class="px-4 py-2 text-gray-500 text-sm">
                    {{ __('No results found') }}
                </div>
            @endforelse
        </div>
    </div>
</div>