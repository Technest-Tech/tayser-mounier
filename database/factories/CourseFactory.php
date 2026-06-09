<?php

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(3, true));
        $isFree = fake()->boolean(30);

        return [
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'thumbnail' => null,
            'price' => $isFree ? 0 : fake()->randomElement([99, 149, 199, 299, 499]),
            'is_free' => $isFree,
            'status' => CourseStatus::Published,
        ];
    }

    public function free(): static
    {
        return $this->state(fn () => ['is_free' => true, 'price' => 0]);
    }

    public function paid(): static
    {
        return $this->state(fn () => ['is_free' => false, 'price' => 199]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => CourseStatus::Draft]);
    }
}
