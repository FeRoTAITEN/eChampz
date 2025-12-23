PlayStation Integration API
===========================

📌 Prerequisites
----------------
1. Node.js service must be running on http://localhost:3001
2. Add to .env: PSN_API_SERVICE_URL=http://localhost:3001
3. Laravel account with verified email

🚀 Quick Setup
--------------
1. Start Node.js service:
   cd psn-api-service && npm start

2. Add to .env:
   PSN_API_SERVICE_URL=http://localhost:3001

3. Ready! 🎉

📝 API Endpoints
----------------

Base URL: http://localhost:8000/api/v1
Auth: Bearer token (Laravel Sanctum)
Requires: Authentication + Verified Email

1. GET /playstation/status
   - Get PlayStation account linking status
   - Response: { linked: true/false, account: {...} }

2. POST /playstation/link
   - Link PlayStation account
   - Body: { "npsso_token": "your_token" }
   - Response: { success: true, data: { account: {...}, games_synced: 25 } }

3. POST /playstation/sync
   - Sync games from PlayStation
   - Response: { success: true, data: { synced_games: 25 } }

4. GET /playstation/games
   - Get list of games
   - Response: { success: true, data: { games: [...], total_games: 25 } }

5. POST /playstation/games/manual
   - Add game manually (without linking PlayStation account)
   - Body: { "name": "Game Name", "playtime_hours": 120.5, "trophies_earned": 50, "platform": "playstation" }
   - Response: { success: true, message: "Game added successfully", data: { game: {...} } }

6. DELETE /playstation/disconnect
   - Disconnect PlayStation account
   - Response: { success: true, message: "..." }

🔑 How to Get NPSSO Token
--------------------------
1. Open https://www.playstation.com and sign in
2. Press F12 (Developer Tools)
3. Go to Application → Cookies → https://www.playstation.com
4. Find cookie named "npsso"
5. Copy the value (64 characters)

📋 Example - Link Account
--------------------------
POST /api/v1/playstation/link
Authorization: Bearer {your_laravel_token}
Content-Type: application/json

{
  "npsso_token": "rQ3zqexhgmfDxUJSfXs2Y8q3ilvEOOyDt2JhkkdSf9cLCmRhiim4eOIMw7UFEIbG"
}

Response:
{
  "success": true,
  "message": "PlayStation account linked and synced successfully",
  "data": {
    "account": {
      "id": 1,
      "platform": "playstation",
      "platform_username": "YourPSNUsername",
      "is_verified": true,
      "games_synced": 25
    }
  }
}

📋 Example - Get Games
----------------------
GET /api/v1/playstation/games
Authorization: Bearer {your_laravel_token}

Response:
{
  "success": true,
  "data": {
    "games": [
      {
        "id": 1,
        "game_name": "God of War",
        "playtime_hours": 20.5,
        "trophies": {
          "bronze": 20,
          "silver": 15,
          "gold": 10,
          "platinum": 1,
          "total": 46,
          "earned": 23,
          "progress_percentage": 50
        }
      }
    ],
    "total_games": 25
  }
}

📋 Example - Add Game Manually
-------------------------------
POST /api/v1/playstation/games/manual
Authorization: Bearer {your_laravel_token}
Content-Type: application/json

{
  "name": "Red Dead Redemption 2",
  "playtime_hours": 120.5,
  "trophies_earned": 50,
  "platform": "playstation"
}

Response:
{
  "success": true,
  "message": "Game added successfully",
  "data": {
    "game": {
      "id": 26,
      "game_name": "Red Dead Redemption 2",
      "playtime_hours": 120.5,
      "trophies_earned": 50,
      "platform": "playstation"
    }
  }
}

Notes:
- name: required (game name)
- playtime_hours: optional (playtime in hours)
- trophies_earned: optional (number of earned trophies)
- platform: optional (default: playstation) - values: playstation, xbox, steam, epic
- No PlayStation account linking required for manual game addition
- If game already exists, data will be updated

⚠️  Common Errors
------------------
- 401: Invalid or expired token
- 403: Email not verified
- 404: PlayStation account not linked
- 500: Node.js service not running or invalid NPSSO token

💡 Important Notes
------------------
- Node.js service must be running at all times
- NPSSO token is valid for limited time (get a new one if it fails)
- Auto-sync happens when linking account
- You can sync manually at any time

🔧 Troubleshooting
------------------
1. Check if Node.js service is running: curl http://localhost:3001/health
2. Verify PSN_API_SERVICE_URL in .env
3. Check Laravel logs: storage/logs/laravel.log
4. Check Node.js service logs in terminal
