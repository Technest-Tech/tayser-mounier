<?php

namespace Database\Factories;

use App\Enums\LessonSource;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        $source = fake()->randomElement(LessonSource::cases());

        return [
            'course_id' => Course::factory(),
            'section' => fake()->randomElement(['Introduction', 'Core Concepts', 'Advanced']),
            'title' => Str::title(fake()->words(4, true)),
            'source' => $source,
            // A real Bunny GUID or YouTube id; placeholders for demo data.
            'video_id' => $source === LessonSource::Youtube
                ? 'dQw4w9WgXcQ'
                : (string) Str::uuid(),
            'duration' => fake()->numberBetween(120, 1800),
            'is_preview' => false,
            'order' => 0,
        ];
    }

    public function preview(): static
    {
        return $this->state(fn () => ['is_preview' => true]);
    }

    public function youtube(): static
    {
        return $this->state(fn () => [
            'source' => LessonSource::Youtube,
            'video_id' => 'dQw4w9WgXcQ',
        ]);
    }

    public function bunny(): static
    {
        return $this->state(fn () => [
            'source' => LessonSource::Bunny,
            'video_id' => (string) Str::uuid(),
        ]);
    }
}
