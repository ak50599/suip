<?php
/**
 * check_database.php - فحص شامل لحالة قاعدة البيانات
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فحص قاعدة البيانات - SUIP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background: #f5f5f5; padding: 20px; }
        .check-container { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status-ok { color: #4caf50; }
        .status-error { color: #f44336; }
        .status-warning { color: #ff9800; }
    </style>
</head>
<body>
    <div class="container">
        <div class="check-container">
            <h2 class="mb-4">🔍 فحص قاعدة البيانات</h2>
            
            <?php
            $checks = [];
            
            // 1. فحص الاتصال
            echo '<h4>1. الاتصال بقاعدة البيانات</h4>';
            try {
                $db = getDB();
                echo '<div class="alert alert-success">✅ الاتصال بقاعدة البيانات يعمل</div>';
                $checks['connection'] = true;
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">❌ فشل الاتصال: ' . $e->getMessage() . '</div>';
                $checks['connection'] = false;
            }
            
            if (!$checks['connection']) {
                echo '<div class="alert alert-warning">💡 تأكد من أن XAMPP يعمل (Apache + MySQL)</div>';
                exit;
            }
            
            // 2. فحص الجداول الأساسية
            echo '<h4 class="mt-4">2. الجداول الأساسية</h4>';
            $requiredTables = [
                'users' => 'جدول المستخدمين',
                'students' => 'جدول الطلاب',
                'professors' => 'جدول الأساتذة',
                'admins' => 'جدول الإداريين',
                'courses' => 'جدول المواد',
                'enrollments' => 'جدول التسجيلات',
                'login_attempts' => 'جدول محاولات الدخول',
                'colleges' => 'جدول الكليات',
                'deans' => 'جدول العمداء',
                'committee' => 'جدول اللجنة',
                'staff' => 'جدول الموظفين',
                'secretaries' => 'جدول السكرتارية'
            ];
            
            $existingTables = $db->fetchAll("SHOW TABLES");
            $existingTableNames = array_map(fn($t) => array_values($t)[0], $existingTables);
            
            echo '<table class="table table-bordered">';
            echo '<thead class="table-dark"><tr><th>الجدول</th><th>الوصف</th><th>الحالة</th></tr></thead><tbody>';
            
            foreach ($requiredTables as $table => $description) {
                $exists = in_array($table, $existingTableNames);
                $status = $exists ? 
                    '<span class="status-ok">✅ موجود</span>' : 
                    '<span class="status-error">❌ مفقود</span>';
                echo "<tr><td>{$table}</td><td>{$description}</td><td>{$status}</td></tr>";
            }
            echo '</tbody></table>';
            
            // 3. فحص المستخدمين
            echo '<h4 class="mt-4">3. المستخدمين في النظام</h4>';
            try {
                $userCounts = $db->fetchAll("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type");
                echo '<table class="table table-bordered">';
                echo '<thead class="table-dark"><tr><th>نوع المستخدم</th><th>العدد</th></tr></thead><tbody>';
                $totalUsers = 0;
                foreach ($userCounts as $uc) {
                    echo "<tr><td>{$uc['user_type']}</td><td>{$uc['count']}</td></tr>";
                    $totalUsers += $uc['count'];
                }
                echo "<tr class='table-primary'><td><strong>المجموع</strong></td><td><strong>{$totalUsers}</strong></td></tr>";
                echo '</tbody></table>';
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">❌ خطأ في جلب المستخدمين: ' . $e->getMessage() . '</div>';
            }
            
            // 4. معلومات تسجيل الدخول
            echo '<h4 class="mt-4">4. معلومات تسجيل الدخول للاختبار</h4>';
            try {
                $sampleUsers = $db->fetchAll("SELECT username, email, full_name, user_type, status FROM users LIMIT 5");
                if (empty($sampleUsers)) {
                    echo '<div class="alert alert-warning">⚠️ لا يوجد مستخدمين في قاعدة البيانات</div>';
                    echo '<div class="alert alert-info">💡 استورد ملف database/schema.sql لإضافة بيانات تجريبية</div>';
                } else {
                    echo '<table class="table table-bordered">';
                    echo '<thead class="table-dark"><tr><th>اسم المستخدم</th><th>البريد</th><th>الاسم</th><th>النوع</th><th>كلمة المرور الافتراضية</th></tr></thead><tbody>';
                    foreach ($sampleUsers as $user) {
                        echo "<tr>";
                        echo "<td>{$user['username']}</td>";
                        echo "<td>{$user['email']}</td>";
                        echo "<td>{$user['full_name']}</td>";
                        echo "<td>{$user['user_type']}</td>";
                        echo "<td><code>student123</code></td>";
                        echo "</tr>";
                    }
                    echo '</tbody></table>';
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">❌ خطأ: ' . $e->getMessage() . '</div>';
            }
            
            // 5. روابط التصحيح
            echo '<h4 class="mt-4">5. أدوات التصحيح</h4>';
            echo '<div class="list-group">';
            echo '<a href="install_complete_database.php" class="list-group-item list-group-item-action list-group-item-primary">🔧 تثبيت/إصلاح الجداول المفقودة</a>';
            echo '<a href="display_all_users.php" class="list-group-item list-group-item-action">👥 عرض جميع المستخدمين</a>';
            echo '<a href="index.php" class="list-group-item list-group-item-action">🏠 الصفحة الرئيسية</a>';
            echo '</div>';
            ?>
            
        </div>
    </div>
</body>
</html>
