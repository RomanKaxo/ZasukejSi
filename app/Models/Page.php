<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Page extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    /**
     * The attributes that are translatable.
     *
     * @var array<int, string>
     */
    public $translatable = ['title', 'description', 'content'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'type',
        'description',
        'content',
        'display_in_menu',
        'display_in_footer',
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'display_in_menu' => 'boolean',
            'display_in_footer' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                // Get title in current locale or first available
                $title = is_array($page->title) 
                    ? ($page->title[app()->getLocale()] ?? array_values($page->title)[0] ?? '') 
                    : $page->title;
                    
                $page->slug = Str::slug($title);
            }
        });
    }

    /**
     * Scope a query to only include published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include pages for menu.
     */
    public function scopeInMenu($query)
    {
        return $query->where('display_in_menu', true);
    }

    /**
     * Scope a query to only include blog posts.
     */
    public function scopeBlog($query)
    {
        return $query->where('type', 'blog');
    }

    /**
     * Scope a query to only include regular pages.
     */
    public function scopePage($query)
    {
        return $query->where('type', 'page');
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('header-image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->useDisk('public');
    }

    /**
     * Calculate approximate reading time based on paragraph content.
     * Average reading speed: 200 words per minute.
     *
     * @return int Reading time in minutes
     */
    public function aproximateReadingTime(): int
    {
        if (!$this->content || !is_array($this->content)) {
            return 0;
        }

        $totalChars = 0;

        // Recursively extract text from all blocks
        $extractText = function ($blocks) use (&$extractText, &$totalChars) {
            foreach ($blocks as $block) {
                if (!is_array($block)) {
                    continue;
                }

                $blockType = $block['type'] ?? '';

                // Check if this is a paragraph block (SkyRaptor)
                if (str_contains($blockType, 'Paragraph')) {
                    if (isset($block['data']['content'])) {
                        $text = strip_tags($block['data']['content']);
                        $totalChars += strlen($text);
                    }
                }

                // Generic check for any nested content arrays
                if (isset($block['data']) && is_array($block['data'])) {
                    foreach ($block['data'] as $key => $value) {
                        if (is_array($value) && $key === 'content') {
                            $extractText($value);
                        }
                    }
                }
            }
        };

        $extractText($this->content);

        // Average word length is ~5 characters, reading speed ~200 words/min
        $words = $totalChars / 5;
        $minutes = ceil($words / 210);

        return max(1, $minutes); // Minimum 1 minute
    }
}
