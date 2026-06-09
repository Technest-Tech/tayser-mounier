<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
