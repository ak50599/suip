<?php
// test_users.php - بيانات المستخدمين للاختبار
// يمكن الوصول لهذا الملف من: http://localhost/suip/test_users.php

require_once 'includes/config.php';

// بيانات المستخدمين للاختبار
$testUsers = [
    [
        'type' => 'طالب',
        'username' => 'student001',
        'password' => 'password',
        'email' => 'student@learnata.edu',
        'full_name' => 'حمزة سامي النواف',
        'phone' => '0991234567',
        'login_url' => SITE_URL . '/index.php',
        'dashboard' => SITE_URL . '/student/dashboard.php'
    ],
    [
        'type' => 'أستاذ',
        'username' => 'prof001',
        'password' => 'password',
        'email' => 'professor@learnata.edu',
        'full_name' => 'د. أحمد محمد',
        'phone' => '0997654321',
        'login_url' => SITE_URL . '/admin/login.php',
        'dashboard' => SITE_URL . '/professor/dashboard.php'
    ],
    [
        'type' => 'إداري',
        'username' => 'admin001',
        'password' => 'password',
        'email' => 'admin@learnata.edu',
        'full_name' => 'مدير النظام',
        'phone' => '0991112222',
        'login_url' => SITE_URL . '/admin/login.php',
        'dashboard' => SITE_URL . '/admin/dashboard.php'
    ],
    [
        'type' => 'عميد',
        'username' => 'dean001',
        'password' => 'password',
        'email' => 'dean@learnata.edu',
        'full_name' => 'د. خالد العميد',
        'phone' => '0993334444',
        'login_url' => SITE_URL . '/admin/login.php',
        'dashboard' => SITE_URL . '/dean/dashboard.php'
    ],
    [
        'type' => 'لجنة القبول',
        'username' => 'committee001',
        'password' => 'password',
        'email' => 'committee@learnata.edu',
        'full_name' => 'لجنة القبول والتسجيل',
        'phone' => '0995556666',
        'login_url' => SITE_URL . '/admin/login.php',
        'dashboard' => SITE_URL . '/committee/dashboard.php'
    ]
];

// إنشاء HTML
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بيانات المستخدمين للاختبار - <?php echo SITE_SHORT_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            color: white;
            padding: 40px 20px;
        }
        .user-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        .user-type {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .type-student { background: rgba(34, 197, 94, 0.3); color: #22c55e; }
        .type-professor { background: rgba(59, 130, 246, 0.3); color: #3b82f6; }
        .type-admin { background: rgba(239, 68, 68, 0.3); color: #ef4444; }
        .type-dean { background: rgba(168, 85, 247, 0.3); color: #a855f7; }
        .type-committee { background: rgba(245, 158, 11, 0.3); color: #f59e0b; }
        .credential {
            background: rgba(0, 0, 0, 0.2);
            padding: 10px 15px;
            border-radius: 10px;
            margin: 5px 0;
            font-family: monospace;
            font-size: 0.95rem;
        }
        .credential-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
            margin-bottom: 3px;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .page-header p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.1rem;
        }
        .quick-links {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .quick-links a {
            color: #818cf8;
            text-decoration: none;
            margin: 0 10px;
        }
        .quick-links a:hover {
            color: #a5b4fc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-users-cog"></i> بيانات المستخدمين للاختبار</h1>
            <p>استخدم هذه البيانات لتسجيل الدخول واختبار النظام</p>
        </div>

        <div class="quick-links text-center">
            <strong>روابط سريعة:</strong>
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-graduation-cap"></i> دخول الطلاب</a> |
            <a href="<?php echo SITE_URL; ?>/admin/login.php"><i class="fas fa-user-shield"></i> دخول الإداريين</a> |
            <a href="<?php echo SITE_URL; ?>/api/swagger.json"><i class="fas fa-code"></i> API Docs</a> |
            <a href="<?php echo SITE_URL; ?>/PROJECT_COMPLETE.md"><i class="fas fa-check-circle"></i> ملخص المشروع</a>
        </div>

        <div class="row">
            <?php foreach ($testUsers as $user): ?>
            <div class="col-md-6 col-lg-4">
                <div class="user-card">
                    <span class="user-type type-<?php echo $user['type'] === 'طالب' ? 'student' : ($user['type'] === 'أستاذ' ? 'professor' : ($user['type'] === 'إداري' ? 'admin' : ($user['type'] === 'عميد' ? 'dean' : 'committee'))); ?>">
                        <i class="fas fa-<?php echo $user['type'] === 'طالب' ? 'graduation-cap' : ($user['type'] === 'أستاذ' ? 'chalkboard-teacher' : ($user['type'] === 'إداري' ? 'user-shield' : ($user['type'] === 'عميد' ? 'university' : 'clipboard-check'))); ?>"></i>
                        <?php echo $user['type']; ?>
                    </span>
                    
                    <h4 class="mb-3"><?php echo $user['full_name']; ?></h4>
                    
                    <div class="credential">
                        <div class="credential-label">اسم المستخدم</div>
                        <div><i class="fas fa-user"></i> <?php echo $user['username']; ?></div>
                    </div>
                    
                    <div class="credential">
                        <div class="credential-label">كلمة المرور</div>
                        <div><i class="fas fa-key"></i> <?php echo $user['password']; ?></div>
                    </div>
                    
                    <div class="credential">
                        <div class="credential-label">البريد الإلكتروني</div>
                        <div><i class="fas fa-envelope"></i> <?php echo $user['email']; ?></div>
                    </div>
                    
                    <div class="credential">
                        <div class="credential-label">رقم الهاتف</div>
                        <div><i class="fas fa-phone"></i> <?php echo $user['phone']; ?></div>
                    </div>
                    
                    <a href="<?php echo $user['login_url']; ?>" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> تسجيل الدخول كـ <?php echo $user['type']; ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4 mb-5">
            <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.3); color: white;">
                <h5><i class="fas fa-info-circle"></i> ملاحظات هامة</h5>
                <ul class="text-start mt-3" style="list-style: none; padding: 0;">
                    <li><i class="fas fa-check text-success"></i> كلمة المرور الافتراضية لجميع المستخدمين: <strong>password</strong></li>
                    <li><i class="fas fa-check text-success"></i> يمكن تغيير كلمة المرور من صفحة الملف الشخصي</li>
                    <li><i class="fas fa-check text-success"></i> جميع البيانات هي بيانات اختبارية فقط</li>
                    <li><i class="fas fa-check text-success"></i> الموقع يدعم اللغتين العربية والإنجليزية</li>
                </ul>
            </div>
        </div>

        <footer class="text-center" style="color: rgba(255, 255, 255, 0.4); padding: 20px;">
            <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?> M.Hamza AlNawaf - SUIP</p>
        </footer>
    </div>
</body>
</html>
