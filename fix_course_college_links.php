<?php
/**
 * fix_course_college_links.php - ربط المواد بالكليات الصحيحة
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDB();
    
    echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head>';
    echo '<meta charset="UTF-8"><title>ربط المواد بالكليات</title>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">';
    echo '<style>body{padding:20px;}</style>';
    echo '</head><body><div class="container">';
    echo '<h2>🔗 ربط المواد بالكليات</h2>';
    
    // عرض قيم college الفريدة في المواد
    echo '<h4 class="mt-4">📋 قيم college في جدول courses:</h4>';
    $collegeValues = $db->fetchAll("SELECT DISTINCT college, COUNT(*) as count FROM courses WHERE college IS NOT NULL AND college != '' GROUP BY college");
    
    echo '<table class="table table-bordered">';
    echo '<tr><th>القيمة في courses.college</th><th>عدد المواد</th></tr>';
    foreach ($collegeValues as $row) {
        echo '<tr><td>' . htmlspecialchars($row['college']) . '</td><td>' . $row['count'] . '</td></tr>';
    }
    echo '</table>';
    
    // عرض أسماء الكليات
    echo '<h4 class="mt-4">🏛️ أسماء الكليات في جدول colleges:</h4>';
    $colleges = $db->fetchAll("SELECT college_code, college_name FROM colleges");
    
    echo '<table class="table table-bordered">';
    echo '<tr><th>الكود</th><th>اسم الكلية</th></tr>';
    foreach ($colleges as $row) {
        echo '<tr><td>' . htmlspecialchars($row['college_code']) . '</td><td>' . htmlspecialchars($row['college_name']) . '</td></tr>';
    }
    echo '</table>';
    
    // تحديث الروابط
    echo '<h4 class="mt-4">🔧 تحديث الروابط:</h4>';
    
    $links = [
        ['كلية الهندسة المعلوماتية', 'IT_ENG'],
        ['كلية تقنية المعلومات', 'IT_ENG'],
        ['كلية طب الأسنان', 'DENT'],
        ['كلية الصيدلة', 'PHAR'],
        ['كلية الهندسة الكيميائية', 'CHEM_ENG'],
        ['كلية هندسة الصناعات الكيميائية', 'CHEM_ENG'],
        ['كلية الترجمة واللغات', 'LANG'],
    ];
    
    foreach ($links as $link) {
        list($courseCollege, $collegeCode) = $link;
        
        // التحقق من وجود الكلية
        $college = $db->fetchOne("SELECT id, college_name FROM colleges WHERE college_code = ?", [$collegeCode]);
        
        if ($college) {
            // عدد المواد قبل التحديث
            $before = $db->fetchOne("SELECT COUNT(*) as count FROM courses WHERE college = ?", [$courseCollege])['count'] ?? 0;
            
            // تحديث المواد لتتطابق مع اسم الكلية
            $db->execute(
                "UPDATE courses SET college = ? WHERE college = ?",
                [$college['college_name'], $courseCollege]
            );
            
            echo "<div class='alert alert-success'>✅ $courseCollege ← {$college['college_name']} (تم تحديث $before مادة)</div>";
        } else {
            echo "<div class='alert alert-warning'>⚠️ لم يتم العثور على الكلية: $collegeCode</div>";
        }
    }
    
    // عرض النتيجة
    echo '<h4 class="mt-4">📊 النتيجة النهائية:</h4>';
    $result = $db->fetchAll("SELECT c.college_name, COUNT(co.id) as course_count 
        FROM colleges c 
        LEFT JOIN courses co ON c.college_name = co.college 
        GROUP BY c.id, c.college_name");
    
    echo '<table class="table table-bordered table-striped">';
    echo '<tr class="table-dark"><th>الكلية</th><th>عدد المواد</th></tr>';
    $total = 0;
    foreach ($result as $row) {
        $count = $row['course_count'] ?? 0;
        $total += $count;
        $class = $count > 0 ? 'table-success' : 'table-warning';
        echo "<tr class='$class'><td>" . htmlspecialchars($row['college_name']) . "</td><td>$count</td></tr>";
    }
    echo "<tr class='table-primary'><td><strong>المجموع</strong></td><td><strong>$total</strong></td></tr>";
    echo '</table>';
    
    echo '<div class="mt-4">';
    echo '<a href="check_courses_by_college.php" class="btn btn-primary">التحقق من المواد</a>';
    echo '<a href="index.php" class="btn btn-outline-dark ms-2">الرئيسية</a>';
    echo '</div>';
    
    echo "</div></body></html>";
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">❌ خطأ: ' . $e->getMessage() . '</div>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
