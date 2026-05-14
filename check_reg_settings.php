<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

try {
    $settings = $db->fetchOne("SELECT * FROM registration_settings LIMIT 1");
    echo "=== إعدادات التسجيل ===\n";
    print_r($settings);
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
