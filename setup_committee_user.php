<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

echo "<h2>إعداد مستخدم اللجنة</h2>";

try {
    // التحقق من وجود المستخدم committee@learnata.edu
    $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", ['committee@learnata.edu']);
    
    if (!$user) {
        echo "<p>المستخدم غير موجود. سيتم إنشاؤه...</p>";
        
        // إنشاء المستخدم
        $password = password_hash('password', PASSWORD_DEFAULT);
        $db->execute(
            "INSERT INTO users (username, email, password, full_name, user_type, status) VALUES (?, ?, ?, ?, ?, ?)",
            ['committee001', 'committee@learnata.edu', $password, 'عضو لجنة القبول', 'committee', 'active']
        );
        $userId = 1;
        echo "<p>✓ تم إنشاء المستخدم committee@learnata.edu</p>";
    } else {
        $userId = $user['id'];
        echo "<p>✓ المستخدم موجود (ID: $userId)</p>";
    }
    
    // التحقق من وجود جدول committees
    try {
        $db->fetchOne("SELECT 1 FROM committees LIMIT 1");
    } catch (Exception $e) {
        // إنشاء الجدول
        $db->execute("CREATE TABLE IF NOT EXISTS committees (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            status VARCHAR(20) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "<p>✓ تم إنشاء جدول committees</p>";
    }
    
    // التحقق من وجود جدول committee_members
    try {
        $db->fetchOne("SELECT 1 FROM committee_members LIMIT 1");
    } catch (Exception $e) {
        $db->execute("CREATE TABLE IF NOT EXISTS committee_members (
            id INT PRIMARY KEY AUTO_INCREMENT,
            committee_id INT NOT NULL,
            user_id INT NOT NULL,
            role VARCHAR(50) DEFAULT 'reviewer',
            status VARCHAR(20) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "<p>✓ تم إنشاء جدول committee_members</p>";
    }
    
    // التحقق من وجود لجنة
    $committee = $db->fetchOne("SELECT * FROM committees WHERE name = ?", ['لجنة القبول والتسجيل']);
    if (!$committee) {
        $db->execute("INSERT INTO committees (name, description) VALUES (?, ?)", 
            ['لجنة القبول والتسجيل', 'لجنة تقييم وقبول الطلاب']);
        $committeeId = 1;
        echo "<p>✓ تم إنشاء لجنة القبول والتسجيل</p>";
    } else {
        $committeeId = $committee['id'];
        echo "<p>✓ لجنة القبول موجودة (ID: $committeeId)</p>";
    }
    
    // التحقق من عضوية المستخدم في اللجنة
    $member = $db->fetchOne("SELECT * FROM committee_members WHERE user_id = ? AND committee_id = ?", 
        [$userId, $committeeId]);
    
    if (!$member) {
        $db->execute("INSERT INTO committee_members (committee_id, user_id, role, status) VALUES (?, ?, ?, ?)",
            [$committeeId, $userId, 'reviewer', 'active']);
        echo "<p style='color:green'>✓ تم إضافة المستخدم للجنة</p>";
    } else {
        echo "<p>✓ المستخدم عضو في اللجنة</p>";
    }
    
    echo "<hr><h3>معلومات تسجيل الدخول:</h3>";
    echo "<p>البريد: committee@learnata.edu</p>";
    echo "<p>كلمة المرور: password</p>";
    echo "<p><a href='admin/login.php'>← تسجيل الدخول الآن</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>خطأ: " . $e->getMessage() . "</p>";
}
?>
