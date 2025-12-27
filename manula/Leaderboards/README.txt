Leaderboards & Ranking System
==============================

Base URL
- Local: http://localhost:8000
- Prefix: /api/v1
- Auth: Bearer token via Laravel Sanctum
- Requires: Authenticated user with verified email

Overview
--------
The Leaderboards API provides competitive rankings based on user XP (experience points).
Users earn XP through various activities and automatically receive ranks based on their total XP.

Ranking System
--------------
Users are automatically ranked based on their total XP.
Ranks are dynamically computed from the 'ranks' table:

1. Bronze: 0-99 XP
2. Silver: 100-499 XP
3. Gold: 500+ XP

Rank is computed in real-time based on current xp_total.
No database updates needed - rank is calculated on the fly using an accessor.

Earning XP
----------
Users earn XP through:

Onboarding Steps:
- Name: 10 XP
- Birthday: 10 XP
- Represent (Recruiters only): 15 XP
- Completion Bonus: 20 XP
- Total possible from onboarding: 55 XP (gamer) or 55 XP (recruiter)

Future XP Sources:
- Posts, achievements, challenges, etc. (to be implemented)

Leaderboard Types
-----------------

1. All-Time Leaderboard
   - Ranks users by total XP earned since account creation
   - Shows overall progression

2. Monthly Leaderboard
   - Ranks users by XP earned in the current calendar month
   - Resets at the start of each month
   - Great for monthly competitions

3. Weekly Leaderboard
   - Ranks users by XP earned in the current week (Monday-Sunday)
   - Resets every Monday
   - Perfect for short-term challenges

Endpoints
---------

1. GET /api/v1/leaderboard/all-time
   Description: Get all-time leaderboard sorted by total XP
   Query Parameters:
     - per_page: (optional, default: 50) Items per page
   Response: Paginated list of users with:
     - position: User's rank on leaderboard (1, 2, 3...)
     - id: User ID
     - name: User's name
     - username: User's username
     - avatar: Avatar file path
     - avatar_url: Full URL to avatar
     - xp_total: Total XP earned
     - rank: User's rank (bronze, silver, gold)

2. GET /api/v1/leaderboard/monthly
   Description: Get monthly leaderboard sorted by XP earned this month
   Query Parameters:
     - per_page: (optional, default: 50) Items per page
   Response: Paginated list with same fields as all-time, plus:
     - monthly_xp: XP earned this month

3. GET /api/v1/leaderboard/weekly
   Description: Get weekly leaderboard sorted by XP earned this week
   Query Parameters:
     - per_page: (optional, default: 50) Items per page
   Response: Paginated list with same fields as all-time, plus:
     - weekly_xp: XP earned this week

Response Format
---------------
All responses follow the standard ApiResponse format:

Success:
{
  "success": true,
  "message": "Success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "position": 1,
        "id": 5,
        "name": "Top Player",
        "username": "top_player",
        "avatar": "avatars/user-5.jpg",
        "avatar_url": "http://localhost:8000/storage/avatars/user-5.jpg",
        "xp_total": 1250,
        "rank": "gold"
      },
      {
        "position": 2,
        "id": 12,
        "name": "Second Place",
        "username": "second_place",
        "avatar": null,
        "avatar_url": null,
        "xp_total": 850,
        "rank": "gold"
      },
      ...
    ],
    "per_page": 50,
    "total": 150
  }
}

Monthly/Weekly Response (additional field):
{
  "position": 1,
  "id": 5,
  "name": "Top Player",
  "username": "top_player",
  "avatar": "avatars/user-5.jpg",
  "avatar_url": "http://localhost:8000/storage/avatars/user-5.jpg",
  "xp_total": 1250,
  "monthly_xp": 250,  // Only in monthly endpoint
  "weekly_xp": 80,    // Only in weekly endpoint
  "rank": "gold"
}

Usage Examples
--------------

1. View All-Time Top Players:
   GET /api/v1/leaderboard/all-time
   Authorization: Bearer {token}

2. View Monthly Competition:
   GET /api/v1/leaderboard/monthly?per_page=100
   Authorization: Bearer {token}

3. View This Week's Leaders:
   GET /api/v1/leaderboard/weekly
   Authorization: Bearer {token}

Pagination
----------
All leaderboard endpoints support pagination:
- Use ?per_page=N to control items per page (max 100)
- Use ?page=N to navigate pages
- Response includes: current_page, per_page, total, last_page

Position Calculation
--------------------
Position is calculated based on:
1. XP (descending) - higher XP = better position
2. User ID (ascending) - tie-breaker for equal XP

Notes
-----
- Leaderboards are public (all authenticated users can view)
- XP transactions are tracked in xp_transactions table
- Rank is computed dynamically from ranks table (not stored in users table)
- Rank thresholds are configurable in the ranks table
- Monthly leaderboard resets on 1st of each month
- Weekly leaderboard resets every Monday
- Position 1 = top of leaderboard
- Users with 0 XP are included in leaderboards
- Rank computation is cached for performance (1 hour)

Testing
-------
Import the Postman collection from this directory to test all endpoints.
Make sure to set your authentication token in the Postman environment variables.

XP Transaction Tracking
------------------------
Every XP award is recorded in the xp_transactions table with:
- user_id: Who earned the XP
- source: Where XP came from (e.g., "onboarding", "post")
- source_id: Specific identifier (e.g., "name", "birthday")
- amount: XP amount earned
- meta: Additional JSON data
- created_at: When XP was earned

This allows for time-based leaderboards and detailed XP history.

