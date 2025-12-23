# PSN API Service

Node.js microservice for PlayStation Network API authentication using Express.js and psn-api library.

## ✅ الحالة الحالية

**API جاهز ويعمل!** لكن يحتاج:
1. تثبيت dependencies: `npm install`
2. تشغيل service: `npm start`
3. إضافة `PSN_API_SERVICE_URL` إلى Laravel `.env`

## Installation

```bash
npm install
```

## Usage

### Start the service:

```bash
npm start
```

Or for development with auto-reload:

```bash
npm run dev
```

The service will run on `http://localhost:3001` by default.

## API Endpoints

### 1. Health Check
```
GET /health
```

Response:
```json
{
  "status": "ok",
  "service": "psn-api-service"
}
```

### 2. Exchange NPSSO Token
```
POST /api/exchange-npsso
Content-Type: application/json

{
  "npsso": "your_npsso_token_here"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "def456ghi789jkl012mno345...",
    "expires_in": 3600,
    "refresh_token_expires_in": 5184000,
    "token_type": "Bearer",
    "scope": "psn:mobile.v2.core psn:clientapp"
  }
}
```

### 3. Get User Profile
```
POST /api/user-profile
Content-Type: application/json

{
  "access_token": "your_access_token_here"
}
```

## Environment Variables

- `PORT` - Server port (default: 3001)

## Integration with Laravel

Laravel can call this service using:

```php
$response = Http::post('http://localhost:3001/api/exchange-npsso', [
    'npsso' => $npssoToken
]);

$data = $response->json();
$accessToken = $data['data']['access_token'];
```

## Notes

- The service uses the official `psn-api` library which is actively maintained
- All authentication is handled by the library
- The service runs on a separate port and can be deployed independently
