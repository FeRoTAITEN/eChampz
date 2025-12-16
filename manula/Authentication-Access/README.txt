Authentication & Access APIs
===========================

Base URL
- Local: http://localhost:8000
- Prefix: /api/v1 unless stated (health is /api/health)
- Auth: Bearer token via Laravel Sanctum
- Global rate limit: 60 requests/minute
- Sensitive endpoints throttled 5 req/min: register, login, password/forgot, password/verify-code, password/reset, email/send-verification, email/verify
- Email validation: RFC+DNS check (email:rfc,dns)

Public Endpoints
- POST /api/v1/register
  body: name, username, email, password, password_confirmation, role (gamer|recruiter)
  notes: 
    - username: required, unique, 3-20 characters, alphanumeric and underscore only (regex: /^[a-zA-Z0-9_]+$/)
    - sends verification code automatically; in local/testing the code is returned in response
    - email must pass RFC+DNS check

- POST /api/v1/login
  body: email, password
  notes: if email not verified, auto-sends verification code and includes it in local/testing.

- GET /api/v1/roles
  returns available roles with value/label.

Password Reset
- POST /api/v1/password/forgot
  body: email
  notes: generic response even if email not found (prevents account enumeration); returns reset code in local/testing; code expires in 60 minutes; throttled 5/min.

- POST /api/v1/password/verify-code
  body: email, code (6 digits)
  result: reset_token (temporary); expires in 15 minutes; throttled 5/min.

- POST /api/v1/password/reset
  body: email, reset_token, password, password_confirmation
  result: resets password, revokes all tokens; throttled 5/min.

Authenticated Endpoints (Authorization: Bearer {token})
- GET /api/v1/user
- POST /api/v1/logout
- POST /api/v1/logout-all

Email Verification (requires auth)
- POST /api/v1/email/send-verification
  sends 6-digit code (returned in local/testing); code expires in 30 minutes; throttled 5/min.

- POST /api/v1/email/verify
  body: code (6 digits); throttled 5/min.

- GET /api/v1/email/status
  returns verified flag, email, verified_at.

Role-Protected (requires auth + verified email)
- GET /api/v1/gamer       (middleware: role:gamer)
- GET /api/v1/recruiter   (middleware: role:recruiter)

Health Check
- GET /api/health

Example Flows
- Onboarding: register -> receive code -> email/verify -> hit role route.
- Password recovery: password/forgot -> password/verify-code -> password/reset.

Expirations
- Email verification code: 30 minutes
- Password reset code: 60 minutes
- Password reset token: 15 minutes

Notes
- In production, codes are sent via email; in local/testing they are returned for convenience.
- All responses follow the ApiResponse helper (success flag, message, data).

Testing
- php artisan test
