<?php
// debug_committee.php - فحص بيانات لجنة القبول

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<h2>فحص بيانات لجنة القبول</h2>";
echo "<hr>";

try {
    $db = getDB();
    
    // 1. التحقق من وجود الجدول
    echo "<h4>1. التحقق من جدول committee_users:</h4>";
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'committee_users'");
    if ($tableExists) {
        echo "✅ جدول committee_users موجود<br>";
    } else {
        echo "❌ جدول committee_users غير موجود!<br>";
    }
    
    // 2. التحقق من المستخدم في جدول users
    echo "<h4>2. البحث في جدول users:</h4>";
    $user = $db->fetchOne("SELECT * FROM users WHERE email = 'committee@learnata.edu'");
    if ($user) {
        echo "✅ المستخدم موجود في users:<br>";
        echo "- ID: " . $user['id'] . "<br>";
        echo "- Username: " . $user['username'] . "<br>";
        echo "- Email: " . $user['email'] . "<br>";
        echo "- User Type: " . $user['user_type'] . "<br>";
        echo "- Status: " . $user['status'] . "<br>";
        echo "- Password Hash: " . substr($user['password'], 0, 20) . "...<br>";
        
        // 3. التحقق من committee_users
        echo "<h4>3. البحث في جدول committee_users:</h4>";
        $committee = $db->fetchOne("SELECT * FROM committee_users WHERE user_id = ?", [$user['id']]);
        if ($committee) {
            echo "✅ العضو موجود في committee_users:<br>";
            echo "- Committee ID: " . $committee['id'] . "<br>";
            echo "- User ID: " . $committee['user_id'] . "<br>";
            echo "- Email: " . $committee['committee_email'] . "<br>";
            echo "- Role: " . $committee['role'] . "<br>";
            echo "- Is Active: " . $committee['is_active'] . "<br>";
        } else {
            echo "❌ العضو غير موجود في committee_users!<br>";
        }
        
        // 4. اختبار الاستعلام المستخدم في login.php
        echo "<h4>4. اختبار الاستعلام المستخدم في login:</h4>";
        $testUser = $db->fetchOne(
            "SELECT u.*, cu.id as committee_id, u.email as committee_email, cu.role, 'committee' as user_category
             FROM users u
             JOIN committee_users cu ON u.id = cu.user_id
             WHERE u.email = ? AND u.status = 'active' AND cu.is_active = 1",
            ['committee@learnata.edu']
        );
        if ($testUser) {
            echo "✅ الاستعلام ناجح! المستخدم موجود:<br>";
            echo "- ID: " . $testUser['id'] . "<br>";
            echo "- Role: " . $testUser['role'] . "<br>";
        } else {
            echo "❌ الاستعلام فشل!<br>";
        }
        
        // 5. التحقق من كلمة المرور
        echo "<h4>5. التحقق من كلمة المرور:</h4>";
        $testPassword = 'password';
        if (password_verify($testPassword, $user['password'])) {
            echo "✅ كلمة المرور 'password' صحيحة!<br>";
        } else {
            echo "❌ كلمة المرور 'password' غير صحيحة!<br>";
        }
        
    } else {
        echo "❌ المستخدم غير موجود في جدول users!<br>";
    }
    
    echo "<hr>";
    echo "<h4>حلول مقترحة:</h4>";
    echo "<ul>";
    echo "<li>إذا كان الجدول غير موجود: <a href='create_committee_table.php'>أنشئ الجدول</a></li>";
    echo "<li>إذا كان المستخدم غير موجود: شغّل create_committee_table.php</li>";
    echo "<li>إذا كانت كلمة المرور غير صحيحة: استخدم السكربت لإعادة تعيينها</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='color:red'>❌ خطأ: " . $e->getMessage() . "</div>";
}
?>
<style>
body { font-family: Arial; padding: 20px; direction: rtl; }
h4 { color: #333; margin-top: 20px; }
</style>
