<?php
// run_admin_tables_migration.php - إنشاء جداول لوحة التحكم الذكية

require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h2>إنشاء جداول لوحة التحكم الذكية</h2>";
echo "<pre>";

$db = getDB();

try {
    // 1. إنشاء جدول سجلات النظام (system_logs)
    echo "1. إنشاء جدول system_logs...\n";
    $sql = "CREATE TABLE IF NOT EXISTS system_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(255) NOT NULL,
        description TEXT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        page_url VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        INDEX idx_action (action)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $db->execute($sql);
    echo "✓ تم إنشاء جدول system_logs بنجاح\n\n";
    
    // 2. إنشاء جدول جلسات المستخدمين (user_sessions)
    echo "2. إنشاء جدول user_sessions...\n";
    $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_id VARCHAR(255) NOT NULL UNIQUE,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_session_id (session_id),
        INDEX idx_last_activity (last_activity)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $db->execute($sql);
    echo "✓ تم إنشاء جدول user_sessions بنجاح\n\n";
    
    // 3. إنشاء جدول فحوصات النظام (health_checks)
    echo "3. إنشاء جدول health_checks...\n";
    $sql = "CREATE TABLE IF NOT EXISTS health_checks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        check_type VARCHAR(50) NOT NULL,
        status VARCHAR(20) NOT NULL,
        message TEXT NULL,
        response_time_ms INT NULL,
        checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_check_type (check_type),
        INDEX idx_checked_at (checked_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $db->execute($sql);
    echo "✓ تم إنشاء جدول health_checks بنجاح\n\n";
    
    // 4. إنشاء جدول سجل التعديلات الحساسة (sensitive_actions_log)
    echo "4. إنشاء جدول sensitive_actions_log...\n";
    $sql = "CREATE TABLE IF NOT EXISTS sensitive_actions_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        target_table VARCHAR(100) NULL,
        target_id INT NULL,
        old_value TEXT NULL,
        new_value TEXT NULL,
        ip_address VARCHAR(45) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_action_type (action_type),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $db->execute($sql);
    echo "✓ تم إنشاء جدول sensitive_actions_log بنجاح\n\n";
    
    // 5. إنشاء جدول النسخ الاحتياطي (backups)
    echo "5. إنشاء جدول backups...\n";
    $sql = "CREATE TABLE IF NOT EXISTS backups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        backup_type VARCHAR(50) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_size BIGINT NULL,
        status VARCHAR(20) NOT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_backup_type (backup_type),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $db->execute($sql);
    echo "✓ تم إنشاء جدول backups بنجاح\n\n";
    
    // 6. إنشاء جدول إعدادات النظام (system_settings)
    echo "6. إنشاء جدول system_settings...\n";
    $sql = "CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NULL,
        description TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_setting_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $db->execute($sql);
    echo "✓ تم إنشاء جدول system_settings بنجاح\n\n";
    
    // إدراج إعدادات افتراضية
    echo "7. إدراج الإعدادات الافتراضية...\n";
    $defaultSettings = [
        'maintenance_mode' => '0',
        'backup_enabled' => '1',
        'backup_frequency' => 'daily',
        'log_retention_days' => '30',
        'max_login_attempts' => '5',
        'session_timeout_minutes' => '30',
        'site_name' => SITE_NAME,
        'site_short_name' => SITE_SHORT_NAME,
        'university_name' => SITE_NAME,
        'contact_email' => 'info@learnata.edu',
        'contact_phone' => '00963-11-1234567'
    ];
    
    foreach ($defaultSettings as $key => $value) {
        $sql = "INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)";
        $db->execute($sql, [$key, $value]);
    }
    echo "✓ تم إدراج الإعدادات الافتراضية بنجاح\n\n";
    
    echo "<h3 style='color: green;'>✅ تم إنشاء جميع الجداول بنجاح!</h3>";
    echo "<p><a href='admin/dashboard.php'>الانتقال إلى لوحة التحكم</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ حدث خطأ:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "</pre>";
?>
