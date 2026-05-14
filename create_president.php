<?php
// create_president.php - إنشاء حساب رئيس الجامعة

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<!DOCTYPE html>
<html dir=\"rtl\" lang=\"ar\">
<head>
    <meta charset=\"UTF-8\">
    <title>إنشاء حساب رئيس الجامعة</title>
    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css\">
    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css\">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; }
        .card { border: none; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .card-header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 20px 20px 0 0 !important; padding: 30px; text-align: center; }
        .card-header i { font-size: 3rem; margin-bottom: 15px; }
        .login-info { background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); padding: 20px; border-radius: 15px; margin-top: 20px; border-right: 5px solid #2196f3; }
        .password-highlight { color: #d32f2f; font-weight: bold; font-size: 1.2rem; }
        .btn-success { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border: none; padding: 12px 30px; font-size: 1.1rem; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
    </style>
</head>
<body>
<div class=\"container\">";

try {
    $db = getDB();
    
    // إنشاء جدول presidents إذا لم يكن موجوداً
    $db->execute("CREATE TABLE IF NOT EXISTS presidents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        president_id VARCHAR(50) UNIQUE,
        office_location VARCHAR(100),
        office_hours VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "<div class=\"card\">
        <div class=\"card-header\">
            <i class=\"fas fa-crown\"></i>
            <h3 class=\"mb-0\">إنشاء حساب رئيس الجامعة</h3>
        </div>
        <div class=\"card-body p-4\">";
    
    // التحقق من وجود المستخدم مسبقاً
    $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ? OR username = ?", 
        ['president@learnata.edu', 'president001']);
    
    if ($existingUser) {
        $userId = $existingUser['id'];
        echo "<div class=\"alert alert-info\">
            <i class=\"fas fa-info-circle me-2\"></i>
            المستخدم موجود مسبقاً (ID: $userId)
        </div>";
    } else {
        // إضافة مستخدم رئيس الجامعة
        $passwordHash = password_hash('password', PASSWORD_DEFAULT);
        
        $userId = $db->insert(
            "INSERT INTO users (username, email, password, full_name, phone, user_type, status) 
             VALUES (?, ?, ?, ?, ?, 'admin', 'active')",
            ['president001', 'president@learnata.edu', $passwordHash, 'رئيس الجامعة', '0991112222']
        );
        
        echo "<div class=\"alert alert-success\">
            <i class=\"fas fa-check-circle me-2\"></i>
            ✅ تم إنشاء المستخدم رئيس الجامعة بنجاح!
        </div>";
    }
    
    // التحقق من وجوده في جدول admins
    $existingAdmin = $db->fetchOne("SELECT id FROM admins WHERE user_id = ?", [$userId]);
    
    if (!$existingAdmin) {
        $adminId = 'ADMIN' . date('Y') . '001';
        $db->execute(
            "INSERT INTO admins (user_id, admin_id, role) 
             VALUES (?, ?, 'president')",
            [$userId, $adminId]
        );
        echo "<div class=\"alert alert-success\">
            <i class=\"fas fa-check-circle me-2\"></i>
            ✅ تم إضافة رئيس الجامعة إلى جدول admins
        </div>";
    }
    
    // التحقق من وجوده في جدول presidents
    $existingPresident = $db->fetchOne("SELECT id FROM presidents WHERE user_id = ?", [$userId]);
    
    if (!$existingPresident) {
        $presidentId = 'PRES' . date('Y') . '001';
        $db->execute(
            "INSERT INTO presidents (user_id, president_id, office_location, office_hours) 
             VALUES (?, ?, 'مبنى الإدارة الرئيسي - الطابق 5', 'الأحد - الخميس: 8:00 - 16:00')",
            [$userId, $presidentId]
        );
        echo "<div class=\"alert alert-success\">
            <i class=\"fas fa-check-circle me-2\"></i>
            ✅ تم إضافة رئيس الجامعة إلى جدول presidents
        </div>";
    }
    
    // عرض بيانات تسجيل الدخول
    echo "
        <div class=\"login-info\">
            <h5 class=\"mb-3\"><i class=\"fas fa-key me-2\"></i>بيانات تسجيل الدخول:</h5>
            <div class=\"row\">
                <div class=\"col-md-6 mb-2\">
                    <strong>اسم المستخدم:</strong><br>
                    <code>president001</code>
                </div>
                <div class=\"col-md-6 mb-2\">
                    <strong>البريد الإلكتروني:</strong><br>
                    <span>president@learnata.edu</span>
                </div>
            </div>
            <div class=\"mt-3\">
                <strong>كلمة المرور:</strong><br>
                <span class=\"password-highlight\">password</span>
            </div>
            <div class=\"mt-3\">
                <strong>الرابط:</strong><br>
                <a href=\"admin/login.php\" target=\"_blank\">admin/login.php</a>
            </div>
        </div>
        
        <div class=\"d-grid gap-2 mt-4\">
            <a href=\"display_all_users.php\" class=\"btn btn-primary btn-lg\">
                <i class=\"fas fa-users me-2\"></i>عرض جميع المستخدمين
            </a>
            <a href=\"admin/login.php\" class=\"btn btn-success btn-lg\" target=\"_blank\">
                <i class=\"fas fa-sign-in-alt me-2\"></i>تسجيل الدخول كرئيس جامعة
            </a>
        </div>
    </div>
</div>";
    
} catch (Exception $e) {
    echo "<div class=\"alert alert-danger\">❌ خطأ: " . $e->getMessage() . "</div>";
}

echo "
</div>
</body>
</html>";
?>
