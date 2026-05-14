<?php
// check_and_fix_colleges.php - فحص وحذف الكليات المكررة

chdir(__DIR__);

require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

echo "<!DOCTYPE html>";
echo "<html lang='ar' dir='rtl'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>فحص الكليات المكررة</title>";
echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css'>";
echo "</head>";
echo "<body class='p-5'>";
echo "<div class='container'>";

try {
    // فحص جميع الكليات
    $colleges = $db->fetchAll("SELECT * FROM colleges ORDER BY id");
    
    echo "<h2 class='mb-4'>الكليات الموجودة في قاعدة البيانات</h2>";
    echo "<p class='alert alert-info'>عدد الكليات: " . count($colleges) . "</p>";
    
    echo "<table class='table table-bordered mb-4'>";
    echo "<thead class='table-dark'><tr><th>ID</th><th>الاسم</th><th>الرمز</th><th>الحالة</th></tr></thead>";
    echo "<tbody>";
    foreach ($colleges as $college) {
        echo "<tr>";
        echo "<td>" . $college['id'] . "</td>";
        echo "<td>" . htmlspecialchars($college['college_name']) . "</td>";
        echo "<td>" . htmlspecialchars($college['college_code'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($college['status']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    
    // فحص الكليات المكررة حسب الاسم
    echo "<h3 class='mb-3'>فحص الكليات المكررة حسب الاسم</h3>";
    $duplicates = $db->fetchAll("
        SELECT college_name, COUNT(*) as count 
        FROM colleges 
        GROUP BY college_name 
        HAVING COUNT(*) > 1
    ");
    
    if (count($duplicates) > 0) {
        echo "<div class='alert alert-warning'>";
        echo "<h5>تم العثور على كليات مكررة:</h5>";
        echo "<ul>";
        foreach ($duplicates as $dup) {
            echo "<li>" . htmlspecialchars($dup['college_name']) . " - مكرر " . $dup['count'] . " مرات</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        // عرض تفاصيل الكليات المكررة
        echo "<h4 class='mb-3'>تفاصيل الكليات المكررة</h4>";
        foreach ($duplicates as $dup) {
            $dupColleges = $db->fetchAll("
                SELECT * FROM colleges 
                WHERE college_name = ? 
                ORDER BY id
            ", [$dup['college_name']]);
            
            echo "<div class='card mb-3'>";
            echo "<div class='card-header bg-warning'>" . htmlspecialchars($dup['college_name']) . "</div>";
            echo "<div class='card-body'>";
            echo "<table class='table table-sm'>";
            echo "<thead><tr><th>ID</th><th>الرمز</th><th>الحالة</th><th>إجراء</th></tr></thead>";
            echo "<tbody>";
            foreach ($dupColleges as $college) {
                echo "<tr>";
                echo "<td>" . $college['id'] . "</td>";
                echo "<td>" . htmlspecialchars($college['college_code'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($college['status']) . "</td>";
                echo "<td>";
                if ($college['status'] == 'inactive') {
                    echo "<span class='badge bg-danger'>غير نشط - يمكن حذفه</span>";
                } else {
                    echo "<span class='badge bg-success'>نشط</span>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div></div>";
        }
        
        // زر لحذف الكليات المكررة غير النشطة
        echo "<div class='alert alert-danger mt-4'>";
        echo "<h5>حذف الكليات المكررة غير النشطة</h5>";
        echo "<p>سيتم حذف الكليات المكررة التي حالتها 'inactive' فقط.</p>";
        echo "<form method='POST'>";
        echo "<button type='submit' name='delete_duplicates' class='btn btn-danger'>حذف الكليات المكررة غير النشطة</button>";
        echo "</form>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>لا توجد كليات مكررة حسب الاسم.</div>";
    }
    
    // معالجة طلب الحذف
    if (isset($_POST['delete_duplicates'])) {
        $deletedCount = 0;
        
        foreach ($duplicates as $dup) {
            // حذف الكليات المكررة غير النشطة (احتفظ بالأول النشط)
            $dupColleges = $db->fetchAll("
                SELECT * FROM colleges 
                WHERE college_name = ? 
                ORDER BY id
            ", [$dup['college_name']]);
            
            $keepFirst = true;
            foreach ($dupColleges as $college) {
                if ($college['status'] == 'inactive' || !$keepFirst) {
                    $db->execute("DELETE FROM colleges WHERE id = ?", [$college['id']]);
                    $deletedCount++;
                } else {
                    $keepFirst = false;
                }
            }
        }
        
        echo "<div class='alert alert-success mt-3'>";
        echo "<h5>تم الحذف بنجاح!</h5>";
        echo "<p>تم حذف " . $deletedCount . " كلية مكررة.</p>";
        echo "<a href='check_and_fix_colleges.php' class='btn btn-primary'>تحديث الصفحة</a>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>خطأ: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
