<?php

namespace App\Livewire;

use App\Models\Notification;
use App\Models\Profile;
use App\Support\RatingScale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire component for member ratings page.
 * Allows male users to quickly rate female profiles with a split view interface.
 */
class MemberRatings extends Component
{
    use WithPagination;

    /** How many profiles the rating-history panel loads at a time. */
    public const PROFILES_PER_PAGE = 15;

    /** Administrative names used by the existing city catalogue. */
    private const REGION_ADMIN_NAMES = [
        'hlavni-mesto-praha' => 'Praha',
        'stredocesky' => 'Středočeský Kraj',
        'jihocesky' => 'Jihočeský Kraj',
        'plzensky' => 'Plzeňský Kraj',
        'karlovarsky' => 'Karlovarský Kraj',
        'ustecky' => 'Ústecký Kraj',
        'liberecky' => 'Liberecký Kraj',
        'kralovehradecky' => 'Královéhradecký Kraj',
        'pardubicky' => 'Pardubický Kraj',
        'vysocina' => 'Vysočina',
        'jihomoravsky' => 'Jihomoravský Kraj',
        'olomoucky' => 'Olomoucký Kraj',
        'zlinsky' => 'Zlínský Kraj',
        'moravskoslezsky' => 'Moravskoslezský Kraj',
    ];

    #[Url(as: 'kraj', except: '')]
    public string $region = '';

    public ?Profile $selectedProfile = null;
    public int $selectedProfileId = 0;
    public bool $isFavorited = false;
    /** User-facing message shown when a rating attempt is refused. */
    public string $ratingError = '';

    protected $listeners = ['profileSelected' => 'selectProfile'];

    public function mount()
    {
        $this->normalizeRegion();
        // Arriving from a profile's "Dát hodnocení" link — open that profile
        // straight away instead of a random one.
        $requested = (int) request('profile', 0);

        if ($requested > 0) {
            $target = $this->profilesQuery()->find($requested);

            if ($target) {
                $this->selectProfile($target->id);

                return;
            }
        }

        // Land on a random profile rather than always the same
        // alphabetically-first one.
        $this->selectInitialProfile();
    }

    public function updatedRegion(): void
    {
        $this->normalizeRegion();
        $this->resetPage();
        $this->ratingError = '';
        $this->selectInitialProfile();
    }

    private function normalizeRegion(): void
    {
        if (! array_key_exists($this->region, self::REGION_ADMIN_NAMES)) {
            $this->region = '';
        }
    }

    private function selectInitialProfile(): void
    {
        $userId = Auth::id();
        $firstProfile = $this->profilesQuery()
            ->whereDoesntHave('ratings', fn($q) => $q->where('user_id', $userId))
            ->inRandomOrder()
            ->first()
            ?? $this->profilesQuery()->inRandomOrder()->first();

        if ($firstProfile) {
            $this->selectProfile($firstProfile->id);
        } else {
            $this->selectedProfile = null;
            $this->selectedProfileId = 0;
            $this->isFavorited = false;
        }
    }

    private function profilesQuery(): Builder
    {
        $query = Profile::approved()->public()
            ->whereHas('user', fn ($q) => $q->where('gender', 'female'));

        if (! isset(self::REGION_ADMIN_NAMES[$this->region])) {
            return $query;
        }

        $regionName = self::REGION_ADMIN_NAMES[$this->region];
        $regionLabel = \App\Http\Controllers\Auth\MemberController::CZECH_REGIONS[$this->region];

        return $query->where('profiles.country_code', 'CZ')
            ->where(function ($query) use ($regionName, $regionLabel) {
                $query->whereExists(function ($cities) use ($regionName, $regionLabel) {
                    $cities->select(DB::raw(1))->from('cities')
                        ->whereColumn('cities.country_code', 'profiles.country_code')
                        ->where(function ($names) {
                            $names->whereRaw('LOWER(cities.name) = LOWER(profiles.city)')
                                ->orWhereRaw('LOWER(cities.name_ascii) = LOWER(profiles.city)');
                        })
                        ->whereIn('cities.admin_name', [$regionName, $regionLabel]);
                });

                // The catalogue calls the capital Prague; Czech profiles use Praha.
                if ($this->region === 'hlavni-mesto-praha') {
                    $query->orWhereIn('profiles.city', ['Praha', 'Prague']);
                }
            });
    }

    /**
     * Base query for profiles that can be rated: approved, public and owned by
     * a female user.
     *
     * The rating aggregates are pulled in with the query (withCount/withAvg plus
     * an eager-loaded rating row for the current user) so the history list can
     * render without issuing three extra queries per profile — previously 20
     * profiles cost 60 additional queries.
     */
    public function availableProfilesQuery()
    {
        $query = $this->profilesQuery()
            ->with([
                'media',
                'ratings' => fn ($q) => $q->where('user_id', Auth::id()),
            ]);

        if (Gate::allows('view-ratings')) {
            $others = fn ($q) => $q->where(fn ($q) => $q
                ->where('user_id', '!=', Auth::id())->orWhereNull('user_id'));
            $query->withCount(['ratings as others_count' => $others])
                ->withAvg(['ratings as others_avg_percentage' => $others], 'percentage');
        }

        return $query->orderBy('display_name');
    }

    /**
     * Paginated list of profiles shown in the rating-history panel.
     */
    public function getAvailableProfiles()
    {
        return $this->availableProfilesQuery()->paginate(self::PROFILES_PER_PAGE);
    }

    /**
     * Select a profile to display in the main view.
     */
    public function selectProfile(int $profileId)
    {
        $this->selectedProfile = $this->profilesQuery()
            ->with(['media', 'user'])
            ->find($profileId);

        if ($this->selectedProfile) {
            $this->selectedProfileId = $profileId;
            $this->updateFavoriteStatus();
        } else {
            $this->selectedProfileId = 0;
            $this->isFavorited = false;
        }
    }

    /**
     * Update the favorite status for the selected profile.
     */
    private function updateFavoriteStatus()
    {
        if (!$this->selectedProfile || !Auth::check()) {
            $this->isFavorited = false;
            return;
        }

        $this->isFavorited = Auth::user()->hasFavorited($this->selectedProfile);
    }

    /**
     * Toggle favorite status for the selected profile.
     */
    public function toggleFavorite()
    {
        if (!Auth::check() || !$this->selectedProfile) {
            return;
        }

        $user = Auth::user();

        // Only male users can favorite profiles
        if (!$user->isMale()) {
            return;
        }

        $this->isFavorited = $user->toggleFavorite($this->selectedProfile);

        if ($this->isFavorited && $this->selectedProfile->user_id) {
            Notification::createForUser(
                $this->selectedProfile->user_id,
                __('notifications.favorite.added_title'),
                __('notifications.favorite.added_message'),
                'info'
            );
        }
    }

    /**
     * Rate the selected profile with a percentage value.
     *
     * The percentage is stored as given. It used to be squeezed into 1-5 stars
     * first (100=>5, 70=>4, 30=>2), which both made star values 3 and 1
     * unreachable and inflated 70% to 80% wherever it was drawn back as a bar.
     */
    public function rateProfile(int $percentage)
    {
        if (!$this->selectedProfile) {
            return;
        }

        // Only the presets the UI actually offers are accepted; the value
        // arrives from the browser and must not be able to set an arbitrary
        // score.
        if (! RatingScale::isOffered($percentage)) {
            $this->ratingError = __('front.profiles.rating.invalid_rating');

            return;
        }

        try {
            // Shared implementation on the model — identical authorization to
            // the rating widget on the public profile detail page.
            $result = $this->selectedProfile->rateByPercentage(Auth::user(), $percentage);

            if ($result !== Profile::RATE_OK) {
                $this->ratingError = match ($result) {
                    Profile::RATE_NOT_LOGGED_IN => __('front.profiles.rating.login_required'),
                    Profile::RATE_NOT_MEMBER => __('front.profiles.rating.members_only'),
                    Profile::RATE_OWN_PROFILE => __('front.profiles.rating.own_profile'),
                    default => __('front.profiles.rating.invalid_rating'),
                };

                return;
            }

            $this->ratingError = '';
        } catch (\Throwable $e) {
            Log::error('Failed to rate profile from member ratings page', [
                'profile_id' => $this->selectedProfile->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);

            $this->ratingError = __('front.profiles.rating.error');

            return;
        }

        // Refresh the view to show the updated rating
        $this->updateFavoriteStatus();
    }

    /**
     * Skip the current profile and move to the next one.
     */
    public function skipProfile()
    {
        $this->moveToNextProfile();
    }

    /**
     * Move to the next unrated profile in the list.
     */
    private function moveToNextProfile()
    {
        if (!Auth::check()) {
            return;
        }

        $userId = Auth::id();
        $currentId = $this->selectedProfileId;

        // Pick a random profile the user hasn't rated yet, excluding the one
        // currently shown.
        $nextProfile = $this->profilesQuery()
            ->whereDoesntHave('ratings', fn($q) => $q->where('user_id', $userId))
            ->where('id', '!=', $currentId)
            ->with(['media'])
            ->inRandomOrder()
            ->first();

        if ($nextProfile) {
            $this->selectProfile($nextProfile->id);
        }
    }

    /**
     * Get the user's existing rating for the selected profile.
     */
    public function getUserRatingForSelected(): ?int
    {
        if (!Auth::check() || !$this->selectedProfile) {
            return null;
        }

        return $this->selectedProfile->getUserRating(Auth::id());
    }

    /**
     * The percentage the member gave the selected profile, which is what the
     * three preset buttons compare against to show the "your rating" marker.
     */
    public function getUserPercentageForSelected(): ?int
    {
        if (! Auth::check() || ! $this->selectedProfile) {
            return null;
        }

        return $this->selectedProfile->getUserPercentage(Auth::id());
    }

    public function render()
    {
        return view('livewire.member-ratings', [
            'profiles' => $this->getAvailableProfiles(),
            'userRating' => $this->getUserRatingForSelected(),
            'userPercentage' => $this->getUserPercentageForSelected(),
            'ratingOptions' => RatingScale::options(),
            'regions' => \App\Http\Controllers\Auth\MemberController::CZECH_REGIONS,
        ]);
    }
}
