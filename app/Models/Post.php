<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'featured_image',
        'meta_data',
        'user_id',
        'published_at',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'published_at' => 'datetime',
    ];

    protected $hidden = [
        'meta_data',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            if (empty($post->excerpt)) {
                $post->excerpt = Str::limit(strip_tags($post->content), 150);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
              ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include posts by a specific user.
     */
    public function scopeByUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Scope a query to search posts by title or content.
     */
    public function scopeSearch(Builder $query, string $search): void
    {
        $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Get the formatted published date.
     */
    public function getPublishedDateAttribute(): ?string
    {
        return $this->published_at?->format('M d, Y');
    }

    /**
     * Get the reading time in minutes.
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // Average reading speed
    }

    /**
     * Check if the post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               $this->published_at && 
               $this->published_at->lte(now());
    }

    /**
     * Check if the post is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Publish the post.
     */
    public function publish(): bool
    {
        return $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);
    }

    /**
     * Archive the post.
     */
    public function archive(): bool
    {
        return $this->update(['status' => 'archived']);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
} 