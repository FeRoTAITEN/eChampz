# eChampz API Endpoints

Base URL: http://localhost:8000/api

## Public (No Auth)

GET /health - Health check
GET /v1/roles - Get available roles
POST /v1/register - Register new user
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

## Role-Based (Requires Token + Verified Email)

GET /v1/gamer - Gamer only
GET /v1/recruiter - Recruiter only

## Request Examples

Register:
POST /v1/register
{
"name": "Ahmed",
"email": "ahmed@test.com",
"password": "12345678",
"password_confirmation": "12345678",
"role": "gamer"
}

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

## Notes

-   All requests need: Accept: application/json
-   Token comes from /register or /login
-   Email verification code expires in 30 minutes
-   Password reset code expires in 60 minutes
