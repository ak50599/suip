<?php
/**
 * install_complete_database.php - تثبيت شامل لجميع جداول قاعدة البيانات
 * 
 * هذا السكريبت يقوم بـ:
 * 1. التحقق من الاتصال بقاعدة البيانات
 * 2. إنشاء جميع الجداول المفقودة
 * 3. إضافة البيانات الأساسية
 * 4. عرض تقرير مفصل
 */

// إعدادات قاعدة البيانات
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'learnata_clone';

// نمط الإخراج
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تثبيت قاعدة البيانات - SUIP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background: #f5f5f5; padding: 20px; }
        .log-container { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #4caf50; }
        .error { color: #f44336; }
        .warning { color: #ff9800; }
        .info { color: #2196f3; }
        .table-status { margin: 5px 0; padding: 10px; border-radius: 5px; }
        .table-success { background: #e8f5e9; border-right: 4px solid #4caf50; }
        .table-error { background: #ffebee; border-right: 4px solid #f44336; }
        .table-exists { background: #e3f2fd; border-right: 4px solid #2196f3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="log-container">
            <h2 class="mb-4"><i class="fas fa-database me-2"></i>تثبيت قاعدة البيانات</h2>
            <div id="log">';

function logMessage($message, $type = 'info') {
    $class = $type === 'success' ? 'success' : ($type === 'error' ? 'error' : ($type === 'warning' ? 'warning' : 'info'));
    echo "<div class=\"{$class}\">{$message}</div>";
    flush();
}

// 1. التحقق من الاتصال بقاعدة البيانات
logMessage("🔌 جاري التحقق من الاتصال بقاعدة البيانات...", "info");

try {
    $pdo = new PDO("mysql:host={$dbHost};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    logMessage("✅ تم الاتصال بـ MySQL بنجاح", "success");
} catch (PDOException $e) {
    logMessage("❌ فشل الاتصال بـ MySQL: " . $e->getMessage(), "error");
    logMessage("💡 تأكد من أن XAMPP يعمل (MySQL)", "warning");
    echo '</div></div></div></body></html>';
    exit;
}

// 2. إنشاء قاعدة البيانات إذا لم تكن موجودة
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    logMessage("✅ قاعدة البيانات '{$dbName}' جاهزة", "success");
} catch (PDOException $e) {
    logMessage("❌ فشل إنشاء قاعدة البيانات: " . $e->getMessage(), "error");
    exit;
}

// الاتصال بقاعدة البيانات
$pdo->exec("USE {$dbName}");

// 3. التحقق من الجداول الموجودة
$existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
logMessage("📊 الجداول الموجودة حالياً: " . count($existingTables), "info");

// 4. تعريف جميع الجداول المطلوبة
$requiredTables = [
    // جدول محاولات تسجيل الدخول
    'login_attempts' => [
        'sql' => "CREATE TABLE IF NOT EXISTS `login_attempts` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `ip_address` varchar(45) NOT NULL,
            `identifier` varchar(255) DEFAULT NULL COMMENT 'username, email, or user_id',
            `attempted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `success` tinyint(1) NOT NULL DEFAULT 0,
            `user_agent` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `ip_address` (`ip_address`),
            KEY `identifier` (`identifier`),
            KEY `attempted_at` (`attempted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'data' => null
    ],

    // جدول الكليات
    'colleges' => [
        'sql' => "CREATE TABLE IF NOT EXISTS `colleges` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `college_code` varchar(20) UNIQUE NOT NULL,
            `college_name` varchar(100) NOT NULL,
            `college_name_en` varchar(100),
            `description` text,
            `status` enum('active', 'inactive') DEFAULT 'active',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'data' => [
            ['MED', 'كلية الطب البشري', 'College of Medicine'],
            ['DENT', 'كلية طب الأسنان', 'College of Dentistry'],
            ['PHAR', 'كلية الصيدلة', 'College of Pharmacy'],
            ['IT_ENG', 'كلية الهندسة المعلوماتية', 'College of Information Engineering'],
            ['PET_ENG', 'كلية الهندسة البترولية', 'College of Petroleum Engineering'],
            ['CHEM_ENG', 'كلية الهندسة الكيميائية', 'College of Chemical Engineering'],
            ['COMP', 'معهد الحاسوب', 'Computer Institute'],
            ['LANG', 'كلية الترجمة واللغات', 'College of Translation and Languages']
        ]
    ],

    // جدول العمداء (الإصدار المُبسط)
    'deans' => [
        'sql' => "CREATE TABLE IF NOT EXISTS `deans` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(11) UNSIGNED NOT NULL,
            `dean_id` varchar(50) NOT NULL,
            `college` varchar(100) NOT NULL,
            `department` varchar(100) DEFAULT NULL,
            `office_location` varchar(100) DEFAULT NULL,
            `office_hours` varchar(255) DEFAULT NULL,
            `specialization` varchar(100) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`),
            UNIQUE KEY `dean_id` (`dean_id`),
            KEY `college` (`college`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'data' => null
    ],

    // جدول لجنة القبول
    'committee' => [
        'sql' => "CREATE TABLE IF NOT EXISTS `committee` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(11) UNSIGNED NOT NULL,
            `committee_id` varchar(50) NOT NULL,
            `role` varchar(50) DEFAULT 'member',
            `department` varchar(100) DEFAULT NULL,
            `responsibilities` text DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`),
            UNIQUE KEY `committee_id` (`committee_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'data' => null
    ],

    // جدول الموظفين
    'staff' => [
        'sql' => "CREATE TABLE IF NOT EXISTS `staff` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(11) UNSIGNED NOT NULL,
            `staff_id` varchar(50) NOT NULL,
            `department` varchar(100) DEFAULT NULL,
            `position` varchar(100) DEFAULT NULL,
            `office_location` varchar(100) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`),
            UNIQUE KEY `staff_id` (`staff_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'data' => null
    ],

    // جدول السكرتارية
    'secretaries' => [
        'sql' => "CREATE TABLE IF NOT EXISTS `secretaries` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(11) UNSIGNED NOT NULL,
            `secretary_id` varchar(50) NOT NULL,
            `department` varchar(100) DEFAULT NULL,
            `office_location` varchar(100) DEFAULT NULL,
            `supervisor_id` int(11) UNSIGNED DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`),
            UNIQUE KEY `secretary_id` (`secretary_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'data' => null
    ],

    // جدول سكرتارية العميد
    'dean_secretaries' => [
        'sql' => "CREATE TABLE IF NOT EXISTS `dean_secretaries` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(11) UNSIGNED NOT NULL,
            `dean_id` int(11) UNSIGNED NOT NULL,
            `secretary_id` varchar(50) NOT NULL,
            `secretary_email` varchar(100) UNIQUE NOT NULL,
            `full_name` varchar(100) NOT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`),
            UNIQUE KEY `secretary_id` (`secretary_id`),
            UNIQUE KEY `secretary_email` (`secretary_email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'data' => null
    ]
];

// 5. إنشاء/تحديث الجداول
$stats = ['created' => 0, 'exists' => 0, 'error' => 0];

echo '<h4 class="mt-4 mb-3">حالة الجداول:</h4>';

foreach ($requiredTables as $tableName => $tableInfo) {
    $tableExists = in_array($tableName, $existingTables);
    
    if ($tableExists) {
        echo "<div class='table-status table-exists'>📘 الجدول '{$tableName}' موجود مسبقاً</div>";
        $stats['exists']++;
        continue;
    }
    
    try {
        $pdo->exec($tableInfo['sql']);
        echo "<div class='table-status table-success'>✅ تم إنشاء جدول '{$tableName}'</div>";
        $stats['created']++;
        
        // إضافة البيانات الافتراضية إذا وجدت
        if (!empty($tableInfo['data'])) {
            $columns = implode(', ', array_map(fn($i) => $i === 0 ? 'college_code' : ($i === 1 ? 'college_name' : 'college_name_en'), array_keys($tableInfo['data'][0])));
            $placeholders = implode(', ', array_fill(0, count($tableInfo['data'][0]), '?'));
            $stmt = $pdo->prepare("INSERT IGNORE INTO {$tableName} ({$columns}) VALUES ({$placeholders})");
            
            foreach ($tableInfo['data'] as $row) {
                $stmt->execute(array_values($row));
            }
            echo "<div class='table-status table-success'>   📥 تم إضافة " . count($tableInfo['data']) . " صفوف</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='table-status table-error'>❌ خطأ في إنشاء '{$tableName}': " . $e->getMessage() . "</div>";
        $stats['error']++;
    }
}

// 6. التحقق من الجداول الأساسية في schema.sql
$essentialTables = ['users', 'students', 'professors', 'admins', 'courses', 'enrollments'];
$missingEssential = [];

foreach ($essentialTables as $table) {
    if (!in_array($table, $existingTables)) {
        $missingEssential[] = $table;
    }
}

if (!empty($missingEssential)) {
    echo '<div class="alert alert-warning mt-4">';
    echo '<h5>⚠️ الجداول الأساسية التالية غير موجودة:</h5>';
    echo '<ul>';
    foreach ($missingEssential as $table) {
        echo "<li>{$table}</li>";
    }
    echo '</ul>';
    echo '<p>يجب استيراد ملف <code>database/schema.sql</code>:</p>';
    echo '<pre class="bg-dark text-white p-2">mysql -u root -p learnata_clone < database/schema.sql</pre>';
    echo '</div>';
}

// 7. إحصائيات نهائية
echo '<hr class="my-4">';
echo '<h4>📊 الإحصائيات:</h4>';
echo "<div class='row'>";
echo "<div class='col-md-3'><div class='alert alert-success'>✅ تم إنشاء: {$stats['created']} جدول</div></div>";
echo "<div class='col-md-3'><div class='alert alert-info'>📘 موجود مسبقاً: {$stats['exists']} جدول</div></div>";
echo "<div class='col-md-3'><div class='alert alert-danger'>❌ فشل: {$stats['error']} جدول</div></div>";
echo "<div class='col-md-3'><div class='alert alert-primary'>📊 المجموع: " . count($existingTables) + $stats['created'] . " جدول</div></div>";
echo "</div>";

// 8. روابط مفيدة
echo '<hr class="my-4">';
echo '<h4>🔗 الخطوات التالية:</h4>';
echo '<div class="list-group">';
echo '<a href="display_all_users.php" class="list-group-item list-group-item-action">👥 عرض معلومات تسجيل الدخول</a>';
echo '<a href="index.php" class="list-group-item list-group-item-action">🏠 الصفحة الرئيسية</a>';
echo '<a href="admin/login.php" class="list-group-item list-group-item-action">🔐 تسجيل الدخول</a>';
echo '</div>';

echo '</div></div></div></body></html>';
