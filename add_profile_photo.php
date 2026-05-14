<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

try {
    // Add profile_photo column
    $db->execute("ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULL AFTER phone");
    echo "✅ تم إضافة عمود profile_photo بنجاح!\n";
    
    // Verify
    $cols = $db->fetchAll("SHOW COLUMNS FROM users");
    echo "\n=== أعمدة جدول users ===\n";
    foreach ($cols as $c) {
        echo $c['Field'] . " - " . $c['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
