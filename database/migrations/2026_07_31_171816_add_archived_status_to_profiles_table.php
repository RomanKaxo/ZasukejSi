<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add the 'archived' value to the profiles.status enum.
     *
     * Only MySQL/MariaDB store this column as a native ENUM. On SQLite (used by
     * the test suite) the column is a plain TEXT field, so there is nothing to
     * alter and the statement must be skipped — `MODIFY COLUMN` is not valid
     * SQLite syntax and would abort the entire migration run.
     */
    public function up(): void
    {
        if (! $this->supportsEnumModification()) {
            return;
        }

        DB::statement("ALTER TABLE profiles MODIFY COLUMN status ENUM('draft', 'pending', 'approved', 'rejected', 'archived') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->supportsEnumModification()) {
            return;
        }

        // Archived profiles would violate the narrowed enum, so fold them back
        // into 'rejected' before shrinking the column.
        DB::table('profiles')->where('status', 'archived')->update(['status' => 'rejected']);

        DB::statement("ALTER TABLE profiles MODIFY COLUMN status ENUM('draft', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'draft'");
    }

    private function supportsEnumModification(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
