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

    protected $fillable = [
        'label',
        'page_id',
        'url',
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

            $items = static::query()->visible()->ordered()->with('page')->get();
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
