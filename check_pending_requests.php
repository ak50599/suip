<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

// جلب جميع الطلبات المعلقة
$pending = $db->fetchAll("SELECT id, application_number, first_name, last_name, status, grade_percentage, certificate_type, created_at, choice1, choice2, choice3, photo_path FROM admission_applications WHERE status = 'pending'");

echo "<h3>الطلبات المعلقة (Pending)</h3>";
echo "<p>العدد: " . count($pending) . "</p>";

if (count($pending) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>رقم الطلب</th><th>الاسم</th><th>الحالة</th><th>المعدل</th><th>نوع الشهادة</th><th>الرغبة 1</th><th>الرغبة 2</th><th>الرغبة 3</th><th>مسار الصورة</th><th>تاريخ الإنشاء</th></tr>";
    foreach ($pending as $p) {
        echo "<tr>";
        echo "<td>" . $p['id'] . "</td>";
        echo "<td>" . $p['application_number'] . "</td>";
        echo "<td>" . $p['first_name'] . ' ' . $p['last_name'] . "</td>";
        echo "<td>" . $p['status'] . "</td>";
        echo "<td>" . $p['grade_percentage'] . "%</td>";
        echo "<td>" . $p['certificate_type'] . "</td>";
        echo "<td>" . ($p['choice1'] ?? 'فارغ') . "</td>";
        echo "<td>" . ($p['choice2'] ?? 'فارغ') . "</td>";
        echo "<td>" . ($p['choice3'] ?? 'فارغ') . "</td>";
        echo "<td>" . ($p['photo_path'] ?? 'فارغ') . "</td>";
        echo "<td>" . $p['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>لا توجد طلبات معلقة</p>";
}

echo "<hr>";

// جلب جميع الطلبات
$all = $db->fetchAll("SELECT id, application_number, first_name, last_name, status, grade_percentage, choice1, choice2, choice3, photo_path FROM admission_applications");

echo "<h3>جميع الطلبات</h3>";
echo "<p>العدد الكلي: " . count($all) . "</p>";

$statusCounts = [];
foreach ($all as $a) {
    $status = $a['status'];
    if (!isset($statusCounts[$status])) {
        $statusCounts[$status] = 0;
    }
    $statusCounts[$status]++;
}

echo "<h4>توزيع الحالات:</h4>";
foreach ($statusCounts as $status => $count) {
    echo "<p>$status: $count</p>";
}

echo "<hr>";
echo "<h3>الطلبات الأخيرة (آخر 10) مع الرغبات والصور</h3>";
$recent = array_slice($all, -10);
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>رقم الطلب</th><th>الاسم</th><th>الحالة</th><th>المعدل</th><th>الرغبة 1</th><th>الرغبة 2</th><th>الرغبة 3</th><th>مسار الصورة</th></tr>";
foreach ($recent as $r) {
    echo "<tr>";
    echo "<td>" . $r['id'] . "</td>";
    echo "<td>" . $r['application_number'] . "</td>";
    echo "<td>" . $r['first_name'] . ' ' . $r['last_name'] . "</td>";
    echo "<td>" . $r['status'] . "</td>";
    echo "<td>" . $r['grade_percentage'] . "%</td>";
    echo "<td>" . ($r['choice1'] ?? 'فارغ') . "</td>";
    echo "<td>" . ($r['choice2'] ?? 'فارغ') . "</td>";
    echo "<td>" . ($r['choice3'] ?? 'فارغ') . "</td>";
    echo "<td>" . ($r['photo_path'] ?? 'فارغ') . "</td>";
    echo "</tr>";
}
echo "</table>";
