<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

echo "<h2>إضافة المستخدم إلى جدول admins</h2>";

try {
    // التحقق من وجود المستخدم في admins
    $existing = $db->fetchOne("SELECT * FROM admins WHERE user_id = 5");
    
    if ($existing) {
        echo "<p>المستخدم موجود بالفعل في جدول admins</p>";
        echo "<p>Admin ID: " . $existing['admin_id'] . "</p>";
        echo "<p>Role: " . $existing['role'] . "</p>";
    } else {
        // إضافة المستخدم
        $db->execute(
            "INSERT INTO admins (user_id, admin_id, role) VALUES (?, ?, ?)",
            [5, 'ADM005', 'president']
        );
        echo "<p style='color:green'>✓ تم إضافة المستخدم بنجاح!</p>";
        echo "<p>User ID: 5</p>";
        echo "<p>Admin ID: ADM005</p>";
        echo "<p>Role: president</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='admin/login.php' style='color:#667eea'>← العودة لتسجيل الدخول</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>خطأ: " . $e->getMessage() . "</p>";
}
?>
