<?php
/**
 * import_college_courses.php - استيراد مواد الكليات
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');

$courseFiles = [
    'ite_courses.sql' => 'كلية الهندسة المعلوماتية',
    'dentistry_courses.sql' => 'كلية طب الأسنان',
    'pharmacy_courses.sql' => 'كلية الصيدلة',
    'chemical_courses.sql' => 'كلية الهندسة الكيميائية',
    'translation_courses.sql' => 'كلية الترجمة واللغات'
];

$basePath = __DIR__ . '/database/data/college_courses/';

try {
    $db = getDB();
    
    echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head>';
    echo '<meta charset="UTF-8"><title>استيراد مواد الكليات</title>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">';
    echo '<style>body{padding:20px;} .success{color:#4caf50;} .error{color:#f44336;}</style>';
    echo '</head><body><div class="container">';
    echo '<h2>📚 استيراد مواد الكليات</h2>';
    
    $totalImported = 0;
    
    foreach ($courseFiles as $file => $collegeName) {
        $filePath = $basePath . $file;
        
        if (!file_exists($filePath)) {
            echo "<div class='alert alert-warning'>⚠️ ملف غير موجود: $file</div>";
            continue;
        }
        
        echo "<h4 class='mt-3'>🏛️ $collegeName</h4>";
        
        // قراءة وتحليل SQL
        $sql = file_get_contents($filePath);
        
        // استخراج استعلامات INSERT
        preg_match_all('/INSERT INTO courses[^;]+;/i', $sql, $matches);
        
        $imported = 0;
        $errors = [];
        
        foreach ($matches[0] as $insert) {
            try {
                $db->execute($insert);
                $imported++;
            } catch (Exception $e) {
                // تجاهل أخطاء التكرار
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    $errors[] = $e->getMessage();
                }
            }
        }
        
        if ($imported > 0) {
            echo "<div class='alert alert-success'>✅ تم استيراد $imported مادة</div>";
            $totalImported += $imported;
        } else {
            echo "<div class='alert alert-info'>📘 المواد موجودة مسبقاً أو لا يوجد مواد جديدة</div>";
        }
        
        if (!empty($errors)) {
            echo "<div class='alert alert-danger'>❌ أخطاء: " . count($errors) . "</div>";
        }
    }
    
    echo "<div class='alert alert-primary mt-4'><h5>📊 المجموع: $totalImported مادة مستوردة</h5></div>";
    echo "<a href='check_courses_by_college.php' class='btn btn-primary'>عرض المواد حسب الكلية</a>";
    echo "</div></body></html>";
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">❌ خطأ: ' . $e->getMessage() . '</div>';
}
