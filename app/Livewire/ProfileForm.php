<?php

namespace App\Livewire;

use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Rule;

class ProfileForm extends Component
{
    protected array $publishRequiredFields = [
        'display_name',
        'age',
        'country_code',
    ];

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('required|email|max:255')]
    public $email = '';

    #[Rule('nullable|string|max:20|unique:users,phone')]
    public $phone = '';

    // Password change fields
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    // Email change fields
    public $new_email = '';
    public $email_change_password = '';

    #[Rule('nullable|string|max:255')]
    public $country = '';

    // Profile model fields
    #[Rule('nullable|string|max:255')]
    public $display_name = '';

    #[Rule('nullable|integer|min:18|max:120')]
    public $age = '';

    #[Rule('nullable|string|max:255')]  
    public $city = '';

    #[Rule('nullable|string|max:2')]
    public $country_code = '';

    #[Rule('nullable|string|max:255')]
    public $address = '';

    #[Rule('nullable|string|max:1200')]
    public $about = '';

    #[Rule('boolean')]
    public $incall = false;

    #[Rule('boolean')]
    public $outcall = false;

    #[Rule('boolean')]
    public $is_porn_actress = false;

    #[Rule('nullable|string')]
    public $availability_hours = '';

    public $local_prices = [];

    public $global_prices = [];

    public $contacts = [];

    #[Rule('nullable|in:pending,approved,rejected')]
    public $status = '';

    public $is_public = false;

    // Dropdown states
    public $countryDropdownOpen = false;
    public $countrySearchTerm = '';
    
    // City autocomplete states
    public $cityDropdownOpen = false;
    public $citySearchTerm = '';
    public $citySuggestions = [];

    // Options arrays
    public $countries = [];

    // Profile state
    public $hasProfile = false;

    public function mount()
    {
        // Get user with profile relationship loaded
        $user = \App\Models\User::with('profile')->find(Auth::id());
        $profile = $user->profile;

        // Set profile state
        $this->hasProfile = !is_null($profile);

        // Load user data
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';

        // Load profile data if exists
        if ($profile) {
            $this->display_name = $profile->display_name ?? '';
            $this->age = $profile->age ?? '';
            $this->city = $profile->city ?? '';
            $this->citySearchTerm = $profile->city ?? ''; // Initialize city search with existing value
            $this->country_code = $profile->country_code ?? '';
            $this->address = $profile->address ?? '';
            $this->about = $profile->about ?? '';
            $this->incall = $profile->incall ?? false;
            $this->outcall = $profile->outcall ?? false;
            $this->is_porn_actress = $profile->is_porn_actress ?? false;
            $this->availability_hours = is_array($profile->availability_hours) 
                ? implode(', ', $profile->availability_hours) 
                : ($profile->availability_hours ?? '');
            $this->local_prices = is_array($profile->local_prices) 
                ? $profile->local_prices 
                : [];
            $this->global_prices = is_array($profile->global_prices) 
                ? $profile->global_prices 
                : [];
            $this->contacts = is_array($profile->contacts) 
                ? $profile->contacts 
                : [];
            $this->status = $profile->status ?? '';
            $this->is_public = $profile->is_public ?? false;
        }

        // Load dropdown options
        $this->loadCountries();
    }

    /**
     * Check if the current user is an admin.
     */
    public function isAdmin(): bool
    {
        $user = \App\Models\User::with('roles')->find(Auth::id());
        return $user->roles()->where('name', 'admin')->exists();
    }

    /**
     * Get the status label with translation.
     */
    public function getStatusLabel(): string
    {
        $statusLabels = [
            'pending' => __('front.profiles.form.pending'),
            'approved' => __('front.profiles.form.approved'),
            'rejected' => __('front.profiles.form.rejected'),
        ];

        return $statusLabels[$this->status] ?? $statusLabels['pending'];
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColor(): string
    {
        $statusColors = [
            'pending' => 'text-yellow-600 bg-yellow-50 border-yellow-200',
            'approved' => 'text-green-600 bg-green-50 border-green-200',
            'rejected' => 'text-red-600 bg-red-50 border-red-200',
        ];

        return $statusColors[$this->status] ?? $statusColors['pending'];
    }

    public function canPublishProfile(): bool
    {
        if (!$this->hasProfile) {
            return false;
        }

        return $this->filledForPublication('display_name')
            && $this->filledForPublication('age')
            && $this->filledForPublication('country_code');
    }

    public function shouldShowPublishRequirement(string $field): bool
    {
        return $this->hasProfile
            && in_array($field, $this->publishRequiredFields, true)
            && !$this->filledForPublication($field)
            && !$this->canPublishProfile();
    }

    protected function filledForPublication(string $field): bool
    {
        $value = $this->{$field};

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return !empty($value);
    }

    protected function syncPublicationState(): void
    {
        if (!$this->canPublishProfile()) {
            $this->is_public = false;
        }
    }

    public function updatedDisplayName(): void
    {
        $this->syncPublicationState();
    }

    public function updatedAge(): void
    {
        $this->syncPublicationState();
    }

    public function updatedCountryCode(): void
    {
        $this->syncPublicationState();
    }

    public function updatedIsPublic($value): void
    {
        if ($value) {
            $this->syncPublicationState();
        }
    }



    public function loadCountries()
    {
        $codes = include base_path('lang/en/codes.php');
        $this->countries = collect($codes)->map(function ($name, $code) {
            return [
                'code' => strtolower($code),
                'name' => $name,
            ];
        })->sortBy('name')->values()->toArray();
    }

    public function toggleCountryDropdown()
    {
        $this->countryDropdownOpen = !$this->countryDropdownOpen;
        $this->countrySearchTerm = '';
    }

    public function selectCountry($countryCode)
    {
        $this->country_code = $countryCode;
        $this->countryDropdownOpen = false;
        $this->countrySearchTerm = '';
        
        // Clear city when country changes (city must match country)
        $this->city = '';
        $this->citySearchTerm = '';
        $this->citySuggestions = [];
    }

    /**
     * Search cities based on country and search term
     */
    public function updatedCitySearchTerm($value)
    {
        // Clear city when search term changes (user must select from suggestions)
        if ($this->city !== $value) {
            $this->city = '';
        }
        
        if (empty($this->country_code) || strlen($value) < 2) {
            $this->citySuggestions = [];
            return;
        }
        
        $this->citySuggestions = \App\Models\City::autocomplete(
            $this->country_code,
            $value,
            10
        )->toArray();
        
        // Open dropdown when there are suggestions
        $this->cityDropdownOpen = !empty($this->citySuggestions) || strlen($value) >= 2;
    }

    /**
     * Select a city from suggestions
     */
    public function selectCity($cityName)
    {
        $this->city = $cityName;
        $this->citySearchTerm = $cityName;
        $this->citySuggestions = [];
        $this->cityDropdownOpen = false;
    }

    /**
     * Toggle city dropdown
     */
    public function toggleCityDropdown()
    {
        if (empty($this->country_code)) {
            return; // Don't open if no country selected
        }
        $this->cityDropdownOpen = !$this->cityDropdownOpen;
    }

    public function getFilteredCountriesProperty()
    {
        if (empty($this->countrySearchTerm)) {
            return $this->countries;
        }

        return array_filter($this->countries, function ($country) {
            return stripos($country['name'], $this->countrySearchTerm) !== false;
        });
    }

    public function addLocalPrice()
    {
        $this->local_prices[] = [
            'time_hours' => '',
            'incall_price' => '',
            'outcall_price' => ''
        ];
    }

    public function removeLocalPrice($index)
    {
        unset($this->local_prices[$index]);
        $this->local_prices = array_values($this->local_prices);
    }

    public function addGlobalPrice()
    {
        $this->global_prices[] = [
            'time_hours' => '',
            'incall_price' => '',
            'outcall_price' => ''
        ];
    }

    public function removeGlobalPrice($index)
    {
        unset($this->global_prices[$index]);
        $this->global_prices = array_values($this->global_prices);
    }

    public function addContact()
    {
        $this->contacts[] = [
            'type' => 'phone',
            'value' => ''
        ];
    }

    public function removeContact($index)
    {
        unset($this->contacts[$index]);
        $this->contacts = array_values($this->contacts);
    }

    /**
     * Build the unique display_name validation rule.
     * Checks across all translatable locales (en, cs) to prevent duplicates.
     */
    protected function uniqueDisplayNameRule(?int $excludeProfileId = null): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($excludeProfileId) {
            $query = Profile::query();
            if ($excludeProfileId) {
                $query->where('id', '!=', $excludeProfileId);
            }

            $exists = $query->where(function ($q) use ($value) {
                $q->where('display_name->en', $value)
                  ->orWhere('display_name->cs', $value);
            })->exists();

            if ($exists) {
                $fail(__('front.profiles.form.display_name_taken'));
            }
        };
    }

    /**
     * Validate that city exists in the database for the selected country.
     * Only validates if city is provided.
     */
    protected function validateCityRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (empty($value) || empty($this->country_code)) {
                return; // City is optional, skip validation if empty
            }

            $exists = \App\Models\City::forCountry($this->country_code)
                ->where('name', $value)
                ->exists();

            if (!$exists) {
                $fail(__('front.profiles.form.city_must_be_selected'));
            }
        };
    }

    /**
     * Create a new profile. Called from the profile creation form.
     * Validates required fields before allowing creation.
     */
    public function createProfile()
    {
        $user = \App\Models\User::find(Auth::id());

        // Guard: only users without a profile can create one
        if ($user->profile) {
            return;
        }

        // Validate required profile fields
        $this->validate([
            'display_name' => [
                'required',
                'string',
                'max:255',
                $this->uniqueDisplayNameRule(),
            ],
            'age' => 'required|integer|min:18|max:120',
            'country_code' => 'required|string|max:2',
            'city' => [
                'nullable',
                'string',
                'max:255',
                $this->validateCityRule(),
            ],
            'address' => 'nullable|string|max:255',
            'about' => 'nullable|string|max:1200',
        ]);

        $profile = new Profile([
            'display_name' => $this->display_name,
            'age' => $this->age,
            'city' => $this->city ?: null,
            'country_code' => strtolower($this->country_code),
            'address' => $this->address ?: null,
            'about' => $this->about ?: null,
            'incall' => false,
            'outcall' => false,
            'is_porn_actress' => false,
            'is_public' => false,
            'status' => 'pending',
        ]);
        $profile->user_id = $user->id;
        $profile->save();

        // Update the hasProfile property
        $this->hasProfile = true;

        // Notify admins about new profile submission
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::createForUser(
                $admin->id,
                __('notifications.admin.new_profile_title'),
                __('notifications.admin.new_profile_message', ['name' => $profile->display_name]),
                'info'
            );
        }

        session()->flash('message', __('front.profiles.form.profile_created'));
        $this->js('window.scrollTo({top: 0, behavior: "smooth"})');
    }

    public function save()
    {
        $user = Auth::user();
        $user = \App\Models\User::find($user->id);
        $isAdmin = $this->isAdmin();
        $this->syncPublicationState();
        
        // Build validation rules for user data
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
        ];

        // If user has a profile, validate profile fields as required
        if ($this->hasProfile) {
            $validationRules['display_name'] = [
                'required',
                'string',
                'max:255',
                $this->uniqueDisplayNameRule($user->profile?->id),
            ];
            $validationRules['age'] = 'required|integer|min:18|max:120';
            $validationRules['country_code'] = 'required|string|max:2';
            $validationRules['city'] = [
                'nullable',
                'string',
                'max:255',
                $this->validateCityRule(),
            ];
            $validationRules['address'] = 'nullable|string|max:255';
            $validationRules['about'] = 'nullable|string|max:1200';
            $validationRules['availability_hours'] = 'nullable|string';
            $validationRules['local_prices'] = 'nullable|array';
            $validationRules['local_prices.*.time_hours'] = 'required|string|max:100';
            $validationRules['local_prices.*.incall_price'] = 'required|numeric|min:0';
            $validationRules['local_prices.*.outcall_price'] = 'nullable|numeric|min:0';
            $validationRules['global_prices'] = 'nullable|array';
            $validationRules['global_prices.*.time_hours'] = 'required|string|max:100';
            $validationRules['global_prices.*.incall_price'] = 'required|numeric|min:0';
            $validationRules['global_prices.*.outcall_price'] = 'nullable|numeric|min:0';
            $validationRules['contacts'] = 'nullable|array';
            $validationRules['contacts.*.type'] = 'required|in:phone,whatsapp,telegram';
            $validationRules['contacts.*.value'] = 'required|string|max:255';
        }

        // Add status validation only for admin users
        if ($isAdmin && $this->hasProfile) {
            $validationRules['status'] = 'nullable|in:pending,approved,rejected';
        }

        // Add password validation rules if user is changing password
        if (!empty($this->current_password) || !empty($this->new_password) || !empty($this->new_password_confirmation)) {
            $validationRules['current_password'] = 'required|current_password';
            $validationRules['new_password'] = 'required|string|min:8|confirmed';
        }

        // Add email change validation rules if user is changing email
        if (!empty($this->new_email)) {
            $validationRules['new_email'] = 'required|email|max:255|unique:users,email,' . $user->id;
            $validationRules['email_change_password'] = 'required|current_password';
        }

        $this->validate($validationRules);

        // Track email change before update
        $emailChanged = !empty($this->new_email) && $user->email !== $this->new_email;

        // Update user data
        $user->name = $this->name;
        if ($emailChanged) {
            $user->email = $this->new_email;
        }
        $user->phone = $this->phone;

        // Mark email as unverified if changed and send verification notification
        if ($emailChanged) {
            $user->email_verified_at = null;
            // Clear email change fields
            $this->email = $this->new_email;
            $this->new_email = '';
            $this->email_change_password = '';
        }

        // Update password if provided
        if (!empty($this->new_password)) {
            $user->password = bcrypt($this->new_password);
            // Clear password fields after update
            $this->current_password = '';
            $this->new_password = '';
            $this->new_password_confirmation = '';
        }

        $user->save();

        // Send email verification notification after save if email changed
        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        // Update existing profile (only if user has one — creation is handled by createProfile())
        if ($user->profile) {
            $profileData = [
                'display_name' => $this->display_name,
                'age' => $this->age,
                'city' => $this->city ?: null,
                'country_code' => $this->country_code ? strtolower($this->country_code) : null,
                'address' => $this->address ?: null,
                'about' => $this->about ?: null,
                'incall' => $this->incall,
                'outcall' => $this->outcall,
                'is_porn_actress' => $this->is_porn_actress,
                'availability_hours' => $this->availability_hours ? explode(', ', $this->availability_hours) : null,
                'local_prices' => $this->local_prices ?: null,
                'global_prices' => $this->global_prices ?: null,
                'contacts' => $this->contacts ?: null,
                'is_public' => $this->is_public,
            ];

            if ($isAdmin) {
                $profileData['status'] = $this->status ?: 'pending';
            }

            $profile = $user->profile;
            foreach ($profileData as $key => $value) {
                $profile->$key = $value;
            }
            $profile->save();
        }

        session()->flash('message', __('front.profiles.form.saved'));
        
        // Scroll to top after successful save
        $this->js('window.scrollTo({top: 0, behavior: "smooth"})');
    }

    public function render()
    {
        return view('livewire.profile-form');
    }

    // Debug method to check profile relationship
    public function checkProfile()
    {
        $user = \App\Models\User::with('profile')->find(Auth::id());
        dd([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'profile_exists' => !is_null($user->profile),
            'profile_data' => $user->profile,
            'has_profile_property' => $this->hasProfile
        ]);
    }
}
