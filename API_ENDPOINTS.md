# eChampz API Endpoints

Base URL: http://localhost:8000/api

## Public (No Auth)

GET /health - Health check
GET /v1/roles - Get available roles
POST /v1/register - Register new user (requires username)
POST /v1/login - Login

## Password Reset

POST /v1/password/forgot - Request reset code
POST /v1/password/verify-code - Verify code
POST /v1/password/reset - Set new password

## Authenticated (Requires Token)

Header: Authorization: Bearer {token}

GET /v1/user - Get current user (includes avatar and avatar_url)
PUT /v1/user - Update user profile (multipart/form-data: name, represent_type, organization_name, position, avatar file)
POST /v1/logout - Logout
POST /v1/logout-all - Logout all devices

## Email Verification (Requires Token)

POST /v1/email/send-verification - Send verification code
POST /v1/email/verify - Verify email
GET /v1/email/status - Check verification status

## Games API (Requires Token + Verified Email)

GET /v1/games - List all games in the system (query: ?search=game_name&per_page=20)
GET /v1/user/favorite-games - Get user's favorite games
POST /v1/user/favorite-games - Add games to favorites (body: game_ids array)
PUT /v1/user/favorite-games - Replace all favorites (body: game_ids array)
DELETE /v1/user/favorite-games/{gameId} - Remove game from favorites

## Posts API (Requires Token + Verified Email)

GET /v1/posts - Get feed (query: ?type=global|following&page=1&per_page=20)
POST /v1/posts - Create post
GET /v1/posts/{id} - Get single post
PUT /v1/posts/{id} - Update post (owner only)
DELETE /v1/posts/{id} - Delete post (owner only)

## Leaderboards API (Requires Token + Verified Email)

GET /v1/leaderboard/all-time - All-time leaderboard by total XP (query: ?per_page=50)
GET /v1/leaderboard/monthly - Monthly leaderboard by XP earned this month
GET /v1/leaderboard/weekly - Weekly leaderboard by XP earned this week

## Role-Based (Requires Token + Verified Email)

GET /v1/gamer - Gamer only
GET /v1/recruiter - Recruiter only

## Request Examples

Register:
POST /v1/register
{
"name": "Ahmed",
"username": "ahmed",
"email": "ahmed@test.com",
"password": "12345678",
"password_confirmation": "12345678",
"role": "gamer"
}

Note: username must be 3-20 characters, unique, alphanumeric and underscore only

Login:
POST /v1/login
{
"email": "ahmed@test.com",
"password": "12345678"
}

Update Profile:
PUT /v1/user
Authorization: Bearer {token}
Content-Type: multipart/form-data
Form Data:
- name: "Updated Name"
- represent_type: "individual"
- organization_name: "Gaming Org"
- position: "Pro Player"
- avatar: [image file - jpeg, png, jpg, gif, max 2MB]

Note: All fields are optional. Email, username, and date_of_birth cannot be changed through this endpoint.

## Response Format

Success:
{
"success": true,
"message": "...",
"data": { ... }
}

Error:
{
"success": false,
"message": "Error reason"
}

## Posts API Examples

Create Post:
POST /v1/posts
Authorization: Bearer {token}
{
  "content": "Hey @john_doe check this out!",
  "type": "text",
  "game_ids": [1, 2],
  "media": [
    {
      "type": "image",
      "url": "https://example.com/image.jpg"
    }
  ]
}

Get Feed:
GET /v1/posts?type=global&per_page=20
Authorization: Bearer {token}

## Games API Examples

Get All Games:
GET /v1/games?search=&per_page=20
Authorization: Bearer {token}

Add Favorite Games:
POST /v1/user/favorite-games
Authorization: Bearer {token}
{
  "game_ids": [1, 5, 12]
}

Get User's Favorite Games:
GET /v1/user/favorite-games
Authorization: Bearer {token}

Remove from Favorites:
DELETE /v1/user/favorite-games/5
Authorization: Bearer {token}

## Leaderboards API Examples

Get All-Time Leaderboard:
GET /v1/leaderboard/all-time?per_page=50
Authorization: Bearer {token}

Get Monthly Leaderboard:
GET /v1/leaderboard/monthly?per_page=50
Authorization: Bearer {token}

Get Weekly Leaderboard:
GET /v1/leaderboard/weekly?per_page=50
Authorization: Bearer {token}

## Notes

-   All requests need: Accept: application/json
-   Token comes from /register or /login
-   Username: 3-20 characters, unique, alphanumeric and underscore only
-   Email verification code expires in 30 minutes
-   Password reset code expires in 60 minutes
-   Posts support @mentions (automatically parsed and validated)
-   Posts support game tags and media attachments
-   Avatar uploads: jpeg, png, jpg, gif formats, max 2MB
-   Avatars stored in storage/app/public/avatars/
-   Avatar URLs auto-generated and included in user response (avatar_url field)
-   Run 'php artisan storage:link' once for avatar access
-   Games catalog: browse and favorite games from the system
-   Favorite games are stored per user and can be managed via API
-   User ranking system: Bronze (0-99 XP), Silver (100-499 XP), Gold (500+ XP)
-   Ranks computed dynamically from 'ranks' table (not stored in users table)
-   XP earned through onboarding: name (10 XP), birthday (10 XP), represent (15 XP), completion (20 XP)
-   XP transactions tracked in 'xp_transactions' table
-   Leaderboards available: all-time, monthly, and weekly based on XP earned
-   Rank automatically computed when user data is accessed
