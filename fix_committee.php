<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

echo "<h2>إنشاء جدول اللجنة وإضافة المستخدم</h2>";

try {
    // إنشاء جدول committees
    $db->execute("CREATE TABLE IF NOT EXISTS committees (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p>✓ جدول committees تم إنشاؤه</p>";
    
    // إنشاء جدول committee_members
    $db->execute("CREATE TABLE IF NOT EXISTS committee_members (
        id INT PRIMARY KEY AUTO_INCREMENT,
        committee_id INT NOT NULL,
        user_id INT NOT NULL,
        role VARCHAR(50) DEFAULT 'reviewer',
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (committee_id) REFERENCES committees(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    echo "<p>✓ جدول committee_members تم إنشاؤه</p>";
    
    // إضافة لجنة القبول
    $existing = $db->fetchOne("SELECT id FROM committees WHERE name = ?", ['لجنة القبول والتسجيل']);
    if (!$existing) {
        $db->execute("INSERT INTO committees (name, description) VALUES (?, ?)", 
            ['لجنة القبول والتسجيل', 'لجنة تقييم وقبول الطلاب']);
        $committeeId = $db->getConnection()->lastInsertId();
        echo "<p>✓ لجنة القبول والتسجيل تم إنشاؤها (ID: $committeeId)</p>";
    } else {
        $committeeId = $existing['id'];
        echo "<p>✓ لجنة القبول والتسجيل موجودة (ID: $committeeId)</p>";
    }
    
    // جلب معرف المستخدم president
    $user = $db->fetchOne("SELECT id FROM users WHERE email = ?", ['president@learnata.edu']);
    if ($user) {
        // التحقق إذا كان عضو بالفعل
        $member = $db->fetchOne("SELECT id FROM committee_members WHERE user_id = ? AND committee_id = ?", 
            [$user['id'], $committeeId]);
        if (!$member) {
            $db->execute("INSERT INTO committee_members (committee_id, user_id, role, status) VALUES (?, ?, ?, ?)",
                [$committeeId, $user['id'], 'chairman', 'active']);
            echo "<p style='color:green'>✓ المستخدم president@learnata.edu أصبح عضو لجنة (رئيس اللجنة)</p>";
        } else {
            echo "<p>✓ المستخدم موجود بالفعل في اللجنة</p>";
        }
    }
    
    echo "<hr><p><a href='admin/login.php' style='color:#667eea'>← تسجيل الدخول الآن</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>خطأ: " . $e->getMessage() . "</p>";
}
?>
