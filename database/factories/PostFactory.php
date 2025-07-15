<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);
        $status = fake()->randomElement(['draft', 'published', 'archived']);
        $publishedAt = $status === 'published' ? fake()->dateTimeBetween('-6 months', 'now') : null;
        
        $content = fake()->paragraphs(rand(5, 15), true);
        
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $content,
            'excerpt' => Str::limit(strip_tags($content), 150),
            'status' => $status,
            'featured_image' => fake()->boolean(60) ? fake()->imageUrl(800, 600, 'business') : null,
            'meta_data' => [
                'seo_title' => $title,
                'seo_description' => fake()->sentence(20),
                'tags' => fake()->words(rand(3, 8)),
                'category' => fake()->randomElement(['Technology', 'Business', 'Lifestyle', 'Health', 'Travel']),
                'reading_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
                'word_count' => str_word_count($content),
            ],
            'user_id' => User::factory(),
            'published_at' => $publishedAt,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'published',
                'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
            ];
        });
    }

    /**
     * Indicate that the post is a draft.
     */
    public function draft(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
                'published_at' => null,
            ];
        });
    }

    /**
     * Indicate that the post is archived.
     */
    public function archived(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'archived',
                'published_at' => fake()->dateTimeBetween('-1 year', '-6 months'),
            ];
        });
    }

    /**
     * Indicate that the post has a featured image.
     */
    public function withFeaturedImage(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'featured_image' => fake()->imageUrl(800, 600, 'business'),
            ];
        });
    }

    /**
     * Indicate that the post is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }

    /**
     * Create a post with specific category.
     */
    public function withCategory(string $category): static
    {
        return $this->state(function (array $attributes) use ($category) {
            $metaData = $attributes['meta_data'] ?? [];
            $metaData['category'] = $category;
            
            return [
                'meta_data' => $metaData,
            ];
        });
    }

    /**
     * Create a long-form post.
     */
    public function longForm(): static
    {
        return $this->state(function (array $attributes) {
            $content = fake()->paragraphs(rand(20, 35), true);
            
            return [
                'content' => $content,
                'excerpt' => Str::limit(strip_tags($content), 200),
                'meta_data' => array_merge($attributes['meta_data'] ?? [], [
                    'word_count' => str_word_count($content),
                    'reading_level' => 'advanced',
                ]),
            ];
        });
    }

    /**
     * Create a short-form post.
     */
    public function shortForm(): static
    {
        return $this->state(function (array $attributes) {
            $content = fake()->paragraphs(rand(2, 5), true);
            
            return [
                'content' => $content,
                'excerpt' => Str::limit(strip_tags($content), 100),
                'meta_data' => array_merge($attributes['meta_data'] ?? [], [
                    'word_count' => str_word_count($content),
                    'reading_level' => 'beginner',
                ]),
            ];
        });
    }

    /**
     * Create a post with custom tags.
     */
    public function withTags(array $tags): static
    {
        return $this->state(function (array $attributes) use ($tags) {
            $metaData = $attributes['meta_data'] ?? [];
            $metaData['tags'] = $tags;
            
            return [
                'meta_data' => $metaData,
            ];
        });
    }

    /**
     * Create a recent post.
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
                'updated_at' => fake()->dateTimeBetween('-1 week', 'now'),
                'published_at' => $attributes['status'] === 'published' 
                    ? fake()->dateTimeBetween('-1 month', 'now') 
                    : null,
            ];
        });
    }

    /**
     * Create a popular post (with engagement metrics).
     */
    public function popular(): static
    {
        return $this->state(function (array $attributes) {
            $metaData = $attributes['meta_data'] ?? [];
            $metaData['views'] = fake()->numberBetween(1000, 10000);
            $metaData['likes'] = fake()->numberBetween(50, 500);
            $metaData['shares'] = fake()->numberBetween(10, 100);
            
            return [
                'meta_data' => $metaData,
                'status' => 'published',
                'published_at' => fake()->dateTimeBetween('-3 months', '-1 month'),
            ];
        });
    }
} 