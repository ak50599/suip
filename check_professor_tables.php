<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

echo "<h2>فحص جداول الأساتذة والمواد</h2>";

// فحص جدول professors
echo "<h3>1. أعمدة جدول professors</h3>";
$columns = $db->fetchAll("SHOW COLUMNS FROM professors");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>الحقل</th><th>النوع</th><th>المفتاح</th></tr>";
foreach ($columns as $col) {
    echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Key']}</td></tr>";
}
echo "</table>";

// فحص جدول professor_courses
echo "<h3>2. أعمدة جدول professor_courses</h3>";
try {
    $columns = $db->fetchAll("SHOW COLUMNS FROM professor_courses");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>الحقل</th><th>النوع</th><th>المفتاح</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Key']}</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color:red'>خطأ: " . $e->getMessage() . "</p>";
}

// عرض بيانات أستاذ تجريبي
echo "<h3>3. بيانات الأستاذ الحالي</h3>";
if (isset($_GET['user_id'])) {
    $prof = $db->fetchOne("SELECT * FROM professors WHERE user_id = ?", [$_GET['user_id']]);
    if ($prof) {
        echo "<pre>";
        print_r($prof);
        echo "</pre>";
        
        // التحقق من المواد المرتبطة
        echo "<h4>المواد المرتبطة بـ professor_id = {$prof['professor_id']}</h4>";
        try {
            $courses = $db->fetchAll(
                "SELECT pc.*, ca.college_name 
                 FROM professor_courses pc 
                 JOIN college_admission_rates ca ON pc.course_id = ca.id 
                 WHERE pc.professor_id = ?",
                [$prof['professor_id']]
            );
            echo "<p>عدد المواد: " . count($courses) . "</p>";
            if (count($courses) > 0) {
                echo "<ul>";
                foreach ($courses as $c) {
                    echo "<li>{$c['college_name']} (course_id: {$c['course_id']})</li>";
                }
                echo "</ul>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red'>خطأ: " . $e->getMessage() . "</p>";
        }
        
        // التحقق إذا كان id مختلفاً
        echo "<h4>المواد المرتبطة بـ id = {$prof['id']}</h4>";
        try {
            $courses2 = $db->fetchAll(
                "SELECT pc.*, ca.college_name 
                 FROM professor_courses pc 
                 JOIN college_admission_rates ca ON pc.course_id = ca.id 
                 WHERE pc.professor_id = ?",
                [$prof['id']]
            );
            echo "<p>عدد المواد: " . count($courses2) . "</p>";
        } catch (Exception $e) {
            echo "<p style='color:red'>خطأ: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>لا يوجد أستاذ بهذا المعرف</p>";
    }
} else {
    echo "<p>أضف ?user_id=XX إلى الرابط</p>";
}
?>
