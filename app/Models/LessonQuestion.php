<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'question',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(LessonQuestionOption::class, 'question_id')->orderBy('order');
    }

    public function correctOption(): ?LessonQuestionOption
    {
        return $this->options->firstWhere('is_correct', true);
    }
}
