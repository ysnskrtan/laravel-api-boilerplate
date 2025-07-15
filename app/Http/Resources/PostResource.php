<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'featured_image' => $this->featured_image,
            'published_at' => $this->published_at?->toISOString(),
            'published_date' => $this->published_date,
            'reading_time' => $this->reading_time,
            'is_published' => $this->isPublished(),
            'is_draft' => $this->isDraft(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Conditional fields
            'meta_data' => $this->when(
                $request->user()?->hasRole(['admin', 'editor']) || 
                $this->user_id === $request->user()?->id,
                $this->meta_data
            ),
            
            // Relationships
            'author' => $this->when(
                $this->relationLoaded('user'),
                function () {
                    return [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                    ];
                }
            ),
            
            'user' => $this->when(
                $this->relationLoaded('user'),
                new UserResource($this->user)
            ),
            
            // URLs
            'urls' => [
                'show' => route('api.v1.posts.show', $this->slug),
                'edit' => $this->when(
                    $request->user()?->hasRole(['admin', 'editor']) || 
                    $this->user_id === $request->user()?->id,
                    route('api.v1.posts.update', $this->slug)
                ),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
} 