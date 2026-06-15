# مشروع مسكن (Maskan) — ملخص التعديلات

## المشروع
تطبيق حجز واستئجار المنتجعات والاستراحات في ليبيا. مبني بـ Laravel 11 (PHP 8.2) و Bootstrap 5.

---

## 2026-06-15 — إصلاح أزرار الرفض والموافقة للمسؤول

### المشكلة
زر رفض العقار لا يعمل بشكل صحيح عند المسؤول.

### التعديلات

1. **`app/Http/Controllers/WebAdminController.php:297`** — `pendingProperties`:
   - كان يعرض **جميع** العقارات بدلاً من التي بانتظار الموافقة فقط.
   - أضفت `->where('status', 'pending')` ليظهر فقط عقارات `pending`.

2. **`resources/views/admin/properties_pending.blade.php:47-53`** — إضافة زر رفض:
   - أضفت زر رفض (`action-btn danger`) بجوار زر الموافقة.
   - يرسل قيمة افتراضية `Rejected by admin` كسبب للرفض.

3. **`resources/views/admin/properties.blade.php:75-89`** — حصر الأزرار:
   - أزرار الموافقة والرفض كانت تظهر لجميع العقارات (محجوزة، صيانة، غير متاحة...).
   - أضفت `@if($property->status === 'pending')` لتظهر فقط للعقارات المعلقة.

4. **`routes/web.php:176-178`** — تغيير نوع المسار:
   - المسارات كانت `Route::match(['GET', 'POST'])` مما يسمح بالوصول عبر GET.
   - غيرت إلى `Route::post()` لمنع الوصول عبر GET الذي يسبب خطأ في `RejectPropertyRequest`.

---

## 2026-03-15 — تحسينات وأعمال سابقة

### التوثيق العربي
أضفت تعليقات توثيق بالعربية لـ:
- 56 ملف PHP
- 55 ملف Blade
- 8 ملفات Python
- 17 مجلد

### التحقق من صحة رقم الهاتف الليبي
- غيرت التحقق من `digits:10` إلى `regex:/^09[0-9]{8}$/` ليتوافق مع أرقام الهواتف الليبية (09XX XXX XXX).

### فلاتر (Flutter) — التحقق من صحة الإدخالات
- **register**: إضافة validation باستخدام `phone` package.
- **login**: التحقق من صحة رقم الهاتف.
- **forgot_password**: التحقق من صحة البريد الإلكتروني.
- **edit_profile**: التحقق من صحة رقم الهاتف والاسم.

### بليد — سمات `pattern` و `placeholder`
- إضافة `pattern="^09[0-9]{8}$"` لحقول الهاتف في ملفات blade.
- إضافة `placeholder` بالعربية.
- الملفات: register, login, edit user, profile, create property, edit property.

### ملفات متأثرة
- `resources/views/auth/register.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/profile/edit.blade.php`
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`
- `resources/views/properties/create.blade.php`
- `resources/views/properties/edit.blade.php`
- `mobile/lib/screens/register_screen.dart`
- `mobile/lib/screens/login_screen.dart`
- `mobile/lib/screens/forgot_password_screen.dart`
- `mobile/lib/screens/edit_profile_screen.dart`
- `app/Http/Requests/RegisterRequest.php`
- `app/Http/Requests/LoginRequest.php`
- `app/Http/Requests/ProfileUpdateRequest.php`
