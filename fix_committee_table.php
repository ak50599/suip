<?php
// fix_committee_table.php - إضافة عمود last_login

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<h2>إصلاح جدول committee_users</h2>";
echo "<hr>";

try {
    $db = getDB();
    
    // إضافة عمود last_login
    $db->execute("ALTER TABLE committee_users ADD COLUMN IF NOT EXISTS last_login DATETIME NULL");
    
    echo "✅ تم إضافة عمود last_login بنجاح!<br>";
    echo "<hr>";
    echo "<a href='admin/login.php' class='btn btn-primary'>جرب تسجيل الدخول الآن</a>";
    
} catch (Exception $e) {
    echo "<div style='color:red'>❌ خطأ: " . $e->getMessage() . "</div>";
}
?>
<style>
body { font-family: Arial; padding: 20px; direction: rtl; }
.btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
</style>
