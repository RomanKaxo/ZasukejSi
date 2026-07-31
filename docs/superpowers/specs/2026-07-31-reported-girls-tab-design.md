# Nahlášené dívky (Reported Girls tab) — design

## Purpose
Member-facing "Reported" tab (`/account/member/reported`) currently shows a "coming soon" placeholder. Replace it with a real list of profiles the logged-in member has reported, each showing the block reason and allegation categories, matching the provided mockups.

## Scope
In scope:
- `reports` data model, migration, seeder (mock data tied to the existing `user@example.com` test member)
- `MemberController::reported()` loading real data
- Reported-girls list page (title, subtitle, divider, grid of cards)
- `reported-info-card` component (compact card, side-by-side with existing `profile-card` in `isReported` mode)
- "Číst celý případ" modal component showing full case details

Out of scope (explicitly deferred):
- A way for a member to *submit* a report from a profile page (no "Nahlásit" button exists anywhere yet)
- Admin review/approval flow for reports
- Interactive photo carousel inside the case modal (static image only)

## Data layer

### Migration: `reports`
| column | type | notes |
|---|---|---|
| id | bigint pk | |
| profile_id | FK → profiles | the reported profile |
| reporter_id | FK → users | the member who reported |
| reason | text | free-text block reason shown in card/modal |
| allegations | json | array of category strings, drawn from a fixed list |
| blocked_at | timestamp nullable | when the profile was blocked as a result |
| timestamps | | |

### Model: `App\Models\Report`
- `belongsTo(Profile::class)`
- `belongsTo(User::class, 'reporter_id')`
- `const ALLEGATION_CATEGORIES = ['Krádež', 'Jiná osoba na fotkách', 'Podvod', 'Ohrožování', 'Falešný profil', 'Nevhodné chování']` (fixed list; `allegations` values must come from this list)

### Seeder
Add a few `Report` rows for `user@example.com` against existing seeded female profiles, with varying reason-text length (to exercise the line-clamp) and 1–4 allegations each.

### Controller
`MemberController::reported()`:
```php
$reports = Report::with('profile.images')
    ->where('reporter_id', $user->id)
    ->latest()
    ->get();
return view('member.reported', compact('user', 'reports'));
```

## View: `resources/views/member/reported.blade.php`
- Title "Nahlášené dívky" (existing `front.account.member.reported` key)
- Subtitle: poppins regular 14px `#505050`, text = `front.account.member.reported_description` (cs value updated to the placeholder copy supplied in the mockup)
- `<hr>` divider below the subtitle
- Grid: `flex flex-wrap gap-x-6 gap-y-8`, one item per report:
  ```blade
  <div class="flex gap-4">
      <x-profile-card :profile="$report->profile" :isReported="true" />
      <x-reported-info-card :report="$report" />
  </div>
  ```
- Empty state: keep existing "coming soon"-style empty block, retitled, when `$reports` is empty.

## Component: `resources/views/components/reported-info-card.blade.php`
Props: `report` (the `Report` model, with `profile` loaded for the name in the modal trigger).

Box: `285x510`, bg `#F2F2F2`, `border-radius:15px`, `box-shadow: 0 15px 15px rgba(92,45,98,0.1)`.

Contents, top to bottom:
1. `TriangleAlert` icon, 32×32, color `#DD3888`
2. "Důvod blokace" — Poppins bold 18px `#505050`
3. `$report->reason`, Poppins regular 14px `#505050`, `line-clamp-6` (truncated with ellipsis)
4. Allegation pills: first 3 of `$report->allegations`, each `171x30 radius-8 bg-white`, Plus Jakarta Sans bold 11px `#505050`, centered text. If `count($report->allegations) > 3`, render a 4th pill with `…` instead of the 4th category.
5. "Číst celý případ" button: `171x40 radius-8 bg-[#DD3888]`, Plus Jakarta Sans bold 14px white. `@click` sets the shared Alpine store's active report id and opens the modal (see below).

## Component: `resources/views/components/reported-case-modal.blade.php`
Rendered once per page (not once per card) and driven by an Alpine store (`Alpine.store('reportedCase', { open: false, report: null })`), matching the pattern already used for `memberSidebar`. Each `reported-info-card`'s button sets `report` (serialized JSON: name, reason, allegations, height, age, location, image url) and `open = true`.

Modal shell: `600x1323`, bg white, `border-radius:24px`, centered overlay with backdrop.
- Close `X` top-right (`close.svg` or existing cross icon)
- Centered `TriangleAlert` icon
- "Důvod blokace" — Poppins bold 36px `#5C2D62`, centered
- Reported member's name below it — Poppins bold, `#DD3888`, centered
- Inner panel: `510x973`, bg `#F2F2F2`, `border-radius:15px`:
  - Top-right: static profile image, `210x265`, with decorative (non-interactive) dot row matching the compact card's photo-count dots
  - Next to the image: **all** allegation pills stacked (no 3-item cap), same pill styling as the compact card
  - Below the image/pills row: height + age pills (`91x30 radius-8 bg-white`) and a location pill (`190x30 radius-8 bg-white`)
  - Below that: full (non-truncated) reason text, Poppins regular 14px `#505050`, spanning the panel width

## Testing
- Feature test: `account.member.reported` route renders reports belonging to the authenticated member only (not other members' reports)
- Component smoke test / manual check: card truncates allegations at 3 + "…", modal shows all allegations and full reason text
- Manual browser check of the page (screenshots) since this is visual UI work
