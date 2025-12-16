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

GET /v1/user - Get current user
POST /v1/logout - Logout
POST /v1/logout-all - Logout all devices

## Email Verification (Requires Token)

POST /v1/email/send-verification - Send verification code
POST /v1/email/verify - Verify email
GET /v1/email/status - Check verification status

## Posts API (Requires Token + Verified Email)

GET /v1/posts - Get feed (query: ?type=global|following&page=1&per_page=20)
POST /v1/posts - Create post
GET /v1/posts/{id} - Get single post
PUT /v1/posts/{id} - Update post (owner only)
DELETE /v1/posts/{id} - Delete post (owner only)

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

## Notes

-   All requests need: Accept: application/json
-   Token comes from /register or /login
-   Username: 3-20 characters, unique, alphanumeric and underscore only
-   Email verification code expires in 30 minutes
-   Password reset code expires in 60 minutes
-   Posts support @mentions (automatically parsed and validated)
-   Posts support game tags and media attachments
