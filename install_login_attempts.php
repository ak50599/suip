<?php
// install_login_attempts.php - إنشاء جدول login_attempts

require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    $db = getDB();
    
    $sql = "CREATE TABLE IF NOT EXISTS `login_attempts` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->execute($sql);
    
    echo "✅ تم إنشاء جدول login_attempts بنجاح!\n";
    
    // التحقق من وجود الجدول
    $result = $db->fetchOne("SHOW TABLES LIKE 'login_attempts'");
    if ($result) {
        echo "✅ تم التحقق: الجدول موجود في قاعدة البيانات\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
