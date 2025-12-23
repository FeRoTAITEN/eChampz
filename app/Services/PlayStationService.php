<?php

namespace App\Services;

use App\Models\PlatformAccount;
use App\Models\PlatformGame;
use App\Models\UserGame;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlayStationService
{
    private const PSN_API_BASE = 'https://m.np.playstation.net/api';
    
    private function getPsnApiServiceUrl(): string
    {
        return config('services.psn_api_service_url', env('PSN_API_SERVICE_URL', 'http://localhost:3001'));
    }

    /**
     * Exchange NPSSO token for access token using Node.js microservice.
     */
    public function exchangeNpssoForAccessToken(string $npsso): array
    {
        try {
            $serviceUrl = $this->getPsnApiServiceUrl();
            
            Log::info('PSN: Exchanging NPSSO token via Node.js service', [
                'service_url' => $serviceUrl,
            ]);

            $response = Http::timeout(30)->post($serviceUrl . '/api/exchange-npsso', [
                'npsso' => $npsso,
            ]);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();
                
                Log::error('PSN API Service failed', [
                    'status' => $status,
                    'body' => $body,
                    'service_url' => $serviceUrl,
                ]);

                // Check if service is running
                if ($status === 0 || $status === 500) {
                    throw new \Exception('PSN API Service is not available. Please make sure Node.js service is running on ' . $serviceUrl);
                }

                throw new \Exception('Failed to exchange NPSSO token: ' . $body);
            }

            $responseData = $response->json();

            if (!isset($responseData['success']) || !$responseData['success']) {
                throw new \Exception('PSN API Service returned error: ' . ($responseData['error'] ?? 'Unknown error'));
            }

            $data = $responseData['data'] ?? [];

            return [
                'access_token' => $data['access_token'] ?? null,
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_in' => $data['expires_in'] ?? 3600,
                'token_type' => $data['token_type'] ?? 'Bearer',
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('PSN API Service connection failed', [
                'error' => $e->getMessage(),
                'service_url' => $this->getPsnApiServiceUrl(),
            ]);
            throw new \Exception('Cannot connect to PSN API Service. Please make sure Node.js service is running on ' . $this->getPsnApiServiceUrl());
        } catch (\Exception $e) {
            Log::error('PSN Service Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get user's PSN profile using Node.js microservice.
     */
    public function getUserProfile(string $accessToken, string $accountId = 'me'): array
    {
        try {
            $serviceUrl = $this->getPsnApiServiceUrl();
            
            Log::info('PSN: Getting user profile via Node.js service', [
                'service_url' => $serviceUrl,
            ]);

            $response = Http::timeout(30)->post($serviceUrl . '/api/user-profile', [
                'access_token' => $accessToken,
            ]);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();
                
                Log::error('PSN API Service failed to get profile', [
                    'status' => $status,
                    'body' => $body,
                    'service_url' => $serviceUrl,
                ]);

                throw new \Exception('Failed to fetch PSN profile: ' . $body);
            }

            $responseData = $response->json();

            if (!isset($responseData['success']) || !$responseData['success']) {
                throw new \Exception('PSN API Service returned error: ' . ($responseData['error'] ?? 'Unknown error'));
            }

            return $responseData['data'] ?? [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('PSN API Service connection failed', [
                'error' => $e->getMessage(),
                'service_url' => $this->getPsnApiServiceUrl(),
            ]);
            throw new \Exception('Cannot connect to PSN API Service. Please make sure Node.js service is running on ' . $this->getPsnApiServiceUrl());
        } catch (\Exception $e) {
            Log::error('PSN Service Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get user's played games with playtime using Node.js microservice.
     */
    public function getUserPlayedGames(string $accessToken, string $accountId = 'me'): array
    {
        try {
            $serviceUrl = $this->getPsnApiServiceUrl();
            
            Log::info('PSN: Getting user games via Node.js service', [
                'service_url' => $serviceUrl,
                'account_id' => $accountId,
            ]);

            $response = Http::timeout(30)->post($serviceUrl . '/api/user-games', [
                'access_token' => $accessToken,
                'accountId' => $accountId !== 'me' ? $accountId : null,
            ]);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();
                
                Log::error('PSN API Service failed to get games', [
                    'status' => $status,
                    'body' => $body,
                    'service_url' => $serviceUrl,
                ]);

                throw new \Exception('Failed to fetch played games: ' . $body);
            }

            $responseData = $response->json();

            if (!isset($responseData['success']) || !$responseData['success']) {
                throw new \Exception('PSN API Service returned error: ' . ($responseData['error'] ?? 'Unknown error'));
            }

            return $responseData['data'] ?? [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('PSN API Service connection failed', [
                'error' => $e->getMessage(),
                'service_url' => $this->getPsnApiServiceUrl(),
            ]);
            throw new \Exception('Cannot connect to PSN API Service. Please make sure Node.js service is running on ' . $this->getPsnApiServiceUrl());
        } catch (\Exception $e) {
            Log::error('PSN Service Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get game trophies.
     */
    public function getGameTrophies(string $accessToken, string $accountId, string $gameId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
            ])->get(self::PSN_API_BASE . "/trophy/v1/users/{$accountId}/titles/{$gameId}/trophyGroups");

            if ($response->failed()) {
                return [];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::warning('Failed to fetch trophies', ['game_id' => $gameId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Sync user's games from PSN.
     */
    public function syncUserGames(PlatformAccount $account): int
    {
        if ($account->isTokenExpired()) {
            throw new \Exception('Access token expired. Please reconnect your PSN account.');
        }

        $games = $this->getUserPlayedGames($account->access_token, $account->platform_account_id ?? 'me');
        $syncedCount = 0;

        // Handle both getUserTitles format (trophyTitles) and direct API format (titles)
        $gameList = $games['trophyTitles'] ?? $games['titles'] ?? [];
        
        foreach ($gameList as $gameData) {
            // Handle getUserTitles format (npCommunicationId) vs direct API format (npTitleId/titleId)
            $gameId = $gameData['npCommunicationId'] ?? $gameData['npTitleId'] ?? $gameData['titleId'] ?? null;
            
            if (!$gameId) {
                continue;
            }

            // Extract trophy data from getUserTitles format
            $definedTrophies = $gameData['definedTrophies'] ?? [];
            $earnedTrophies = $gameData['earnedTrophies'] ?? [];
            
            $trophySummary = [
                'bronze' => (int) ($earnedTrophies['bronze'] ?? 0),
                'silver' => (int) ($earnedTrophies['silver'] ?? 0),
                'gold' => (int) ($earnedTrophies['gold'] ?? 0),
                'platinum' => (int) ($earnedTrophies['platinum'] ?? 0),
                'total' => (int) (($definedTrophies['bronze'] ?? 0) + ($definedTrophies['silver'] ?? 0) + ($definedTrophies['gold'] ?? 0) + ($definedTrophies['platinum'] ?? 0)),
                'earned' => (int) (($earnedTrophies['bronze'] ?? 0) + ($earnedTrophies['silver'] ?? 0) + ($earnedTrophies['gold'] ?? 0) + ($earnedTrophies['platinum'] ?? 0)),
                'percentage' => (int) ($gameData['progress'] ?? 0),
            ];

            // Calculate playtime in minutes (getUserTitles doesn't provide playtime, so it stays 0)
            $playtimeMinutes = 0;
            if (isset($gameData['playDuration']['value'])) {
                $playtimeMinutes = (int) ($gameData['playDuration']['value'] / 60); // Convert seconds to minutes
            }

            // Get game name and icon
            $gameName = $gameData['trophyTitleName'] ?? $gameData['name'] ?? 'Unknown';
            $gameIcon = $gameData['trophyTitleIconUrl'] ?? $gameData['image']['url'] ?? null;

            // Get or create platform game (shared game data)
            $platformGame = PlatformGame::firstOrCreate(
                [
                    'platform' => 'playstation',
                    'platform_game_id' => $gameId,
                ],
                [
                    'name' => $gameName,
                    'icon_url' => $gameIcon,
                    'metadata' => [
                        'trophyTitlePlatform' => $gameData['trophyTitlePlatform'] ?? null,
                        'trophySetVersion' => $gameData['trophySetVersion'] ?? null,
                    ],
                ]
            );

            // Update platform game if name or icon changed
            if ($platformGame->name !== $gameName || $platformGame->icon_url !== $gameIcon) {
                $platformGame->update([
                    'name' => $gameName,
                    'icon_url' => $gameIcon,
                ]);
            }

            // Store platform-specific data in JSON
            $platformSpecificData = [
                'trophies' => [
                    'bronze' => $trophySummary['bronze'],
                    'silver' => $trophySummary['silver'],
                    'gold' => $trophySummary['gold'],
                    'platinum' => $trophySummary['platinum'],
                    'total' => $trophySummary['total'],
                    'earned' => $trophySummary['earned'],
                    'progress' => $trophySummary['percentage'],
                ],
            ];

            UserGame::updateOrCreate(
                [
                    'user_id' => $account->user_id,
                    'platform_account_id' => $account->id,
                    'platform_game_id' => $platformGame->id, // FK to platform_games
                ],
                [
                    'total_playtime_minutes' => $playtimeMinutes,
                    'trophies_bronze' => $trophySummary['bronze'],
                    'trophies_silver' => $trophySummary['silver'],
                    'trophies_gold' => $trophySummary['gold'],
                    'trophies_platinum' => $trophySummary['platinum'],
                    'trophies_total' => $trophySummary['total'],
                    'trophies_earned' => $trophySummary['earned'],
                    'trophy_progress_percentage' => $trophySummary['percentage'],
                    'platform_specific_data' => $platformSpecificData,
                    'metadata' => $gameData,
                    'last_played_at' => isset($gameData['lastUpdatedDateTime']) 
                        ? \Carbon\Carbon::parse($gameData['lastUpdatedDateTime']) 
                        : (isset($gameData['lastPlayedDateTime']) ? \Carbon\Carbon::parse($gameData['lastPlayedDateTime']) : null),
                    'last_synced_at' => now(),
                ]
            );

            $syncedCount++;
        }

        $account->update(['last_synced_at' => now()]);

        return $syncedCount;
    }

    /**
     * Calculate trophy summary from trophy data.
     */
    private function calculateTrophySummary(array $trophyData): array
    {
        $bronze = 0;
        $silver = 0;
        $gold = 0;
        $platinum = 0;
        $earned = 0;
        $total = 0;

        foreach ($trophyData['trophyGroups'] ?? [] as $group) {
            foreach ($group['trophies'] ?? [] as $trophy) {
                $total++;
                $earned += $trophy['earned'] ? 1 : 0;

                switch ($trophy['trophyType'] ?? 'bronze') {
                    case 'bronze':
                        $bronze++;
                        break;
                    case 'silver':
                        $silver++;
                        break;
                    case 'gold':
                        $gold++;
                        break;
                    case 'platinum':
                        $platinum++;
                        break;
                }
            }
        }

        $percentage = $total > 0 ? round(($earned / $total) * 100) : 0;

        return [
            'bronze' => $bronze,
            'silver' => $silver,
            'gold' => $gold,
            'platinum' => $platinum,
            'total' => $total,
            'earned' => $earned,
            'percentage' => $percentage,
        ];
    }
}
