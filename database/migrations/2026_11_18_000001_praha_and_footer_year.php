<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Tabulka měst pochází z worldcities (anglické názvy): nová dívka si vybrala „Prague",
// karta pak ukazovala „Prague", zatímco starší profily mají „Praha". Sjednotit na český název. Zároveň rok v patičce.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cities')->where('country_code', 'CZ')->where('name', 'Prague')->update(['name' => 'Praha']);
        DB::table('profiles')->where('city', 'Prague')->update(['city' => 'Praha']);

        // Rok v patičce: soubor jazyka má teď „© :year …“; přepsat i neupravenou kopii textu v databázi
        // (texty jdou měnit v adminu, vlastní úpravy nechat být).
        DB::table('translations')->where('group', 'front')->where('key', 'footer.copyright')
            ->where('value', '© 2025 ZasukejSi.cz')->update(['value' => '© :year ZasukejSi.cz']);
    }

    public function down(): void
    {
        // Záměrně bez návratu: „Praha" je správný název, starý stav nechceme obnovit.
    }
};
