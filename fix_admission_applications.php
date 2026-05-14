<?php
// fix_admission_applications.php - إضافة الأعمدة المفقودة

require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h2>إصلاح جدول طلبات القبول</h2>";
echo "<hr>";

try {
    $db = getDB();
    
    // التحقق من وجود عمود accepted_by
    $columns = $db->fetchAll("SHOW COLUMNS FROM admission_applications");
    $columnNames = array_column($columns, 'Field');
    
    echo "الأعمدة الحالية: " . implode(', ', $columnNames) . "<br><br>";
    
    // إضافة عمود accepted_by إذا لم يكن موجوداً
    if (!in_array('accepted_by', $columnNames)) {
        $db->execute("ALTER TABLE admission_applications ADD COLUMN accepted_by INT DEFAULT NULL");
        echo "✅ تم إضافة عمود accepted_by<br>";
    } else {
        echo "ℹ️ عمود accepted_by موجود مسبقاً<br>";
    }
    
    // إضافة عمود rejection_reason إذا لم يكن موجوداً
    if (!in_array('rejection_reason', $columnNames)) {
        $db->execute("ALTER TABLE admission_applications ADD COLUMN rejection_reason TEXT DEFAULT NULL");
        echo "✅ تم إضافة عمود rejection_reason<br>";
    } else {
        echo "ℹ️ عمود rejection_reason موجود مسبقاً<br>";
    }
    
    // إضافة عمود accepted_college إذا لم يكن موجوداً
    if (!in_array('accepted_college', $columnNames)) {
        $db->execute("ALTER TABLE admission_applications ADD COLUMN accepted_college VARCHAR(100) DEFAULT NULL");
        echo "✅ تم إضافة عمود accepted_college<br>";
    } else {
        echo "ℹ️ عمود accepted_college موجود مسبقاً<br>";
    }
    
    // إضافة عمود acceptance_date إذا لم يكن موجوداً
    if (!in_array('acceptance_date', $columnNames)) {
        $db->execute("ALTER TABLE admission_applications ADD COLUMN acceptance_date DATETIME DEFAULT NULL");
        echo "✅ تم إضافة عمود acceptance_date<br>";
    } else {
        echo "ℹ️ عمود acceptance_date موجود مسبقاً<br>";
    }
    
    echo "<hr>";
    echo "<div class='alert alert-success'>✅ تم إصلاح الجدول بنجاح!</div>";
    echo "<a href='committee/dashboard.php' class='btn btn-primary'>الذهاب لصفحة اللجنة</a>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ خطأ: " . $e->getMessage() . "</div>";
}
?>
