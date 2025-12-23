<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PlatformAccount;
use App\Models\PlatformGame;
use App\Models\UserGame;
use App\Services\PlayStationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayStationController extends BaseController
{
    protected PlayStationService $psnService;

    public function __construct(PlayStationService $psnService)
    {
        $this->psnService = $psnService;
    }

    /**
     * Get PlayStation account status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $account = PlatformAccount::where('user_id', $user->id)
            ->where('platform', 'playstation')
            ->first();

        if (!$account) {
            return $this->successResponse([
                'linked' => false,
            ]);
        }

        return $this->successResponse([
            'linked' => true,
            'account' => [
                'platform_username' => $account->platform_username,
                'is_verified' => $account->is_verified,
                'last_synced_at' => $account->last_synced_at?->toIso8601String(),
                'games_count' => $account->games()->count(),
            ],
        ]);
    }

    /**
     * Link PlayStation account using NPSSO token.
     */
    public function link(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'npsso_token' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        // Check if user already has a PSN account linked
        $existingAccount = PlatformAccount::where('user_id', $user->id)
            ->where('platform', 'playstation')
            ->first();

        if ($existingAccount) {
            return $this->errorResponse('PlayStation account already linked. Please disconnect first.', 400);
        }

        DB::beginTransaction();
        try {
            // Exchange NPSSO for access token
            $tokenData = $this->psnService->exchangeNpssoForAccessToken($request->npsso_token);

            if (!$tokenData['access_token']) {
                throw new \Exception('Failed to obtain access token');
            }

            // Get user profile to get account ID and onlineId
            $profile = $this->psnService->getUserProfile($tokenData['access_token']);

            // Extract onlineId from profile (this is the PSN username)
            $onlineId = null;
            
            // Try direct onlineId field first
            if (isset($profile['onlineId'])) {
                $onlineId = $profile['onlineId'];
            }
            // Try profiles array
            elseif (isset($profile['profiles']) && is_array($profile['profiles']) && count($profile['profiles']) > 0) {
                $onlineId = $profile['profiles'][0]['onlineId'] ?? null;
            }
            // Fallback to accountId
            elseif (isset($profile['accountId'])) {
                $onlineId = $profile['accountId'];
            }

            if (!$onlineId) {
                \Log::error('PSN Profile structure', ['profile' => $profile]);
                throw new \Exception('Could not retrieve PSN username from profile');
            }

            // Create platform account - use onlineId from API (not user input)
            $account = PlatformAccount::create([
                'user_id' => $user->id,
                'platform' => 'playstation',
                'platform_username' => $onlineId, // From API, not user input
                'platform_account_id' => $profile['accountId'] ?? null,
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600),
                'npsso_token' => $request->npsso_token,
                'is_verified' => true,
            ]);

            // Auto-sync games after linking
            $syncedCount = 0;
            try {
                $syncedCount = $this->psnService->syncUserGames($account);
            } catch (\Exception $e) {
                \Log::warning('Failed to sync games during linking', ['error' => $e->getMessage()]);
            }

            DB::commit();

            return $this->createdResponse([
                'account' => [
                    'id' => $account->id,
                    'platform' => $account->platform,
                    'platform_username' => $account->platform_username,
                    'is_verified' => $account->is_verified,
                    'games_synced' => $syncedCount,
                ],
            ], 'PlayStation account linked and synced successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to link PlayStation account: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sync games from PlayStation.
     */
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();

        $account = PlatformAccount::where('user_id', $user->id)
            ->where('platform', 'playstation')
            ->first();

        if (!$account) {
            return $this->errorResponse('PlayStation account not linked', 404);
        }

        try {
            $syncedCount = $this->psnService->syncUserGames($account);

            return $this->successResponse([
                'synced_games' => $syncedCount,
                'last_synced_at' => $account->fresh()->last_synced_at?->toIso8601String(),
            ], "Successfully synced {$syncedCount} games");
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to sync games: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user's games from PlayStation.
     */
    public function games(Request $request): JsonResponse
    {
        $user = $request->user();

        $account = PlatformAccount::where('user_id', $user->id)
            ->where('platform', 'playstation')
            ->first();

        if (!$account) {
            return $this->errorResponse('PlayStation account not linked', 404);
        }

        $games = $account->games()
            ->with('platformGame') // Eager load platform game data
            ->orderBy('last_played_at', 'desc')
            ->get()
            ->map(function ($game) {
                return [
                    'id' => $game->id,
                    'game_name' => $game->platformGame->name ?? 'Unknown',
                    'game_icon_url' => $game->platformGame->icon_url ?? null,
                    'platform' => $game->platformGame->platform ?? 'playstation',
                    'playtime_hours' => $game->playtime_hours,
                    'playtime_minutes' => $game->total_playtime_minutes,
                    'trophies' => [
                        'bronze' => $game->trophies_bronze,
                        'silver' => $game->trophies_silver,
                        'gold' => $game->trophies_gold,
                        'platinum' => $game->trophies_platinum,
                        'total' => $game->trophies_total,
                        'earned' => $game->trophies_earned,
                        'progress_percentage' => $game->trophy_progress_percentage,
                    ],
                    'last_played_at' => $game->last_played_at?->toIso8601String(),
                ];
            });

        return $this->successResponse([
            'games' => $games,
            'total_games' => $games->count(),
        ]);
    }

    /**
     * Add a game manually (simple version).
     */
    public function addGameManually(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'playtime_hours' => ['nullable', 'numeric', 'min:0'],
            'trophies_earned' => ['nullable', 'integer', 'min:0'],
            'platform' => ['nullable', 'string', 'in:playstation,xbox,steam,epic'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();
        $platform = $request->platform ?? 'playstation';
        $playtimeHours = $request->playtime_hours ?? 0;
        $playtimeMinutes = (int) ($playtimeHours * 60);
        $trophiesEarned = $request->trophies_earned ?? 0;

        // Generate a unique platform_game_id for manual games (shared across users)
        // Use slug from name to make it consistent
        $slug = \Illuminate\Support\Str::slug($request->name);
        $platformGameId = 'manual-' . $platform . '-' . $slug;

        DB::beginTransaction();
        try {
            // Get or create platform game
            $platformGame = PlatformGame::firstOrCreate(
                [
                    'platform' => $platform,
                    'platform_game_id' => $platformGameId,
                ],
                [
                    'name' => $request->name,
                    'icon_url' => null,
                    'metadata' => ['manual' => true],
                ]
            );

            // Check if user already has this game (manual or synced)
            $existingUserGame = UserGame::where('user_id', $user->id)
                ->where('platform_game_id', $platformGame->id)
                ->first();

            if ($existingUserGame) {
                // Update existing game instead of creating new one
                $existingUserGame->update([
                    'total_playtime_minutes' => $playtimeMinutes,
                    'trophies_earned' => $trophiesEarned,
                    'trophies_total' => $trophiesEarned > 0 ? $trophiesEarned : $existingUserGame->trophies_total,
                    'last_played_at' => now(),
                    'last_synced_at' => now(),
                ]);
                $userGame = $existingUserGame;
            } else {
                // Create new user game
                $userGame = UserGame::create([
                    'user_id' => $user->id,
                    'platform_account_id' => null, // Manual games don't need platform account
                    'platform_game_id' => $platformGame->id,
                    'total_playtime_minutes' => $playtimeMinutes,
                    'trophies_bronze' => 0,
                    'trophies_silver' => 0,
                    'trophies_gold' => 0,
                    'trophies_platinum' => 0,
                    'trophies_total' => $trophiesEarned > 0 ? $trophiesEarned : 0,
                    'trophies_earned' => $trophiesEarned,
                    'trophy_progress_percentage' => 0,
                    'platform_specific_data' => null,
                    'metadata' => ['manual' => true],
                    'last_played_at' => now(),
                    'last_synced_at' => now(),
                ]);
            }

            DB::commit();

            return $this->createdResponse([
                'game' => [
                    'id' => $userGame->id,
                    'game_name' => $platformGame->name,
                    'playtime_hours' => $userGame->playtime_hours,
                    'trophies_earned' => $userGame->trophies_earned,
                    'platform' => $platformGame->platform,
                ],
            ], 'Game added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to add game: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Disconnect PlayStation account.
     */
    public function disconnect(Request $request): JsonResponse
    {
        $user = $request->user();

        $account = PlatformAccount::where('user_id', $user->id)
            ->where('platform', 'playstation')
            ->first();

        if (!$account) {
            return $this->errorResponse('PlayStation account not linked', 404);
        }

        $account->delete();

        return $this->successResponse(null, 'PlayStation account disconnected successfully');
    }
}
