<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h2>معلومات الجلسة الحالية</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    $db = getDB();
    try {
        $committee = $db->fetchOne(
            "SELECT cm.committee_id, c.name as committee_name, cm.role as committee_role 
            FROM committee_members cm 
            LEFT JOIN committees c ON cm.committee_id = c.id 
            WHERE cm.user_id = ? AND cm.status = 'active'",
            [$_SESSION['user_id']]
        );
        echo "<h3>معلومات اللجنة من قاعدة البيانات:</h3>";
        print_r($committee);
    } catch (Exception $e) {
        echo "<p>خطأ: " . $e->getMessage() . "</p>";
    }
}

echo "<hr><a href='admin/login.php'>تسجيل الدخول</a> | <a href='committee/dashboard.php'>صفحة اللجنة</a>";
