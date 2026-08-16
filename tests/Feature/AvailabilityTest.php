<?php

namespace Tests\Feature;

use App\Support\Availability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Opening hours have been written in three shapes by three different screens.
 * Reading has to cope with all of them and produce one — a range per named day.
 */
class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_canonical_shape_passes_through(): void
    {
        $value = [
            'always_online' => false,
            'schedule' => ['monday' => ['from' => '9:00', 'to' => '17:00']],
        ];

        $this->assertSame($value, Availability::normalize($value));
    }

    public function test_a_day_to_hours_map_becomes_a_schedule(): void
    {
        $normalized = Availability::normalize(['Monday' => '9:00-17:00']);

        $this->assertSame(
            ['monday' => ['from' => '9:00', 'to' => '17:00']],
            $normalized['schedule']
        );
    }

    public function test_a_list_of_czech_strings_becomes_a_schedule(): void
    {
        $normalized = Availability::normalize(['Pondělí 9:00-17:00', 'Úterý 10:00-18:00']);

        $this->assertSame([
            'monday' => ['from' => '9:00', 'to' => '17:00'],
            'tuesday' => ['from' => '10:00', 'to' => '18:00'],
        ], $normalized['schedule']);
    }

    public function test_hours_without_minutes_are_padded(): void
    {
        $normalized = Availability::normalize(['monday' => '9-17']);

        $this->assertSame(['from' => '9:00', 'to' => '17:00'], $normalized['schedule']['monday']);
    }

    public function test_the_week_comes_back_in_order(): void
    {
        $normalized = Availability::normalize([
            'friday' => '9:00-17:00',
            'monday' => '9:00-17:00',
            'wednesday' => '9:00-17:00',
        ]);

        $this->assertSame(['monday', 'wednesday', 'friday'], array_keys($normalized['schedule']));
    }

    public function test_always_online_survives_normalisation(): void
    {
        $normalized = Availability::normalize(['always_online' => true, 'schedule' => []]);

        $this->assertTrue($normalized['always_online']);
        $this->assertTrue(Availability::isAlwaysOnline(['always_online' => true]));
    }

    public function test_unrecognisable_input_yields_an_empty_schedule(): void
    {
        foreach ([null, '', [], ['nonsense'], ['nesmysl' => 'nic']] as $input) {
            $normalized = Availability::normalize($input);

            $this->assertSame([], $normalized['schedule']);
            $this->assertFalse($normalized['always_online']);
        }
    }

    public function test_a_day_without_a_usable_range_is_dropped(): void
    {
        // An empty "to" is an open-ended range, which is not a claim we should
        // publish on the provider's behalf.
        $normalized = Availability::normalize([
            'schedule' => [
                'monday' => ['from' => '9:00', 'to' => ''],
                'tuesday' => ['from' => '9:00', 'to' => '17:00'],
            ],
        ]);

        $this->assertArrayNotHasKey('monday', $normalized['schedule']);
        $this->assertArrayHasKey('tuesday', $normalized['schedule']);
    }

    // --- the member form's single text field --------------------------------

    public function test_the_schedule_survives_a_round_trip_through_text(): void
    {
        app()->setLocale('cs');

        $stored = [
            'always_online' => false,
            'schedule' => [
                'monday' => ['from' => '9:00', 'to' => '17:00'],
                'saturday' => ['from' => '12:00', 'to' => '20:00'],
            ],
        ];

        $text = Availability::toText($stored);

        $this->assertSame('Pondělí 9:00 – 17:00, Sobota 12:00 – 20:00', $text);
        $this->assertSame($stored, Availability::fromText($text, $stored));
    }

    public function test_text_never_reads_as_the_word_array(): void
    {
        app()->setLocale('cs');

        // imploding the canonical shape directly produced "Array", which is
        // what the member form used to put in the field.
        $text = Availability::toText([
            'always_online' => false,
            'schedule' => ['monday' => ['from' => '9:00', 'to' => '17:00']],
        ]);

        $this->assertStringNotContainsString('Array', $text);
    }

    public function test_saving_text_does_not_clear_always_online(): void
    {
        app()->setLocale('cs');

        // The text field cannot express "always online", so saving must carry
        // the stored value over rather than silently dropping it.
        $result = Availability::fromText('Pondělí 9:00 – 17:00', ['always_online' => true, 'schedule' => []]);

        $this->assertTrue($result['always_online']);
        $this->assertArrayHasKey('monday', $result['schedule']);
    }

    public function test_empty_text_yields_an_empty_schedule(): void
    {
        $this->assertSame([], Availability::fromText('', null)['schedule']);
        $this->assertSame([], Availability::fromText(null, null)['schedule']);
    }

    public function test_lines_read_as_a_named_day_and_a_range(): void
    {
        app()->setLocale('cs');

        $lines = Availability::lines([
            'schedule' => [
                'monday' => ['from' => '9:00', 'to' => '17:00'],
                'saturday' => ['from' => '12:00', 'to' => '20:00'],
            ],
        ]);

        $this->assertSame([
            'Pondělí' => '9:00 – 17:00',
            'Sobota' => '12:00 – 20:00',
        ], $lines);
    }
}
