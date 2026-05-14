<?php
// ملف اختبار لعرض بيانات الرسم البياني

require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h1>اختبار بيانات الرسم البياني</h1>";

try {
    $db = getDB();
    
    // 1. عرض جميع الكليات
    echo "<h2>1. قائمة الكليات (college_admission_rates)</h2>";
    $colleges = $db->fetchAll("SELECT id, college_name FROM college_admission_rates ORDER BY id");
    echo "<table border='1'><tr><th>ID</th><th>اسم الكلية</th></tr>";
    foreach ($colleges as $c) {
        echo "<tr><td>{$c['id']}</td><td>{$c['college_name']}</td></tr>";
    }
    echo "</table>";
    echo "<p>عدد الكليات: " . count($colleges) . "</p>";
    
    // 2. عرض الطلبات المقبولة
    echo "<h2>2. طلبات القبول المقبولة (admission_applications)</h2>";
    $applications = $db->fetchAll("SELECT id, accepted_college, status, acceptance_date FROM admission_applications WHERE status = 'accepted' LIMIT 20");
    echo "<table border='1'><tr><th>ID</th><th>الكلية المقبول بها</th><th>الحالة</th><th>تاريخ القبول</th></tr>";
    foreach ($applications as $app) {
        echo "<tr><td>{$app['id']}</td><td>{$app['accepted_college']}</td><td>{$app['status']}</td><td>{$app['acceptance_date']}</td></tr>";
    }
    echo "</table>";
    echo "<p>عدد الطلبات المقبولة: " . count($applications) . "</p>";
    
    // 3. الاستعلام الجديد
    echo "<h2>3. نتيجة الاستعلام الجديد (العدد حسب الكلية)</h2>";
    $studentsDistribution = $db->fetchAll("
        SELECT 
            car.college_name, 
            COUNT(aa.id) as student_count
        FROM college_admission_rates car
        LEFT JOIN admission_applications aa ON car.college_name = aa.accepted_college 
            AND aa.status = 'accepted'
        GROUP BY car.id, car.college_name
        ORDER BY student_count DESC
    ");
    
    echo "<table border='1'><tr><th>الكلية</th><th>عدد الطلاب</th></tr>";
    foreach ($studentsDistribution as $row) {
        echo "<tr><td>{$row['college_name']}</td><td>{$row['student_count']}</td></tr>";
    }
    echo "</table>";
    
    // 4. اختبار استدعاء API
    echo "<h2>4. بيانات API (JSON)</h2>";
    echo "<pre>";
    
    $distributionLabels = [];
    $distributionData = [];
    foreach ($studentsDistribution as $row) {
        $distributionLabels[] = $row['college_name'];
        $distributionData[] = (int)$row['student_count'];
    }
    
    echo "Labels: " . json_encode($distributionLabels, JSON_UNESCAPED_UNICODE) . "\n";
    echo "Data: " . json_encode($distributionData, JSON_UNESCAPED_UNICODE) . "\n";
    echo "</pre>";
    
    echo "<hr><a href='admin/president.php' style='font-size: 20px;'>العودة لصفحة رئيس الجامعة</a>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>خطأ: " . $e->getMessage() . "</h2>";
}
