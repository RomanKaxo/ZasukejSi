<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Values that were only reachable inside JSON.
 *
 * Height, weight, bust, nationality, languages and the messenger flags lived
 * in the `content` blob; the phone number lived in `contacts`. Nothing could
 * be searched, filtered, sorted or joined on any of them — the duplicate
 * finder had to load every profile and compare in PHP, and an admin looking
 * for a phone number had nowhere to type it.
 *
 * The JSON stays as it is and keeps being written; these columns carry the
 * same values in a form the database can work with.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->after('address');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('phone');
            $table->unsignedSmallInteger('weight_kg')->nullable()->after('height_cm');
            $table->string('bust_size', 8)->nullable()->after('weight_kg');
            $table->char('nationality', 2)->nullable()->after('bust_size');
            $table->string('languages')->nullable()->after('nationality');
            $table->boolean('has_whatsapp')->default(false)->after('languages');
            $table->boolean('has_telegram')->default(false)->after('has_whatsapp');

            // The phone is the strongest duplicate signal there is, so it is
            // the one worth an index.
            $table->index('phone');
            $table->index(['height_cm', 'weight_kg']);
        });

        $this->backfill();
    }

    /**
     * Fill the new columns from the JSON every existing profile already has.
     *
     * Done row by row rather than in SQL: the JSON shapes differ between
     * profiles created by the admin, by the account form and by the importer,
     * and picking a phone out of `contacts` is not something SQL should be
     * asked to do across three drivers.
     */
    private function backfill(): void
    {
        DB::table('profiles')
            ->select(['id', 'content', 'contacts'])
            ->orderBy('id')
            ->chunk(200, function ($profiles) {
                foreach ($profiles as $profile) {
                    $content = json_decode((string) $profile->content, true) ?: [];
                    $contacts = json_decode((string) $profile->contacts, true) ?: [];

                    $values = array_filter([
                        'phone' => self::firstPhone($contacts),
                        'height_cm' => self::intOrNull($content['card_height_cm'] ?? null),
                        'weight_kg' => self::intOrNull($content['weight_kg'] ?? null),
                        'bust_size' => self::stringOrNull($content['bust_size'] ?? null),
                        'nationality' => self::countryOrNull($content['nationality'] ?? null),
                        'languages' => self::stringOrNull($content['languages'] ?? null),
                    ], fn ($value) => $value !== null);

                    $values['has_whatsapp'] = (bool) ($content['has_whatsapp'] ?? false);
                    $values['has_telegram'] = (bool) ($content['has_telegram'] ?? false);

                    DB::table('profiles')->where('id', $profile->id)->update($values);
                }
            });
    }

    /** @param  array<mixed>  $contacts */
    private static function firstPhone(array $contacts): ?string
    {
        foreach ($contacts as $contact) {
            if (! is_array($contact)) {
                continue;
            }

            $type = strtolower((string) ($contact['type'] ?? ''));
            $value = trim((string) ($contact['value'] ?? ''));

            if ($value !== '' && ($type === 'phone' || $type === '')) {
                return mb_substr($value, 0, 32);
            }
        }

        return null;
    }

    private static function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function countryOrNull(mixed $value): ?string
    {
        $value = self::stringOrNull($value);

        return $value === null ? null : mb_strtolower(mb_substr($value, 0, 2));
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['height_cm', 'weight_kg']);
            $table->dropColumn([
                'phone', 'height_cm', 'weight_kg', 'bust_size',
                'nationality', 'languages', 'has_whatsapp', 'has_telegram',
            ]);
        });
    }
};
