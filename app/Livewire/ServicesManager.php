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
        }
    }

    public function toggleService($serviceId)
    {
        $user = Auth::user();
        
        // Profile must exist (enforced by middleware)
        if (!$user->profile) {
            return;
        }

        $this->profile = $user->profile;

        // Toggle the service
        $this->profile->toggleService($serviceId);

        // Refresh selected services
        $this->selectedServices = $this->profile->fresh()->services->pluck('id')->toArray();

        session()->flash('message', 'Služba byla úspěšně aktualizována!');
    }

    public function render()
    {
        return view('livewire.services-manager');
    }
}

