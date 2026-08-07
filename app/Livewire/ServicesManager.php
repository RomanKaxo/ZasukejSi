<?php

namespace App\Livewire;

use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ServicesManager extends Component
{
    // Profile state
    public $hasProfile = false;
    public $profile = null;

    // Services state
    public $services = [];
    public $selectedServices = [];

    // Languages state — the 20 most widely spoken languages in Europe.
    public $languages = [
        'Angličtina', 'Němčina', 'Francouzština', 'Italština', 'Španělština',
        'Polština', 'Rumunština', 'Nizozemština', 'Řečtina', 'Portugalština',
        'Švédština', 'Maďarština', 'Čeština', 'Bulharština', 'Srbština',
        'Slovenština', 'Dánština', 'Finština', 'Norština', 'Ukrajinština',
    ];
    public $selectedLanguages = [];

    public function mount()
    {
        // Get user with profile relationship loaded
        $user = \App\Models\User::with('profile')->find(Auth::id());
        $this->profile = $user->profile;

        // Set profile state
        $this->hasProfile = !is_null($this->profile);

        // Load all active services
        $this->services = Service::active()->ordered()->get();

        // Load selected services for this profile
        if ($this->hasProfile) {
            $this->selectedServices = $this->profile->services->pluck('id')->toArray();

            // Languages are stored as a comma separated string inside the
            // profile's `content` JSON blob (see Profile::getLanguagesAttribute).
            $this->selectedLanguages = $this->profile->languages
                ? array_filter(array_map('trim', explode(',', $this->profile->languages)))
                : [];
        }
    }

    public function toggleService($serviceId)
    {
        $user = Auth::user();

        // Profile must exist (enforced by middleware)
        if (!$user->profile) {
            return;
        }

        // The id arrives from the browser, so it must be validated before it
        // reaches the pivot table — an unknown id would hit the foreign key
        // constraint and blow up with a 500, and an inactive service should not
        // be selectable at all.
        $service = Service::active()->find($serviceId);

        if (! $service) {
            session()->flash('error', __('front.account.services.invalid_service'));
            return;
        }

        $this->profile = $user->profile;

        // Toggle the service
        $this->profile->toggleService($service->id);

        // Refresh selected services
        $this->selectedServices = $this->profile->fresh()->services->pluck('id')->toArray();

        session()->flash('message', __('front.account.services.success'));
    }

    public function toggleLanguage($language)
    {
        $user = Auth::user();

        // Profile must exist (enforced by middleware)
        if (!$user->profile) {
            return;
        }

        // Only allow languages from the known list — anything else is rejected
        // so the stored value never drifts from what the UI can display.
        if (!in_array($language, $this->languages, true)) {
            session()->flash('error', __('front.account.services.invalid_language'));
            return;
        }

        $this->profile = $user->profile;

        if (($key = array_search($language, $this->selectedLanguages, true)) !== false) {
            unset($this->selectedLanguages[$key]);
        } else {
            $this->selectedLanguages[] = $language;
        }
        $this->selectedLanguages = array_values($this->selectedLanguages);

        $this->profile->content = array_merge(
            is_array($this->profile->content) ? $this->profile->content : [],
            ['languages' => implode(', ', $this->selectedLanguages) ?: null]
        );
        $this->profile->save();

        session()->flash('message', __('front.account.services.languages_success'));
    }

    public function render()
    {
        return view('livewire.services-manager');
    }
}

