<?php

namespace App\Models;

use App\Enums\BookStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'author',
        'description',
        'cover',
        'file',
        'sample',
        'price',
        'is_free',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_free' => 'boolean',
            'status' => BookStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Book $book) {
            if (blank($book->slug)) {
                $book->slug = Str::slug($book->title);
            }
            // Keep price/is_free consistent.
            if ($book->is_free) {
                $book->price = 0;
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

    // Scopes ----------------------------------------------------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', BookStatus::Published);
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
                ->orWhere('author', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // Helpers ---------------------------------------------------------------

    public function isPublished(): bool
    {
        return $this->status === BookStatus::Published;
    }

    /**
     * The file a visitor is allowed to read/download for free:
     * the full book for free titles, otherwise the sample preview.
     * The full file of a paid book is never exposed publicly.
     */
    public function accessibleFilePath(): ?string
    {
        return $this->is_free ? $this->file : $this->sample;
    }

    public function hasAccessibleFile(): bool
    {
        return filled($this->accessibleFilePath());
    }
}
