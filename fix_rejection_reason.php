<?php
/**
 * fix_rejection_reason.php - إضافة عمود rejection_reason للجدول
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<!DOCTYPE html>
<html dir='rtl' lang='ar'>
<head>
    <meta charset='UTF-8'>
    <title>إصلاح قاعدة البيانات</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css'>
</head>
<body class='p-4'>
<div class='container'>";

try {
    $db = getDB();
    
    // التحقق من وجود العمود
    echo "<h3>التحقق من عمود rejection_reason...</h3>";
    
    try {
        $db->query("SELECT rejection_reason FROM admission_applications LIMIT 1");
        echo "<div class='alert alert-info'>✅ العمود موجود بالفعل!</div>";
    } catch (Exception $e) {
        // العمود غير موجود، قم بإضافته
        $db->execute("ALTER TABLE admission_applications ADD COLUMN rejection_reason TEXT NULL AFTER status");
        echo "<div class='alert alert-success'>✅ تم إضافة عمود rejection_reason بنجاح!</div>";
    }
    
    echo "<div class='mt-4'>
        <a href='committee/smart-admission.php' class='btn btn-primary'>العودة لنظام القبول الذكي</a>
        <a href='index.php' class='btn btn-secondary'>الصفحة الرئيسية</a>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ خطأ: " . $e->getMessage() . "</div>";
}

echo "
</div>
</body>
</html>";
?>
