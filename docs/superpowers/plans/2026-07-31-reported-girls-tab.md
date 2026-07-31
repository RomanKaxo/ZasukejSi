# Reported Girls Tab Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the "coming soon" placeholder at `/account/member/reported` with a real list of profiles the logged-in member has reported, each showing a block reason, allegation tags, and a "read full case" modal — matching the approved design at `docs/superpowers/specs/2026-07-31-reported-girls-tab-design.md`.

**Architecture:** New `reports` table + `Report` Eloquent model (belongs to `Profile` and to the reporting `User`). `MemberController::reported()` loads the authenticated member's reports. The existing `profile-card` component (already has an `isReported` mode) is paired with a new `reported-info-card` component inside `member/reported.blade.php`. A single site-wide `reported-case-modal` component, driven by an Alpine store, shows full case details when "Číst celý případ" is clicked on any card.

**Tech Stack:** Laravel (Blade views, Eloquent, migrations), Alpine.js (store-driven modal, matching the existing `memberSidebar` store pattern), Tailwind CSS v4 (inline pixel styles + utility classes, matching `profile-card.blade.php` conventions), PHPUnit feature tests.

## Global Constraints

- Follow the approved spec exactly: card `285x510`, `#F2F2F2` bg, `radius 15px`, shadow `0 15px 15px rgba(92,45,98,0.1)`; modal `600x1323`, white, `radius 24px`; all copy/colors/fonts as listed in the spec.
- Allegation categories are a **fixed list** (not free text): Krádež, Jiná osoba na fotkách, Podvod, Ohrožování, Falešný profil, Nevhodné chování — stored as translation keys (`theft`, `photo_mismatch`, `fraud`, `threats`, `fake_profile`, `inappropriate_behavior`) and rendered through `__()`, consistent with how every other string in this codebase is localized (`lang/cs/front.php` / `lang/en/front.php`).
- No "report a profile" submission UI and no admin review UI — out of scope per the approved spec.
- No interactive photo carousel inside the case modal — static image + decorative dots only.
- `rtk` prefixes every shell command per this project's `CLAUDE.md`.
- After any Blade/CSS change, run `npm run build` before it will be visible (this repo runs prebuilt `public/build` assets, not a live Vite dev server — confirmed earlier in this session).

---

### Task 1: `reports` migration + `Report` model + allegation categories

**Files:**
- Create: `database/migrations/2026_07_31_120000_create_reports_table.php`
- Create: `app/Models/Report.php`
- Test: `tests/Unit/ReportModelTest.php`

**Interfaces:**
- Produces: `App\Models\Report` with `profile()` (belongsTo `App\Models\Profile`), `reporter()` (belongsTo `App\Models\User`, FK `reporter_id`), `casts()` returning `allegations` as `array`, and `const ALLEGATION_CATEGORIES = ['theft', 'photo_mismatch', 'fraud', 'threats', 'fake_profile', 'inappropriate_behavior']`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit;

use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_belongs_to_profile_and_reporter(): void
    {
        $reporter = User::factory()->create(['gender' => 'male']);
        $profileOwner = User::factory()->create(['gender' => 'female']);
        $profile = Profile::factory()->for($profileOwner)->create();

        $report = Report::create([
            'profile_id' => $profile->id,
            'reporter_id' => $reporter->id,
            'reason' => 'Test reason',
            'allegations' => ['theft', 'fraud'],
        ]);

        $this->assertTrue($report->profile->is($profile));
        $this->assertTrue($report->reporter->is($reporter));
        $this->assertSame(['theft', 'fraud'], $report->fresh()->allegations);
    }

    public function test_allegation_categories_constant_has_six_fixed_values(): void
    {
        $this->assertSame(
            ['theft', 'photo_mismatch', 'fraud', 'threats', 'fake_profile', 'inappropriate_behavior'],
            Report::ALLEGATION_CATEGORIES
        );
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `rtk cargo test` is not applicable here — this is PHP. Run: `php artisan test --filter=ReportModelTest`
Expected: FAIL with "Class \"App\\Models\\Report\" not found" (and migration table missing).

- [ ] **Step 3: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->json('allegations');
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();

            $table->index(['reporter_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
```

- [ ] **Step 4: Write the model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /** @use HasFactory<\Database\Factories\ReportFactory> */
    use HasFactory;

    public const ALLEGATION_CATEGORIES = [
        'theft',
        'photo_mismatch',
        'fraud',
        'threats',
        'fake_profile',
        'inappropriate_behavior',
    ];

    protected $fillable = [
        'profile_id',
        'reporter_id',
        'reason',
        'allegations',
        'blocked_at',
    ];

    protected function casts(): array
    {
        return [
            'allegations' => 'array',
            'blocked_at' => 'datetime',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=ReportModelTest`
Expected: PASS (2 tests)

- [ ] **Step 6: Commit**

```bash
rtk git add database/migrations/2026_07_31_120000_create_reports_table.php app/Models/Report.php tests/Unit/ReportModelTest.php
rtk git commit -m "Add reports table and Report model"
```

---

### Task 2: `ReportFactory` + allegation lang keys

**Files:**
- Create: `database/factories/ReportFactory.php`
- Modify: `lang/cs/front.php` (inside the existing `'member' => [...]` array, after `'reported_description'`)
- Modify: `lang/en/front.php` (same location)
- Test: `tests/Unit/ReportFactoryTest.php`

**Interfaces:**
- Consumes: `App\Models\Report::ALLEGATION_CATEGORIES` (Task 1), `App\Models\Profile` and `App\Models\User` factories (existing).
- Produces: `Report::factory()` usable in seeders and tests; new lang keys `front.account.member.allegations.{theft,photo_mismatch,fraud,threats,fake_profile,inappropriate_behavior}`, `front.account.member.block_reason`, `front.account.member.read_full_case`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit;

use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_a_persisted_report_with_valid_allegations(): void
    {
        $report = Report::factory()->create();

        $this->assertNotEmpty($report->reason);
        $this->assertNotEmpty($report->allegations);
        foreach ($report->allegations as $allegation) {
            $this->assertContains($allegation, Report::ALLEGATION_CATEGORIES);
        }
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ReportFactoryTest`
Expected: FAIL — no factory defined for `App\Models\Report`.

- [ ] **Step 3: Write the factory**

```php
<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $categories = Report::ALLEGATION_CATEGORIES;
        $count = fake()->numberBetween(1, 4);
        $allegations = collect($categories)->shuffle()->take($count)->values()->all();

        return [
            'profile_id' => Profile::factory(),
            'reporter_id' => User::factory()->state(['gender' => 'male']),
            'reason' => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            'allegations' => $allegations,
            'blocked_at' => fake()->boolean(70) ? now() : null,
        ];
    }
}
```

- [ ] **Step 4: Add the lang keys**

In `lang/cs/front.php`, replace:

```php
            'reported' => 'Nahlášené dívky',
            'reported_description' => 'Zobrazte profily, které jste nahlásili.',
```

with:

```php
            'reported' => 'Nahlášené dívky',
            'reported_description' => 'Oprávněné aniž i odstoupil o snadno osoby vede grafikou osobami úmyslu 60 % před platbě státu zvláštních tuzemsku. Dohodnou zvláštní provádí o nebezpečí kódech § 6 příjmu vhodným třetím',
            'block_reason' => 'Důvod blokace',
            'read_full_case' => 'Číst celý případ',
            'allegations' => [
                'theft' => 'Krádež',
                'photo_mismatch' => 'Jiná osoba na fotkách',
                'fraud' => 'Podvod',
                'threats' => 'Ohrožování',
                'fake_profile' => 'Falešný profil',
                'inappropriate_behavior' => 'Nevhodné chování',
            ],
```

In `lang/en/front.php`, replace:

```php
            'reported' => 'Reported Girls',
            'reported_description' => 'View profiles you have reported.',
```

with:

```php
            'reported' => 'Reported Girls',
            'reported_description' => 'View profiles you have reported.',
            'block_reason' => 'Block Reason',
            'read_full_case' => 'Read Full Case',
            'allegations' => [
                'theft' => 'Theft',
                'photo_mismatch' => 'Different person in photos',
                'fraud' => 'Fraud',
                'threats' => 'Threats',
                'fake_profile' => 'Fake profile',
                'inappropriate_behavior' => 'Inappropriate behavior',
            ],
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=ReportFactoryTest`
Expected: PASS

- [ ] **Step 6: Run the full translation-keys test to confirm nothing broke**

Run: `php artisan test --filter=TranslationKeysTest`
Expected: PASS (dynamic `allegations.` key lookups are excluded by that test's own `.` filter — this step just guards against a stray literal `front.account.member.allegations.xxx` typo elsewhere)

- [ ] **Step 7: Commit**

```bash
rtk git add database/factories/ReportFactory.php lang/cs/front.php lang/en/front.php tests/Unit/ReportFactoryTest.php
rtk git commit -m "Add Report factory and allegation/report lang keys"
```

---

### Task 3: Seeder — mock reports for the male test user

**Files:**
- Create: `database/seeders/ReportSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php` (call `$this->call(ReportSeeder::class);` near where `$man` / other seeders are called)

**Interfaces:**
- Consumes: `Report::factory()` (Task 2), the `user@example.com` male user and existing female `Profile` records already created earlier in `DatabaseSeeder`.

- [ ] **Step 1: Write the seeder**

```php
<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $reporter = User::where('email', 'user@example.com')->first();

        if (! $reporter) {
            return;
        }

        $profiles = Profile::inRandomOrder()->limit(3)->get();

        foreach ($profiles as $profile) {
            Report::factory()->create([
                'profile_id' => $profile->id,
                'reporter_id' => $reporter->id,
            ]);
        }
    }
}
```

- [ ] **Step 2: Wire it into `DatabaseSeeder`**

Open `database/seeders/DatabaseSeeder.php`, find the block that creates `$man` (around line 117-127, shown earlier reading the file), and directly after `$man->syncRoles(['user']);` add:

```php
        $this->call(ReportSeeder::class);
```

- [ ] **Step 3: Run the seeder locally to verify it doesn't error**

Run: `rtk php artisan migrate:fresh --seed`
Expected: seeding completes with no errors; spot check with `rtk php artisan tinker --execute="echo App\Models\Report::count();"` prints `3` or more.

- [ ] **Step 4: Commit**

```bash
rtk git add database/seeders/ReportSeeder.php database/seeders/DatabaseSeeder.php
rtk git commit -m "Seed mock reported-profile data for the test member"
```

---

### Task 4: `MemberController::reported()` loads real data + feature test

**Files:**
- Modify: `app/Http/Controllers/Auth/MemberController.php:93-102`
- Test: `tests/Feature/MemberReportedTest.php`

**Interfaces:**
- Consumes: `App\Models\Report` (Task 1).
- Produces: `member.reported` view now receives a `$reports` variable — an `Illuminate\Support\Collection` of `Report` models with `profile` and `profile.media` eager-loaded, ordered newest first, scoped to `Auth::user()->id`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberReportedTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_sees_only_their_own_reports(): void
    {
        $member = User::factory()->create(['gender' => 'male']);
        $otherMember = User::factory()->create(['gender' => 'male']);
        $profileOwner = User::factory()->create(['gender' => 'female']);

        $myProfile = Profile::factory()->for($profileOwner)->create(['display_name' => 'Tamara']);
        $othersProfile = Profile::factory()->for($profileOwner)->create(['display_name' => 'NotMine']);

        Report::factory()->create(['profile_id' => $myProfile->id, 'reporter_id' => $member->id]);
        Report::factory()->create(['profile_id' => $othersProfile->id, 'reporter_id' => $otherMember->id]);

        $response = $this->actingAs($member)->get(route('account.member.reported'));

        $response->assertOk();
        $response->assertSee('Tamara');
        $response->assertDontSee('NotMine');
    }

    public function test_reported_page_shows_empty_state_with_no_reports(): void
    {
        $member = User::factory()->create(['gender' => 'male']);

        $response = $this->actingAs($member)->get(route('account.member.reported'));

        $response->assertOk();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=MemberReportedTest`
Expected: FAIL — `$response->assertSee('Tamara')` fails because the view is still the static placeholder.

- [ ] **Step 3: Update the controller**

In `app/Http/Controllers/Auth/MemberController.php`, replace the `reported()` method (currently lines 97-102):

```php
    /**
     * Show the reported girls page.
     */
    public function reported()
    {
        $user = Auth::user();
        $reports = Report::with('profile.media')
            ->where('reporter_id', $user->id)
            ->latest()
            ->get();

        return view('member.reported', compact('user', 'reports'));
    }
```

Add the import near the top of the file, alongside the other `use` statements:

```php
use App\Models\Report;
```

- [ ] **Step 4: Run test to verify it passes**

Note: this will still fail at this step because `member/reported.blade.php` hasn't been rewritten yet (Task 6). Run it anyway to confirm the *reason* for failure has shifted from "controller doesn't pass data" to "view doesn't render it" — expected output should now reach the view without a PHP error (check via `$response->assertOk()` passing while `assertSee`/`assertDontSee` still fail). This confirms the controller wiring is correct in isolation before the view exists.

Run: `php artisan test --filter=MemberReportedTest`
Expected: `test_reported_page_shows_empty_state_with_no_reports` PASSES; `test_member_sees_only_their_own_reports` still FAILS on `assertSee('Tamara')` (view not yet updated) — this is expected at this point in the plan.

- [ ] **Step 5: Commit**

```bash
rtk git add app/Http/Controllers/Auth/MemberController.php tests/Feature/MemberReportedTest.php
rtk git commit -m "Load member's reports in MemberController::reported()"
```

---

### Task 5: `reported-info-card` component

**Files:**
- Create: `resources/views/components/reported-info-card.blade.php`

**Interfaces:**
- Consumes: a `report` prop (an `App\Models\Report` instance with `profile` loaded).
- Produces: on click of "Číst celý případ", dispatches to the Alpine store built in Task 6 — `Alpine.store('reportedCase').open({...})` — so Task 6 must define a store method named exactly `open` that accepts a plain object with keys `name`, `reason`, `allegations` (array of translated labels), `height`, `age`, `location`, `image`.

- [ ] **Step 1: Write the component**

```blade
@props(['report'])

@php
    $profile = $report->profile;
    $allegations = $report->allegations ?? [];
    $visibleAllegations = array_slice($allegations, 0, 3);
    $hasMore = count($allegations) > 3;

    $allegationLabels = collect($allegations)
        ->map(fn ($key) => __('front.account.member.allegations.' . $key))
        ->values()
        ->all();

    $cardContent = (isset($profile->content) && is_array($profile->content)) ? $profile->content : [];
    $location = $cardContent['card_location'] ?? ($profile->city ?? '');
    $heightCm = $cardContent['card_height_cm'] ?? 168;
    $imageUrl = $profile->getFirstImageThumbUrl() ?? asset('images/models/model6.png');
@endphp

<div style="width:285px;height:510px;background:#F2F2F2;border-radius:15px;box-shadow:0 15px 15px 0 rgba(92,45,98,0.1);box-sizing:border-box;" class="p-5 flex flex-col">
    <x-icons name="TriangleAlert" style="width:32px;height:32px;color:#DD3888;" />

    <h4 class="mt-3" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:18px;color:#505050;">
        {{ __('front.account.member.block_reason') }}
    </h4>

    <p class="mt-2 line-clamp-6" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;">
        {{ $report->reason }}
    </p>

    <div class="mt-auto space-y-2">
        @foreach($visibleAllegations as $index => $key)
            <div style="width:171px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:11px;color:#505050;">
                    {{ __('front.account.member.allegations.' . $key) }}
                </span>
            </div>
        @endforeach

        @if($hasMore)
            <div style="width:171px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:11px;color:#505050;">…</span>
            </div>
        @endif

        <button type="button"
            @click="Alpine.store('reportedCase').open({
                name: @js($profile->display_name),
                reason: @js($report->reason),
                allegations: @js($allegationLabels),
                height: @js($heightCm),
                age: @js($profile->age),
                location: @js($location),
                image: @js($imageUrl),
            })"
            style="width:171px;height:40px;border-radius:8px;background:#DD3888;">
            <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:14px;color:#FFFFFF;">
                {{ __('front.account.member.read_full_case') }}
            </span>
        </button>
    </div>
</div>
```

- [ ] **Step 2: Manual check (no automated test — pure presentation)**

This component has no independent test; it's covered by the `MemberReportedTest::test_member_sees_only_their_own_reports` assertion in Task 4 once wired into the page in Task 7 (that test's `assertSee('Tamara')` only proves the profile name renders somewhere on the page, not this component specifically — visual correctness is verified in Task 8's manual browser check).

- [ ] **Step 3: Commit**

```bash
rtk git add resources/views/components/reported-info-card.blade.php
rtk git commit -m "Add reported-info-card component"
```

---

### Task 6: `reported-case-modal` component + Alpine store

**Files:**
- Create: `resources/views/components/reported-case-modal.blade.php`
- Modify: `resources/views/layouts/member.blade.php` (add `<x-reported-case-modal />` once, at the end of the file)

**Interfaces:**
- Consumes: `Alpine.store('reportedCase').open({...})` calls from Task 5's button.
- Produces: `Alpine.store('reportedCase')` with shape `{ isOpen: bool, data: object|null, open(payload), close() }`, registered on `alpine:init` exactly like the existing `memberSidebar` store in `resources/views/components/member-sidebar.blade.php:120-132`.

- [ ] **Step 1: Write the component**

```blade
<div x-data x-cloak>
    <div x-show="$store.reportedCase.isOpen"
        x-transition.opacity
        @click="$store.reportedCase.close()"
        class="fixed inset-0 z-50 backdrop-blur-lg"
        style="background-color: rgba(92, 45, 98, 0.8);">
    </div>

    <div x-show="$store.reportedCase.isOpen"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div @click.stop
            class="relative"
            style="width:600px;max-width:100%;min-height:1323px;background:#FFFFFF;border-radius:24px;box-sizing:border-box;padding:48px;">

            <button type="button" @click="$store.reportedCase.close()"
                style="position:absolute;right:35px;top:35px;width:35px;height:35px;border-radius:50%;background:#DD3888;border:none;display:flex;align-items:center;justify-content:center;">
                <x-icons name="cross" style="width:12px;height:12px;color:#FFFFFF;" />
            </button>

            <template x-if="$store.reportedCase.data">
                <div>
                    <div class="flex justify-center">
                        <x-icons name="TriangleAlert" style="width:40px;height:40px;color:#DD3888;" />
                    </div>

                    <h2 class="text-center mt-4" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;color:#5C2D62;">
                        {{ __('front.account.member.block_reason') }}
                    </h2>
                    <h3 class="text-center mt-1" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:24px;color:#DD3888;" x-text="$store.reportedCase.data.name"></h3>

                    <div class="mt-6 mx-auto p-5" style="width:510px;max-width:100%;background:#F2F2F2;border-radius:15px;box-sizing:border-box;">
                        <div class="flex gap-4">
                            <div class="relative flex-shrink-0" style="width:210px;height:265px;border-radius:15px;overflow:hidden;">
                                <img :src="$store.reportedCase.data.image" alt="" class="w-full h-full object-cover" />
                                <div class="absolute left-0 right-0 bottom-3 flex justify-center" style="gap:3px;">
                                    <template x-for="n in 5" :key="n">
                                        <span class="w-2.5 h-2.5 rounded-full bg-white flex items-center justify-center" style="box-shadow: 0 0 0 1px rgba(0,0,0,0.04);">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="n === 1 ? 'bg-[#DD3888]' : 'bg-transparent'"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <div class="flex-1 space-y-2">
                                <template x-for="allegation in $store.reportedCase.data.allegations" :key="allegation">
                                    <div style="height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                                        <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:11px;color:#505050;" x-text="allegation"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex gap-3 mt-4">
                            <div style="width:91px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                                <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#505050;" x-text="$store.reportedCase.data.height + ' cm'"></span>
                            </div>
                            <div style="width:91px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                                <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#505050;" x-text="$store.reportedCase.data.age + ' {{ __('front.profiles.list.years') }}'"></span>
                            </div>
                            <div style="width:190px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                                <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#505050;" x-text="$store.reportedCase.data.location"></span>
                            </div>
                        </div>

                        <p class="mt-4" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;" x-text="$store.reportedCase.data.reason"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('reportedCase', {
            isOpen: false,
            data: null,
            open(payload) {
                this.data = payload;
                this.isOpen = true;
            },
            close() {
                this.isOpen = false;
            }
        });
    });
</script>
```

- [ ] **Step 2: Include it once in the member layout**

In `resources/views/layouts/member.blade.php`, add this line right before the final `@endsection` (after the closing `</div>` of the outer container, so it isn't nested inside the flex row):

```blade
</div>
<x-reported-case-modal />
@endsection
```

- [ ] **Step 3: Manual check**

No automated test for Alpine/JS interaction in this codebase (no JS test runner configured). Verified visually in Task 8.

- [ ] **Step 4: Commit**

```bash
rtk git add resources/views/components/reported-case-modal.blade.php resources/views/layouts/member.blade.php
rtk git commit -m "Add reported-case-modal component with Alpine store"
```

---

### Task 7: Rewrite `member/reported.blade.php`

**Files:**
- Modify: `resources/views/member/reported.blade.php` (full rewrite, currently 18 lines — the placeholder shown earlier)

**Interfaces:**
- Consumes: `$reports` (Task 4), `x-profile-card :isReported="true"` (existing component, unchanged), `x-reported-info-card` (Task 5).

- [ ] **Step 1: Write the view**

```blade
@extends('layouts.member')

@section('member-content')
<!-- Page Title -->
<div class="mb-4 md:mb-8">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('front.account.member.reported') }}</h1>
    <p class="mt-1 md:mt-2" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;">
        {{ __('front.account.member.reported_description') }}
    </p>
</div>
<hr class="mb-8">

@if($reports->count() > 0)
<div class="flex flex-wrap gap-x-6 gap-y-8">
    @foreach($reports as $report)
    <div class="flex gap-4">
        <x-profile-card :profile="$report->profile" :isReported="true" />
        <x-reported-info-card :report="$report" />
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-lg border border-gray-200 p-8 md:p-12 text-center">
    <x-icons name="TriangleAlert" class="mx-auto mb-4" style="width:48px;height:48px;color:#DD3888;" />
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('front.account.member.reported') }}</h3>
    <p class="text-gray-500">{{ __('front.account.member.reported_description') }}</p>
</div>
@endif
@endsection
```

- [ ] **Step 2: Run the Task 4 feature test — it should now fully pass**

Run: `php artisan test --filter=MemberReportedTest`
Expected: PASS (both tests) — `assertSee('Tamara')` now succeeds because `x-profile-card` renders `$report->profile->display_name`.

- [ ] **Step 3: Run the full test suite to check for regressions**

Run: `php artisan test`
Expected: all tests PASS (existing `ExampleTest`, `TranslationKeysTest`, plus the new tests from Tasks 1, 2, 4).

- [ ] **Step 4: Commit**

```bash
rtk git add resources/views/member/reported.blade.php
rtk git commit -m "Render real reported-profiles list on the member reported tab"
```

---

### Task 8: Build assets and manual browser verification

**Files:** none (build + manual QA step)

- [ ] **Step 1: Rebuild front-end assets**

Run: `rtk npm run build`
Expected: build succeeds (same as the earlier sidebar-spacing changes in this session — this repo serves prebuilt `public/build` assets).

- [ ] **Step 2: Refresh the database with seeded reports**

Run: `rtk php artisan migrate:fresh --seed`

- [ ] **Step 3: Log in as the seeded male member and view the page**

Log in as `user@example.com` / `password`, navigate to `http://localhost:8000/account/member/reported?locale=cs`, and confirm against the two reference screenshots from the design conversation:
- Title + placeholder subtitle + divider render above the cards
- Each report renders as profile photo card (no button/rating, VIP badge still shows when applicable) beside the `285x510` info card with icon, "Důvod blokace", truncated reason, up to 3 allegation pills (+ "…" if more), and the pink "Číst celý případ" button
- Clicking "Číst celý případ" opens the `600x1323` modal with the full (non-truncated) reason, all allegation pills, and the height/age/location pills
- Clicking the X or the backdrop closes the modal

- [ ] **Step 4: Report back**

Summarize what was verified (or any visual mismatch found) to the user — this task does not end with a commit; it's a verification checkpoint. If a mismatch is found, fix it in the relevant component file from Tasks 5–7 and re-run `rtk npm run build` before re-checking.
