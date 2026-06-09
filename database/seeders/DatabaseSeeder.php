<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (Filament panel) ------------------------------------------------
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        // A demo student --------------------------------------------------------
        User::factory()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'role' => UserRole::Student,
        ]);

        // Categories ------------------------------------------------------------
        $categories = collect([
            'Programming', 'Design', 'Business', 'Languages',
        ])->map(fn (string $name) => Category::factory()->create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
        ]));

        // Courses with lessons --------------------------------------------------
        $categories->each(function (Category $category) {
            Course::factory()
                ->count(3)
                ->for($category)
                ->create()
                ->each(function (Course $course) {
                    // First lesson is always a free preview.
                    Lesson::factory()->preview()->create([
                        'course_id' => $course->id,
                        'section' => 'Introduction',
                        'title' => 'Welcome & Course Overview',
                        'order' => 1,
                    ]);

                    // Remaining lessons.
                    Lesson::factory()->count(5)->sequence(
                        fn ($sequence) => ['order' => $sequence->index + 2],
                    )->create(['course_id' => $course->id]);
                });
        });
    }
}
