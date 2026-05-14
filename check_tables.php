<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();
$tables = $db->fetchAll('SHOW TABLES');

echo "=== الجداول ===\n";
foreach ($tables as $t) {
    echo array_values($t)[0] . "\n";
}

// Check if users table exists
$check = $db->fetchOne("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'users'");
echo "\nusers table exists: " . ($check['count'] > 0 ? 'YES' : 'NO') . "\n";

if ($check['count'] > 0) {
    echo "\n=== أعمدة جدول users ===\n";
    $cols = $db->fetchAll("SHOW COLUMNS FROM users");
    foreach ($cols as $c) {
        echo $c['Field'] . " - " . $c['Type'] . "\n";
    }
}
