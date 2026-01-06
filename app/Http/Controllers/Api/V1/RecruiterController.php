<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Models\Game;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecruiterController extends BaseController
{
    /**
     * Search gamers by Game, Rank, or Region.
     * 
     * Query parameters:
     * - game_id: Filter by game ID
     * - rank: Filter by rank (bronze, silver, gold)
     * - region: Filter by region (optional, if region field exists)
     * - per_page: Number of results per page (default: 20)
     * - page: Page number (default: 1)
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'game_id' => ['sometimes', 'integer', 'exists:games,id'],
            'rank' => ['sometimes', 'string', 'in:bronze,silver,gold'],
            'region' => ['sometimes', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $perPage = $request->get('per_page', 20);
        $gameId = $request->get('game_id');
        $rank = $request->get('rank');
        $region = $request->get('region');

        // Start with gamers only
        $query = User::where('role', UserRole::GAMER)
            ->whereNotNull('email_verified_at')
            ->whereNotNull('onboarding_completed_at');

        // Filter by game (users who have favorited this game)
        if ($gameId) {
            $query->whereHas('favoriteGames', function ($gameQuery) use ($gameId) {
                $gameQuery->where('games.id', $gameId);
            });
        }

        // Filter by rank
        if ($rank) {
            $rankModel = Rank::where('name', $rank)->first();
            if ($rankModel) {
                $query->where('xp_total', '>=', $rankModel->min_xp);
                if ($rankModel->max_xp !== null) {
                    $query->where('xp_total', '<=', $rankModel->max_xp);
                }
            }
        }

        // Filter by region (if region field exists in users table)
        // For now, this is a placeholder - you can add region field via migration if needed
        if ($region) {
            // Uncomment when region field is added:
            // $query->where('region', $region);
        }

        // Get results with pagination
        $gamers = $query->select([
            'id',
            'name',
            'username',
            'avatar',
            'xp_total',
            'email_verified_at',
            'onboarding_completed_at',
        ])
        ->with(['favoriteGames:id,name,icon_url'])
        ->orderBy('xp_total', 'desc')
        ->paginate($perPage);

        // Append rank to each gamer
        $gamers->getCollection()->transform(function ($gamer) {
            return [
                'id' => $gamer->id,
                'name' => $gamer->name,
                'username' => $gamer->username,
                'avatar_url' => $gamer->avatar_url,
                'xp_total' => $gamer->xp_total,
                'rank' => $gamer->rank,
                'favorite_games' => $gamer->favoriteGames->map(function ($game) {
                    return [
                        'id' => $game->id,
                        'name' => $game->name,
                        'icon_url' => $game->icon_url,
                    ];
                }),
            ];
        });

        return $this->successResponse($gamers, 'Gamers retrieved successfully');
    }

    /**
     * Get gamer cards with basic info and XP.
     * 
     * Query parameters:
     * - per_page: Number of results per page (default: 20)
     * - page: Page number (default: 1)
     */
    public function gamerCards(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $perPage = $request->get('per_page', 20);

        $gamers = User::where('role', UserRole::GAMER)
            ->whereNotNull('email_verified_at')
            ->whereNotNull('onboarding_completed_at')
            ->select([
                'id',
                'name',
                'username',
                'avatar',
                'xp_total',
                'email_verified_at',
                'onboarding_completed_at',
            ])
            ->orderBy('xp_total', 'desc')
            ->paginate($perPage);

        // Transform to gamer cards format
        $gamers->getCollection()->transform(function ($gamer) {
            return [
                'id' => $gamer->id,
                'name' => $gamer->name,
                'username' => $gamer->username,
                'avatar_url' => $gamer->avatar_url,
                'xp_total' => $gamer->xp_total,
                'rank' => $gamer->rank,
            ];
        });

        return $this->successResponse($gamers, 'Gamer cards retrieved successfully');
    }

    /**
     * View full gamer profile.
     */
    public function gamerProfile(Request $request, int $gamerId): JsonResponse
    {
        $gamer = User::where('id', $gamerId)
            ->where('role', UserRole::GAMER)
            ->whereNotNull('email_verified_at')
            ->whereNotNull('onboarding_completed_at')
            ->with([
                'favoriteGames:id,name,icon_url,slug',
                'platformGames' => function ($query) {
                    $query->select([
                        'id',
                        'user_id',
                        'platform_game_id',
                        'total_playtime_minutes',
                        'trophies_total',
                        'trophies_earned',
                        'trophy_progress_percentage',
                        'last_played_at',
                    ])
                    ->with(['platformGame:id,name,icon_url,platform'])
                    ->orderBy('total_playtime_minutes', 'desc')
                    ->limit(10); // Top 10 games by playtime
                },
                'posts' => function ($query) {
                    $query->select(['id', 'user_id', 'content', 'type', 'created_at'])
                        ->orderBy('created_at', 'desc')
                        ->limit(5); // Recent 5 posts
                },
            ])
            ->first();

        if (!$gamer) {
            return $this->notFoundResponse('Gamer not found');
        }

        // Build full profile response
        $profile = [
            'id' => $gamer->id,
            'name' => $gamer->name,
            'username' => $gamer->username,
            'avatar_url' => $gamer->avatar_url,
            'xp_total' => $gamer->xp_total,
            'rank' => $gamer->rank,
            'date_of_birth' => $gamer->date_of_birth?->format('Y-m-d'),
            'represent_type' => $gamer->represent_type,
            'organization_name' => $gamer->organization_name,
            'position' => $gamer->position,
            'onboarding_completed_at' => $gamer->onboarding_completed_at?->toIso8601String(),
            'favorite_games' => $gamer->favoriteGames->map(function ($game) {
                return [
                    'id' => $game->id,
                    'name' => $game->name,
                    'icon_url' => $game->icon_url,
                    'slug' => $game->slug,
                ];
            }),
            'platform_games' => $gamer->platformGames->map(function ($userGame) {
                return [
                    'id' => $userGame->id,
                    'game_name' => $userGame->platformGame?->name,
                    'game_icon_url' => $userGame->platformGame?->icon_url,
                    'platform' => $userGame->platformGame?->platform,
                    'total_playtime_minutes' => $userGame->total_playtime_minutes,
                    'total_playtime_hours' => round($userGame->total_playtime_minutes / 60, 2),
                    'trophies_total' => $userGame->trophies_total,
                    'trophies_earned' => $userGame->trophies_earned,
                    'trophy_progress_percentage' => $userGame->trophy_progress_percentage,
                    'last_played_at' => $userGame->last_played_at?->toIso8601String(),
                ];
            }),
            'recent_posts_count' => $gamer->posts->count(),
            'recent_posts' => $gamer->posts->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'type' => $post->type,
                    'created_at' => $post->created_at->toIso8601String(),
                ];
            }),
        ];

        return $this->successResponse($profile, 'Gamer profile retrieved successfully');
    }

    /**
     * Get contact link/info for a gamer.
     * This returns public contact information that recruiters can use.
     */
    public function contactLink(Request $request, int $gamerId): JsonResponse
    {
        $gamer = User::where('id', $gamerId)
            ->where('role', UserRole::GAMER)
            ->whereNotNull('email_verified_at')
            ->whereNotNull('onboarding_completed_at')
            ->select([
                'id',
                'name',
                'username',
                'email',
                'avatar',
                'organization_name',
                'position',
                'represent_type',
            ])
            ->first();

        if (!$gamer) {
            return $this->notFoundResponse('Gamer not found');
        }

        // Return contact information
        $contactInfo = [
            'id' => $gamer->id,
            'name' => $gamer->name,
            'username' => $gamer->username,
            'avatar_url' => $gamer->avatar_url,
            'email' => $gamer->email,
            'organization_name' => $gamer->organization_name,
            'position' => $gamer->position,
            'represent_type' => $gamer->represent_type,
            'profile_url' => url("/api/v1/recruiter/gamer-profile/{$gamer->id}"),
        ];

        return $this->successResponse($contactInfo, 'Contact information retrieved successfully');
    }
}

