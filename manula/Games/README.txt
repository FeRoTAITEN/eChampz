Games & Favorites APIs
======================

Base URL
- Local: http://localhost:8000
- Prefix: /api/v1
- Auth: Bearer token via Laravel Sanctum
- Requires: Authenticated user with verified email

Overview
--------
The Games API allows users to browse the game catalog and manage their favorite games.
There are two types of games in the system:
1. Games catalog (games table) - general game library for tagging/favorites
2. Platform games (user_games) - user's actual games from PlayStation, Xbox, etc.

This API deals with the games catalog for favorites management.

Endpoints
---------

1. GET /api/v1/games
   Description: List all games in the system catalog
   Query Parameters:
     - search: (optional) Search games by name
     - per_page: (optional, default: 20) Items per page
   Response: Paginated list of games
   Example: GET /api/v1/games?search=Call&per_page=10

2. GET /api/v1/user/favorite-games
   Description: Get current user's favorite games
   Response: Array of favorite games

3. POST /api/v1/user/favorite-games
   Description: Add games to user's favorites (non-destructive)
   Body: 
     {
       "game_ids": [1, 5, 12]
     }
   Notes:
     - Adds games without removing existing favorites
     - Duplicate entries are ignored
     - All game IDs must exist in the games table
   Response: Updated list of favorites

4. PUT /api/v1/user/favorite-games
   Description: Set/Replace all favorite games
   Body:
     {
       "game_ids": [1, 5, 12]
     }
   Notes:
     - Replaces entire favorites list
     - Pass empty array to clear all favorites
   Response: Updated list of favorites

5. DELETE /api/v1/user/favorite-games/{gameId}
   Description: Remove a specific game from favorites
   Parameters:
     - gameId: ID of the game to remove
   Response: Updated list of favorites

Response Format
---------------
All responses follow the standard ApiResponse format:

Success:
{
  "success": true,
  "message": "...",
  "data": { ... }
}

Error:
{
  "success": false,
  "message": "Error reason",
  "errors": { ... }
}

Game Object Structure
---------------------
{
  "id": 1,
  "name": "Call of Duty: Modern Warfare",
  "slug": "call-of-duty-modern-warfare",
  "icon_url": "https://example.com/icon.png",
  "created_at": "2025-01-01T00:00:00.000000Z",
  "updated_at": "2025-01-01T00:00:00.000000Z"
}

Usage Examples
--------------

1. Browse Games:
   GET /api/v1/games
   Authorization: Bearer {token}

2. Search Games:
   GET /api/v1/games?search=FIFA
   Authorization: Bearer {token}

3. View My Favorites:
   GET /api/v1/user/favorite-games
   Authorization: Bearer {token}

4. Add to Favorites:
   POST /api/v1/user/favorite-games
   Authorization: Bearer {token}
   Content-Type: application/json
   {
     "game_ids": [1, 2, 3]
   }

5. Replace Favorites:
   PUT /api/v1/user/favorite-games
   Authorization: Bearer {token}
   Content-Type: application/json
   {
     "game_ids": [5, 8, 12]
   }

6. Remove from Favorites:
   DELETE /api/v1/user/favorite-games/5
   Authorization: Bearer {token}

Notes
-----
- All endpoints require authentication (Bearer token)
- All endpoints require verified email
- Game IDs must exist in the games table
- Adding duplicate favorites is safe (ignored automatically)
- Pagination is available on the games list endpoint
- Favorites are stored in the game_user pivot table
- Favorites are returned ordered by game name

Testing
-------
Import the Postman collection from this directory to test all endpoints.
Make sure to set your authentication token in the Postman environment variables.



