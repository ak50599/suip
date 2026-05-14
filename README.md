# بوابة الطالب الإلكترونية (SUIP - Student University Information Portal)

نظام إدارة جامعي متكامل باستخدام PHP 8+, MySQL, Bootstrap 5 RTL مع واجهة عربية.  
تم إعادة هيكلة النظام بالكامل لاتباع نمط MVC مع تحسينات أمنية واضافات جديدة.

## 🔥 التحديثات الجديدة (إبريل 2026)

### ✅ إعادة الهيكلة الكاملة (MVC)
- **BaseController** - Controller أساسي مشترك
- **Template Engine** - فصل PHP عن HTML
- **Service Layer** - طبقة Business Logic
- **Repository Pattern** - عزل قاعدة البيانات

### ✅ تحسينات الأمان
- **Rate Limiting** - حماية من Brute Force (5 محاولات = 15 دقيقة قفل)
- **HTTPS Cookies** - مع SameSite=Strict
- **إخفاء الأخطاء** في الإنتاج مع تسجيل في ملفات

### ✅ RESTful API
- نقاط نهاية موحدة للإشعارات والطلاب والمواد
- وثائق Swagger/OpenAPI

### ✅ PHPUnit Tests
- 23 اختبار (100% نجاح)
- اختبارات Unit و Integration

---

## 🏗️ الهيكل الجديد (MVC)

```
suip/
├── 📁 includes/           # Core Components
│   ├── BaseController.php    # Controller الأساسي
│   ├── View.php              # Template Engine
│   ├── Logger.php            # Logging مركزي
│   ├── RateLimiter.php       # حماية Brute Force
│   ├── db.php                # Database (PDO + Singleton)
│   ├── auth.php              # المصادقة
│   └── functions.php         # دوال مساعدة
│
├── 📁 src/               # Business Logic
│   ├── Services/
│   │   ├── StudentService.php    # منطق الطلاب
│   │   └── CourseService.php     # منطق المواد
│   └── Repositories/
│       ├── BaseRepository.php    # Repository أساسي
│       ├── StudentRepository.php # Repository طلاب
│       └── UserRepository.php    # Repository مستخدمين
│
├── 📁 api/               # RESTful API
│   ├── ApiHandler.php        # API Router
│   ├── NotificationsController.php
│   ├── index.php             # نقاط النهاية
│   └── swagger.json          # وثائق API
│
├── 📁 templates/         # Views (Templates)
│   ├── layout/main.php       # Layout رئيسي
│   ├── partials/
│   │   ├── sidebar.php       # Sidebar ديناميكي
│   │   └── header.php        # Header
│   └── student/
│       └── dashboard.php     # قالب Dashboard
│
├── 📁 student/           # Controllers الطالب
│   ├── dashboard.php         # محدث لـ MVC
│   └── StudentController.php # Controller جديد
│
├── 📁 admin/             # Controllers الإدارة
├── 📁 professor/         # Controllers الأساتذة
├── 📁 dean/              # Controllers العمداء
├── 📁 database/          # قاعدة البيانات
│   ├── schema/           # الهيكل الأساسي
│   ├── migrations/       # الترقيات
│   ├── data/             # البيانات
│   └── utils/            # سكريبتات مساعدة
│
├── 📁 tests/             # PHPUnit Tests
│   ├── Unit/
│   │   ├── AuthTest.php
│   │   └── ValidationTest.php
│   └── Integration/
│       └── DatabaseTest.php
│
├── 📁 logs/              # سجلات الأخطاء
├── 📁 vendor/            # Composer Dependencies
├── composer.json         # إدارة الاعتماديات
├── phpunit.xml          # إعدادات PHPUnit
├── phpstan.neon         # إعدادات PHPStan
└── .htaccess            # إعدادات Apache
```

---

## 🚀 الميزات

## الميزات الرئيسية

### 1. نظام المصادقة والأمان
- تسجيل دخول/خروج آمن
- حماية CSRF
- إدارة الجلسات
- صلاحيات متدرجة (RBAC)

### 2. ثلاثة أنواع مستخدمين
- **طالب**: الوصول إلى المواد، الدرجات، الجدول، التسجيل، المدفوعات
- **أستاذ**: إدارة المواد، إدخال الدرجات، رفع المحتوى
- **إداري**: إدارة المستخدمين، المواد، الإعلانات، التقارير

### 3. الميزات الأكاديمية
- تسجيل المواد الدراسية
- عرض الجداول الدراسية
- إدخال وعرض الدرجات
- رفع وتحميل المحتوى الأكاديمي

### 4. الميزات المالية
- عرض الرسوم والمدفوعات
- فواتير إلكترونية
- تتبع المدفوعات

### 5. نظام الإشعارات
- إشعارات فورية
- إعلانات الجامعة
- تنبيهات المواعيد

## المتطلبات

- PHP 8.0 أو أحدث
- MySQL 8.0 أو أحدث
- Apache/Nginx
- متصفح حديث يدعم ES6

## التثبيت

### 1. إعداد قاعدة البيانات
```bash
mysql -u root -p < database/schema.sql
```

### 2. إعداد الإعدادات
تعديل ملف `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'اسم_المستخدم');
define('DB_PASS', 'كلمة_المرور');
define('DB_NAME', 'learnata_clone');
```

### 3. إعدادات الموقع
```php
define('SITE_URL', 'http://localhost/learnata-clone');
```

### 4. صلاحيات المجلدات
```bash
chmod 755 uploads/
chmod 644 includes/config.php
```

## بيانات الاختبار

### طالب تجريبي
- اسم المستخدم: `student001`
- كلمة المرور: `password`
- البريد: `student@learnata.edu`

### أستاذ تجريبي
- اسم المستخدم: `prof001`
- كلمة المرور: `password`
- البريد: `professor@learnata.edu`

### إداري تجريبي
- اسم المستخدم: `admin001`
- كلمة المرور: `password`
- البريد: `admin@learnata.edu`

## هيكل المجلدات

```
/learnata-clone/
├── admin/              # لوحة تحكم الإداري
├── professor/          # لوحة تحكم الأستاذ
├── student/            # لوحة تحكم الطالب
├── api/                # نقاط نهاية API
├── assets/             # CSS, JS, Images
├── database/           # ملفات SQL
├── includes/           # ملفات PHP المشتركة
├── uploads/            # ملفات مرفوعة
├── index.php          # صفحة تسجيل الدخول
├── logout.php         # تسجيل الخروج
└── README.md          # هذا الملف
```

## التقنيات المستخدمة

### Frontend
- HTML5
- CSS3 (Custom + Bootstrap 5.3 RTL)
- JavaScript (ES6+)
- Font Awesome Icons
- Google Fonts (Tajawal)

### Backend
- PHP 8.x (OOP)
- MySQL 8.x
- PDO للاتصال بقاعدة البيانات
- Sessions للمصادقة

### الأمان
- CSRF Protection
- Password Hashing (bcrypt)
- SQL Injection Prevention (Prepared Statements)
- XSS Protection
- **Rate Limiting** - حماية من Brute Force
- **HTTPS Cookies** - مع SameSite=Strict

---

## 🧪 الاختبارات والأدوات

### PHPUnit Tests
```bash
# تشغيل جميع الاختبارات
c:\xampp\php\php.exe vendor/bin/phpunit

# نتيجة: 23/23 اختبار ناجح (100%)
```

### PHPStan - تحليل الكود
```bash
# فحص الكود
php vendor/bin/phpstan analyse

# النتيجة: 13 خطأ فقط (غير حرجة)
```

### PHP_CodeSniffer - تنسيق الكود
```bash
# تنسيق تلقائي
php vendor/bin/phpcbf --standard=PSR12 src includes

# فحص
php vendor/bin/phpcs --standard=PSR12 src includes

# النتيجة: 6 أخطاء فقط من 561
```

### Composer Scripts
```bash
# اختبارات
composer test
composer test:unit
composer test:integration

# تحليل
composer analyse

# تنسيق
composer lint
composer lint:fix
```

---

## 📚 وثائق API (Swagger)

وثائق API متاحة في `api/swagger.json`

### نقاط النهاية المتاحة:
- `GET /notifications` - جلب الإشعارات
- `POST /notifications/{id}/read` - تحديد كمقروء
- `GET /students` - جلب الطلاب (Admin)
- `GET /courses` - جلب المواد
- `GET /me` - معلومات المستخدم
- `PUT /me` - تحديث المستخدم
- `GET /stats/dashboard` - إحصائيات

---

## 🚀 الاستخدام

### تسجيل الدخول
1. افتح `http://localhost/learnata-clone`
2. أدخل اسم المستخدم وكلمة المرور
3. يتم توجيهك تلقائياً حسب نوع المستخدم

### للطلاب
- عرض المواد المسجلة
- متابعة الدرجات
- عرض الجدول الدراسي
- الوصول للمحتوى الأكاديمي

### للأساتذة
- إدارة المواد المُدرَّسة
- إدخال درجات الطلاب
- رفع المحتوى الأكاديمي
- متابعة تسليمات الواجبات

### للإداريين
- إدارة جميع المستخدمين
- إدارة المواد والفصول
- إدارة الإعلانات
- عرض التقارير والإحصائيات

## ✅ التطوير المستقبلي

### تم إنجازه (إبريل 2026):
- ✅ إعادة الهيكلة (MVC)
- ✅ Rate Limiting
- ✅ HTTPS Cookies
- ✅ RESTful API
- ✅ PHPUnit Tests (23 اختبار)
- ✅ Service Layer
- ✅ Repository Pattern
- ✅ Logger مركزي
- ✅ Template Engine

### قيد التطوير:
- [ ] تطبيق موبايل (React Native)
- [ ] نظام المحادثات المباشرة
- [ ] التكامل مع بوابات الدفع الإلكتروني
- [ ] نظام الذكاء الاصطناعي للدعم الطلابي
- [ ] تطبيق إلكتروني للحضور والغياب

## المساهمة

نرحب بمساهماتكم! يرجى اتباع الخطوات التالية:
1. Fork المشروع
2. إنشاء فرع للميزة الجديدة
3. Commit التغييرات
4. Push إلى الفرع
5. فتح Pull Request

## الترخيص

هذا المشروع مفتوح المصدر تحت ترخيص MIT.

## الدعم

للاستفسارات والدعم الفني:
- البريد الإلكتروني: support@learnata.edu
- الهاتف: +963-XX-XXXXXXX

## المطورون

تم تطوير هذا النظام بواسطة مجموعة اوتوماتا4.

---

**ملاحظة**: هذا نظام تعليمي للتدريب والتعلم. تأكد من اتباع أفضل ممارسات الأمان في بيئة الإنتاج.

