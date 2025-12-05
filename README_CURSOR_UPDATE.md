# تحديث Cursor - دليل شامل

## المشكلة
روابط التحديث المباشرة محمية (403 Forbidden) ولا يمكن التنزيل تلقائياً من سطر الأوامر.

## الحلول المتاحة

### الحل 1: التحديث من خلال Cursor نفسه (الأسهل)
1. افتح Cursor
2. اضغط على أيقونة الإعدادات (⚙️) في الزاوية السفلية اليسرى
3. اختر "Check for Updates"
4. إذا كان هناك تحديث، سيتم تنزيله تلقائياً

### الحل 2: التحديث اليدوي
1. قم بزيارة: https://cursor.sh
2. اضغط على "Download" لتنزيل أحدث إصدار
3. أغلق Cursor إذا كان يعمل:
   ```bash
   pkill -f cursor.AppImage
   ```
4. استبدل الملف:
   ```bash
   sudo cp ~/Downloads/Cursor-*.AppImage /opt/cursor/cursor.AppImage
   sudo chmod +x /opt/cursor/cursor.AppImage
   ```

### الحل 3: استخدام السكريبت المرفق
تم إنشاء سكريبت `update-cursor-full.sh` الذي يحاول جميع الطرق الممكنة:

```bash
sudo /home/fero/Documents/echampz-project/update-cursor-full.sh
```

**ملاحظة:** السكريبت سيقوم بإنشاء نسخة احتياطية تلقائياً قبل التحديث.

## معلومات الإصدار الحالي
- المسار: `/opt/cursor/cursor.AppImage`
- الإصدار الحالي: يمكن التحقق منه بـ:
  ```bash
  /opt/cursor/cursor.AppImage --appimage-version
  ```

## النسخ الاحتياطية
يتم حفظ النسخ الاحتياطية في: `/opt/cursor/backups/`

