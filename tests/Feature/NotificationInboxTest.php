<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
    }

    public function test_active_inbox_requires_an_authenticated_verified_account(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));

        $unverified = User::factory()->create(['gender' => 'male', 'email_verified_at' => null]);
        $this->actingAs($unverified)->get(route('notifications.index'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_active_inbox_shows_only_visible_unarchived_notifications(): void
    {
        $member = $this->member();
        $other = $this->member();
        Notification::createForUser($member->id, 'Own active notice', 'Personal message');
        Notification::createGlobal('Shared active notice', 'Shared message');
        Notification::createForUser($other->id, 'Foreign notice', 'Private to someone else');
        Notification::forAdmins('Admin notice', 'Operational message');
        Notification::createForUser($member->id, 'Archived personal notice', 'Old message')->archive();

        $this->actingAs($member)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Own active notice')
            ->assertSee('Shared active notice')
            ->assertDontSee('Foreign notice')
            ->assertDontSee('Admin notice')
            ->assertDontSee('Archived personal notice')
            ->assertSee(__('front.notifications.mark_read'))
            ->assertSee(route('notifications.archived'), false);
    }

    public function test_global_archive_history_uses_the_current_users_timestamp(): void
    {
        $reader = $this->member();
        $other = $this->member();
        $global = Notification::createGlobal('Shared archive history', 'Shared message');
        $at = now()->setDate(2026, 10, 13)->setTime(12, 34);
        $this->travelTo($at);

        $this->actingAs($reader)->from(route('notifications.index'))
            ->post(route('notifications.archive', $global))
            ->assertRedirect(route('notifications.index'));

        $this->assertNull($global->fresh()->archived_at);
        $this->assertDatabaseHas('notification_user_states', [
            'notification_id' => $global->id,
            'user_id' => $reader->id,
            'archived_at' => $at->format('Y-m-d H:i:s'),
        ]);
        $this->actingAs($reader)->get(route('notifications.archived'))
            ->assertOk()
            ->assertSee('Shared archive history')
            ->assertSee('13.10.2026 12:34')
            ->assertDontSee(route('notifications.delete', $global), false);
        $this->actingAs($reader)->get(route('notifications.index'))->assertDontSee('Shared archive history');
        $this->actingAs($other)->get(route('notifications.index'))->assertOk()->assertSee('Shared archive history');
        $this->actingAs($other)->get(route('notifications.archived'))
            ->assertOk()
            ->assertDontSee('data-notification-id="'.$global->id.'"', false);
    }

    public function test_notification_pages_keep_the_members_navigation(): void
    {
        $member = $this->member();
        Notification::createForUser($member->id, 'Personal history', 'Old message')->archive();

        foreach (['notifications.index', 'notifications.archived'] as $route) {
            $this->actingAs($member)->get(route($route))
                ->assertOk()
                ->assertSee(route('account.member.favorites'), false)
                ->assertDontSee(route('account.photos'), false);
        }
    }

    public function test_notification_pages_keep_the_providers_navigation(): void
    {
        $provider = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        foreach (['notifications.index', 'notifications.archived'] as $route) {
            $this->actingAs($provider)->get(route($route))
                ->assertOk()
                ->assertSee(route('account.photos'), false)
                ->assertDontSee(route('account.member.favorites'), false);
        }
    }

    public function test_reading_a_shared_notice_only_changes_the_readers_state(): void
    {
        $reader = $this->member();
        $other = $this->member();
        $global = Notification::createGlobal('Shared unread notice', 'Shared message');
        $foreign = Notification::createForUser($other->id, 'Foreign personal notice', 'Private message');

        $this->actingAs($reader)->from(route('notifications.index'))
            ->post(route('notifications.read', $global))
            ->assertRedirect(route('notifications.index'));
        $this->assertNull($global->fresh()->read_at);
        $this->assertTrue($global->fresh()->isReadBy($reader->id));
        $this->assertFalse($global->fresh()->isReadBy($other->id));

        $this->actingAs($reader)->post(route('notifications.read', $foreign))->assertForbidden();
        $this->actingAs($reader)->post(route('notifications.archive', $foreign))->assertForbidden();
        $this->assertNull($foreign->fresh()->read_at);
        $this->assertNull($foreign->fresh()->archived_at);
    }
}
