# دليل اختبار PSN API Service

## الخطوة 1: تثبيت Dependencies

```bash
cd psn-api-service
npm install
```

## الخطوة 2: تشغيل Service

```bash
npm start
```

يجب أن ترى:
```
🚀 PSN API Service running on http://localhost:3001
📝 Endpoints:
   POST /api/exchange-npsso
   POST /api/user-profile
   GET  /health
```

## الخطوة 3: اختبار Health Check

في terminal جديد:

```bash
curl http://localhost:3001/health
```

**النتيجة المتوقعة:**
```json
{
  "status": "ok",
  "service": "psn-api-service"
}
```

## الخطوة 4: اختبار Exchange NPSSO Token

### 4.1 الحصول على NPSSO Token

1. افتح https://www.playstation.com وسجّل الدخول
2. اضغط `F12` → Application → Cookies → `https://www.playstation.com`
3. ابحث عن cookie باسم `npsso` وانسخ قيمته

### 4.2 اختبار API

```bash
curl -X POST http://localhost:3001/api/exchange-npsso \
  -H "Content-Type: application/json" \
  -d '{"npsso": "YOUR_NPSSO_TOKEN_HERE"}'
```

**النتيجة المتوقعة (نجاح):**
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

**النتيجة المتوقعة (فشل):**
```json
{
  "success": false,
  "error": "Error message here",
  "code": "ERROR_CODE"
}
```

## الخطوة 5: اختبار Laravel Integration

### 5.1 إضافة إلى .env

أضف هذا السطر إلى `.env`:

```env
PSN_API_SERVICE_URL=http://localhost:3001
```

### 5.2 اختبار من Laravel

استخدم Postman أو curl:

```bash
# تأكد من أنك مسجل دخول وحصلت على token
curl -X POST http://localhost:8000/api/v1/playstation/link \
  -H "Authorization: Bearer YOUR_LARAVEL_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"npsso_token": "YOUR_NPSSO_TOKEN"}'
```

## الخطوة 6: مراقبة Logs

### Node.js Service Logs
في terminal الذي شغلت فيه `npm start`، سترى:
```
[2025-12-21T...] Exchange request received for NPSSO: rQ3zqexhgmfDxUJSfXs2...
Step 1: Exchanging NPSSO for Access Code...
✅ Access Code obtained
Step 2: Exchanging Access Code for Access Token...
✅ Access Token obtained
```

### Laravel Logs
راجع `storage/logs/laravel.log`:
```php
[2025-12-21 ...] PSN: Exchanging NPSSO token via Node.js service
```

## استكشاف الأخطاء

### خطأ: "Cannot connect to PSN API Service"
**السبب:** Node.js service غير شغال
**الحل:**
```bash
cd psn-api-service
npm start
```

### خطأ: "NPSSO token is required"
**السبب:** لم ترسل NPSSO token
**الحل:** تأكد من إرسال `npsso` في body

### خطأ: "Failed to exchange NPSSO token"
**السبب:** NPSSO token غير صحيح أو منتهي
**الحل:** احصل على NPSSO token جديد من PlayStation.com

### خطأ: "Port 3001 already in use"
**السبب:** Port 3001 مستخدم
**الحل:** غيّر PORT في `.env` أو أوقف العملية المستخدمة:
```bash
lsof -ti:3001 | xargs kill
```

## اختبار سريع (Test Script)

أنشئ ملف `test.sh`:

```bash
#!/bin/bash

# Test Health
echo "Testing Health Check..."
curl http://localhost:3001/health
echo -e "\n\n"

# Test Exchange (ضع NPSSO token هنا)
echo "Testing Exchange NPSSO..."
curl -X POST http://localhost:3001/api/exchange-npsso \
  -H "Content-Type: application/json" \
  -d '{"npsso": "YOUR_NPSSO_TOKEN"}'
echo -e "\n"
```

شغله:
```bash
chmod +x test.sh
./test.sh
```
