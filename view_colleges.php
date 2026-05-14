<?php
/**
 * view_colleges.php - عرض الكليات الموجودة في قاعدة البيانات
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDB();
    $colleges = $db->fetchAll("SELECT * FROM colleges ORDER BY id");
} catch (Exception $e) {
    $colleges = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الكليات - SUIP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; }
        .college-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-right: 4px solid #667eea;
        }
        .college-code {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4"><i class="fas fa-university me-2 text-primary"></i>الكليات الموجودة في النظام</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <h5>❌ خطأ:</h5>
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="install_complete_database.php" class="btn btn-warning">تثبيت الجداول المفقودة</a>
            </div>
        <?php elseif (empty($colleges)): ?>
            <div class="alert alert-warning">
                <h5>⚠️ لا توجد كليات في قاعدة البيانات!</h5>
                <a href="install_database.php" class="btn btn-primary">تثبيت قاعدة البيانات</a>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                ✅ عدد الكليات: <?php echo count($colleges); ?> كلية
            </div>
            
            <?php foreach ($colleges as $college): ?>
            <div class="college-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="college-code"><?php echo htmlspecialchars($college['college_code']); ?></span>
                        <h4 class="mt-2 mb-1"><?php echo htmlspecialchars($college['college_name']); ?></h4>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($college['college_name_en']); ?></p>
                    </div>
                    <span class="badge bg-<?php echo $college['status'] === 'active' ? 'success' : 'secondary'; ?>">
                        <?php echo $college['status'] === 'active' ? 'نشط' : 'غير نشط'; ?>
                    </span>
                </div>
                <?php if (!empty($college['description'])): ?>
                <p class="mt-2 text-muted"><?php echo htmlspecialchars($college['description']); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="mt-4 text-center">
            <a href="display_all_users.php" class="btn btn-outline-primary">👥 عرض المستخدمين</a>
            <a href="index.php" class="btn btn-outline-success">🏠 الصفحة الرئيسية</a>
        </div>
    </div>
</body>
</html>
