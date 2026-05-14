<?php
// install_missing_tables.php - تثبيت جميع الجداول المفقودة

require_once 'includes/config.php';
require_once 'includes/db.php';

$tables = [
    // جدول الكليات (يجب إنشاؤه قبل deans)
    'colleges' => "CREATE TABLE IF NOT EXISTS `colleges` (
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

    // جدول العمداء
    'deans' => "CREATE TABLE IF NOT EXISTS `deans` (
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

    // جدول لجنة القبول
    'committee' => "CREATE TABLE IF NOT EXISTS `committee` (
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

    // جدول الموظفين
    'staff' => "CREATE TABLE IF NOT EXISTS `staff` (
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

    // جدول السكرتارية
    'secretaries' => "CREATE TABLE IF NOT EXISTS `secretaries` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

$db = getDB();
$success = [];
$errors = [];

foreach ($tables as $name => $sql) {
    try {
        $db->execute($sql);
        $success[] = $name;
        echo "✅ تم إنشاء جدول: {$name}\n";
    } catch (Exception $e) {
        $errors[] = $name . ': ' . $e->getMessage();
        echo "❌ خطأ في إنشاء جدول {$name}: " . $e->getMessage() . "\n";
    }
}

// إضافة بيانات الكليات إذا تم إنشاء الجدول بنجاح
if (in_array('colleges', $success)) {
    try {
        $collegesData = [
            ['MED', 'كلية الطب البشري', 'College of Medicine'],
            ['DENT', 'كلية طب الأسنان', 'College of Dentistry'],
            ['PHAR', 'كلية الصيدلة', 'College of Pharmacy'],
            ['IT_ENG', 'كلية الهندسة المعلوماتية', 'College of Information Engineering'],
            ['PET_ENG', 'كلية الهندسة البترولية', 'College of Petroleum Engineering'],
            ['CHEM_ENG', 'كلية الهندسة الكيميائية', 'College of Chemical Engineering'],
            ['COMP', 'معهد الحاسوب', 'Computer Institute'],
            ['LANG', 'كلية الترجمة واللغات', 'College of Translation and Languages']
        ];
        
        foreach ($collegesData as $college) {
            $db->execute(
                "INSERT IGNORE INTO colleges (college_code, college_name, college_name_en) VALUES (?, ?, ?)",
                $college
            );
        }
        echo "✅ تم إضافة بيانات الكليات\n";
    } catch (Exception $e) {
        echo "⚠️ لم يتم إضافة بيانات الكليات: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "النتيجة:\n";
echo "✅ نجح: " . count($success) . " جداول\n";
if (!empty($errors)) {
    echo "❌ فشل: " . count($errors) . " جداول\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
}
