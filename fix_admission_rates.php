<?php
/**
 * fix_admission_rates.php - تحديث معدلات القبول في قاعدة البيانات
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<!DOCTYPE html>
<html dir='rtl' lang='ar'>
<head>
    <meta charset='UTF-8'>
    <title>تحديث معدلات القبول</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css'>
</head>
<body class='p-4'>
<div class='container'>
    <h1>🎓 تحديث معدلات القبول للكليات</h1>
";

try {
    $db = getDB();
    
    // معدلات القبول للشهادات السورية
    $syrian_rates = [
        ['كلية الطب البشري', 96.00, 200, 120, 80],
        ['كلية طب الأسنان', 90.00, 150, 90, 60],
        ['كلية الصيدلة', 85.00, 180, 108, 72],
        ['كلية الهندسة المعلوماتية', 80.00, 150, 90, 60],
        ['كلية الهندسة البترولية', 75.00, 120, 72, 48],
        ['كلية الهندسة الكيميائية', 70.00, 100, 60, 40],
        ['كلية الترجمة واللغات', 60.00, 80, 48, 32],
        ['معهد الحاسوب', 55.00, 60, 36, 24],
    ];
    
    // معدلات القبول للشهادات غير السورية
    $non_syrian_rates = [
        ['كلية الطب البشري', 95.00, 200, 80, 120],
        ['كلية طب الأسنان', 90.00, 150, 60, 90],
        ['كلية الصيدلة', 85.00, 180, 72, 108],
        ['كلية الهندسة المعلوماتية', 85.00, 150, 60, 90],
        ['كلية الهندسة البترولية', 80.00, 120, 48, 72],
        ['كلية الهندسة الكيميائية', 80.00, 100, 40, 60],
        ['كلية الترجمة واللغات', 75.00, 80, 32, 48],
        ['معهد الحاسوب', 70.00, 60, 24, 36],
    ];
    
    echo "<h3 class='mt-4'>📊 تحديث معدلات الشهادات السورية:</h3>
    <table class='table table-striped'>
    <thead class='table-dark'><tr><th>الكلية</th><th>المعدل الأدنى</th><th>السعة</th><th>سوري</th><th>غير سوري</th></tr></thead><tbody>";
    
    foreach ($syrian_rates as $rate) {
        try {
            // التحقق من وجود الكلية
            $existing = $db->query("SELECT id FROM college_admission_rates WHERE college_name = ?", [$rate[0]])->fetch();
            
            if ($existing) {
                // تحديث
                $db->execute("UPDATE college_admission_rates 
                    SET min_grade = ?, capacity = ?, current_accepted_syrian = 0, current_accepted_non_syrian = 0, is_active = 1 
                    WHERE id = ?",
                    [$rate[1], $rate[2], $existing['id']]);
                echo "<tr class='table-success'><td>{$rate[0]}</td><td>{$rate[1]}%</td><td>{$rate[2]}</td><td>{$rate[3]}</td><td>{$rate[4]}</td></tr>";
            } else {
                // إدراج جديد
                $db->execute("INSERT INTO college_admission_rates 
                    (college_name, college_name_en, min_grade, max_grade, capacity, current_accepted_syrian, current_accepted_non_syrian, is_active) 
                    VALUES (?, ?, ?, 100, ?, 0, 0, 1)",
                    [$rate[0], 'College', $rate[1], $rate[2]]);
                echo "<tr class='table-info'><td>{$rate[0]} (جديد)</td><td>{$rate[1]}%</td><td>{$rate[2]}</td><td>{$rate[3]}</td><td>{$rate[4]}</td></tr>";
            }
        } catch (Exception $e) {
            echo "<tr class='table-danger'><td>{$rate[0]}</td><td colspan='4'>❌ خطأ: {$e->getMessage()}</td></tr>";
        }
    }
    
    echo "</tbody></table>";
    
    // عرض المعدلات المحدثة
    echo "<h3 class='mt-4'>📋 المعدلات الحالية في قاعدة البيانات:</h3>
    <table class='table table-bordered'>
    <thead class='table-dark'><tr><th>الكلية</th><th>المعدل الأدنى</th><th>السعة</th><th>حالة</th></tr></thead><tbody>";
    
    $current = $db->query("SELECT * FROM college_admission_rates ORDER BY min_grade DESC")->fetchAll();
    foreach ($current as $college) {
        $status = $college['is_active'] ? '✅ نشط' : '❌ معطل';
        echo "<tr><td>{$college['college_name']}</td><td>{$college['min_grade']}%</td><td>{$college['capacity']}</td><td>{$status}</td></tr>";
    }
    
    echo "</tbody></table>";
    
    echo "<div class='alert alert-success mt-4'>✅ تم تحديث معدلات القبول بنجاح!</div>
    <a href='committee/smart-admission.php' class='btn btn-primary'>الذهاب لنظام القبول الذكي</a>
    <a href='index.php' class='btn btn-secondary'>الصفحة الرئيسية</a>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ خطأ: " . $e->getMessage() . "</div>";
}

echo "
</div>
</body>
</html>";
?>
