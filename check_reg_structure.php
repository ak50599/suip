<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

try {
    $cols = $db->fetchAll("SHOW COLUMNS FROM registration_settings");
    echo "=== هيكل جدول registration_settings ===\n";
    foreach ($cols as $c) {
        echo $c['Field'] . " - " . $c['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
