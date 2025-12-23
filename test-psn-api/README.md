# اختبار PSN API

هذا المجلد يحتوي على scripts بسيطة لاختبار PSN API مباشرة.

## الملفات

- `test_psn.php` - Script بسيط لاختبار PSN API من سطر الأوامر

## طريقة الاستخدام

### 1. الحصول على NPSSO Token

1. افتح https://www.playstation.com وسجّل الدخول
2. اضغط `F12` → Application → Cookies → `https://www.playstation.com`
3. ابحث عن cookie باسم `npsso` وانسخ قيمته (64 حرف)

### 2. تعديل Script

افتح `test_psn.php` وعدّل السطر:

```php
$npsso = 'YOUR_NPSSO_TOKEN_HERE'; // ضع NPSSO token هنا
```

ضع NPSSO token الذي نسخته.

### 3. تشغيل Script

```bash
php test_psn.php
```

## ما يفعله Script

1. **الخطوة 1:** يتبادل NPSSO token للحصول على Access Code
2. **الخطوة 2:** يتبادل Access Code للحصول على Access Token
3. **الخطوة 3:** يستخدم Access Token لجلب معلومات المستخدم

## مثال على Output

```
🚀 بدء اختبار PSN API
========================

✅ NPSSO Token: a1b2c3d4e5f6g7h8i9j0...

📝 الخطوة 1: الحصول على Access Code...
   Status Code: 302
✅ Access Code: xyz123abc456def789ghi012...

📝 الخطوة 2: تبادل Access Code للحصول على Access Token...
   Status Code: 200
✅ Access Token: eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...
✅ Refresh Token: def456ghi789jkl012mno345...
✅ Expires In: 3600 seconds

📝 الخطوة 3: اختبار استخدام Access Token...
   Status Code: 200
✅ نجح! PSN Username: YourPSNUsername

🎉 جميع الاختبارات نجحت!
========================
Access Token: eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...
PSN Username: YourPSNUsername

✨ انتهى الاختبار
```

## استكشاف الأخطاء

### خطأ: "يجب وضع NPSSO token"
- تأكد من وضع NPSSO token في المتغير `$npsso`

### خطأ: "فشل في الحصول على Access Code"
- تأكد من أن NPSSO token صحيح وحديث
- جرب الحصول على NPSSO token جديد

### خطأ: "Bad client credentials"
- هذا يعني أن Access Code صحيح لكن Token Exchange فشل
- Script يجرب طريقتين تلقائياً

### خطأ: "فشل في استخدام Access Token"
- Access Token قد يكون غير صحيح
- جرب الحصول على NPSSO token جديد وكرر العملية

## ملاحظات

- NPSSO token صالح لمدة محدودة
- إذا فشل الاختبار، احصل على NPSSO token جديد
- لا تشارك NPSSO token أو Access Token مع أحد
