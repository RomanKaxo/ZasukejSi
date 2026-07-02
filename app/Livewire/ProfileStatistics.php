<?php

namespace App\Livewire;

use App\Models\Profile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;

class ProfileStatistics extends Component
{
    public ?Profile $profile = null;
    public Carbon $currentMonth;
    public string $variant = 'homepage';
    public string $instanceId = '';
    public int $yAxisMax = 120;
    public int $yAxisStep = 20;
    public array $chartLabels = [];
    public array $chartValues = [];
    public array $chartColors = [];
    public array $chartVip = [];

    public function mount(string $variant = 'homepage')
    {
        $this->variant = $variant;
        $this->instanceId = (string) Str::uuid();

        $user = auth()->user();
        
        if ($user) {
            $user->load('profile');
            if ($user->profile) {
                $this->profile = $user->profile;
            }
        }

        if (!$this->profile) {
            $this->profile = \App\Models\Profile::first();
        }
        
        $this->currentMonth = now()->startOfMonth();

        $this->generateChartData();
    }

    private function generateChartData(): void
    {
        $this->chartLabels = [
            '10. 9.', '11. 9.', '12. 9.', '13. 9.', '14. 9.', '15. 9.', '16. 9.', '17. 9.', '18. 9.',
            '19. 9.', '20. 9.', '21. 9.', '22. 9.', '23. 9.', '24. 9.', '25. 9.'
        ];

        if ($this->variant === 'detail') {
            $this->chartValues = [8, 13, 18, 15, 12, 3, 12, 4, 12, 27, 3, 5, 11, 6, 9, 12];
            $this->yAxisMax = 30;
            $this->yAxisStep = 5;
        } else {
            $this->chartValues = [38, 42, 64, 60, 22, 38, 43, 64, 44, 68, 96, 104, 66, 84, 83, 36];
            $this->yAxisMax = 120;
            $this->yAxisStep = 20;
        }

        $this->chartColors = [];
        foreach (array_keys($this->chartValues) as $idx) {
            $this->chartColors[] = $idx < 9 ? '#DD3888' : '#5C2D62';
        }

        $this->chartVip = [];
        foreach (array_keys($this->chartValues) as $idx) {
            $this->chartVip[] = $this->variant === 'detail' ? $idx === 10 : $idx === 11;
        }
    }

    public function render()
    {
        return view('livewire.profile-statistics');
    }
}
