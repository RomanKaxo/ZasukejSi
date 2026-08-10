# Profile Segments Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a "segments" tagging system to `Profile` (manual admin-assigned labels + a derived "VIP" segment), manageable in Filament, and surfaced on the profile card, profile detail page, listing filter, and admin table — without changing any existing behavior.

**Architecture:** New `Segment` model + `segments`/`profile_segment` tables, following the exact pattern already used by `Service`/`profile_service`. `Profile::allSegments()` merges the manual relation with a synthetic VIP entry derived from `Profile::isVip()` (VIP is never stored). A new `SegmentResource` (Filament v4) mirrors `ServiceResource`. Frontend changes are additive: new badge markup and a new filter control, no existing markup is removed or restructured.

**Tech Stack:** Laravel 12, FilamentPHP 4, spatie/laravel-translatable, Livewire 3, MySQL/MariaDB, PHPUnit (Feature tests), Pest is not used — this repo uses PHPUnit-style `Tests\TestCase` classes.

## Global Constraints

- DB is MySQL/MariaDB — no Postgres-only syntax in migrations.
- Segment "VIP" is never persisted to `profile_segment`; it is always derived from `Profile::isVip()` (which checks `activeSubscription`).
- No new frontend framework — Blade + Livewire + Alpine only, matching `resources/views` conventions already in the repo.
- Every new admin-facing string needs both a `cs` and `en` translation (this repo audits translations via `app/Console/Commands/AuditTranslations.php`).
- Do not modify the pixel-precise inline styles already in `profile-card.blade.php` / `profile-detail.blade.php`; only add new markup blocks in the indicated anchor points.
- All new Eloquent relations that get looped over in Blade must be eager-loaded at the query site (`ProfileList::profiles()`), never lazy-loaded per row.

---

### Task 1: `Segment` model, migrations, factory

**Files:**
- Create: `database/migrations/2026_08_10_000001_create_segments_table.php`
- Create: `database/migrations/2026_08_10_000002_create_profile_segment_table.php`
- Create: `app/Models/Segment.php`
- Create: `database/factories/SegmentFactory.php`
- Test: `tests/Feature/SegmentModelTest.php`

**Interfaces:**
- Produces: `Segment` model with `belongsToMany(Profile::class, 'profile_segment')->withTimestamps()` as `profiles()`, `scopeActive($query)`, `scopeOrdered($query)` (ordered by `sort_order` then `name`), fillable `['name', 'slug', 'color', 'icon', 'sort_order', 'is_active']`, translatable `['name']`, cast `is_active` => boolean.
- Produces: `Segment::factory()` with a valid `definition()` (usable by later tasks/tests).

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SegmentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_segment_can_be_attached_to_a_profile(): void
    {
        $segment = Segment::factory()->create(['name' => ['cs' => 'Nová', 'en' => 'New']]);
        $profile = Profile::factory()->create();

        $profile->segments()->attach($segment->id);

        $this->assertTrue($profile->fresh()->segments->contains('id', $segment->id));
        $this->assertTrue($segment->fresh()->profiles->contains('id', $profile->id));
    }

    public function test_scope_active_excludes_inactive_segments(): void
    {
        Segment::factory()->create(['is_active' => true, 'sort_order' => 1]);
        Segment::factory()->create(['is_active' => false, 'sort_order' => 2]);

        $this->assertCount(1, Segment::active()->get());
    }

    public function test_scope_ordered_sorts_by_sort_order(): void
    {
        Segment::factory()->create(['name' => ['cs' => 'B', 'en' => 'B'], 'sort_order' => 2]);
        Segment::factory()->create(['name' => ['cs' => 'A', 'en' => 'A'], 'sort_order' => 1]);

        $names = Segment::ordered()->pluck('sort_order')->all();

        $this->assertSame([1, 2], $names);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SegmentModelTest`
Expected: FAIL — class `App\Models\Segment` not found.

- [ ] **Step 3: Write the migrations**

`database/migrations/2026_08_10_000001_create_segments_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Translatable segment label
            $table->string('slug')->unique();
            $table->string('color')->default('#5C2D62');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('segments');
    }
};
```

`database/migrations/2026_08_10_000002_create_profile_segment_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_segment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('segment_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['profile_id', 'segment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_segment');
    }
};
```

- [ ] **Step 4: Write the `Segment` model**

`app/Models/Segment.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Segment extends Model
{
    use HasFactory, HasTranslations;

    /**
     * @var array<int, string>
     */
    public $translatable = ['name'];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Profiles this segment has been manually assigned to.
     */
    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'profile_segment')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}
```

- [ ] **Step 5: Write the factory**

`database/factories/SegmentFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Segment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Segment>
 */
class SegmentFactory extends Factory
{
    protected $model = Segment::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ['cs' => ucfirst($name), 'en' => ucfirst($name)],
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 100000),
            'color' => fake()->hexColor(),
            'icon' => null,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
```

- [ ] **Step 6: Run migrations and tests**

Run: `php artisan migrate` then `php artisan test --filter=SegmentModelTest`
Expected: PASS (3 tests).

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_08_10_000001_create_segments_table.php database/migrations/2026_08_10_000002_create_profile_segment_table.php app/Models/Segment.php database/factories/SegmentFactory.php tests/Feature/SegmentModelTest.php
git commit -m "feat: add Segment model, segments/profile_segment tables"
```

---

### Task 2: `Profile::segments()` relation and `allSegments()` helper

**Files:**
- Modify: `app/Models/Profile.php` (add relation + helper near the existing `services()` method, ~line 214-218)
- Test: `tests/Feature/ProfileSegmentsTest.php`

**Interfaces:**
- Consumes: `Segment` model from Task 1 (`app/Models/Segment.php`), `Profile::isVip()` (already exists at `app/Models/Profile.php:282`).
- Produces: `Profile::segments()` (belongsToMany), `Profile::allSegments(): \Illuminate\Support\Collection` returning objects/arrays each shaped `['id' => ?int, 'slug' => string, 'name' => string, 'color' => string, 'icon' => ?string, 'is_vip' => bool]`, VIP entry has `id === null`, `slug === 'vip'`, `is_vip === true`; no duplicates; excludes inactive segments.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileSegmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_segments_includes_manually_assigned_active_segments(): void
    {
        $profile = Profile::factory()->create();
        $active = Segment::factory()->create(['is_active' => true, 'slug' => 'nova']);
        $inactive = Segment::factory()->create(['is_active' => false, 'slug' => 'archiv']);
        $profile->segments()->attach([$active->id, $inactive->id]);

        $slugs = $profile->fresh()->allSegments()->pluck('slug');

        $this->assertTrue($slugs->contains('nova'));
        $this->assertFalse($slugs->contains('archiv'));
    }

    public function test_all_segments_includes_derived_vip_segment_when_active_subscription_exists(): void
    {
        $profile = Profile::factory()->create();
        $type = SubscriptionType::create([
            'name' => ['cs' => 'Elite', 'en' => 'Elite'],
            'slug' => 'elite-test',
            'price' => 100,
            'duration_days' => 30,
            'is_active' => true,
        ]);
        Subscription::create([
            'profile_id' => $profile->id,
            'subscription_type_id' => $type->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        $segments = $profile->fresh()->allSegments();

        $this->assertTrue($segments->contains('slug', 'vip'));
        $this->assertTrue($segments->firstWhere('slug', 'vip')['is_vip']);
    }

    public function test_all_segments_has_no_duplicates_and_no_vip_without_subscription(): void
    {
        $profile = Profile::factory()->create();
        $segment = Segment::factory()->create(['slug' => 'top-lokalita']);
        $profile->segments()->attach($segment->id);

        $segments = $profile->fresh()->allSegments();

        $this->assertCount(1, $segments);
        $this->assertFalse($segments->contains('slug', 'vip'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProfileSegmentsTest`
Expected: FAIL — `Call to undefined method App\Models\Profile::segments()`.

- [ ] **Step 3: Implement the relation and helper**

In `app/Models/Profile.php`, immediately after the existing `services()` method (after line 218, before the `ratings()` method), add:

```php
    /**
     * Manually assigned segments (admin-managed labels).
     */
    public function segments()
    {
        return $this->belongsToMany(Segment::class, 'profile_segment')
            ->withTimestamps();
    }

    /**
     * All segments this profile should display: manually assigned active
     * segments plus a synthetic "VIP" entry derived from the active
     * subscription. VIP is never stored in `profile_segment` — it mirrors
     * the existing isVip()/scopeVip() pattern that replaced the old
     * `is_vip` column.
     *
     * @return \Illuminate\Support\Collection<int, array{id: ?int, slug: string, name: string, color: string, icon: ?string, is_vip: bool}>
     */
    public function allSegments(): \Illuminate\Support\Collection
    {
        $manual = $this->segments
            ->where('is_active', true)
            ->map(fn (Segment $segment) => [
                'id' => $segment->id,
                'slug' => $segment->slug,
                'name' => $segment->name,
                'color' => $segment->color,
                'icon' => $segment->icon,
                'is_vip' => false,
            ])
            ->values();

        if ($this->isVip()) {
            $manual->push([
                'id' => null,
                'slug' => 'vip',
                'name' => 'VIP',
                'color' => '#FFB700',
                'icon' => 'star',
                'is_vip' => true,
            ]);
        }

        return $manual;
    }
```

Add `use App\Models\Segment;` is unnecessary since `Segment` is in the same `App\Models` namespace.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ProfileSegmentsTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Models/Profile.php tests/Feature/ProfileSegmentsTest.php
git commit -m "feat: add Profile::segments() relation and allSegments() helper"
```

---

### Task 3: Translations for segments

**Files:**
- Create: `lang/cs/segments.php`
- Create: `lang/en/segments.php`
- Modify: `lang/cs/filament.php` (navigation array, after the `'services' => 'Služby',` line)
- Modify: `lang/en/filament.php` (navigation array, after the `'services' => 'Services',` line)
- Modify: `lang/cs/common.php` (after `'Services' => 'Služby',`)
- Modify: `lang/en/common.php` (after `'Services' => 'Services',`)
- Modify: `lang/cs/profiles.php` (`filters` array)
- Modify: `lang/en/profiles.php` (`filters` array)
- Test: `tests/Feature/SegmentTranslationsTest.php`

**Interfaces:**
- Produces: `segments.form.{name,slug,color,icon,sort_order,sort_order_helper,active,active_helper}`, `segments.table.{name,slug,color,active,sort_order,created}` in both locales; `filament.navigation.segments`; `common.Segment` / `common.Segments`; `profiles.filters.segment`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class SegmentTranslationsTest extends TestCase
{
    public function test_segment_translation_keys_exist_in_both_locales(): void
    {
        foreach (['cs', 'en'] as $locale) {
            app()->setLocale($locale);

            $this->assertNotEquals('segments.form.name', __('segments.form.name'));
            $this->assertNotEquals('segments.table.name', __('segments.table.name'));
            $this->assertNotEquals('filament.navigation.segments', __('filament.navigation.segments'));
            $this->assertNotEquals('common.Segment', __('common.Segment'));
            $this->assertNotEquals('common.Segments', __('common.Segments'));
            $this->assertNotEquals('profiles.filters.segment', __('profiles.filters.segment'));
        }
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SegmentTranslationsTest`
Expected: FAIL — `__()` returns the raw key strings because the files/keys don't exist yet.

- [ ] **Step 3: Create `lang/cs/segments.php`**

```php
<?php

return [
    'form' => [
        'name' => 'Název segmentu',
        'slug' => 'Slug',
        'color' => 'Barva',
        'icon' => 'Ikona',
        'sort_order' => 'Pořadí řazení',
        'sort_order_helper' => 'Nižší čísla se zobrazí jako první',
        'active' => 'Aktivní',
        'active_helper' => 'Neaktivní segmenty nebudou viditelné na webu',
    ],
    'table' => [
        'name' => 'Název segmentu',
        'slug' => 'Slug',
        'color' => 'Barva',
        'active' => 'Aktivní',
        'sort_order' => 'Pořadí řazení',
        'created' => 'Vytvořeno',
    ],
];
```

- [ ] **Step 4: Create `lang/en/segments.php`**

```php
<?php

return [
    'form' => [
        'name' => 'Segment Name',
        'slug' => 'Slug',
        'color' => 'Color',
        'icon' => 'Icon',
        'sort_order' => 'Sort Order',
        'sort_order_helper' => 'Lower numbers appear first',
        'active' => 'Active',
        'active_helper' => 'Inactive segments won\'t be visible on the site',
    ],
    'table' => [
        'name' => 'Segment Name',
        'slug' => 'Slug',
        'color' => 'Color',
        'active' => 'Active',
        'sort_order' => 'Sort Order',
        'created' => 'Created',
    ],
];
```

- [ ] **Step 5: Add navigation, common, and filter keys**

In `lang/cs/filament.php`, in the `'navigation' => [ ... ]` array, right after the line `'services' => 'Služby',`, add:

```php
        'segments' => 'Segmenty',
```

In `lang/en/filament.php`, in the same array after `'services' => 'Services',`, add:

```php
        'segments' => 'Segments',
```

In `lang/cs/common.php`, right after `'Services' => 'Služby',`, add:

```php
    'Segment' => 'Segment',
    'Segments' => 'Segmenty',
```

In `lang/en/common.php`, right after `'Services' => 'Services',`, add:

```php
    'Segment' => 'Segment',
    'Segments' => 'Segments',
```

In `lang/cs/profiles.php`, find the `'filters' => [ ... ]` array (contains `'status'`, `'city'`, `'country'`, `'gender'`) and add:

```php
        'segment' => 'Segment',
```

In `lang/en/profiles.php`, in the equivalent `'filters' => [ ... ]` array, add:

```php
        'segment' => 'Segment',
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=SegmentTranslationsTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add lang/cs/segments.php lang/en/segments.php lang/cs/filament.php lang/en/filament.php lang/cs/common.php lang/en/common.php lang/cs/profiles.php lang/en/profiles.php tests/Feature/SegmentTranslationsTest.php
git commit -m "feat: add cs/en translations for segments"
```

---

### Task 4: `SegmentResource` (Filament admin CRUD)

**Files:**
- Create: `app/Filament/Resources/Segments/SegmentResource.php`
- Create: `app/Filament/Resources/Segments/Schemas/SegmentForm.php`
- Create: `app/Filament/Resources/Segments/Tables/SegmentsTable.php`
- Create: `app/Filament/Resources/Segments/Pages/ListSegments.php`
- Create: `app/Filament/Resources/Segments/Pages/CreateSegment.php`
- Create: `app/Filament/Resources/Segments/Pages/EditSegment.php`
- Test: `tests/Feature/SegmentResourceTest.php`

**Interfaces:**
- Consumes: `Segment` model (Task 1), translation keys `segments.form.*` / `segments.table.*` / `filament.navigation.segments` / `common.Segment` / `common.Segments` (Task 3).
- Produces: routes `segments.index` / `segments.create` / `segments.edit` under the Filament admin panel, following the exact structure of `ServiceResource`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SegmentResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create(['email' => 'admin-segments@example.com']);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_can_create_a_segment(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Segments\Pages\CreateSegment::class)
            ->fillForm([
                'name' => 'Ověřená',
                'slug' => 'overena',
                'color' => '#00B80F',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('segments', ['slug' => 'overena']);
    }

    public function test_admin_can_edit_a_segment(): void
    {
        $admin = $this->admin();
        $segment = Segment::factory()->create(['slug' => 'top-lokalita']);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Segments\Pages\EditSegment::class, ['record' => $segment->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($segment->fresh()->is_active);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SegmentResourceTest`
Expected: FAIL — class `App\Filament\Resources\Segments\Pages\CreateSegment` not found.

- [ ] **Step 3: Create `SegmentForm`**

`app/Filament/Resources/Segments/Schemas/SegmentForm.php`:

```php
<?php

namespace App\Filament\Resources\Segments\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SegmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label(__('segments.form.name') . ' (' . strtoupper(app()->getLocale()) . ')')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                        if ($record && $record->exists) {
                            $currentLocale = app()->getLocale();
                            $component->state($record->getTranslation('name', $currentLocale));
                        }
                    }),

                TextInput::make('slug')
                    ->label(__('segments.form.slug'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                ColorPicker::make('color')
                    ->label(__('segments.form.color'))
                    ->default('#5C2D62'),

                TextInput::make('icon')
                    ->label(__('segments.form.icon'))
                    ->maxLength(100),

                TextInput::make('sort_order')
                    ->label(__('segments.form.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText(__('segments.form.sort_order_helper')),

                Toggle::make('is_active')
                    ->label(__('segments.form.active'))
                    ->default(true)
                    ->helperText(__('segments.form.active_helper')),
            ]);
    }
}
```

- [ ] **Step 4: Create `SegmentsTable`**

`app/Filament/Resources/Segments/Tables/SegmentsTable.php`:

```php
<?php

namespace App\Filament\Resources\Segments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SegmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('segments.table.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('segments.table.slug'))
                    ->searchable()
                    ->sortable(),

                ColorColumn::make('color')
                    ->label(__('segments.table.color')),

                TextColumn::make('sort_order')
                    ->label(__('segments.table.sort_order'))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('segments.table.active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('segments.table.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

- [ ] **Step 5: Create the resource pages**

`app/Filament/Resources/Segments/Pages/ListSegments.php`:

```php
<?php

namespace App\Filament\Resources\Segments\Pages;

use App\Filament\Resources\Segments\SegmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSegments extends ListRecords
{
    protected static string $resource = SegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```

`app/Filament/Resources/Segments/Pages/CreateSegment.php`:

```php
<?php

namespace App\Filament\Resources\Segments\Pages;

use App\Filament\Resources\Segments\SegmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSegment extends CreateRecord
{
    protected static string $resource = SegmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentLocale = app()->getLocale();

        if (isset($data['name'])) {
            $data['name'] = [$currentLocale => $data['name']];
        }

        return $data;
    }
}
```

`app/Filament/Resources/Segments/Pages/EditSegment.php`:

```php
<?php

namespace App\Filament\Resources\Segments\Pages;

use App\Filament\Resources\Segments\SegmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSegment extends EditRecord
{
    protected static string $resource = SegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentLocale = app()->getLocale();

        if (isset($data['name'])) {
            $existingTranslations = $this->record->getTranslations('name');
            $existingTranslations[$currentLocale] = $data['name'];
            $data['name'] = $existingTranslations;
        }

        return $data;
    }
}
```

- [ ] **Step 6: Create `SegmentResource`**

`app/Filament/Resources/Segments/SegmentResource.php`:

```php
<?php

namespace App\Filament\Resources\Segments;

use App\Filament\Resources\Segments\Pages\CreateSegment;
use App\Filament\Resources\Segments\Pages\EditSegment;
use App\Filament\Resources\Segments\Pages\ListSegments;
use App\Filament\Resources\Segments\Schemas\SegmentForm;
use App\Filament\Resources\Segments\Tables\SegmentsTable;
use App\Models\Segment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SegmentResource extends Resource
{
    protected static ?string $model = Segment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 21;

    public static function getNavigationLabel(): string
    {
        return __('filament.navigation.segments');
    }

    public static function getModelLabel(): string
    {
        return __('common.Segment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('common.Segments');
    }

    public static function form(Schema $schema): Schema
    {
        return SegmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SegmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSegments::route('/'),
            'create' => CreateSegment::route('/create'),
            'edit' => EditSegment::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=SegmentResourceTest`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add app/Filament/Resources/Segments tests/Feature/SegmentResourceTest.php
git commit -m "feat: add SegmentResource Filament admin CRUD"
```

---

### Task 5: Seed default segments

**Files:**
- Create: `database/seeders/SegmentSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php` (add `use Database\Seeders\SegmentSeeder;` and `$this->call(SegmentSeeder::class);` next to the existing `$this->call(SubscriptionTypeSeeder::class);` call)
- Test: `tests/Feature/SegmentSeederTest.php`

**Interfaces:**
- Consumes: `Segment` model (Task 1).
- Produces: 3 default segments in the DB after seeding (`nova`, `overena`, `top-lokalita`), idempotent (`firstOrCreate` on `slug`).

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Segment;
use Database\Seeders\SegmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SegmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_default_segments_idempotently(): void
    {
        (new SegmentSeeder())->run();
        (new SegmentSeeder())->run();

        $this->assertSame(3, Segment::count());
        $this->assertTrue(Segment::where('slug', 'nova')->exists());
        $this->assertTrue(Segment::where('slug', 'overena')->exists());
        $this->assertTrue(Segment::where('slug', 'top-lokalita')->exists());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SegmentSeederTest`
Expected: FAIL — class `Database\Seeders\SegmentSeeder` not found.

- [ ] **Step 3: Write the seeder**

`database/seeders/SegmentSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Segment;
use Illuminate\Database\Seeder;

class SegmentSeeder extends Seeder
{
    public function run(): void
    {
        $segments = [
            ['slug' => 'nova', 'name' => ['cs' => 'Nová', 'en' => 'New'], 'color' => '#5C2D62', 'sort_order' => 1],
            ['slug' => 'overena', 'name' => ['cs' => 'Ověřená', 'en' => 'Verified'], 'color' => '#00B80F', 'sort_order' => 2],
            ['slug' => 'top-lokalita', 'name' => ['cs' => 'Top lokalita', 'en' => 'Top location'], 'color' => '#DD3888', 'sort_order' => 3],
        ];

        foreach ($segments as $segment) {
            Segment::firstOrCreate(
                ['slug' => $segment['slug']],
                [
                    'name' => $segment['name'],
                    'color' => $segment['color'],
                    'sort_order' => $segment['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
```

- [ ] **Step 4: Register it in `DatabaseSeeder`**

In `database/seeders/DatabaseSeeder.php`, add the import next to the other seeder imports at the top:

```php
use Database\Seeders\SegmentSeeder;
```

And add the call right after `$this->call(SubscriptionTypeSeeder::class);`:

```php
        $this->call(SegmentSeeder::class);
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=SegmentSeederTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add database/seeders/SegmentSeeder.php database/seeders/DatabaseSeeder.php tests/Feature/SegmentSeederTest.php
git commit -m "feat: seed default profile segments"
```

---

### Task 6: Assign segments to profiles in the admin (`ProfileResource`)

**Files:**
- Modify: `app/Filament/Resources/Profiles/Schemas/ProfileForm.php` (add a segments field after the `Toggle::make('is_public')` field, ~line 227-229)
- Modify: `app/Filament/Resources/Profiles/Tables/ProfilesTable.php` (add a segments badge column after `is_public`, ~line 67-69, and a `SelectFilter` after the `city` filter, ~line 101)
- Test: `tests/Feature/ProfileResourceSegmentsTest.php`

**Interfaces:**
- Consumes: `Segment` model and `Profile::segments()` (Tasks 1–2).
- Produces: admin can attach/detach segments on a profile via `ProfileResource`; admin table shows segment badges and can filter by segment.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileResourceSegmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_segments_to_a_profile_via_edit_form(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create(['email' => 'admin-profile-segments@example.com']);
        $admin->assignRole('admin');

        $profile = Profile::factory()->create();
        $segment = Segment::factory()->create(['slug' => 'nova']);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Profiles\Pages\EditProfile::class, ['record' => $profile->getRouteKey()])
            ->fillForm(['segments' => [$segment->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($profile->fresh()->segments->contains('id', $segment->id));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProfileResourceSegmentsTest`
Expected: FAIL — the `segments` form field doesn't exist yet, so `fillForm` has nothing to set (assertHasNoFormErrors passes but the assignment assertion fails).

- [ ] **Step 3: Add the segments field to `ProfileForm`**

In `app/Filament/Resources/Profiles/Schemas/ProfileForm.php`, add `use Filament\Forms\Components\CheckboxList;` to the `use` block at the top, then insert this immediately after the `Toggle::make('is_public')` block (before the closing `]);`):

```php
                CheckboxList::make('segments')
                    ->label(__('common.Segments'))
                    ->relationship('segments', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible($isAdmin),
```

- [ ] **Step 4: Add the segments column and filter to `ProfilesTable`**

In `app/Filament/Resources/Profiles/Tables/ProfilesTable.php`, add `use Filament\Tables\Columns\TextColumn;` is already imported; add this column right after the `IconColumn::make('is_public')` block (~line 67-69):

```php
                TextColumn::make('segments.name')
                    ->label(__('common.Segments'))
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->allSegments()->pluck('name')),
```

And add this filter in the `->filters([...])` array, right after the `city` `SelectFilter` block:

```php
                SelectFilter::make('segments')
                    ->label(__('profiles.filters.segment'))
                    ->relationship('segments', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', app()->getLocale())),
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=ProfileResourceSegmentsTest`
Expected: PASS.

- [ ] **Step 6: Run the full backend test suite so far**

Run: `php artisan test --filter=Segment`
Expected: PASS for every Segment*/ProfileSegments*/ProfileResourceSegments* test written so far.

- [ ] **Step 7: Commit**

```bash
git add app/Filament/Resources/Profiles/Schemas/ProfileForm.php app/Filament/Resources/Profiles/Tables/ProfilesTable.php tests/Feature/ProfileResourceSegmentsTest.php
git commit -m "feat: manage and filter profile segments from ProfileResource"
```

---

### Task 7: Audit pass — fix N+1s, authorization, safe deletion, translatable fallback

This task is a checklist-driven review of everything built in Tasks 1–6, done **before** any frontend wiring in Tasks 8–11. Each item below is verified by running a command or writing a small regression test; fix in place if it fails.

**Files (expected touch points, exact set depends on findings):**
- Modify: `app/Models/Profile.php` (`allSegments()` — add translatable fallback)
- Modify: `app/Filament/Resources/Profiles/Tables/ProfilesTable.php` (eager-load segments in the column if `getStateUsing` triggers N+1 across rows)
- Test: `tests/Feature/SegmentAuditTest.php`

- [ ] **Step 1: Write regression tests for the audit findings**

```php
<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SegmentAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_translatable_name_falls_back_to_configured_fallback_locale(): void
    {
        app()->setLocale('en');
        $segment = Segment::factory()->create(['name' => ['cs' => 'Pouze česky']]);
        $profile = Profile::factory()->create();
        $profile->segments()->attach($segment->id);

        $name = $segment->fresh()->getTranslation('name', 'en', useFallbackLocale: true);

        $this->assertSame('Pouze česky', $name);
    }

    public function test_deleting_a_segment_does_not_break_allsegments_or_leave_orphan_pivot_rows(): void
    {
        $segment = Segment::factory()->create();
        $profile = Profile::factory()->create();
        $profile->segments()->attach($segment->id);

        $segment->delete();

        $this->assertDatabaseMissing('profile_segment', ['segment_id' => $segment->id]);
        $this->assertCount(0, $profile->fresh()->allSegments());
    }

    public function test_deleting_a_profile_does_not_leave_orphan_pivot_rows(): void
    {
        $segment = Segment::factory()->create();
        $profile = Profile::factory()->create();
        $profile->segments()->attach($segment->id);
        $profileId = $profile->id;

        $profile->forceDelete();

        $this->assertDatabaseMissing('profile_segment', ['profile_id' => $profileId]);
    }

    public function test_loading_many_profiles_with_segments_does_not_n_plus_one(): void
    {
        $segment = Segment::factory()->create();
        Profile::factory()->count(5)->create()->each(fn (Profile $p) => $p->segments()->attach($segment->id));

        DB::enableQueryLog();
        $profiles = Profile::with('segments')->get();
        foreach ($profiles as $profile) {
            $profile->allSegments();
        }
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // 1 query for profiles + 1 query for the eager-loaded segments pivot join,
        // regardless of how many profiles there are.
        $this->assertLessThanOrEqual(2, $queryCount);
    }
}
```

- [ ] **Step 2: Run the tests**

Run: `php artisan test --filter=SegmentAuditTest`
Expected: the fallback-locale test should PASS as-is (spatie/laravel-translatable supports `useFallbackLocale`). The two cascade-delete tests should PASS given the `onDelete('cascade')` in Task 1's migrations — if either FAILS, the migration's foreign key definition is wrong; fix it and re-run `php artisan migrate:fresh` before re-testing. The N+1 test should PASS because `Profile::with('segments')` is used explicitly in the test — this test exists to catch a *future* regression, not to fix current code.

- [ ] **Step 3: Verify indexes exist**

Run: `php artisan tinker --execute="print_r(Illuminate\Support\Facades\Schema::getIndexes('profile_segment'));"`
Expected: output includes an index covering `profile_id` (from the `foreignId()->constrained()` call) and the `unique` index on `['profile_id', 'segment_id']`. If a lookup by `profile_id` alone isn't covered by an index, add `$table->index('profile_id');` to the migration from Task 1 before it has been run in any shared environment (if already deployed, add a new migration instead — check with the user before altering a migration already merged to `main`).

- [ ] **Step 4: Verify Filament authorization**

Run: `php artisan tinker --execute="echo (new App\Filament\Resources\Segments\SegmentResource())::class;"` and manually confirm in `app/Filament/Resources/Profiles/ProfileResource.php:getEloquentQuery()` (lines 54-68) that non-admin users still only see their own profile — this method is unchanged by Task 6, only the form/table for the admin-visible fields changed. Confirm `SegmentResource` has no custom `getEloquentQuery()` override, so it inherits Filament's default panel-level authorization (same as `ServiceResource`, which also has none) — segment management is implicitly admin-only because the whole Filament panel requires the `admin` role to log in (check `app/Providers/Filament/AdminPanelProvider.php` for the panel's auth guard/middleware to confirm this assumption holds).

- [ ] **Step 5: Fix anything the above steps surfaced**

If Step 2, 3, or 4 uncovered a real defect, fix it directly in the relevant file from Tasks 1–6 and re-run `php artisan test --filter=Segment` to confirm the whole batch still passes.

- [ ] **Step 6: Commit**

```bash
git add tests/Feature/SegmentAuditTest.php
git commit -m "test: add segment audit regression coverage (N+1, cascade delete, fallback locale)"
```

(If Step 5 produced additional file changes, `git add` those files too before committing, and mention them in the commit message.)

---

### Task 8: Eager-load and filter segments in the public listing (`ProfileList`)

**Files:**
- Modify: `app/Livewire/ProfileList.php`
- Test: `tests/Feature/ProfileListSegmentFilterTest.php`

**Interfaces:**
- Consumes: `Profile::segments()` relation, `Segment::active()->ordered()` (Tasks 1–2).
- Produces: new public property `$segmentId` (int|string, default `''`), added to `$queryString` as `'segmentId' => ['except' => '', 'as' => 'segment']`, new method `toggleSegment($segmentId)`, `$query->with('segments')` added to every branch that returns profiles, `$query->whereHas('segments', fn ($q) => $q->where('segments.id', $this->segmentId))` applied when `$this->segmentId` is set. Existing filters, computed properties, and the showcase-profiles branch are otherwise untouched.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Profile;
use App\Models\Segment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileListSegmentFilterTest extends TestCase
{
    use RefreshDatabase;

    private function approvedPublicProfile(string $name): Profile
    {
        return Profile::factory()->create([
            'status' => 'approved',
            'is_public' => true,
            'display_name' => $name,
        ]);
    }

    public function test_filtering_by_segment_only_returns_matching_profiles(): void
    {
        $segment = Segment::factory()->create();
        $matching = $this->approvedPublicProfile('Has Segment');
        $matching->segments()->attach($segment->id);
        $this->approvedPublicProfile('No Segment');

        Livewire::test(\App\Livewire\ProfileList::class)
            ->set('segmentId', $segment->id)
            ->assertSee('Has Segment')
            ->assertDontSee('No Segment');
    }

    public function test_segments_are_eager_loaded_to_avoid_n_plus_one(): void
    {
        $segment = Segment::factory()->create();
        for ($i = 0; $i < 3; $i++) {
            $this->approvedPublicProfile("Profile {$i}")->segments()->attach($segment->id);
        }

        \Illuminate\Support\Facades\DB::enableQueryLog();
        Livewire::test(\App\Livewire\ProfileList::class)
            ->set('region', '')
            ->set('ageMin', '18'); // forces the non-showcase query branch without changing result set materially
        $queries = \Illuminate\Support\Facades\DB::getQueryLog();
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $segmentQueries = collect($queries)->filter(fn ($q) => str_contains($q['query'], 'profile_segment'));
        $this->assertLessThanOrEqual(1, $segmentQueries->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProfileListSegmentFilterTest`
Expected: FAIL — `Livewire\Component` has no public property `segmentId`.

- [ ] **Step 3: Implement the changes**

In `app/Livewire/ProfileList.php`:

Add the property near the other quick-filter properties (after `public $hasRating = false;` on line 34):

```php
    public $segmentId = '';
```

Add it to `$queryString` (inside the array starting at line 36):

```php
        'segmentId' => ['except' => '', 'as' => 'segment'],
```

Add to `mount()` (after `$this->hasRating = request()->boolean('rated');` on line 69):

```php
        $this->segmentId = request('segment', '');
```

Add a toggle method, near `toggleRating()` (after line 161):

```php
    public function toggleSegment($segmentId)
    {
        $this->segmentId = $this->segmentId == $segmentId ? '' : $segmentId;
        $this->resetPage();
    }
```

Add `$segmentId` to the `activeFiltersCount()` computed method (inside the body, before `return $count;` on line 177):

```php
        if ($this->segmentId) $count++;
```

Add `$segmentId` to `usesShowcaseProfiles()` (inside the `&&` chain, before the closing `;` on line 365):

```php
            && $this->segmentId === ''
```

Add `->with('segments')` to both query builders. On line 185 (`$showcaseQuery = Profile::with(['user:id,name,last_activity', 'media'])`), change to:

```php
            $showcaseQuery = Profile::with(['user:id,name,last_activity', 'media', 'segments'])
```

On line 228 (`$query = Profile::with(['user:id,name,last_activity', 'media'])`), change to:

```php
        $query = Profile::with(['user:id,name,last_activity', 'media', 'segments'])
```

Add the filter application, after the `if ($this->hasRating) { ... }` block (after line 298, before `$profiles = $query->paginate($this->perPage);`):

```php
        if ($this->segmentId) {
            $query->whereHas('segments', function ($q) {
                $q->where('segments.id', $this->segmentId);
            });
        }
```

Add `'id'` is already present in `getPublicProfileColumns()` — no change needed there since `segments` is a relation, not a column, but confirm `id` stays in that array (it does, line 373) since Eloquent needs it to hydrate the pivot relation.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ProfileListSegmentFilterTest`
Expected: PASS.

- [ ] **Step 5: Run the full existing `ProfileList`-adjacent suite to confirm no regression**

Run: `php artisan test --filter=ProfileList`
Expected: PASS (no other `ProfileList*` tests should have broken).

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/ProfileList.php tests/Feature/ProfileListSegmentFilterTest.php
git commit -m "feat: filter public profile listing by segment"
```

---

### Task 9: Segment badges on the profile card

**Files:**
- Modify: `resources/views/components/profile-card.blade.php`
- Test: `tests/Feature/ProfileCardSegmentBadgeTest.php`

**Interfaces:**
- Consumes: `Profile::allSegments()` (Task 2). Renders only the *non*-VIP entries as new badges — the existing `$isVip` badge (lines 126-131 and 195-200) already covers the VIP case and must not be duplicated or altered.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileCardSegmentBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_card_renders_manual_segment_badge(): void
    {
        $segment = Segment::factory()->create(['name' => ['cs' => 'Top lokalita', 'en' => 'Top location']]);
        $profile = Profile::factory()->create(['status' => 'approved', 'is_public' => true]);
        $profile->segments()->attach($segment->id);

        $html = $this->blade(
            '<x-profile-card :profile="$profile" />',
            ['profile' => $profile->fresh()->load('segments')]
        )->toHtml();

        $this->assertStringContainsString('Top lokalita', $html);
    }

    public function test_profile_card_does_not_render_a_segment_badge_when_none_assigned(): void
    {
        $profile = Profile::factory()->create(['status' => 'approved', 'is_public' => true]);

        $html = $this->blade(
            '<x-profile-card :profile="$profile" />',
            ['profile' => $profile->fresh()->load('segments')]
        )->toHtml();

        $this->assertStringNotContainsString('home-profile-card-segment-badge', $html);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProfileCardSegmentBadgeTest`
Expected: FAIL — "Top lokalita" not present anywhere in the rendered card.

- [ ] **Step 3: Add the badge markup**

In `resources/views/components/profile-card.blade.php`, in the `@php` block at the top (after line 25, `$isOnline = ...`), add:

```php
    $extraSegments = $isModel ? $profile->allSegments()->reject(fn ($segment) => $segment['is_vip']) : collect();
```

Then, inside the existing badge stack `<div class="absolute ... home-profile-card-badge-stack">` (which starts at line 103 and is conditioned on `@if((!$shouldBlur) && ($isVerified || $isVip || $isOnline) && !$simpleMode)`), add the new badges right before that `<div>`'s closing `@endif` (i.e., right after the existing VIP block that ends at line 131, still inside the same wrapping `<div>`):

```php
            @foreach($extraSegments as $segment)
            <div class="home-profile-card-badge home-profile-card-segment-badge" style="width:auto;min-width:50px;height:26px;margin-top:5px;border-radius:999px;background:{{ $segment['color'] }};display:flex;align-items:center;justify-content:center;padding:0 8px;">
                <span style="font-family:'Poppins', sans-serif; font-weight:900; font-size:9px; color:#FFFFFF; line-height:1; white-space:nowrap;">{{ $segment['name'] }}</span>
            </div>
            @endforeach
```

Also widen the `@if` guard on line 102 so the stack renders even when a profile has *only* extra segments and none of the pre-existing badge conditions: change

```php
        @if((!$shouldBlur) && ($isVerified || $isVip || $isOnline) && !$simpleMode)
```

to

```php
        @if((!$shouldBlur) && ($isVerified || $isVip || $isOnline || $extraSegments->isNotEmpty()) && !$simpleMode)
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ProfileCardSegmentBadgeTest`
Expected: PASS.

- [ ] **Step 5: Visually confirm no regression**

Run the dev server (`php artisan serve` or the project's usual `run` skill) and open a listing page with an existing VIP/verified/online profile; confirm the VIP/verified/online badges render exactly as before and the new segment badge (if any) stacks below them without overlapping.

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/profile-card.blade.php tests/Feature/ProfileCardSegmentBadgeTest.php
git commit -m "feat: render segment badges on the profile card"
```

---

### Task 10: Segment badges on the profile detail page

**Files:**
- Modify: `resources/views/components/profile-detail.blade.php`
- Test: `tests/Feature/ProfileDetailSegmentBadgeTest.php`

**Interfaces:**
- Consumes: `Profile::allSegments()` (Task 2). Renders extra segments as new `<span class="vip-profile-status-pill">` elements inside the existing `.vip-profile-status-bar` (line 2476-2485), reusing that class so styling is consistent without new CSS.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileDetailSegmentBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_detail_page_shows_segment_badge(): void
    {
        \App\Models\City::create([
            'name' => 'Praha', 'name_ascii' => 'Praha', 'country_code' => 'CZ', 'population' => 1300000,
        ]);
        $segment = Segment::factory()->create(['name' => ['cs' => 'Ověřená', 'en' => 'Verified segment']]);
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'is_public' => true,
            'city' => 'Praha',
            'country_code' => 'cz',
        ]);
        $profile->segments()->attach($segment->id);

        $response = $this->get(route('profiles.show', $profile));

        $response->assertOk();
        $response->assertSee('Ověřená');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProfileDetailSegmentBadgeTest`
Expected: FAIL — "Ověřená" not present in the response body.

- [ ] **Step 3: Add the badge markup**

Find where `$profile` is passed into `profile-detail.blade.php` (the controller/route rendering `profiles.show`) and confirm `segments` is eager-loaded there — check `grep -rn "profile-detail" resources/views` and the controller action for `profiles.show`; if `$profile` isn't already loaded with `->load('segments')` or eager-loaded at the query, add `$profile->load('segments');` in that controller method before the view is returned.

In `resources/views/components/profile-detail.blade.php`, inside the `.vip-profile-status-bar` div (starts at line 2476), right after the closing `</span>` of the `vip-profile-status-pill--verification` pill (line 2484, before the `</div>` on line 2485), add:

```php
                @foreach($profile->allSegments()->reject(fn ($segment) => $segment['is_vip']) as $segment)
                <span class="vip-profile-status-pill" style="background: {{ $segment['color'] }}1A; color: {{ $segment['color'] }};">
                    {{ $segment['name'] }}
                </span>
                @endforeach
```

(The `1A` suffix appended to the hex color is an 10%-alpha background tint using the existing pill's padding/shape — no new CSS class needed, matching how `.vip-profile-status-pill--primary` / `--verification` already use inline-friendly styling conventions in this file.)

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ProfileDetailSegmentBadgeTest`
Expected: PASS.

- [ ] **Step 5: Visually confirm no regression**

Open an existing profile detail page in the browser; confirm the VIP/verification pills still render unchanged and the new segment pill(s) appear inline after them without breaking the row layout on mobile widths.

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/profile-detail.blade.php tests/Feature/ProfileDetailSegmentBadgeTest.php
git commit -m "feat: render segment badges on the profile detail page"
```

(If Step 3 required a controller change to eager-load `segments`, `git add` that file too.)

---

### Task 11: Segment filter control in the listing UI

**Files:**
- Modify: `resources/views/livewire/profile-list.blade.php`
- Test: `tests/Feature/ProfileListSegmentFilterUiTest.php`

**Interfaces:**
- Consumes: `ProfileList::$segmentId` / `toggleSegment()` (Task 8), `Segment::active()->ordered()->get()` for the option list.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Segment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileListSegmentFilterUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_segment_filter_select_lists_active_segments(): void
    {
        $segment = Segment::factory()->create(['name' => ['cs' => 'Nová', 'en' => 'New'], 'is_active' => true]);
        Segment::factory()->create(['is_active' => false]);

        Livewire::test(\App\Livewire\ProfileList::class)
            ->assertSee('Nová')
            ->assertSee('wire:model.live="segmentId"', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProfileListSegmentFilterUiTest`
Expected: FAIL — no `segmentId` select rendered yet.

- [ ] **Step 3: Add the filter control**

In `app/Livewire/ProfileList.php`, add a computed property for the options (near `activeFiltersCount()`, after its closing `}` around line 178):

```php
    #[Computed]
    public function availableSegments()
    {
        return \App\Models\Segment::active()->ordered()->get();
    }
```

In `resources/views/livewire/profile-list.blade.php`, add a `<select>` control right before the existing `resetFilters` button block that follows the quick-filter pills (insert it right after the closing of the `toggleRating` button, before the `@if($activeFiltersCount > 0)` / `resetFilters` block — locate the two occurrences at lines ~516-527 and ~625-636, and add this snippet in both, mirroring how every other quick filter appears twice for mobile/desktop):

```blade
                <select wire:model.live="segmentId" class="mobile-filter-pill" style="appearance:auto;">
                    <option value="">{{ __('profiles.filters.segment') }}</option>
                    @foreach($this->availableSegments as $segment)
                        <option value="{{ $segment->id }}">{{ $segment->getTranslation('name', app()->getLocale()) }}</option>
                    @endforeach
                </select>
```

Use the `mobile-filter-pill` class for the first (mobile) occurrence and `filter-pill` for the second (desktop) occurrence, matching the class already used by the sibling buttons at each respective location.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ProfileListSegmentFilterUiTest`
Expected: PASS.

- [ ] **Step 5: Run the entire test suite**

Run: `php artisan test`
Expected: PASS — no pre-existing test broken by any change in Tasks 1–11.

- [ ] **Step 6: Visually confirm no regression**

Open the listing page in the browser at both mobile and desktop widths; confirm all pre-existing filter pills (age, recommendation, verified photo, video, actress, new, rating) are unchanged in position and behavior, and the new segment `<select>` sits alongside them and actually filters results when changed.

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/ProfileList.php resources/views/livewire/profile-list.blade.php tests/Feature/ProfileListSegmentFilterUiTest.php
git commit -m "feat: add segment filter control to the profile listing UI"
```

---

## Final acceptance check

- [ ] Run `php artisan test` — full suite green.
- [ ] Run `php artisan translation:audit` (or the project's `AuditTranslations` command — check `app/Console/Commands/AuditTranslations.php` for its exact signature) to confirm no missing `cs`/`en` keys were introduced.
- [ ] Confirm in the browser: admin can create/edit/delete a segment, assign it to a profile, see it as a badge on the card and detail page, and filter the public listing by it; VIP badge behavior is unchanged; no existing page's layout shifted.
