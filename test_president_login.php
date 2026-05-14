<?php
// test_president_login.php - اختبار تسجيل دخول رئيس الجامعة

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<!DOCTYPE html>
<html dir=\"rtl\" lang=\"ar\">
<head>
    <meta charset=\"UTF-8\">
    <title>اختبار تسجيل دخول رئيس الجامعة</title>
    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css\">
    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css\">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 30px auto; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0 !important; padding: 20px; }
        .test-pass { color: #28a745; }
        .test-fail { color: #dc3545; }
        .login-box { background: #e3f2fd; padding: 20px; border-radius: 10px; margin-top: 20px; border-right: 5px solid #2196f3; }
    </style>
</head>
<body>
<div class=\"container\">";

try {
    $db = getDB();
    
    echo "<div class=\"card\">
        <div class=\"card-header\">
            <h3 class=\"mb-0\"><i class=\"fas fa-crown me-2\"></i>اختبار تسجيل دخول رئيس الجامعة</h3>
        </div>
        <div class=\"card-body\">";
    
    // اختبار 1: التحقق من وجود المستخدم
    echo "<h5>1. التحقق من وجود المستخدم:</h5>";
    $user = $db->fetchOne("SELECT * FROM users WHERE email = 'president@learnata.edu'");
    if ($user) {
        echo "<p class=\"test-pass\"><i class=\"fas fa-check-circle\"></i> ✅ المستخدم موجود</p>";
        echo "<ul>";
        echo "<li>ID: " . $user['id'] . "</li>";
        echo "<li>Username: " . htmlspecialchars($user['username']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
        echo "<li>User Type: " . htmlspecialchars($user['user_type']) . "</li>";
        echo "<li>Status: " . htmlspecialchars($user['status']) . "</li>";
        echo "</ul>";
        
        // اختبار 2: التحقق من وجوده في جدول admins
        echo "<h5>2. التحقق من وجوده في جدول admins:</h5>";
        $admin = $db->fetchOne("SELECT * FROM admins WHERE user_id = ?", [$user['id']]);
        if ($admin) {
            echo "<p class=\"test-pass\"><i class=\"fas fa-check-circle\"></i> ✅ موجود في جدول admins</p>";
            echo "<ul>";
            echo "<li>Admin ID: " . htmlspecialchars($admin['admin_id']) . "</li>";
            echo "<li>Role: " . htmlspecialchars($admin['role']) . "</li>";
            echo "</ul>";
            
            // اختبار 3: محاكاة تسجيل الدخول
            echo "<h5>3. محاكاة تسجيل الدخول:</h5>";
            $testPassword = 'password';
            if (password_verify($testPassword, $user['password'])) {
                echo "<p class=\"test-pass\"><i class=\"fas fa-check-circle\"></i> ✅ كلمة المرور صحيحة</p>";
                
                // اختبار 4: التحقق من الاستعلام المستخدم في login.php
                echo "<h5>4. اختبار استعلام تسجيل الدخول:</h5>";
                $loginUser = $db->fetchOne(
                    "SELECT u.*, a.admin_id, a.role as admin_role, 'admin' as user_category
                     FROM users u
                     JOIN admins a ON u.id = a.user_id
                     WHERE u.email = ? AND u.status = 'active' AND u.user_type = 'admin'",
                    ['president@learnata.edu']
                );
                if ($loginUser) {
                    echo "<p class=\"test-pass\"><i class=\"fas fa-check-circle\"></i> ✅ استعلام تسجيل الدخول يعمل</p>";
                    echo "<p>Admin Role: " . htmlspecialchars($loginUser['admin_role']) . "</p>";
                    
                    echo "<div class=\"alert alert-success mt-4\">
                        <h5><i class=\"fas fa-check-circle me-2\"></i>✅ جميع الاختبارات ناجحة!</h5>
                        <p>يمكن لرئيس الجامعة تسجيل الدخول بنجاح.</p>
                    </div>";
                } else {
                    echo "<p class=\"test-fail\"><i class=\"fas fa-times-circle\"></i> ❌ استعلام تسجيل الدخول فشل</p>";
                }
            } else {
                echo "<p class=\"test-fail\"><i class=\"fas fa-times-circle\"></i> ❌ كلمة المرور غير صحيحة</p>";
            }
        } else {
            echo "<p class=\"test-fail\"><i class=\"fas fa-times-circle\"></i> ❌ غير موجود في جدول admins</p>";
        }
    } else {
        echo "<p class=\"test-fail\"><i class=\"fas fa-times-circle\"></i> ❌ المستخدم غير موجود</p>";
    }
    
    // بيانات تسجيل الدخول
    echo "<div class=\"login-box\">";
    echo "<h5><i class=\"fas fa-key me-2\"></i>بيانات تسجيل الدخول:</h5>";
    echo "<p><strong>البريد:</strong> president@learnata.edu</p>";
    echo "<p><strong>كلمة المرور:</strong> <span class=\"text-danger fw-bold\">password</span></p>";
    echo "<p><strong>الرابط:</strong> <a href=\"admin/login.php\" target=\"_blank\">admin/login.php</a></p>";
    echo "</div>";
    
    // أزرار
    echo "<div class=\"d-grid gap-2 mt-4\">";
    echo "<a href=\"admin/login.php\" class=\"btn btn-success btn-lg\" target=\"_blank\"><i class=\"fas fa-sign-in-alt me-2\"></i>تسجيل الدخول الآن</a>";
    echo "<a href=\"display_all_users.php\" class=\"btn btn-primary\"><i class=\"fas fa-users me-2\"></i>عرض جميع المستخدمين</a>";
    echo "</div>";
    
    echo "</div></div>";
    
} catch (Exception $e) {
    echo "<div class=\"alert alert-danger\">❌ خطأ: " . $e->getMessage() . "</div>";
}

echo "
</div>
</body>
</html>";
?>
