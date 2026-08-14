<?php

namespace Tests\Feature;

use App\Livewire\MessagesBadge;
use App\Livewire\NotificationsDropdown;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The header used to render a static bell with a hardcoded "14" badge, no click
 * handler and no dropdown, plus a mail badge hardcoded to "654" — while a fully
 * implemented App\Livewire\NotificationsDropdown sat unreferenced anywhere in
 * the codebase.
 */
class HeaderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
    }

    public function test_unread_count_reflects_personal_notifications(): void
    {
        $user = $this->member();
        Notification::createForUser($user->id, 'A', 'a');
        Notification::createForUser($user->id, 'B', 'b');

        Livewire::actingAs($user)
            ->test(NotificationsDropdown::class)
            ->assertSet('isOpen', false)
            ->assertSee('A')
            ->assertSee('B');

        $this->assertSame(2, Livewire::actingAs($user)->test(NotificationsDropdown::class)->instance()->unreadCount);
    }

    public function test_marking_one_as_read_lowers_the_count(): void
    {
        $user = $this->member();
        $notification = Notification::createForUser($user->id, 'Ahoj', 'zpráva');

        Livewire::actingAs($user)
            ->test(NotificationsDropdown::class)
            ->call('markAsRead', $notification->id);

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame(0, Livewire::actingAs($user)->test(NotificationsDropdown::class)->instance()->unreadCount);
    }

    public function test_mark_all_as_read_clears_the_badge(): void
    {
        $user = $this->member();
        Notification::createForUser($user->id, 'A', 'a');
        Notification::createForUser($user->id, 'B', 'b');
        Notification::createGlobal('Global', 'g');

        Livewire::actingAs($user)
            ->test(NotificationsDropdown::class)
            ->call('markAllAsRead');

        $this->assertSame(0, Livewire::actingAs($user)->test(NotificationsDropdown::class)->instance()->unreadCount);
    }

    /**
     * A global notification is one shared row. Reading it must be recorded per
     * user, otherwise the first reader marks it read for the whole platform.
     * The dropdown template previously rendered `!$notification->read_at`,
     * which did exactly that.
     */
    public function test_reading_a_global_notification_does_not_affect_other_users(): void
    {
        $reader = $this->member();
        $other = $this->member();
        $global = Notification::createGlobal('Údržba', 'V neděli od 2:00');

        Livewire::actingAs($reader)
            ->test(NotificationsDropdown::class)
            ->call('markAsRead', $global->id);

        $this->assertNull($global->fresh()->read_at, 'The shared row must not be mutated.');
        $this->assertTrue($global->fresh()->isReadBy($reader->id));
        $this->assertFalse($global->fresh()->isReadBy($other->id));
        $this->assertSame(1, Livewire::actingAs($other)->test(NotificationsDropdown::class)->instance()->unreadCount);
    }

    public function test_archiving_a_global_notification_only_hides_it_for_that_user(): void
    {
        $archiver = $this->member();
        $other = $this->member();
        $global = Notification::createGlobal('Novinka', 'text');

        Livewire::actingAs($archiver)
            ->test(NotificationsDropdown::class)
            ->call('archive', $global->id);

        $this->assertNull($global->fresh()->archived_at);
        Livewire::actingAs($archiver)->test(NotificationsDropdown::class)->assertDontSee('Novinka');
        Livewire::actingAs($other)->test(NotificationsDropdown::class)->assertSee('Novinka');
    }

    public function test_a_user_cannot_archive_someone_elses_personal_notification(): void
    {
        $owner = $this->member();
        $intruder = $this->member();
        $notification = Notification::createForUser($owner->id, 'Soukromé', 'text');

        Livewire::actingAs($intruder)
            ->test(NotificationsDropdown::class)
            ->call('archive', $notification->id);

        $this->assertNull($notification->fresh()->archived_at);
    }

    public function test_messages_badge_shows_the_real_unread_count(): void
    {
        $user = $this->member();
        $sender = $this->member();

        Message::create(['from_user_id' => $sender->id, 'to_user_id' => $user->id, 'message' => 'ahoj']);
        Message::create(['from_user_id' => $sender->id, 'to_user_id' => $user->id, 'message' => 'jsi tam?']);
        Message::create(['from_user_id' => $sender->id, 'to_user_id' => $user->id, 'message' => 'přečteno', 'read_at' => now()]);

        $badge = Livewire::actingAs($user)->test(MessagesBadge::class);

        $this->assertSame(2, $badge->instance()->unreadCount);
        $badge->assertDontSee('654');
    }

    public function test_messages_badge_is_zero_for_a_fresh_account(): void
    {
        $this->assertSame(0, Livewire::actingAs($this->member())->test(MessagesBadge::class)->instance()->unreadCount);
    }

    /**
     * The whole point of the fix: the header must render the working component,
     * not the old static markup.
     */
    public function test_header_renders_the_live_components_not_hardcoded_badges(): void
    {
        $response = $this->actingAs($this->member())->get('/');

        $response->assertOk();
        $response->assertSeeLivewire(NotificationsDropdown::class);
        $response->assertSeeLivewire(MessagesBadge::class);
        $response->assertDontSee('>14</div>', false);
        $response->assertDontSee('>654</div>', false);
    }
}
