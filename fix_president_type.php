<?php
// fix_president_type.php - تصحيح نوع مستخدم رئيس الجامعة

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<!DOCTYPE html>
<html dir=\"rtl\" lang=\"ar\">
<head>
    <meta charset=\"UTF-8\">
    <title>تصحيح رئيس الجامعة</title>
    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css\">
    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css\">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
        .card-header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 20px 20px 0 0 !important; padding: 25px; text-align: center; }
        .card-header i { font-size: 3rem; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class=\"container\">";

try {
    $db = getDB();
    
    echo "<div class=\"card\">
        <div class=\"card-header\">
            <i class=\"fas fa-crown\"></i>
            <h3 class=\"mb-0\">تصحيح رئيس الجامعة</h3>
        </div>
        <div class=\"card-body p-4\">";
    
    // التحقق من المستخدم الحالي
    $user = $db->fetchOne("SELECT * FROM users WHERE email = 'president@learnata.edu'");
    if ($user) {
        echo "<h5>الحالة الحالية:</h5>";
        echo "<ul>";
        echo "<li>ID: " . $user['id'] . "</li>";
        echo "<li>User Type: <span class=\"badge bg-warning\">" . htmlspecialchars($user['user_type']) . "</span></li>";
        echo "</ul>";
        
        // تصحيح نوع المستخدم
        if ($user['user_type'] === 'super_admin') {
            $db->execute("UPDATE users SET user_type = 'admin' WHERE id = ?", [$user['id']]);
            echo "<div class=\"alert alert-success\">
                <i class=\"fas fa-check-circle me-2\"></i>
                ✅ تم تصحيح نوع المستخدم من 'super_admin' إلى 'admin'
            </div>";
        } else {
            echo "<div class=\"alert alert-info\">
                <i class=\"fas fa-info-circle me-2\"></i>
                ℹ️ نوع المستخدم صحيح: 'admin'
            </div>";
        }
        
        // اختبار الاستعلام مرة أخرى
        $testUser = $db->fetchOne(
            "SELECT u.*, a.admin_id, a.role as admin_role, 'admin' as user_category
             FROM users u
             JOIN admins a ON u.id = a.user_id
             WHERE u.email = ? AND u.status = 'active' AND u.user_type = 'admin'",
            ['president@learnata.edu']
        );
        
        if ($testUser) {
            echo "<div class=\"alert alert-success mt-3\">
                <h5><i class=\"fas fa-check-circle me-2\"></i>✅ نجح!</h5>
                <p>يمكن الآن تسجيل الدخول بنجاح.</p>
            </div>";
            
            echo "<div class=\"d-grid gap-2 mt-4\">";
            echo "<a href=\"admin/login.php\" class=\"btn btn-success btn-lg\" target=\"_blank\"><i class=\"fas fa-sign-in-alt me-2\"></i>تسجيل الدخول الآن</a>";
            echo "<a href=\"display_all_users.php\" class=\"btn btn-primary\"><i class=\"fas fa-users me-2\"></i>عرض جميع المستخدمين</a>";
            echo "</div>";
        } else {
            echo "<div class=\"alert alert-danger mt-3\">
                ❌ لا يزال هناك مشكلة في الاستعلام
            </div>";
        }
    } else {
        echo "<div class=\"alert alert-danger\">❌ المستخدم غير موجود</div>";
    }
    
    echo "</div></div>";
    
} catch (Exception $e) {
    echo "<div class=\"alert alert-danger\">❌ خطأ: " . $e->getMessage() . "</div>";
}

echo "
</div>
</body>
</html>";
?>
