<?php

namespace Tests\Feature;

use App\Livewire\MemberRatings;
use App\Livewire\ReportProfileModal;
use App\Models\City;
use App\Models\MemberSubscription;
use App\Models\PaymentMethod;
use App\Models\Profile;
use App\Models\ProfileReport;
use App\Models\Rating;
use App\Models\SubscriptionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MemberAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CurrencySeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function member(): User
    {
        return User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
    }

    private function profile(): Profile
    {
        return Profile::factory()->for(User::factory()->create(['gender' => 'female']))
            ->approved()->public()->create(['display_name' => ['cs' => 'Member audit profile', 'en' => 'Member audit profile']]);
    }

    private function pageText(User $member): string
    {
        $html = $this->actingAs($member)->get('/account/member/ratings')->assertOk()->getContent();

        return preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html)));
    }

    public function test_signed_in_report_submitted_from_real_modal_is_in_own_member_history(): void
    {
        $member = $this->member();
        $profile = $this->profile();
        Livewire::actingAs($member)->test(ReportProfileModal::class)
            ->call('show', $profile->id)
            ->set('email', $member->email)
            ->set('message', 'Own signed-in report from the public profile modal')
            ->call('submit')->assertHasNoErrors()->assertSet('submitted', true);

        $this->actingAs($member)->get('/account/member/reported')->assertOk()
            ->assertSee('Member audit profile')
            ->assertSee('Own signed-in report from the public profile modal');
    }

    public function test_expired_member_cannot_read_community_percentages_from_member_rating_history(): void
    {
        $member = $this->member();
        $profile = $this->profile();
        Rating::factory()->create(['profile_id' => $profile->id, 'user_id' => $this->member()->id, 'percentage' => 100]);
        $this->assertFalse($member->canSeeRatings());
        $this->assertStringNotContainsString('Ostatní: 100 %', $this->pageText($member));
    }

    public function test_other_users_rating_aggregate_excludes_current_members_vote(): void
    {
        $member = $this->member();
        $profile = $this->profile();
        $plan = SubscriptionType::create(['name' => ['cs' => 'Audit premium', 'en' => 'Audit premium'], 'slug' => 'audit-member', 'audience' => 'member', 'price' => 299, 'duration_days' => 30, 'is_active' => true]);
        MemberSubscription::create(['user_id' => $member->id, 'subscription_type_id' => $plan->id, 'status' => 'active', 'starts_at' => now(), 'ends_at' => now()->addDays(30)]);
        Rating::factory()->create(['profile_id' => $profile->id, 'user_id' => $member->id, 'percentage' => 30]);
        Rating::factory()->create(['profile_id' => $profile->id, 'user_id' => $this->member()->id, 'percentage' => 100]);
        $text = $this->pageText($member);
        $this->assertStringContainsString('Ty: 30 %', $text);
        $this->assertStringContainsString('Ostatní: 100 %', $text);
    }

    public function test_report_ownership_uses_authentication_and_survives_an_email_change(): void
    {
        $member = $this->member();
        $profile = $this->profile();
        Livewire::actingAs($member)->test(ReportProfileModal::class)
            ->call('show', $profile->id)
            ->set('email', 'different-contact@example.test')
            ->set('message', 'Authenticated report with a different contact address')
            ->call('submit')->assertHasNoErrors();
        $this->assertDatabaseHas('profile_reports', ['reporter_id' => $member->id, 'email' => 'different-contact@example.test']);
        $member->update(['email' => 'changed-member@example.test']);
        $this->actingAs($member)->get('/account/member/reported')->assertOk()
            ->assertSee('Authenticated report with a different contact address');
    }

    public function test_an_anonymous_report_cannot_claim_member_history_by_contact_email(): void
    {
        $member = $this->member();
        ProfileReport::create(['profile_id' => $this->profile()->id, 'email' => $member->email, 'message' => 'Anonymous unowned report']);
        $this->actingAs($member)->get('/account/member/reported')->assertOk()->assertDontSee('Anonymous unowned report');
    }

    public function test_region_filters_selection_history_and_url_restoration(): void
    {
        $member = $this->member();
        City::create(['name' => 'Ostrava', 'name_ascii' => 'Ostrava', 'country_code' => 'CZ', 'admin_name' => 'Moravskoslezský Kraj']);
        $prague = $this->profile();
        $prague->update(['city' => 'Praha', 'country_code' => 'CZ', 'display_name' => ['cs' => 'Prague member profile']]);
        $ostrava = $this->profile();
        $ostrava->update(['city' => 'Ostrava', 'country_code' => 'CZ', 'display_name' => ['cs' => 'Ostrava member profile']]);
        $abroad = $this->profile();
        $abroad->update(['city' => 'Ostrava', 'country_code' => 'SK', 'display_name' => ['cs' => 'Other country profile']]);

        $component = Livewire::actingAs($member)->withQueryParams(['kraj' => 'moravskoslezsky'])->test(MemberRatings::class)
            ->assertSet('region', 'moravskoslezsky')->assertSet('selectedProfileId', $ostrava->id)
            ->assertSee('Ostrava member profile')->assertDontSee('Prague member profile')->assertDontSee('Other country profile');
        $this->assertSame([$ostrava->id], $component->instance()->getAvailableProfiles()->pluck('id')->all());
        $component->set('region', 'hlavni-mesto-praha')->assertSet('selectedProfileId', $prague->id)
            ->assertSee('Prague member profile')->assertDontSee('Ostrava member profile');
        $component->set('region', 'unknown-region')->assertSet('region', '');
    }

    public function test_archive_keeps_private_profiles_hidden_and_has_no_dead_detail_link(): void
    {
        $public = $this->profile();
        $public->update(['status' => 'archived', 'display_name' => ['cs' => 'Public archived audit profile']]);
        $private = $this->profile();
        $private->update(['status' => 'archived', 'is_public' => false, 'display_name' => ['cs' => 'Private archived audit profile']]);

        $this->actingAs($this->member())->get('/account/member/archive')->assertOk()
            ->assertSee('Public archived audit profile')->assertDontSee('Private archived audit profile')
            ->assertDontSee('href="'.route('profiles.show', $public).'"', false);
        $this->get(route('profiles.show', $private))->assertNotFound();
    }

    public function test_granted_query_does_not_claim_a_missing_or_expired_membership(): void
    {
        $member = $this->member();
        $this->actingAs($member)->get('/account/member/membership/success?granted=1')->assertOk()
            ->assertViewHas('state', 'unverified');
        $plan = SubscriptionType::create(['name' => ['cs' => 'Manual audit plan'], 'slug' => 'manual-audit', 'audience' => 'member', 'price' => 299, 'duration_days' => 30, 'is_active' => true]);
        MemberSubscription::create(['user_id' => $member->id, 'subscription_type_id' => $plan->id, 'status' => 'active', 'starts_at' => now()->subDays(40), 'ends_at' => now()->subDays(10), 'metadata' => ['source' => 'manual', 'paid' => false]]);
        $this->get('/account/member/membership/success?granted=1')->assertOk()->assertViewHas('state', 'unverified');
    }

    public function test_disabled_stripe_with_retained_credentials_makes_no_gateway_request(): void
    {
        config(['services.stripe.secret' => null]);
        $gateway = $this->createMock(\Stripe\HttpClient\ClientInterface::class);
        $gateway->expects($this->never())->method('request');
        \Stripe\ApiRequestor::setHttpClient($gateway);
        try {
            PaymentMethod::create(['code' => 'stripe', 'is_enabled' => false, 'settings' => ['secret_key' => 'sk_test_DISABLED_AUDIT_ONLY']]);
            $plan = SubscriptionType::create(['name' => ['cs' => 'Audit plan'], 'slug' => 'disabled-gateway-audit', 'audience' => 'member', 'price' => 299, 'price_czk' => 299, 'duration_days' => 30, 'is_active' => true]);
            $this->actingAs($this->member())->from('/account/member/membership')
                ->post(route('account.member.membership.checkout', $plan))
                ->assertRedirect('/account/member/membership')->assertSessionHas('error', __('front.membership.payments_unavailable'));
            $this->assertDatabaseCount('member_subscriptions', 0);
        } finally {
            \Stripe\ApiRequestor::setHttpClient(\Stripe\HttpClient\CurlClient::instance());
        }
    }

    public function test_archive_and_reported_case_do_not_request_inaccessible_profile_photos(): void
    {
        Storage::fake('profile-media');
        Queue::fake();
        $member = $this->member();
        $profile = $this->profile();
        $profile->update(['status' => 'archived']);
        $media = $profile->addMedia(UploadedFile::fake()->image('archived-member-audit.jpg'))->toMediaCollection('profile-images');
        $url = $media->getUrl();
        $this->actingAs($member)->get($url)->assertNotFound();
        $this->get('/account/member/archive')->assertOk()->assertDontSee($url, false);
        ProfileReport::create(['profile_id' => $profile->id, 'reporter_id' => $member->id, 'email' => $member->email, 'message' => 'A real report for an archived profile with protected photos']);
        $this->get('/account/member/reported')->assertOk()
            ->assertSee('A real report for an archived profile with protected photos')->assertDontSee($url, false);
        $this->actingAs($profile->user)->get($url)->assertOk();
    }
}
