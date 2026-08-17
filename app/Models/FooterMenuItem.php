<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Spatie\Translatable\HasTranslations;
use Throwable;

/**
 * One link in the footer menu.
 *
 * The footer used to list CMS pages under their own titles, which meant it
 * could not carry a link worded differently from the page, two links pointing
 * at one page, or anything outside the CMS.
 */
class FooterMenuItem extends Model
{
    use HasFactory;
    use HasTranslations;

    /** The design draws three columns. */
    public const COLUMNS = [1, 2, 3];

    /**
     * Who the link is for.
     *
     * The design lists a VIP plan for women and a Premium one for men next to
     * each other, which only makes sense to somebody who is neither yet. A
     * signed-in visitor is offered the one that applies to her or him.
     */
    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_GUESTS = 'guests';
    public const AUDIENCE_WOMEN = 'women';
    public const AUDIENCE_MEN = 'men';

    protected $fillable = [
        'label',
        'page_id',
        'url',
        'audience',
        'column',
        'sort_order',
        'opens_in_new_tab',
        'is_visible',
    ];

    public array $translatable = ['label'];

    protected function casts(): array
    {
        return [
            'column' => 'integer',
            'sort_order' => 'integer',
            'opens_in_new_tab' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /** @return array<string, string> */
    public static function audienceOptions(): array
    {
        return [
            self::AUDIENCE_ALL => 'Všem',
            self::AUDIENCE_GUESTS => 'Jen nepřihlášeným',
            self::AUDIENCE_WOMEN => 'Jen ženám',
            self::AUDIENCE_MEN => 'Jen mužům',
        ];
    }

    /**
     * Whether this link belongs in the footer the current visitor sees.
     *
     * An unknown value shows the link rather than hiding it: a typo in the
     * column must not make a link silently disappear.
     */
    public function isForCurrentVisitor(): bool
    {
        $user = auth()->user();

        return match ($this->audience) {
            self::AUDIENCE_GUESTS => $user === null,
            self::AUDIENCE_WOMEN => $user !== null && $user->isFemale(),
            self::AUDIENCE_MEN => $user !== null && $user->isMale(),
            default => true,
        };
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('column')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Where the link goes.
     *
     * A page that has been unpublished or deleted yields null, and the footer
     * leaves the item out rather than rendering a link into nothing.
     */
    public function resolvedUrl(): ?string
    {
        if ($this->page_id) {
            $page = $this->relationLoaded('page') ? $this->page : $this->page()->first();

            if (! $page || ! $page->is_published) {
                return null;
            }

            return url('/' . ltrim((string) $page->slug, '/'));
        }

        $url = trim((string) $this->url);

        return $url !== '' ? $url : null;
    }

    /**
     * The footer's three columns, or an empty collection before the table
     * exists — the site still has to boot during a migration run.
     *
     * @return \Illuminate\Support\Collection<int, Collection<int, self>>
     */
    public static function columns(): \Illuminate\Support\Collection
    {
        try {
            if (! Schema::hasTable('footer_menu_items')) {
                return collect();
            }

            $items = static::query()
                ->visible()
                ->ordered()
                ->with('page')
                ->get()
                ->filter(fn (self $item) => $item->isForCurrentVisitor());
        } catch (Throwable) {
            return collect();
        }

        if ($items->isEmpty()) {
            return collect();
        }

        return collect(self::COLUMNS)
            ->mapWithKeys(fn (int $column) => [
                $column => $items->where('column', $column)->values(),
            ])
            // A column nobody has filled is not drawn, so two configured
            // columns do not leave a hole where the third would be.
            ->filter(fn (Collection $column) => $column->isNotEmpty());
    }
}
