<?php

namespace Tests\Feature;

use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SegmentResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create(['email' => 'admin-segments@example.com']);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_can_create_a_segment(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Segments\Pages\CreateSegment::class)
            ->fillForm([
                'name' => 'Ověřená',
                'slug' => 'overena',
                'color' => '#00B80F',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('segments', ['slug' => 'overena']);
    }

    public function test_admin_can_edit_a_segment(): void
    {
        $admin = $this->admin();
        $segment = Segment::factory()->create(['slug' => 'top-lokalita']);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Segments\Pages\EditSegment::class, ['record' => $segment->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($segment->fresh()->is_active);
    }
}
