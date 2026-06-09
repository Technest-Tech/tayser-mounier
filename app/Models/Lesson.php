<?php

namespace App\Models;

use App\Enums\LessonSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'section',
        'title',
        'source',
        'video_id',
        'duration',
        'is_preview',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'source' => LessonSource::class,
            'is_preview' => 'boolean',
            'duration' => 'integer',
            'order' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function isPreview(): bool
    {
        return $this->is_preview;
    }
}
