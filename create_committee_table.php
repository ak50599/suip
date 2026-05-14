<?php
// create_committee_table.php - إنشاء جدول لجنة القبول وإضافة حساب

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<h2>إنشاء جدول لجنة القبول</h2>";
echo "<hr>";

try {
    $db = getDB();
    
    // حذف الجدول القديم إذا كان موجوداً باسم مختلف
    $db->execute("DROP TABLE IF EXISTS committee_users");
    
    // إنشاء الجدول بالاسم الصحيح
    $db->execute("CREATE TABLE IF NOT EXISTS committee_members (
        committee_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        committee_name VARCHAR(100) DEFAULT 'لجنة القبول والتسجيل',
        committee_email VARCHAR(100) UNIQUE,
        role ENUM('reviewer', 'manager', 'admin') DEFAULT 'reviewer',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ تم إنشاء جدول committee_members بنجاح<br>";
    
    // التحقق من وجود المستخدم
    $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", ['committee@learnata.edu']);
    
    if (!$existingUser) {
        // إضافة مستخدم اللجنة
        $userId = $db->insert("INSERT INTO users (username, email, password, full_name, phone, user_type, status) 
                      VALUES ('committee001', 'committee@learnata.edu', 
                      '$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
                      'لجنة القبول والتسجيل', '0993334444', 'admin', 'active')");
        
        echo "✅ تم إضافة المستخدم committee001 (ID: $userId)<br>";
    } else {
        $userId = $existingUser['id'];
        echo "ℹ️ المستخدم موجود مسبقاً (ID: $userId)<br>";
    }
    
    // التحقق من وجود العضو في committee_members
    $existingCommittee = $db->fetchOne("SELECT committee_id FROM committee_members WHERE user_id = ?", [$userId]);
    
    if (!$existingCommittee) {
        $db->execute("INSERT INTO committee_members (user_id, committee_name, committee_email, role, status) 
                      VALUES (?, 'لجنة القبول والتسجيل', 'committee@learnata.edu', 'reviewer', 'active')", [$userId]);
        echo "✅ تم إضافة العضو إلى committee_members<br>";
    } else {
        echo "ℹ️ العضو موجود مسبقاً في committee_members<br>";
    }
    
    echo "<hr>";
    echo "<div class='alert alert-success'>✅ تم إعداد حساب لجنة القبول بنجاح!</div>";
    echo "<h4>بيانات الدخول:</h4>";
    echo "<ul>";
    echo "<li><strong>البريد:</strong> committee@learnata.edu</li>";
    echo "<li><strong>كلمة المرور:</strong> password</li>";
    echo "<li><strong>الرابط:</strong> <a href='admin/login.php'>admin/login.php</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ خطأ: " . $e->getMessage() . "</div>";
}
?>
<style>
body { font-family: Arial; padding: 20px; direction: rtl; }
.alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
.alert-success { background: #d4edda; color: #155724; }
.alert-danger { background: #f8d7da; color: #721c24; }
</style>
