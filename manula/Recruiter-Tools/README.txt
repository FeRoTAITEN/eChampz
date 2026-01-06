Recruiter Tools APIs
====================

Base URL
- Local: http://localhost:8000
- Prefix: /api/v1
- Auth: Bearer token via Laravel Sanctum
- Requires: Authentication + Verified Email + Recruiter Role
- Global rate limit: 60 requests/minute

Authenticated Endpoints (Authorization: Bearer {token} + verified email + recruiter role)
- GET    /api/v1/recruiter/search              Search gamers by Game, Rank, or Region
- GET    /api/v1/recruiter/gamer-cards         Get gamer cards (Basic Info + XP)
- GET    /api/v1/recruiter/gamer-profile/{id} View full gamer profile
- GET    /api/v1/recruiter/contact-link/{id}   Get contact information for a gamer

1) Search Gamers
- GET /api/v1/recruiter/search
  Query params:
    - game_id: integer (optional) - Filter by game ID (users who favorited this game)
    - rank: string (optional) - Filter by rank: "bronze", "silver", or "gold"
    - region: string (optional) - Filter by region (placeholder - requires region field in users table)
    - per_page: integer 1-100 (default: 20)
    - page: integer (default: 1)
  Notes:
    - Returns only verified gamers who completed onboarding
    - Results ordered by XP (descending)
    - Each result includes: id, name, username, avatar_url, xp_total, rank, favorite_games
  Response: Paginated list of gamers matching criteria

2) Get Gamer Cards
- GET /api/v1/recruiter/gamer-cards
  Query params:
    - per_page: integer 1-100 (default: 20)
    - page: integer (default: 1)
  Notes:
    - Returns basic info + XP for quick browsing
    - Results ordered by XP (descending)
    - Each card includes: id, name, username, avatar_url, xp_total, rank
  Response: Paginated list of gamer cards

3) View Full Gamer Profile
- GET /api/v1/recruiter/gamer-profile/{gamerId}
  Path params:
    - gamerId: integer (required) - The ID of the gamer
  Notes:
    - Returns comprehensive profile information
    - Includes favorite games, platform games (top 10 by playtime), recent posts (last 5)
    - Only returns verified gamers who completed onboarding
  Response: Full gamer profile with all details

4) Get Contact Link
- GET /api/v1/recruiter/contact-link/{gamerId}
  Path params:
    - gamerId: integer (required) - The ID of the gamer
  Notes:
    - Returns contact information for reaching out to the gamer
    - Includes email, organization info, and profile URL
    - Only returns verified gamers who completed onboarding
  Response: Contact information including email and profile details

User Ranks
----------
Ranks are automatically calculated based on XP:
- Bronze: 0-99 XP
- Silver: 100-499 XP
- Gold: 500+ XP

Rank filtering uses the ranks table to determine XP ranges for each rank.

Game Filtering
--------------
When filtering by game_id:
- Searches for gamers who have favorited the specified game
- Game must exist in the games table
- Uses the game_user pivot table (favorite games)

Region Filtering
----------------
- Region filtering is prepared but not yet active
- Requires adding a 'region' field to the users table via migration
- Currently accepts the parameter but doesn't filter (placeholder)

Example Request - Search
-------------------------
GET /api/v1/recruiter/search?game_id=1&rank=silver&per_page=20
Authorization: Bearer {token}
Accept: application/json

Example Response - Search
-------------------------
{
  "success": true,
  "message": "Gamers retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 5,
        "name": "John Doe",
        "username": "john_doe",
        "avatar_url": "http://localhost:8000/storage/avatars/avatar.jpg",
        "xp_total": 250,
        "rank": "silver",
        "favorite_games": [
          {
            "id": 1,
            "name": "Valorant",
            "icon_url": "https://example.com/valorant.png"
          }
        ]
      }
    ],
    "first_page_url": "http://localhost:8000/api/v1/recruiter/search?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/v1/recruiter/search?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://localhost:8000/api/v1/recruiter/search",
    "per_page": 20,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}

Example Request - Gamer Cards
------------------------------
GET /api/v1/recruiter/gamer-cards?per_page=10
Authorization: Bearer {token}
Accept: application/json

Example Response - Gamer Cards
-------------------------------
{
  "success": true,
  "message": "Gamer cards retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 3,
        "name": "Jane Smith",
        "username": "jane_smith",
        "avatar_url": null,
        "xp_total": 550,
        "rank": "gold"
      },
      {
        "id": 5,
        "name": "John Doe",
        "username": "john_doe",
        "avatar_url": "http://localhost:8000/storage/avatars/avatar.jpg",
        "xp_total": 250,
        "rank": "silver"
      }
    ],
    ...
  }
}

Example Request - Full Profile
------------------------------
GET /api/v1/recruiter/gamer-profile/5
Authorization: Bearer {token}
Accept: application/json

Example Response - Full Profile
--------------------------------
{
  "success": true,
  "message": "Gamer profile retrieved successfully",
  "data": {
    "id": 5,
    "name": "John Doe",
    "username": "john_doe",
    "avatar_url": "http://localhost:8000/storage/avatars/avatar.jpg",
    "xp_total": 250,
    "rank": "silver",
    "date_of_birth": "1995-05-15",
    "represent_type": "individual",
    "organization_name": null,
    "position": null,
    "onboarding_completed_at": "2025-12-10T10:30:00.000000Z",
    "favorite_games": [
      {
        "id": 1,
        "name": "Valorant",
        "icon_url": "https://example.com/valorant.png",
        "slug": "valorant"
      },
      {
        "id": 2,
        "name": "Call of Duty",
        "icon_url": "https://example.com/cod.png",
        "slug": "call-of-duty"
      }
    ],
    "platform_games": [
      {
        "id": 10,
        "game_name": "Red Dead Redemption 2",
        "game_icon_url": "https://example.com/rdr2.png",
        "platform": "playstation",
        "total_playtime_minutes": 1200,
        "total_playtime_hours": 20.0,
        "trophies_total": 50,
        "trophies_earned": 35,
        "trophy_progress_percentage": 70,
        "last_played_at": "2025-12-15T18:00:00.000000Z"
      }
    ],
    "recent_posts_count": 3,
    "recent_posts": [
      {
        "id": 20,
        "content": "Just hit a new personal best!",
        "type": "text",
        "created_at": "2025-12-20T14:30:00.000000Z"
      }
    ]
  }
}

Example Request - Contact Link
-------------------------------
GET /api/v1/recruiter/contact-link/5
Authorization: Bearer {token}
Accept: application/json

Example Response - Contact Link
--------------------------------
{
  "success": true,
  "message": "Contact information retrieved successfully",
  "data": {
    "id": 5,
    "name": "John Doe",
    "username": "john_doe",
    "avatar_url": "http://localhost:8000/storage/avatars/avatar.jpg",
    "email": "john.doe@example.com",
    "organization_name": null,
    "position": null,
    "represent_type": "individual",
    "profile_url": "http://localhost:8000/api/v1/recruiter/gamer-profile/5"
  }
}

Access Control
--------------
All endpoints require:
1. Valid authentication token (Bearer token)
2. Verified email address
3. Recruiter role (role:recruiter)

Only recruiters can access these endpoints. Gamers cannot access recruiter tools.

Filtering Logic
---------------
- Search endpoint supports multiple filters (game_id, rank, region)
- Filters can be combined (e.g., search for silver rank gamers who favorited game ID 1)
- All filters are optional - if none provided, returns all verified gamers
- Results are always ordered by XP (descending)

Data Privacy
------------
- Only returns gamers who have:
  - Verified their email
  - Completed onboarding
  - Role set to "gamer"
- Contact information (email) is only available via contact-link endpoint
- Profile data includes public information only

Notes
-----
- All endpoints require authentication (Bearer token)
- All endpoints require verified email
- All endpoints require recruiter role
- Region filtering is a placeholder (requires database migration)
- Game filtering searches favorite games only
- Rank is automatically calculated from XP
- Results are paginated (default 20 per page, max 100)
- Platform games show top 10 by playtime
- Recent posts show last 5 posts

Testing
-------
- Use Postman collection for manual testing
- Ensure you have a recruiter account with verified email
- Create test gamers with verified emails and completed onboarding
- Test with different rank filters (bronze, silver, gold)
- Test with game_id filters (use existing game IDs)
- Verify pagination works correctly

Future Enhancements
-------------------
- Add region field to users table for region filtering
- Add advanced search filters (age range, platform, etc.)
- Add sorting options (by name, by join date, etc.)
- Add export functionality for gamer lists
- Add bookmark/favorite gamers feature
- Add contact request system (instead of direct email access)

