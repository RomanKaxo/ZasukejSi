<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Console\Command;

/**
 * Files away notifications nobody is going to act on any more.
 *
 * Archiving existed as a button and only as a button, so the bell kept
 * everything a person had ever been sent. After a few months that list is not
 * a feed, it is a landfill: the one message that matters sits under sixty that
 * were dealt with in March.
 *
 * Two rules, and the second is the important one.
 *
 * Read notifications are archived once they have had time to matter. An
 * **unread** one is never touched however old it is — the whole point of unread
 * is that nobody has looked, and quietly filing it away would turn „you were
 * not told" into „you were told and missed it".
 *
 * Global notifications are the exception to the exception: they are one shared
 * row for everybody, so „read" has no single answer. Those are archived purely
 * on age, and generously — an announcement from two months ago is stale for
 * every reader, including the ones who never opened it.
 */
class ArchiveNotificationsCommand extends Command
{
    protected $signature = 'notifications:archive
        {--days= : Override the configured age in days}
        {--dry-run : Report what would be archived, change nothing}';

    protected $description = 'Archive read notifications, and global ones, once they are old enough';

    /** Používá se, když v nastavení nic není. */
    public const DEFAULT_DAYS = 30;

    /** Sdílené oznámení má delší život: nikdo za všechny nerozhodne, že je přečtené. */
    public const GLOBAL_MULTIPLIER = 2;

    public function handle(): int
    {
        $days = $this->days();
        $dryRun = (bool) $this->option('dry-run');

        $read = Notification::query()
            ->whereNull('archived_at')
            ->where('is_global', false)
            ->whereNotNull('read_at')
            ->where('read_at', '<=', now()->subDays($days));

        $stale = Notification::query()
            ->whereNull('archived_at')
            ->where('is_global', true)
            ->where('created_at', '<=', now()->subDays($days * self::GLOBAL_MULTIPLIER));

        if ($dryRun) {
            $this->line(sprintf(
                'Archivovalo by se: %d přečtených starších %d dnů, %d sdílených starších %d dnů.',
                $read->count(),
                $days,
                $stale->count(),
                $days * self::GLOBAL_MULTIPLIER,
            ));

            return self::SUCCESS;
        }

        $archivedRead = $read->update(['archived_at' => now()]);
        $archivedGlobal = $stale->update(['archived_at' => now()]);

        $this->info(sprintf(
            'Archivováno: %d přečtených, %d sdílených. Nepřečtená zůstala.',
            $archivedRead,
            $archivedGlobal,
        ));

        return self::SUCCESS;
    }

    private function days(): int
    {
        if ($this->option('days') !== null) {
            return max(1, (int) $this->option('days'));
        }

        return max(1, (int) Setting::get('notifications_archive_days', self::DEFAULT_DAYS));
    }
}
