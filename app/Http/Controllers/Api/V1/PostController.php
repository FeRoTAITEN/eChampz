<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use App\Services\MentionParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostController extends BaseController
{
    protected MentionParser $mentionParser;

    public function __construct(MentionParser $mentionParser)
    {
        $this->mentionParser = $mentionParser;
    }

    /**
     * Create a new post.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string', 'max:5000'],
            'type' => ['nullable', 'in:text,image,video'],
            'game_ids' => ['nullable', 'array'],
            'game_ids.*' => ['exists:games,id'],
            'media' => ['nullable', 'array'],
            'media.*.type' => ['required_with:media', 'in:image,video'],
            'media.*.url' => ['required_with:media', 'string'],
            'media.*.thumbnail_url' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        DB::beginTransaction();
        try {
            // Create post
            $post = Post::create([
                'user_id' => $user->id,
                'content' => $request->content,
                'type' => $request->type ?? 'text',
            ]);

            // Parse and store mentions
            $this->processMentions($post, $request->content);

            // Attach games
            if ($request->has('game_ids')) {
                $post->games()->attach($request->game_ids);
            }

            // Store media
            if ($request->has('media')) {
                foreach ($request->media as $index => $mediaItem) {
                    $post->media()->create([
                        'type' => $mediaItem['type'],
                        'url' => $mediaItem['url'],
                        'thumbnail_url' => $mediaItem['thumbnail_url'] ?? null,
                        'order' => $index,
                    ]);
                }
            }

            DB::commit();

            // Load relationships for response
            $post->load(['user', 'mentions.user', 'media', 'games']);

            return $this->createdResponse(
                $this->formatPostResponse($post),
                'Post created successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single post.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $post = Post::with(['user', 'mentions.user', 'media', 'games'])
            ->findOrFail($id);

        // Increment views
        $post->increment('views');

        return $this->successResponse($this->formatPostResponse($post));
    }

    /**
     * Get posts feed (global or following).
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => ['nullable', 'in:global,following'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $type = $request->input('type', 'global');
        $perPage = $request->input('per_page', 20);

        $query = Post::with(['user', 'mentions.user', 'media', 'games'])
            ->latest();

        // Filter by following (if implemented)
        if ($type === 'following') {
            $user = $request->user();
            // TODO: Implement following logic
            // $followingIds = $user->following()->pluck('id');
            // $query->whereIn('user_id', $followingIds);
        }

        $posts = $query->paginate($perPage);

        $formattedPosts = $posts->map(function ($post) {
            return $this->formatPostResponse($post);
        });

        return $this->successResponse([
            'posts' => $formattedPosts,
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Update post (if allowed).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $user = $request->user();

        // Check ownership
        if ($post->user_id !== $user->id) {
            return $this->forbiddenResponse('You can only edit your own posts');
        }

        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $post->update(['content' => $request->content]);

            // Re-process mentions (delete old, create new)
            $post->mentions()->delete();
            $this->processMentions($post, $request->content);

            DB::commit();

            $post->load(['user', 'mentions.user', 'media', 'games']);

            return $this->successResponse(
                $this->formatPostResponse($post),
                'Post updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete post.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $user = $request->user();

        if ($post->user_id !== $user->id) {
            return $this->forbiddenResponse('You can only delete your own posts');
        }

        $post->delete();

        return $this->successResponse(null, 'Post deleted successfully');
    }

    /**
     * Process mentions from post content.
     */
    protected function processMentions(Post $post, string $content): void
    {
        $positions = $this->mentionParser->findMentionPositions($content);
        
        if (empty($positions)) {
            return;
        }

        $usernames = array_column($positions, 'username');
        $validUsers = $this->mentionParser->validateMentions($usernames);

        foreach ($positions as $mentionData) {
            $user = $validUsers->get($mentionData['username']);
            
            if ($user) {
                $post->mentions()->create([
                    'user_id' => $user->id,
                    'position' => $mentionData['position'],
                    'length' => $mentionData['length'],
                ]);

                // TODO: Send notification to mentioned user
                // event(new UserMentioned($post, $user));
            }
        }
    }

    /**
     * Format post for API response.
     */
    protected function formatPostResponse(Post $post): array
    {
        return [
            'id' => $post->id,
            'content' => $post->content, // Raw text (for editing)
            'content_segments' => $post->content_segments, // Pre-processed for display
            'type' => $post->type,
            'views' => $post->views,
            'upvotes' => $post->upvotes,
            'downvotes' => $post->downvotes,
            'shares' => $post->shares,
            'created_at' => $post->created_at->toISOString(),
            'updated_at' => $post->updated_at->toISOString(),
            'user' => [
                'id' => $post->user->id,
                'name' => $post->user->name,
                'username' => $post->user->username,
                'avatar_url' => $post->user->avatar_url ?? null,
            ],
            'mentions' => $post->mentions->map(function ($mention) {
                return [
                    'user_id' => $mention->user_id,
                    'username' => $mention->user->username,
                    'name' => $mention->user->name,
                ];
            }),
            'media' => $post->media->map(function ($media) {
                return [
                    'id' => $media->id,
                    'type' => $media->type,
                    'url' => $media->url,
                    'thumbnail_url' => $media->thumbnail_url,
                ];
            }),
            'games' => $post->games->map(function ($game) {
                return [
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                    'icon_url' => $game->icon_url,
                ];
            }),
        ];
    }
}


