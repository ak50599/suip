<?php
/**
 * create_registration_settings.php - إنشاء جدول إعدادات التسجيل
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDB();
    
    // إنشاء جدول إعدادات التسجيل
    $sql = "CREATE TABLE IF NOT EXISTS registration_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        is_open TINYINT(1) DEFAULT 0,
        start_date DATETIME,
        end_date DATETIME,
        semester_id INT,
        max_credits INT DEFAULT 18,
        min_credits INT DEFAULT 12,
        notes TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->execute($sql);
    
    // إضافة إعدادات افتراضية
    $db->execute(
        "INSERT INTO registration_settings (is_open, start_date, end_date, notes) 
         VALUES (0, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'إعدادات افتراضية')"
    );
    
    echo '<div class="alert alert-success">✅ تم إنشاء جدول registration_settings بنجاح!</div>';
    echo '<a href="dean/registration_control.php" class="btn btn-primary">العودة لصفحة التحكم</a>';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">❌ خطأ: ' . $e->getMessage() . '</div>';
}
