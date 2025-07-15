<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\ApiController;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class PostController extends ApiController
{
    /**
     * Display a listing of posts with advanced querying.
     */
    public function index(Request $request): JsonResponse
    {
        $posts = QueryBuilder::for(Post::class)
            ->with('user:id,name,email')
            ->allowedFilters([
                'title',
                'status',
                AllowedFilter::exact('user_id'),
                AllowedFilter::scope('search'),
                AllowedFilter::scope('published'),
                AllowedFilter::scope('draft'),
            ])
            ->allowedSorts([
                'title',
                'status',
                'published_at',
                'created_at',
                'updated_at',
                AllowedSort::field('author', 'users.name'),
            ])
            ->allowedIncludes(['user'])
            ->defaultSort('-created_at')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse(
            PostResource::collection($posts)->response()->getData(true),
            'Posts retrieved successfully'
        );
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'featured_image' => 'nullable|string|max:255',
            'meta_data' => 'nullable|array',
            'published_at' => 'nullable|date|after_or_equal:now',
        ]);

        $validated['user_id'] = auth()->id();

        // Auto-set published_at if status is published and no date provided
        if ($validated['status'] === 'published' && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = Post::create($validated);
        $post->load('user:id,name,email');

        return $this->successResponse(
            new PostResource($post),
            'Post created successfully',
            201
        );
    }

    /**
     * Display the specified post.
     */
    public function show(string $slug): JsonResponse
    {
        $post = QueryBuilder::for(Post::class)
            ->with('user:id,name,email')
            ->allowedIncludes(['user'])
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->successResponse(
            new PostResource($post),
            'Post retrieved successfully'
        );
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        // Check if user owns the post or is admin
        if ($post->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => ['sometimes', 'required', Rule::in(['draft', 'published', 'archived'])],
            'featured_image' => 'nullable|string|max:255',
            'meta_data' => 'nullable|array',
            'published_at' => 'nullable|date',
        ]);

        // Auto-set published_at if status is changed to published
        if (isset($validated['status']) && $validated['status'] === 'published' && !$post->published_at) {
            $validated['published_at'] = now();
        }

        $post->update($validated);
        $post->load('user:id,name,email');

        return $this->successResponse(
            new PostResource($post),
            'Post updated successfully'
        );
    }

    /**
     * Remove the specified post.
     */
    public function destroy(string $slug): JsonResponse
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        // Check if user owns the post or is admin
        if ($post->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $post->delete();

        return $this->successResponse(
            null,
            'Post deleted successfully'
        );
    }

    /**
     * Publish a post.
     */
    public function publish(string $slug): JsonResponse
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        // Check if user owns the post or is admin
        if ($post->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($post->publish()) {
            $post->load('user:id,name,email');
            
            return $this->successResponse(
                new PostResource($post),
                'Post published successfully'
            );
        }

        return $this->errorResponse('Failed to publish post', 500);
    }

    /**
     * Archive a post.
     */
    public function archive(string $slug): JsonResponse
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        // Check if user owns the post or is admin
        if ($post->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($post->archive()) {
            $post->load('user:id,name,email');
            
            return $this->successResponse(
                new PostResource($post),
                'Post archived successfully'
            );
        }

        return $this->errorResponse('Failed to archive post', 500);
    }

    /**
     * Get posts by the authenticated user.
     */
    public function myPosts(Request $request): JsonResponse
    {
        $posts = QueryBuilder::for(Post::class)
            ->with('user:id,name,email')
            ->where('user_id', auth()->id())
            ->allowedFilters([
                'title',
                'status',
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts([
                'title',
                'status',
                'published_at',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse(
            PostResource::collection($posts)->response()->getData(true),
            'Your posts retrieved successfully'
        );
    }

    /**
     * Get published posts only.
     */
    public function published(Request $request): JsonResponse
    {
        $posts = QueryBuilder::for(Post::class)
            ->with('user:id,name,email')
            ->published()
            ->allowedFilters([
                'title',
                AllowedFilter::exact('user_id'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts([
                'title',
                'published_at',
                'created_at',
                AllowedSort::field('author', 'users.name'),
            ])
            ->allowedIncludes(['user'])
            ->defaultSort('-published_at')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse(
            PostResource::collection($posts)->response()->getData(true),
            'Published posts retrieved successfully'
        );
    }
} 