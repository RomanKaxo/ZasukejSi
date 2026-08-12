<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\Report;

/**
 * Controller for male user (member) account features.
 * Male users have different features than female users who manage profiles.
 */
class MemberController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Show the member dashboard (user settings).
     */
    public function dashboard()
    {
        $user = Auth::user();
        $user->load(['roles', 'permissions']);
        
        return view('member.dashboard', compact('user'));
    }

    /**
     * Czech regions (kraje) offered in the ratings page filter.
     */
    public const CZECH_REGIONS = [
        'hlavni-mesto-praha' => 'Hlavní město Praha',
        'stredocesky' => 'Středočeský kraj',
        'jihocesky' => 'Jihočeský kraj',
        'plzensky' => 'Plzeňský kraj',
        'karlovarsky' => 'Karlovarský kraj',
        'ustecky' => 'Ústecký kraj',
        'liberecky' => 'Liberecký kraj',
        'kralovehradecky' => 'Královéhradecký kraj',
        'pardubicky' => 'Pardubický kraj',
        'vysocina' => 'Kraj Vysočina',
        'jihomoravsky' => 'Jihomoravský kraj',
        'olomoucky' => 'Olomoucký kraj',
        'zlinsky' => 'Zlínský kraj',
        'moravskoslezsky' => 'Moravskoslezský kraj',
    ];

    /**
     * Show the ratings page.
     */
    public function ratings()
    {
        $user = Auth::user();

        return view('member.ratings', [
            'user' => $user,
            'regions' => self::CZECH_REGIONS,
        ])->with('wideContent', true);
    }

    /**
     * Show the favorites page.
     */
    public function favorites()
    {
        $user = Auth::user();
        $favorites = $user->favoriteProfiles()
            ->approved()
            ->public()
            ->with(['media'])
            ->latest('profile_favorites.created_at')
            ->paginate(12);
        
        return view('member.favorites', compact('user', 'favorites'))->with('wideContent', true);
    }

    /**
     * Remove a profile from favorites.
     */
    public function removeFavorite(Request $request, \App\Models\Profile $profile)
    {
        $user = Auth::user();
        $user->favoriteProfiles()->detach($profile->id);

        // The favorites grid removes cards via fetch(), so answer AJAX callers
        // with JSON — returning a redirect made it impossible for the frontend
        // to tell success from failure.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('front.favorites.removed'),
            ]);
        }

        return redirect()->route('account.member.favorites')
            ->with('status', __('front.favorites.removed'));
    }

    /**
     * Show the girls of the month page (TOP 50 all-time ranking).
     */
    public function girlsOfMonth(Request $request)
    {
        $user = Auth::user();

        $ageRange = $request->string('age_range')->toString();
        $ageBounds = self::AGE_RANGES[$ageRange] ?? null;

        $topIds = \App\Models\Profile::approved()
            ->public()
            ->withAvg('ratings', 'rating')
            ->when($ageBounds, fn ($query) => $query->whereBetween('age', $ageBounds))
            ->orderByDesc('ratings_avg_rating')
            ->limit(50)
            ->pluck('id');

        $profiles = \App\Models\Profile::whereIn('id', $topIds)
            ->with(['media'])
            ->withAvg('ratings', 'rating')
            ->orderByDesc('ratings_avg_rating')
            ->paginate(16)
            ->withQueryString();

        return view('member.girls-of-month', [
            'user' => $user,
            'profiles' => $profiles,
            'ageRanges' => self::AGE_RANGES,
            'selectedAgeRange' => $ageRange,
        ])->with('wideContent', true);
    }

    /**
     * Age range filter options, shared by the girls archive and the
     * girls-of-the-month ranking.
     */
    public const AGE_RANGES = [
        '18-20' => [18, 20],
        '21-25' => [21, 25],
        '26-30' => [26, 30],
        '31-35' => [31, 35],
        '36-40' => [36, 40],
        '41-45' => [41, 45],
        '46-50' => [46, 50],
    ];

    /**
     * Show the girls archive page.
     */
    public function archive(Request $request)
    {
        $user = Auth::user();

        $ageRange = $request->string('age_range')->toString();
        $ageBounds = self::AGE_RANGES[$ageRange] ?? null;

        $profiles = \App\Models\Profile::archived()
            ->with(['media'])
            ->when($ageBounds, fn ($query) => $query->whereBetween('age', $ageBounds))
            ->latest()
            ->paginate(16)
            ->withQueryString();

        return view('member.archive', [
            'user' => $user,
            'profiles' => $profiles,
            'ageRanges' => self::AGE_RANGES,
            'selectedAgeRange' => $ageRange,
        ])->with('wideContent', true);
    }

    /**
     * Show the reported girls page.
     */
    public function reported()
    {
        $user = Auth::user();
        $reports = Report::with('profile.media', 'profile.activeSubscription')
            ->whereHas('profile')
            ->where('reporter_id', $user->id)
            ->latest()
            ->get();

        return view('member.reported', compact('user', 'reports'))->with('wideContent', true);
    }

    /**
     * Update user settings.
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,' . $user->id],
        ];

        // Add email change validation if new_email is provided
        if ($request->filled('new_email')) {
            $rules['new_email'] = ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id];
            $rules['email_change_password'] = ['required', 'current_password'];
        }

        $request->validate($rules);

        $user->name = $request->name;
        // The `phone` column has a unique index; an empty string (unlike
        // NULL) counts as a real value there, so a second user with no
        // phone number would collide and the save would fail silently.
        $user->phone = $request->phone !== '' ? $request->phone : null;

        // Handle email change
        $emailChanged = false;
        if ($request->filled('new_email') && $user->email !== $request->new_email) {
            $user->email = $request->new_email;
            $user->email_verified_at = null;
            $emailChanged = true;
        }

        $user->save();

        // Send email verification notification after save
        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('account.member.dashboard')->with('status', 'settings-updated');
    }

    /**
     * Show the password change form.
     */
    public function showPasswordForm()
    {
        return view('member.password.edit');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('account.member.dashboard')->with('status', 'password-updated');
    }
}
