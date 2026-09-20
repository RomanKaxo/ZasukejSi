<?php

namespace App\Livewire;

use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
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

    #[Rule('nullable|string|max:640')]
    public $about = '';

    #[Rule('nullable|numeric|min:30|max:300')]
    public $weight_kg = '';

    #[Rule('nullable|integer|min:100|max:250')]
    public $height_cm = '';

    #[Rule('nullable|string|max:2')]
    public $nationality = '';

    #[Rule('nullable|string|max:2')]
    public $bust_size = '';

    /**
     * Comma-separated language codes, e.g. "cs,en,de".
     *
     * Profile::getLanguagesAttribute() reads this back out of the `content`
     * JSON and the detail page renders it, but the property was missing here —
     * so a provider had no way to set her languages at all and the detail page
     * always fell back to a canned default.
     */
    #[Rule('nullable|string|max:255')]
    public $languages = '';

    #[Rule('nullable|string|max:10')]
    public $local_currency = 'Kč';

    #[Rule('nullable|string|max:10')]
    public $global_currency = 'EUR';

    #[Rule('boolean')]
    public $has_whatsapp = false;

    #[Rule('boolean')]
    public $has_telegram = false;

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

    // Fixed 30-minute / 60-minute price slots, shown as dedicated fields
    // above the free-form custom-duration rows in $local_prices/$global_prices.
    public $local_price_30_incall = '';
    public $local_price_30_outcall = '';
    public $local_price_60_incall = '';
    public $local_price_60_outcall = '';

    public $global_price_30_incall = '';
    public $global_price_30_outcall = '';
    public $global_price_60_incall = '';
    public $global_price_60_outcall = '';

    public $contacts = [];

    #[Rule('nullable|in:pending,approved,rejected')]
    public $status = '';

    public $is_public = false;

    /**
     * Bust sizes offered in the form.
     *
     * Was a hardcoded `['A'..'H']`, so nobody could add a size and the scraper
     * had nothing to check against; it now comes from the same list the admin
     * edits under Vlastnosti profilů.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function bustSizeOptions(): array
    {
        return array_keys(\App\Models\ProfileAttributeOption::optionsFor('bust_size'));
    }
    public $currencyOptions = ['Kč'];
    public $globalCurrencyOptions = ['EUR'];

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
            $this->country_code = $profile->country_code ?? '';
            $this->address = $profile->address ?? '';
            $this->about = $profile->about ?? '';
            $content = is_array($profile->content) ? $profile->content : [];
            $this->weight_kg = $content['weight_kg'] ?? '';
            $this->height_cm = $content['card_height_cm'] ?? '';
            $this->nationality = $content['nationality'] ?? '';
            $this->bust_size = $content['bust_size'] ?? '';
            $this->languages = $content['languages'] ?? '';
            $this->local_currency = $content['local_currency'] ?? 'Kč';
            $this->global_currency = $content['global_currency'] ?? 'EUR';
            $this->has_whatsapp = $content['has_whatsapp'] ?? false;
            $this->has_telegram = $content['has_telegram'] ?? false;
            $this->incall = $profile->incall ?? false;
            $this->outcall = $profile->outcall ?? false;
            $this->is_porn_actress = $profile->is_porn_actress ?? false;
            // imploding the stored array gave "Array" on the canonical shape,
            // which nests a schedule under a key. Availability::toText knows
            // every shape this column has been written in.
            $this->availability_hours = \App\Support\Availability::toText($profile->availability_hours);
            [
                $this->local_price_30_incall,
                $this->local_price_30_outcall,
                $this->local_price_60_incall,
                $this->local_price_60_outcall,
                $this->local_prices,
            ] = $this->splitFixedPrices($this->normalizePriceRows($profile->local_prices));

            [
                $this->global_price_30_incall,
                $this->global_price_30_outcall,
                $this->global_price_60_incall,
                $this->global_price_60_outcall,
                $this->global_prices,
            ] = $this->splitFixedPrices($this->normalizePriceRows($profile->global_prices));
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
        // Same countries as shown in the homepage "browse by country" sidebar
        $allowedCodes = ['al', 'ad', 'am', 'be', 'by', 'ba', 'bg', 'me', 'cz'];

        $codes = include base_path('lang/en/codes.php');
        $this->countries = collect($codes)
            ->filter(fn ($name, $code) => in_array(strtolower($code), $allowedCodes, true))
            ->map(function ($name, $code) {
                return [
                    'code' => strtolower($code),
                    'name' => $name,
                ];
            })->sortBy('name')->values()->toArray();
    }

    /**
     * All cities for the selectable countries, grouped by country code.
     *
     * Sent to the browser once so the country -> city dependency is resolved
     * entirely client-side; picking a country or city triggers no Livewire
     * round-trip at all (values are submitted with the form).
     */
    protected function citiesByCountry(): array
    {
        $codes = collect($this->countries)->pluck('code')->all();

        if (empty($codes)) {
            return [];
        }

        return \Illuminate\Support\Facades\Cache::remember(
            'profile-form-cities-' . md5(implode(',', $codes)),
            now()->addDay(),
            function () use ($codes) {
                return \App\Models\City::whereIn('country_code', array_map('strtoupper', $codes))
                    ->orderByDesc('population')
                    ->get(['name', 'country_code'])
                    ->groupBy(fn ($city) => strtolower($city->country_code))
                    ->map(fn ($group) => $group->pluck('name')->unique()->values()->all())
                    ->all();
            }
        );
    }

    /**
     * Coerce stored price data into the list-of-rows shape the form edits.
     *
     * Legacy profiles stored a slot => price map ('30min' => 2500) instead of
     * rows. Loading that straight into the form produced validation errors on
     * `local_prices.*.time_hours` for fields the user could not see or correct,
     * making the whole profile impossible to save. A data migration converts the
     * existing rows; this guard stops any stragglers from locking the form again.
     */
    protected function normalizePriceRows($value): array
    {
        if (! is_array($value) || $value === []) {
            return [];
        }

        if (array_is_list($value)) {
            return $value;
        }

        $slotHours = [
            '30min' => 0.5,
            '1hour' => 1,
            '2hours' => 2,
            '3hours' => 3,
            'overnight' => 12,
        ];

        $rows = [];

        foreach ($value as $slot => $price) {
            if (is_array($price)) {
                $rows[] = $price;
                continue;
            }

            if (! is_numeric($price) || ! isset($slotHours[$slot])) {
                continue;
            }

            $rows[] = [
                'time_hours' => $slotHours[$slot],
                'incall_price' => (int) $price,
                'outcall_price' => null,
            ];
        }

        usort($rows, fn ($a, $b) => ($a['time_hours'] ?? 0) <=> ($b['time_hours'] ?? 0));

        return array_values($rows);
    }

    /**
     * Pull the fixed 30-minute (0.5h) and 60-minute (1h) rows out of a price
     * row list so they can be edited as dedicated fields, leaving only the
     * custom-duration rows for the "Add another" list.
     */
    protected function splitFixedPrices(array $rows): array
    {
        $thirty = ['incall' => '', 'outcall' => ''];
        $sixty = ['incall' => '', 'outcall' => ''];
        $rest = [];

        foreach ($rows as $row) {
            $hours = is_numeric($row['time_hours'] ?? null) ? (float) $row['time_hours'] : null;

            if ($hours === 0.5) {
                $thirty = [
                    'incall' => $row['incall_price'] ?? '',
                    'outcall' => $row['outcall_price'] ?? '',
                ];
                continue;
            }

            if ($hours === 1.0) {
                $sixty = [
                    'incall' => $row['incall_price'] ?? '',
                    'outcall' => $row['outcall_price'] ?? '',
                ];
                continue;
            }

            $rest[] = $row;
        }

        return [$thirty['incall'], $thirty['outcall'], $sixty['incall'], $sixty['outcall'], $rest];
    }

    /**
     * Reassemble the fixed 30/60-minute fields and the custom-duration rows
     * back into the single row list the `prices` columns store.
     */
    protected function buildPriceRows($incall30, $outcall30, $incall60, $outcall60, array $customRows): array
    {
        $rows = [];

        if ($incall30 !== '' && $incall30 !== null) {
            $rows[] = [
                'time_hours' => 0.5,
                'incall_price' => (float) $incall30,
                'outcall_price' => ($outcall30 !== '' && $outcall30 !== null) ? (float) $outcall30 : null,
            ];
        }

        if ($incall60 !== '' && $incall60 !== null) {
            $rows[] = [
                'time_hours' => 1,
                'incall_price' => (float) $incall60,
                'outcall_price' => ($outcall60 !== '' && $outcall60 !== null) ? (float) $outcall60 : null,
            ];
        }

        return array_merge($rows, $customRows);
    }

    /**
     * Choices for a price row's time allocation: 30 min, 1 h, 1:30 h ... 24 h.
     *
     * A value saved earlier that is not on the half-hour grid (scraped or
     * hand-typed, e.g. 0.75) is kept as an extra option, otherwise the select
     * would silently show nothing for it and the row would look empty.
     *
     * @return array<string, string> option value => label
     */
    public function durationOptions(mixed $current = null): array
    {
        $format = function (float $hours): string {
            $minutes = (int) round($hours * 60);

            if ($minutes < 60) {
                return $minutes . ' min';
            }

            return $minutes % 60 === 0
                ? intdiv($minutes, 60) . ' h'
                : intdiv($minutes, 60) . ':' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) . ' h';
        };

        $options = [];
        for ($hours = 0.5; $hours <= 24; $hours += 0.5) {
            $options[(string) $hours] = $format($hours);
        }

        if (is_numeric($current) && (float) $current > 0 && ! isset($options[(string) (float) $current])) {
            $options[(string) (float) $current] = $format((float) $current);
            uksort($options, fn ($a, $b) => (float) $a <=> (float) $b);
        }

        return $options;
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
            'about' => 'nullable|string|max:640',
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
            $validationRules['about'] = 'nullable|string|max:640';
            $validationRules['weight_kg'] = 'nullable|numeric|min:30|max:300';
            $validationRules['height_cm'] = 'nullable|integer|min:100|max:250';
            // An empty list would turn into a bare `in:` that rejects every
            // value, so an unpopulated catalogue only relaxes the rule.
            $validationRules['bust_size'] = $this->bustSizeOptions === []
                ? 'nullable|string|max:8'
                : 'nullable|string|in:' . implode(',', $this->bustSizeOptions);
            $validationRules['languages'] = 'nullable|string|max:255';
            $validationRules['availability_hours'] = 'nullable|string';
            $validationRules['local_price_30_incall'] = 'nullable|numeric|min:0';
            $validationRules['local_price_30_outcall'] = 'nullable|numeric|min:0';
            $validationRules['local_price_60_incall'] = 'nullable|numeric|min:0';
            $validationRules['local_price_60_outcall'] = 'nullable|numeric|min:0';
            $validationRules['local_prices'] = 'nullable|array';
            $validationRules['local_prices.*.time_hours'] = 'required|numeric|min:0.5|max:24|multiple_of:0.5';
            $validationRules['local_prices.*.incall_price'] = 'required|numeric|min:0';
            $validationRules['local_prices.*.outcall_price'] = 'nullable|numeric|min:0';
            $validationRules['global_price_30_incall'] = 'nullable|numeric|min:0';
            $validationRules['global_price_30_outcall'] = 'nullable|numeric|min:0';
            $validationRules['global_price_60_incall'] = 'nullable|numeric|min:0';
            $validationRules['global_price_60_outcall'] = 'nullable|numeric|min:0';
            $validationRules['global_prices'] = 'nullable|array';
            $validationRules['global_prices.*.time_hours'] = 'required|numeric|min:0.5|max:24|multiple_of:0.5';
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

        try {
            $this->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // A validation failure anywhere in this form (e.g. a price row
            // with an incall/outcall amount but no duration) used to fail
            // silently: nothing saved, no scroll, no banner — the error sat
            // as small red text next to whichever field caused it, easy to
            // miss below the fold. Scroll up so the failure is visible.
            $this->js('window.scrollTo({top: 0, behavior: "smooth"})');
            throw $e;
        }

        // Track email change before update
        $emailChanged = !empty($this->new_email) && $user->email !== $this->new_email;

        // Update user data
        $user->name = $this->name;
        if ($emailChanged) {
            $user->email = $this->new_email;
        }
        // The `phone` column has a unique index; an empty string (unlike
        // NULL) counts as a real value there, so a second user with no
        // phone number would collide and the save would fail silently.
        $user->phone = $this->phone !== '' ? $this->phone : null;

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
                // Profile::setCountryCodeAttribute() normalises this to
                // uppercase so it joins cleanly against cities.country_code.
                'country_code' => $this->country_code ?: null,
                'address' => $this->address ?: null,
                'about' => $this->about ?: null,
                'content' => array_merge(is_array($user->profile->content) ? $user->profile->content : [], [
                    'weight_kg' => $this->weight_kg ?: null,
                    'card_height_cm' => $this->height_cm ?: null,
                    'nationality' => $this->nationality ? strtolower($this->nationality) : null,
                    'bust_size' => $this->bust_size ?: null,
                    'languages' => $this->languages ?: null,
                    'local_currency' => $this->local_currency ?: 'Kč',
                    'global_currency' => $this->global_currency ?: 'EUR',
                    'has_whatsapp' => (bool) $this->has_whatsapp,
                    'has_telegram' => (bool) $this->has_telegram,
                ]),
                'incall' => $this->incall,
                'outcall' => $this->outcall,
                'is_porn_actress' => $this->is_porn_actress,
                // explode() stored a flat list, which is not a shape any reader
                // expects — every save from this form undid the canonical one.
                // fromText() parses the same text back into it and carries
                // `always_online` over, since a text field cannot express it.
                'availability_hours' => $this->availability_hours
                    ? \App\Support\Availability::fromText($this->availability_hours, $user->profile->availability_hours ?? null)
                    : null,
                'local_prices' => $this->buildPriceRows(
                    $this->local_price_30_incall,
                    $this->local_price_30_outcall,
                    $this->local_price_60_incall,
                    $this->local_price_60_outcall,
                    $this->local_prices
                ) ?: null,
                'global_prices' => $this->buildPriceRows(
                    $this->global_price_30_incall,
                    $this->global_price_30_outcall,
                    $this->global_price_60_incall,
                    $this->global_price_60_outcall,
                    $this->global_prices
                ) ?: null,
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
        return view('livewire.profile-form', [
            'citiesByCountry' => $this->citiesByCountry(),
        ]);
    }
}
