<?php

namespace App\Livewire;

use App\Models\Profile;
use App\Models\ProfileReport;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReportProfileModal extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public ?int $profileId = null;

    public bool $submitted = false;

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string|max:2000')]
    public string $message = '';

    #[Validate('nullable|image|max:5120')]
    public $screenshot = null;

    protected $listeners = ['show-report-modal' => 'show', 'hide-report-modal' => 'hide'];

    public function show($profileId): void
    {
        $this->profileId = (int) (is_array($profileId) ? ($profileId['profileId'] ?? null) : $profileId);
        $this->showModal = true;
        $this->submitted = false;
    }

    public function hide(): void
    {
        $this->showModal = false;
        $this->reset(['email', 'message', 'screenshot', 'submitted', 'profileId']);
        $this->resetErrorBag();
    }

    public function submit(): void
    {
        $this->validate();

        if (! $this->profileId || ! Profile::whereKey($this->profileId)->exists()) {
            $this->addError('email', __('front.profiles.detail_page.report_modal.invalid_profile'));

            return;
        }

        ProfileReport::create([
            'profile_id' => $this->profileId,
            'email' => $this->email,
            'message' => $this->message,
            'screenshot_path' => $this->screenshot?->store('profile-reports', 'public'),
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.report-profile-modal');
    }
}
