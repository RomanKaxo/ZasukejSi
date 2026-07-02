<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AutocompleteSelect extends Component
{
    public string $name;
    public string $label;
    public string $placeholder;
    public array $options;
    public mixed $value;
    public string $wireModel;
    public bool $clearOnClick;
    public bool $searchable;
    public ?string $wireClick;
    public ?string $wireFocus;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $name,
        string $label,
        array $options = [],
        mixed $value = null,
        string $placeholder = '',
        string $wireModel = '',
        bool $clearOnClick = true,
        bool $searchable = true,
        ?string $wireClick = null,
        ?string $wireFocus = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
        $this->value = $value;
        $this->placeholder = $placeholder ?: "Select {$label}...";
        $this->wireModel = $wireModel ?: $name;
        $this->clearOnClick = $clearOnClick;
        $this->searchable = $searchable;
        $this->wireClick = $wireClick;
        $this->wireFocus = $wireFocus;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.autocomplete-select');
    }

    /**
     * Get the dropdown property name for this field
     */
    public function getDropdownProperty(): string
    {
        return 'show' . ucfirst($this->name) . 'Dropdown';
    }

    /**
     * Get the filtered options property name for this field
     */
    public function getFilteredOptionsProperty(): string
    {
        return 'filtered' . ucfirst($this->name) . 'Options';
    }
}