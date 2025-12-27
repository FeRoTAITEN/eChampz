<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends BaseController
{
    /**
     * Get all-time leaderboard.
     */
    public function allTime(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 50);
        
        $leaderboard = User::select('id', 'name', 'username', 'avatar', 'xp_total')
            ->orderBy('xp_total', 'desc')
            ->orderBy('id', 'asc')
            ->paginate($perPage);

        // Add position/rank to each user
        $leaderboard->getCollection()->transform(function ($user, $index) use ($leaderboard) {
            $position = ($leaderboard->currentPage() - 1) * $leaderboard->perPage() + $index + 1;
            return [
                'position' => $position,
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatar_url,
                'xp_total' => $user->xp_total,
                'rank' => $user->rank,
            ];
        });

        return $this->successResponse($leaderboard);
    }

    /**
     * Get monthly leaderboard (based on XP earned this month).
     */
    public function monthly(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 50);
        $startOfMonth = Carbon::now()->startOfMonth();

        $leaderboard = User::select('users.id', 'users.name', 'users.username', 'users.avatar', 'users.xp_total')
            ->leftJoin('xp_transactions', 'users.id', '=', 'xp_transactions.user_id')
            ->where('xp_transactions.created_at', '>=', $startOfMonth)
            ->groupBy('users.id', 'users.name', 'users.username', 'users.avatar', 'users.xp_total')
            ->selectRaw('COALESCE(SUM(xp_transactions.amount), 0) as monthly_xp')
            ->orderBy('monthly_xp', 'desc')
            ->orderBy('users.id', 'asc')
            ->paginate($perPage);

        // Add position/rank to each user
        $leaderboard->getCollection()->transform(function ($user, $index) use ($leaderboard) {
            $position = ($leaderboard->currentPage() - 1) * $leaderboard->perPage() + $index + 1;
            return [
                'position' => $position,
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'avatar_url' => asset('storage/' . $user->avatar),
                'xp_total' => $user->xp_total,
                'monthly_xp' => $user->monthly_xp,
                'rank' => $user->rank,
            ];
        });

        return $this->successResponse($leaderboard);
    }

    /**
     * Get weekly leaderboard (based on XP earned this week).
     */
    public function weekly(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 50);
        $startOfWeek = Carbon::now()->startOfWeek();

        $leaderboard = User::select('users.id', 'users.name', 'users.username', 'users.avatar', 'users.xp_total')
            ->leftJoin('xp_transactions', 'users.id', '=', 'xp_transactions.user_id')
            ->where('xp_transactions.created_at', '>=', $startOfWeek)
            ->groupBy('users.id', 'users.name', 'users.username', 'users.avatar', 'users.xp_total')
            ->selectRaw('COALESCE(SUM(xp_transactions.amount), 0) as weekly_xp')
            ->orderBy('weekly_xp', 'desc')
            ->orderBy('users.id', 'asc')
            ->paginate($perPage);

        // Add position/rank to each user
        $leaderboard->getCollection()->transform(function ($user, $index) use ($leaderboard) {
            $position = ($leaderboard->currentPage() - 1) * $leaderboard->perPage() + $index + 1;
            return [
                'position' => $position,
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'avatar_url' => asset('storage/' . $user->avatar),
                'xp_total' => $user->xp_total,
                'weekly_xp' => $user->weekly_xp,
                'rank' => $user->rank,
            ];
        });

        return $this->successResponse($leaderboard);
    }
}
