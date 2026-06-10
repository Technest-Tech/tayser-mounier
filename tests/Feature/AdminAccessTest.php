<?php

namespace Tests\Feature;

use App\Filament\Resources\AccessCodeResource\Pages\ListAccessCodes;
use App\Models\AccessCode;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reach_the_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertSuccessful();
    }

    public function test_student_is_forbidden_from_the_panel(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/admin')->assertForbidden();
    }

    public function test_resource_pages_render_for_admins(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/courses')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/categories')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/access-codes')->assertSuccessful();
    }

    public function test_admin_can_generate_a_batch_of_codes(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->paid()->create();

        Livewire::actingAs($admin)
            ->test(ListAccessCodes::class)
            ->callTableAction('generate', data: [
                'course_id' => $course->id,
                'quantity' => 7,
                'expires_at' => null,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame(7, AccessCode::where('course_id', $course->id)->count());
    }
}
