<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

// جلب جميع الأعمدة من جدول الطلبات
$columns = $db->fetchAll("SHOW COLUMNS FROM admission_applications");

echo "<h3>أعمدة جدول admission_applications</h3>";
echo "<ul>";
foreach ($columns as $col) {
    echo "<li><strong>" . $col['Field'] . "</strong> - " . $col['Type'] . "</li>";
}
echo "</ul>";

echo "<hr>";

// جلب بيانات أول طلب
$sample = $db->fetchOne("SELECT * FROM admission_applications LIMIT 1");

echo "<h3>بيانات أول طلب (مثال)</h3>";
if ($sample) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>الحقل</th><th>القيمة</th></tr>";
    foreach ($sample as $key => $value) {
        echo "<tr>";
        echo "<td><strong>$key</strong></td>";
        echo "<td>" . ($value ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>لا توجد طلبات في قاعدة البيانات</p>";
}

echo "<hr>";

// التحقق من وجود حقول الرغبات والصور
echo "<h3>التحقق من حقول مهمة</h3>";
$requiredFields = ['choice1', 'choice2', 'choice3', 'photo_path'];
foreach ($requiredFields as $field) {
    $exists = false;
    foreach ($columns as $col) {
        if ($col['Field'] === $field) {
            $exists = true;
            break;
        }
    }
    echo "<p><strong>$field:</strong> " . ($exists ? "موجود ✓" : "غير موجود ✗") . "</p>";
}

echo "<hr>";

// جلب الطلبات مع الرغبات والصور
$apps = $db->fetchAll("SELECT id, application_number, first_name, last_name, choice1, choice2, choice3, photo_path FROM admission_applications LIMIT 5");

echo "<h3>أول 5 طلبات مع الرغبات والصور</h3>";
if (count($apps) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>الاسم</th><th>الرغبة 1</th><th>الرغبة 2</th><th>الرغبة 3</th><th>مسار الصورة</th></tr>";
    foreach ($apps as $app) {
        echo "<tr>";
        echo "<td>" . $app['id'] . "</td>";
        echo "<td>" . $app['first_name'] . ' ' . $app['last_name'] . "</td>";
        echo "<td>" . ($app['choice1'] ?? 'فارغ') . "</td>";
        echo "<td>" . ($app['choice2'] ?? 'فارغ') . "</td>";
        echo "<td>" . ($app['choice3'] ?? 'فارغ') . "</td>";
        echo "<td>" . ($app['photo_path'] ?? 'فارغ') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>لا توجد طلبات</p>";
}

echo "<hr>";

// التحقق من وجود مجلد الصور
$photoDir = __DIR__ . '/uploads/photos';
echo "<h3>التحقق من مجلد الصور</h3>";
echo "<p>مسار مجلد الصور: $photoDir</p>";
echo "<p>المجلد موجود: " . (is_dir($photoDir) ? "نعم ✓" : "لا ✗") . "</p>";
if (is_dir($photoDir)) {
    $files = scandir($photoDir);
    echo "<p>عدد الملفات في المجلد: " . count(array_diff($files, ['.', '..'])) . "</p>";
    echo "<p>أول 5 ملفات:</p>";
    echo "<ul>";
    $count = 0;
    foreach (array_diff($files, ['.', '..']) as $file) {
        if ($count < 5) {
            echo "<li>$file</li>";
            $count++;
        }
    }
    echo "</ul>";
}
?>
