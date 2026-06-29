<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'price',
        'price_usd',
        'is_free',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_usd' => 'decimal:2',
            'is_free' => 'boolean',
            'status' => CourseStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Course $course) {
            if (blank($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
            // Keep price/is_free consistent.
            if ($course->is_free) {
                $course->price = 0;
                $course->price_usd = 0;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Relationships ---------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function previewLessons(): HasMany
    {
        return $this->lessons()->where('is_preview', true);
    }

    public function accessCodes(): HasMany
    {
        return $this->hasMany(AccessCode::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    // Scopes ----------------------------------------------------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', CourseStatus::Published);
    }

    public function scopeFree(Builder $query): Builder
    {
        return $query->where('is_free', true);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('is_free', false);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // Helpers ---------------------------------------------------------------

    public function isPublished(): bool
    {
        return $this->status === CourseStatus::Published;
    }

    public function totalDurationSeconds(): int
    {
        return (int) $this->lessons()->sum('duration');
    }
}
