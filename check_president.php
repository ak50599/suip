<?php
// check_president.php - التحقق من وجود رئيس الجامعة

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<!DOCTYPE html>
<html dir=\"rtl\" lang=\"ar\">
<head>
    <meta charset=\"UTF-8\">
    <title>التحقق من رئيس الجامعة</title>
    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css\">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 30px auto; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0 !important; padding: 20px; }
        .status-found { color: #28a745; font-weight: bold; }
        .status-missing { color: #dc3545; font-weight: bold; }
        .user-card { border-right: 4px solid #28a745; }
        .login-info { background: #e3f2fd; padding: 15px; border-radius: 10px; margin-top: 10px; }
    </style>
</head>
<body>
<div class=\"container\">";

try {
    $db = getDB();
    
    echo "<div class=\"card\">
        <div class=\"card-header\">
            <h3 class=\"mb-0\"><i class=\"fas fa-crown me-2\"></i>التحقق من رئيس الجامعة</h3>
        </div>
        <div class=\"card-body\">";
    
    // البحث عن رئيس الجامعة في جدول users
    $presidentUsers = $db->fetchAll("SELECT * FROM users WHERE user_type = 'president' OR email LIKE '%president%' OR username LIKE '%president%'");
    
    // البحث في جدول presidents إذا كان موجوداً
    $presidentsTableExists = $db->fetchOne("SHOW TABLES LIKE 'presidents'");
    $presidentsList = [];
    if ($presidentsTableExists) {
        $presidentsList = $db->fetchAll("SELECT p.*, u.username, u.email, u.full_name, u.password 
                                          FROM presidents p 
                                          JOIN users u ON p.user_id = u.id");
    }
    
    // عرض النتائج
    if (count($presidentUsers) > 0 || count($presidentsList) > 0) {
        echo "<div class=\"alert alert-success\">
            <i class=\"fas fa-check-circle me-2\"></i>
            <span class=\"status-found\">✅ تم العثور على رئيس جامعة!</span>
        </div>";
        
        // عرض المستخدمين من جدول users
        foreach ($presidentUsers as $user) {
            echo "<div class=\"card mb-3 user-card\">
                <div class=\"card-body\">
                    <h5><i class=\"fas fa-user-tie me-2\"></i>" . htmlspecialchars($user['full_name']) . "</h5>
                    <div class=\"row\">
                        <div class=\"col-md-4\">
                            <strong>اسم المستخدم:</strong><br>
                            <code>" . htmlspecialchars($user['username']) . "</code>
                        </div>
                        <div class=\"col-md-4\">
                            <strong>البريد:</strong><br>
                            " . htmlspecialchars($user['email']) . "
                        </div>
                        <div class=\"col-md-4\">
                            <strong>نوع المستخدم:</strong><br>
                            <span class=\"badge bg-primary\">" . htmlspecialchars($user['user_type']) . "</span>
                        </div>
                    </div>
                    <div class=\"login-info mt-3\">
                        <strong>كلمة المرور:</strong> 
                        <span class=\"text-danger fw-bold\">password</span>
                    </div>
                </div>
            </div>";
        }
        
        // عرض المستخدمين من جدول presidents
        foreach ($presidentsList as $president) {
            echo "<div class=\"card mb-3 user-card\">
                <div class=\"card-body\">
                    <h5><i class=\"fas fa-crown me-2\"></i>" . htmlspecialchars($president['full_name']) . "</h5>
                    <div class=\"row\">
                        <div class=\"col-md-4\">
                            <strong>اسم المستخدم:</strong><br>
                            <code>" . htmlspecialchars($president['username']) . "</code>
                        </div>
                        <div class=\"col-md-4\">
                            <strong>البريد:</strong><br>
                            " . htmlspecialchars($president['email']) . "
                        </div>
                        <div class=\"col-md-4\">
                            <strong>رقم الرئيس:</strong><br>
                            " . htmlspecialchars($president['president_id'] ?? 'غير محدد') . "
                        </div>
                    </div>
                    <div class=\"login-info mt-3\">
                        <strong>كلمة المرور:</strong> 
                        <span class=\"text-danger fw-bold\">password</span>
                    </div>
                </div>
            </div>";
        }
        
    } else {
        echo "<div class=\"alert alert-warning\">
            <i class=\"fas fa-exclamation-triangle me-2\"></i>
            <span class=\"status-missing\">❌ لا يوجد رئيس جامعة!</span>
        </div>
        
        <div class=\"mt-4\">
            <h5>إنشاء حساب رئيس جامعة:</h5>
            <p>هل تريد إنشاء حساب لرئيس الجامعة الآن؟</p>
            <a href=\"create_president.php\" class=\"btn btn-success\">
                <i class=\"fas fa-plus me-2\"></i>إنشاء حساب رئيس الجامعة
            </a>
        </div>";
    }
    
    echo "</div></div>";
    
    // زر العودة
    echo "<div class=\"text-center mt-4\">
        <a href=\"display_all_users.php\" class=\"btn btn-primary\">
            <i class=\"fas fa-arrow-right me-2\"></i>العودة لعرض جميع المستخدمين
        </a>
    </div>";
    
} catch (Exception $e) {
    echo "<div class=\"alert alert-danger\">❌ خطأ: " . $e->getMessage() . "</div>";
}

echo "
</div>
<script src=\"https://kit.fontawesome.com/a076d05399.js\" crossorigin=\"anonymous\"></script>
</body>
</html>";
?>
