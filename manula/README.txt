eChampz API Documentation
=========================

Welcome to the eChampz API documentation. This folder contains detailed guides and Postman collections for all API endpoints.

Quick Start
-----------
1. Start the Laravel server: php artisan serve
2. Import the relevant Postman collection from each folder
3. Set your base_url and token variables in Postman
4. Follow the README.txt in each folder for specific endpoint details

API Modules
-----------

📁 Authentication-Access/
   - User registration and login
   - Email verification
   - Password reset
   - User profile management (including avatar upload)
   - Logout and session management
   
   Key Endpoints:
   - POST /api/v1/register
   - POST /api/v1/login
   - GET /api/v1/user
   - PUT /api/v1/user (profile + avatar update)
   - POST /api/v1/email/verify
   - POST /api/v1/password/reset

📁 Onboarding/
   - User onboarding flow
   - Profile completion steps
   - Name, birthday, and representation type setup
   
   Key Endpoints:
   - GET /api/v1/onboarding/status
   - POST /api/v1/onboarding/name
   - POST /api/v1/onboarding/birthday
   - POST /api/v1/onboarding/represent

📁 Games/
   - Browse game catalog
   - Manage favorite games
   - Search games
   
   Key Endpoints:
   - GET /api/v1/games
   - GET /api/v1/user/favorite-games
   - POST /api/v1/user/favorite-games
   - DELETE /api/v1/user/favorite-games/{id}

📁 PlayStation-Integration/
   - Link PlayStation Network account
   - Sync PSN games and trophies
   - View PlayStation game library
   - Manual game additions
   
   Key Endpoints:
   - GET /api/v1/playstation/status
   - POST /api/v1/playstation/link
   - POST /api/v1/playstation/sync
   - GET /api/v1/playstation/games

📁 Posts/
   - Create and manage posts
   - Social feed (global and following)
   - @mentions support
   - Game tags and media attachments
   
   Key Endpoints:
   - GET /api/v1/posts
   - POST /api/v1/posts
   - GET /api/v1/posts/{id}
   - PUT /api/v1/posts/{id}
   - DELETE /api/v1/posts/{id}

📁 Leaderboards/
   - XP-based competitive rankings
   - All-time, monthly, and weekly leaderboards
   - Automatic rank calculation (Bronze, Silver, Gold)
   - User position tracking
   
   Key Endpoints:
   - GET /api/v1/leaderboard/all-time
   - GET /api/v1/leaderboard/monthly
   - GET /api/v1/leaderboard/weekly

Base Configuration
------------------
Base URL: http://localhost:8000
API Prefix: /api/v1
Authentication: Bearer Token (Laravel Sanctum)

All authenticated endpoints require:
Header: Authorization: Bearer {your_token}

Rate Limiting
-------------
- Global: 60 requests/minute
- Sensitive endpoints (login, register, password reset, email verification): 5 requests/minute

Common Response Format
----------------------
Success Response:
{
  "success": true,
  "message": "Success message",
  "data": { ... }
}

Error Response:
{
  "success": false,
  "message": "Error message",
  "errors": { ... }  // Optional validation errors
}

Authentication Flow
-------------------
1. Register: POST /api/v1/register
   → Returns token + verification required flag
   
2. Verify Email: POST /api/v1/email/verify
   → Code sent to email (returned in local/testing mode)
   
3. Complete Onboarding: 
   - POST /api/v1/onboarding/name
   - POST /api/v1/onboarding/birthday
   - POST /api/v1/onboarding/represent
   
4. Start Using API: All endpoints now accessible

User Roles
----------
- gamer: Regular player accounts
- recruiter: Organization/recruiter accounts

Each role has specific route access controlled by middleware.

User Ranks (XP-Based)
---------------------
- Bronze: 0-99 XP (default starting rank)
- Silver: 100-499 XP
- Gold: 500+ XP

Ranks are automatically updated when users earn XP through onboarding, achievements, and other activities.

Testing
-------
Each folder contains:
- README.txt: Detailed endpoint documentation
- postman_collection.json: Ready-to-import Postman collection

Import the collections into Postman and set these environment variables:
- base_url: http://localhost:8000
- token: (obtained from login/register)

Development Notes
-----------------
- Run migrations: php artisan migrate
- Seed data: php artisan db:seed
- Storage link (for avatars): php artisan storage:link
- Run tests: php artisan test

Support
-------
For more information, see:
- API_ENDPOINTS.md (in project root)
- DATABASE_DESIGN.md (in project root)
- PROJECT_SUMMARY.txt (in project root)

Last Updated: December 27, 2025

