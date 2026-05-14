<?php
/**
 * install_database.php - تثبيت قاعدة البيانات بخطوة واحدة
 * 
 * هذا السكريبت يقوم بـ:
 * 1. استيراد ملف schema_complete.sql بالكامل
 * 2. إنشاء جميع الجداول والبيانات الافتراضية
 */

// إعدادات قاعدة البيانات
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'learnata_clone';

// نمط الإخراج
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تثبيت قاعدة البيانات - SUIP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background: #f5f5f5; padding: 20px; }
        .install-container { 
            max-width: 800px; 
            margin: 50px auto; 
            background: white; 
            border-radius: 15px; 
            padding: 40px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .step { 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 8px; 
            border-right: 4px solid #ddd;
        }
        .step-success { background: #e8f5e9; border-right-color: #4caf50; }
        .step-error { background: #ffebee; border-right-color: #f44336; }
        .step-info { background: #e3f2fd; border-right-color: #2196f3; }
        .progress { height: 30px; border-radius: 15px; }
    </style>
</head>
<body>
    <div class="install-container">
        <h2 class="text-center mb-4"><i class="fas fa-database me-2 text-primary"></i>تثبيت قاعدة البيانات</h2>
        
        <?php
        $steps = [];
        
        // الخطوة 1: التحقق من الاتصال
        echo '<div class="step step-info">🔌 جاري الاتصال بـ MySQL...</div>';
        try {
            $pdo = new PDO("mysql:host={$dbHost};charset=utf8mb4", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            echo '<div class="step step-success">✅ تم الاتصال بـ MySQL بنجاح</div>';
            $steps[] = 'connection';
        } catch (PDOException $e) {
            echo '<div class="step step-error">❌ فشل الاتصال: ' . $e->getMessage() . '</div>';
            echo '<div class="alert alert-warning mt-3">💡 تأكد من تشغيل XAMPP (MySQL)</div>';
            exit;
        }
        
        // الخطوة 2: إنشاء قاعدة البيانات
        echo '<div class="step step-info">📦 جاري إنشاء قاعدة البيانات...</div>';
        try {
            $pdo->exec("DROP DATABASE IF EXISTS {$dbName}");
            $pdo->exec("CREATE DATABASE {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE {$dbName}");
            echo '<div class="step step-success">✅ تم إنشاء قاعدة البيانات ' . $dbName . '</div>';
            $steps[] = 'database';
        } catch (PDOException $e) {
            echo '<div class="step step-error">❌ فشل إنشاء قاعدة البيانات: ' . $e->getMessage() . '</div>';
            exit;
        }
        
        // الخطوة 3: قراءة واستيراد ملف SQL
        echo '<div class="step step-info">📄 جاري قراءة ملف schema_complete.sql...</div>';
        $sqlFile = __DIR__ . '/database/schema_complete.sql';
        
        if (!file_exists($sqlFile)) {
            echo '<div class="step step-error">❌ ملف schema_complete.sql غير موجود!</div>';
            exit;
        }
        
        $sql = file_get_contents($sqlFile);
        if (empty($sql)) {
            echo '<div class="step step-error">❌ ملف SQL فارغ!</div>';
            exit;
        }
        
        echo '<div class="step step-success">✅ تم قراءة الملف (' . strlen($sql) . ' بايت)</div>';
        
        // الخطوة 4: تقسيم وتنفيذ SQL
        echo '<div class="step step-info">⚙️ جاري تنفيذ الاستعلامات...</div>';
        
        // تقسيم الاستعلامات
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        $total = count($statements);
        $success = 0;
        $errors = [];
        
        echo '<div class="progress mb-3"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div></div>';
        echo '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>#</th><th>الاستعلام</th><th>الحالة</th></tr></thead><tbody>';
        
        foreach ($statements as $i => $statement) {
            if (empty($statement)) continue;
            
            $progress = round((($i + 1) / $total) * 100);
            echo '<script>document.querySelector(".progress-bar").style.width = "' . $progress . '%";</script>';
            flush();
            
            // استخراج نوع الاستعلام
            $firstLine = explode("\n", trim($statement))[0];
            $queryType = '';
            if (stripos($firstLine, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?(\w+)/i', $statement, $matches);
                $queryType = 'إنشاء جدول: ' . ($matches[1] ?? 'unknown');
            } elseif (stripos($firstLine, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO\s+(\w+)/i', $statement, $matches);
                $queryType = 'إضافة بيانات: ' . ($matches[1] ?? 'unknown');
            } elseif (stripos($firstLine, 'CREATE DATABASE') !== false) {
                $queryType = 'إنشاء قاعدة البيانات';
            } elseif (stripos($firstLine, 'USE ') !== false) {
                $queryType = 'تحديد قاعدة البيانات';
            } else {
                $queryType = substr($firstLine, 0, 50) . '...';
            }
            
            try {
                $pdo->exec($statement);
                $success++;
                $status = '<span class="badge bg-success">✓</span>';
            } catch (PDOException $e) {
                $errors[] = $e->getMessage();
                $status = '<span class="badge bg-danger" title="' . htmlspecialchars($e->getMessage()) . '">✗</span>';
            }
            
            echo '<tr><td>' . ($i + 1) . '</td><td>' . htmlspecialchars($queryType) . '</td><td>' . $status . '</td></tr>';
            flush();
        }
        
        echo '</tbody></table></div>';
        
        // النتيجة النهائية
        echo '<div class="alert alert-info mt-4">';
        echo '<h5>📊 النتيجة:</h5>';
        echo '<ul>';
        echo '<li>✅ نجح: ' . $success . ' استعلام</li>';
        echo '<li>❌ فشل: ' . count($errors) . ' استعلام</li>';
        echo '<li>📊 المجموع: ' . $total . ' استعلام</li>';
        echo '</ul>';
        echo '</div>';
        
        if (count($errors) > 0) {
            echo '<div class="alert alert-warning">';
            echo '<h6>⚠️ الأخطاء:</h6>';
            echo '<ul class="small">';
            foreach (array_slice($errors, 0, 5) as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            if (count($errors) > 5) {
                echo '<li>... و ' . (count($errors) - 5) . ' أخطاء أخرى</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        
        // التحقق من الجداول المُنشأة
        echo '<div class="step step-info">🔍 جاري التحقق من الجداول...</div>';
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo '<div class="step step-success">✅ تم إنشاء ' . count($tables) . ' جدول:</div>';
        echo '<div class="row"><div class="col-md-6"><ul class="list-group">';
        foreach ($tables as $table) {
            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo $table;
            // عدد الصفوف
            $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            echo '<span class="badge bg-primary rounded-pill">' . $count . '</span>';
            echo '</li>';
        }
        echo '</ul></div></div>';
        
        // روابط مفيدة
        echo '<div class="mt-4 text-center">';
        echo '<h5>🔗 الخطوات التالية:</h5>';
        echo '<div class="btn-group-vertical w-100">';
        echo '<a href="display_all_users.php" class="btn btn-outline-primary">👥 عرض معلومات تسجيل الدخول</a>';
        echo '<a href="index.php" class="btn btn-outline-success">🏠 الصفحة الرئيسية</a>';
        echo '<a href="admin/login.php" class="btn btn-outline-info">🔐 تسجيل الدخول</a>';
        echo '</div>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
