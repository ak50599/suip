<?php
/**
 * fix_and_import_courses.php - إصلاح جدول المواد واستيراد جميع الكليات
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');

$courseFiles = [
    'ite_courses.sql' => 'كلية الهندسة المعلوماتية',
    'dentistry_courses.sql' => 'كلية طب الأسنان',
    'pharmacy_courses.sql' => 'كلية الصيدلة',
    'chemical_courses.sql' => 'كلية الهندسة الكيميائية',
    'translation_courses.sql' => 'كلية الترجمة واللغات',
    'completed_courses.sql' => 'المواد المشتركة'
];

$basePath = __DIR__ . '/database/';

try {
    $db = getDB();
    
    echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head>';
    echo '<meta charset="UTF-8"><title>إصلاح واستيراد المواد</title>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">';
    echo '<style>body{padding:20px;} .log{padding:10px;margin:5px 0;border-radius:5px;}</style>';
    echo '</head><body><div class="container">';
    echo '<h2>🔧 إصلاح جدول المواد واستيراد الكليات</h2>';
    
    // الخطوة 1: التحقق من أعمدة الجدول
    echo '<div class="alert alert-info">📋 الخطوة 1: التحقق من هيكل الجدول...</div>';
    
    $columns = $db->fetchAll("SHOW COLUMNS FROM courses");
    $columnNames = array_column($columns, 'Field');
    
    // إضافة عمود college إذا لم يكن موجوداً
    if (!in_array('college', $columnNames)) {
        echo '<div class="alert alert-warning">⚠️ عمود college غير موجود، جاري الإضافة...</div>';
        
        // إضافة العمود
        $db->execute("ALTER TABLE courses ADD COLUMN college VARCHAR(100) AFTER course_name_en");
        $db->execute("ALTER TABLE courses ADD INDEX college_idx (college)");
        
        // تحديث المواد الموجودة - نقل department إلى college
        try {
            $db->execute("UPDATE courses SET college = department WHERE college IS NULL OR college = ''");
        } catch (Exception $e) {
            // تجاهل إذا لم يكن department موجوداً
        }
        
        echo '<div class="alert alert-success">✅ تم إضافة عمود college</div>';
    } else {
        echo '<div class="alert alert-success">✅ عمود college موجود</div>';
    }
    
    // إضافة عمود year إذا لم يكن موجوداً
    if (!in_array('year', $columnNames)) {
        echo '<div class="alert alert-warning">⚠️ عمود year غير موجود، جاري الإضافة...</div>';
        $db->execute("ALTER TABLE courses ADD COLUMN year INT DEFAULT 1 AFTER college");
        $db->execute("UPDATE courses SET year = level WHERE year IS NULL OR year = 0");
        echo '<div class="alert alert-success">✅ تم إضافة عمود year</div>';
    }
    
    // إضافة عمود semester إذا لم يكن موجوداً
    if (!in_array('semester', $columnNames)) {
        echo '<div class="alert alert-warning">⚠️ عمود semester غير موجود، جاري الإضافة...</div>';
        $db->execute("ALTER TABLE courses ADD COLUMN semester INT DEFAULT 1 AFTER year");
        echo '<div class="alert alert-success">✅ تم إضافة عمود semester</div>';
    }
    
    // الخطوة 2: استيراد المواد
    echo '<div class="alert alert-info mt-4">📚 الخطوة 2: استيراد مواد الكليات...</div>';
    
    $totalImported = 0;
    $totalSkipped = 0;
    
    foreach ($courseFiles as $file => $collegeName) {
        $filePath = $basePath . $file;
        
        if (!file_exists($filePath)) {
            echo "<div class='log alert alert-warning'>⚠️ ملف غير موجود: $file</div>";
            continue;
        }
        
        echo "<h5 class='mt-3'>🏛️ $collegeName</h5>";
        
        $sql = file_get_contents($filePath);
        
        // استخراج استعلامات INSERT
        preg_match_all('/INSERT INTO courses[^;]+;/i', $sql, $matches);
        
        $imported = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($matches[0] as $insert) {
            try {
                $db->execute($insert);
                $imported++;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    $skipped++;
                } else {
                    $errors[] = $e->getMessage();
                }
            }
        }
        
        $totalImported += $imported;
        $totalSkipped += $skipped;
        
        if ($imported > 0) {
            echo "<div class='log alert alert-success'>✅ مستوردة: $imported | ⏭️ موجودة: $skipped</div>";
        } else {
            echo "<div class='log alert alert-info'>📘 جميع المواد موجودة مسبقاً ($skipped)</div>";
        }
        
        if (!empty($errors) && count($errors) < 3) {
            foreach ($errors as $err) {
                echo "<div class='log alert alert-danger'>❌ $err</div>";
            }
        }
    }
    
    // الخلاصة
    echo '<div class="alert alert-primary mt-4">';
    echo '<h5>📊 الخلاصة:</h5>';
    echo '<ul>';
    echo "<li>✅ المواد المستوردة: $totalImported</li>";
    echo "<li>⏭️ المواد الموجودة: $totalSkipped</li>";
    
    // عدد المواد الكلي
    $totalCourses = $db->fetchOne("SELECT COUNT(*) as count FROM courses")['count'] ?? 0;
    echo "<li>📚 إجمالي المواد في النظام: $totalCourses</li>";
    echo '</ul>';
    echo '</div>';
    
    // روابط
    echo '<div class="mt-4">';
    echo '<a href="check_courses_by_college.php" class="btn btn-primary me-2">عرض المواد حسب الكلية</a>';
    echo '<a href="dean/courses.php" class="btn btn-success me-2">إدارة المواد</a>';
    echo '<a href="index.php" class="btn btn-outline-dark">الرئيسية</a>';
    echo '</div>';
    
    echo "</div></body></html>";
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">❌ خطأ: ' . $e->getMessage() . '</div>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
