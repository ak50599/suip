<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

// التحقق من وجود المستخدم في جدول users
$user = $db->fetchOne("SELECT * FROM users WHERE email = ?", ['president@learnata.edu']);

echo "<h2>فحص المستخدم president@learnata.edu</h2>";

if ($user) {
    echo "<h3>جدول users:</h3>";
    echo "<p>ID: " . $user['id'] . "</p>";
    echo "<p>Username: " . $user['username'] . "</p>";
    echo "<p>Email: " . $user['email'] . "</p>";
    echo "<p>Type: " . $user['user_type'] . "</p>";
    echo "<p>Status: " . $user['status'] . "</p>";
    
    // التحقق من جدول admins
    $admin = $db->fetchOne("SELECT * FROM admins WHERE user_id = ?", [$user['id']]);
    echo "<h3>جدول admins:</h3>";
    if ($admin) {
        echo "<p style='color:green'>✓ موجود في admins</p>";
        echo "<p>Admin ID: " . $admin['admin_id'] . "</p>";
        echo "<p>Role: " . $admin['role'] . "</p>";
    } else {
        echo "<p style='color:red'>✗ غير موجود في admins</p>";
    }
    
    // التحقق من committee_members
    $committee = $db->fetchOne("SELECT * FROM committee_members WHERE user_id = ? AND status = 'active'", [$user['id']]);
    echo "<h3>جدول committee_members:</h3>";
    if ($committee) {
        echo "<p style='color:green'>✓ عضو لجنة</p>";
        echo "<p>Committee ID: " . $committee['committee_id'] . "</p>";
    } else {
        echo "<p style='color:red'>✗ ليس عضو لجنة</p>";
    }
} else {
    echo "<p style='color:red'>المستخدم غير موجود</p>";
}
?>
