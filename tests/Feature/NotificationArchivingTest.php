<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Oznámení, na která už nikdo nezareaguje.
 *
 * Archivace byla jenom tlačítko, takže zvonek držel všechno, co komu kdy
 * přišlo. Po pár měsících to není přehled, ale skládka: ta jedna zpráva, na
 * které záleží, leží pod šedesáti vyřízenými z března.
 */
class NotificationArchivingTest extends TestCase
{
    use RefreshDatabase;

    private function notification(array $attributes = []): Notification
    {
        $user = User::factory()->create();

        $notification = Notification::create(array_merge([
            'user_id' => $user->id,
            'is_global' => false,
            'title' => 'Zpráva',
            'message' => 'Text.',
        ], $attributes));

        // created_at se dá nastavit až po vytvoření: timestampy si Eloquent
        // přepíše sám.
        if (isset($attributes['created_at'])) {
            $notification->forceFill(['created_at' => $attributes['created_at']])->save();
        }

        return $notification->fresh();
    }

    public function test_a_read_notification_is_archived_once_it_is_old_enough(): void
    {
        $old = $this->notification(['read_at' => now()->subDays(40)]);

        $this->artisan('notifications:archive')->assertSuccessful();

        $this->assertNotNull($old->fresh()->archived_at);
    }

    public function test_a_recently_read_notification_is_left_alone(): void
    {
        $fresh = $this->notification(['read_at' => now()->subDays(2)]);

        $this->artisan('notifications:archive')->assertSuccessful();

        $this->assertNull($fresh->fresh()->archived_at);
    }

    /**
     * To podstatné: nepřečtené se nearchivuje nikdy.
     *
     * Smyslem nepřečteného je, že se na to nikdo nepodíval. Uklidit ho stranou
     * by z „nikdo vám to neřekl" udělalo „řekli a vy jste to přehlédli".
     */
    public function test_an_unread_notification_is_never_archived_however_old(): void
    {
        $ancient = $this->notification(['created_at' => now()->subYear()]);

        $this->artisan('notifications:archive')->assertSuccessful();

        $this->assertNull($ancient->fresh()->archived_at);
    }

    /** Sdílené oznámení nikdo za všechny nepřečte, takže rozhoduje jen stáří. */
    public function test_a_global_notification_is_archived_by_age(): void
    {
        $global = Notification::create([
            'user_id' => null,
            'is_global' => true,
            'title' => 'Oznámení všem',
            'message' => 'Text.',
        ]);

        $global->forceFill(['created_at' => now()->subDays(100)])->save();

        $this->artisan('notifications:archive')->assertSuccessful();

        $this->assertNotNull($global->fresh()->archived_at);
    }

    public function test_a_recent_global_notification_stays(): void
    {
        $global = Notification::create([
            'user_id' => null,
            'is_global' => true,
            'title' => 'Oznámení všem',
            'message' => 'Text.',
        ]);

        $global->forceFill(['created_at' => now()->subDays(10)])->save();

        $this->artisan('notifications:archive')->assertSuccessful();

        $this->assertNull($global->fresh()->archived_at);
    }

    public function test_the_period_is_configurable(): void
    {
        Setting::set('notifications_archive_days', 5);

        $notification = $this->notification(['read_at' => now()->subDays(7)]);

        $this->artisan('notifications:archive')->assertSuccessful();

        $this->assertNotNull($notification->fresh()->archived_at);
    }

    public function test_a_dry_run_changes_nothing(): void
    {
        $old = $this->notification(['read_at' => now()->subDays(40)]);

        $this->artisan('notifications:archive', ['--dry-run' => true])->assertSuccessful();

        $this->assertNull($old->fresh()->archived_at);
    }

    /** Co je archivované, se archivovat znovu nemá. */
    public function test_an_archived_notification_keeps_its_original_date(): void
    {
        $archived = $this->notification([
            'read_at' => now()->subDays(90),
            'archived_at' => now()->subDays(60),
        ]);

        $before = $archived->archived_at;

        $this->artisan('notifications:archive')->assertSuccessful();

        $this->assertTrue($before->equalTo($archived->fresh()->archived_at));
    }
}
