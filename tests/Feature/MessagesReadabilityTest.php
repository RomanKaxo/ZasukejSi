<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The inbox showed a name, a timestamp and an unread badge — never a word of
 * what anyone wrote. In the thread the sender's own bubble carried
 * `bg-primary text-white`; `bg-primary` is not a generated utility in this
 * project, so it was white text on white.
 */
class MessagesReadabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function user(string $name, string $gender = 'female'): User
    {
        return User::factory()->create(['name' => $name, 'gender' => $gender]);
    }

    public function test_the_inbox_shows_the_last_message(): void
    {
        $me = $this->user('Petr', 'male');
        $her = $this->user('Jana');

        Message::create([
            'from_user_id' => $her->id,
            'to_user_id' => $me->id,
            'message' => 'Dobrý den, ozvěte se prosím večer.',
        ]);

        $response = $this->actingAs($me)->get(route('messages.index'));

        $response->assertSuccessful();
        $response->assertSee('Jana');
        $response->assertSee('Dobrý den, ozvěte se prosím večer.');
    }

    public function test_the_inbox_marks_a_preview_of_my_own_message(): void
    {
        $me = $this->user('Petr', 'male');
        $her = $this->user('Jana');

        Message::create([
            'from_user_id' => $her->id,
            'to_user_id' => $me->id,
            'message' => 'První zpráva od ní.',
        ]);
        Message::create([
            'from_user_id' => $me->id,
            'to_user_id' => $her->id,
            'message' => 'Poslední zpráva ode mě.',
        ]);

        $response = $this->actingAs($me)->get(route('messages.index'));

        // The newest message is the preview, whoever wrote it.
        $response->assertSee('Poslední zpráva ode mě.');
        $response->assertSee(__('front.messages.you_prefix'));
    }

    public function test_the_inbox_counts_unread_messages(): void
    {
        $me = $this->user('Petr', 'male');
        $her = $this->user('Jana');

        foreach (['jedna', 'dvě', 'tři'] as $text) {
            Message::create([
                'from_user_id' => $her->id,
                'to_user_id' => $me->id,
                'message' => $text,
            ]);
        }

        // My own message must not count as unread for me.
        Message::create([
            'from_user_id' => $me->id,
            'to_user_id' => $her->id,
            'message' => 'odpověď',
        ]);

        $response = $this->actingAs($me)->get(route('messages.index'));

        $response->assertSuccessful();
        $response->assertSee('msg-row-badge', false);
        $response->assertSee('>3</span>', false);
    }

    public function test_a_conversation_with_a_deleted_account_does_not_break_the_inbox(): void
    {
        $me = $this->user('Petr', 'male');
        $her = $this->user('Jana');

        Message::create([
            'from_user_id' => $her->id,
            'to_user_id' => $me->id,
            'message' => 'Zpráva od účtu, který zmizí.',
        ]);

        $her->delete();

        $this->actingAs($me)->get(route('messages.index'))->assertSuccessful();
    }

    public function test_the_thread_shows_both_sides_with_readable_bubbles(): void
    {
        $me = $this->user('Petr', 'male');
        $her = $this->user('Jana');

        Message::create([
            'from_user_id' => $her->id,
            'to_user_id' => $me->id,
            'message' => 'Text od ní.',
        ]);
        Message::create([
            'from_user_id' => $me->id,
            'to_user_id' => $her->id,
            'message' => 'Text ode mě.',
        ]);

        $response = $this->actingAs($me)->get(route('messages.show', $her));

        $response->assertSuccessful();
        $response->assertSee('Text od ní.');
        $response->assertSee('Text ode mě.');

        // Both bubble kinds carry their own colours.
        $response->assertSee('msg-bubble-mine', false);
        $response->assertSee('msg-bubble-theirs', false);
    }

    /**
     * `--color-primary` lives in `:root`, not in Tailwind's `@theme`, so
     * `bg-primary` and `text-primary` are not generated — they resolve to
     * nothing. The messages screens must not depend on them again.
     */
    public function test_the_message_views_do_not_rely_on_ungenerated_colour_classes(): void
    {
        foreach (['index', 'show'] as $view) {
            $source = file_get_contents(resource_path("views/messages/{$view}.blade.php"));

            // Only `class` attributes — the comments above explain the bug and
            // naturally name the classes that caused it.
            preg_match_all('/class="([^"]*)"/', $source, $matches);

            foreach ($matches[1] as $classList) {
                $this->assertDoesNotMatchRegularExpression(
                    '/\b(bg|text|border)-primary\b(?!-)/',
                    $classList,
                    "messages/{$view}.blade.php se spoléhá na nevygenerovanou třídu."
                );
            }
        }
    }

    public function test_the_thread_says_whether_my_message_was_read(): void
    {
        $me = $this->user('Petr', 'male');
        $her = $this->user('Jana');

        Message::create([
            'from_user_id' => $me->id,
            'to_user_id' => $her->id,
            'message' => 'Nepřečtená.',
        ]);

        $this->actingAs($me)->get(route('messages.show', $her))
            ->assertSee(__('front.messages.delivered'));

        Message::query()->update(['read_at' => now()]);

        $this->actingAs($me)->get(route('messages.show', $her))
            ->assertSee(__('front.messages.read'));
    }

    /**
     * Messages written in the same second used to come back in whatever order
     * the database felt like, so a reply could appear above what it answered.
     */
    public function test_the_thread_is_ordered_even_within_one_second(): void
    {
        $me = $this->user('Petr', 'male');
        $her = $this->user('Jana');

        $at = now();

        foreach (['první', 'druhá', 'třetí'] as $text) {
            Message::create([
                'from_user_id' => $her->id,
                'to_user_id' => $me->id,
                'message' => $text,
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }

        $html = $this->actingAs($me)->get(route('messages.show', $her))->getContent();

        $this->assertLessThan(strpos($html, 'druhá'), strpos($html, 'první'));
        $this->assertLessThan(strpos($html, 'třetí'), strpos($html, 'druhá'));
    }

    public function test_the_thread_links_to_the_other_persons_profile(): void
    {
        $me = $this->user('Petr', 'male');
        $her = $this->user('Jana');
        $profile = Profile::factory()->create(['user_id' => $her->id]);

        Message::create([
            'from_user_id' => $her->id,
            'to_user_id' => $me->id,
            'message' => 'Ahoj.',
        ]);

        $this->actingAs($me)->get(route('messages.show', $her))
            ->assertSee(route('profiles.show', $profile), false);
    }

    public function test_an_empty_inbox_still_renders(): void
    {
        $me = $this->user('Petr', 'male');

        $this->actingAs($me)->get(route('messages.index'))
            ->assertSuccessful()
            ->assertSee(__('front.messages.no_messages_yet'));
    }
}
