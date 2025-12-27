<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameController extends BaseController
{
    /**
     * Get all games in the system.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search');

        $query = Game::query();

        // Search by name
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $games = $query->orderBy('name')->paginate($perPage);

        return $this->successResponse($games);
    }

    /**
     * Get user's favorite games.
     */
    public function getFavorites(Request $request): JsonResponse
    {
        $user = $request->user();
        $favorites = $user->favoriteGames()->orderBy('name')->get();

        return $this->successResponse($favorites);
    }

    /**
     * Add games to user's favorites.
     */
    public function addFavorites(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'game_ids' => ['required', 'array', 'min:1'],
            'game_ids.*' => ['required', 'integer', 'exists:games,id'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        // Sync will add only new games (won't duplicate)
        $user->favoriteGames()->syncWithoutDetaching($request->game_ids);

        // Get updated favorites
        $favorites = $user->favoriteGames()->orderBy('name')->get();

        return $this->successResponse($favorites, 'Favorite games updated successfully');
    }

    /**
     * Remove a game from user's favorites.
     */
    public function removeFavorite(Request $request, int $gameId): JsonResponse
    {
        $user = $request->user();

        // Check if game exists
        $game = Game::find($gameId);
        if (!$game) {
            return $this->notFoundResponse('Game not found');
        }

        // Detach the game
        $user->favoriteGames()->detach($gameId);

        // Get updated favorites
        $favorites = $user->favoriteGames()->orderBy('name')->get();

        return $this->successResponse($favorites, 'Game removed from favorites');
    }

    /**
     * Set user's favorite games (replace all).
     */
    public function setFavorites(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'game_ids' => ['required', 'array'],
            'game_ids.*' => ['required', 'integer', 'exists:games,id'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        // Sync will replace all favorites
        $user->favoriteGames()->sync($request->game_ids);

        // Get updated favorites
        $favorites = $user->favoriteGames()->orderBy('name')->get();

        return $this->successResponse($favorites, 'Favorite games set successfully');
    }
}
