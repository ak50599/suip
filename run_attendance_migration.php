<?php
// run_attendance_migration.php - تشغيل ترحيل جدول الحضور

require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h2>إنشاء جداول نظام الحضور الذكي</h2>";
echo "<hr>";

try {
    $db = getDB();
    
    // قراءة ملف الترحيل
    $migrationFile = __DIR__ . '/database/migrations/create_attendance_table.sql';
    if (!file_exists($migrationFile)) {
        throw new Exception("ملف الترحيل غير موجود: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // تقسيم الاستعلامات وتنفيذها
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        try {
            $db->execute($query);
            echo "✅ تم التنفيذ بنجاح<br>";
        } catch (Exception $e) {
            // تجاهل الأخطاء المتوقعة (مثل الجداول الموجودة)
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "⚠️ تحذير: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "<hr>";
    echo "<div class='alert alert-success'>✅ تم إنشاء جداول الحضور بنجاح!</div>";
    echo "<a href='index.php' class='btn btn-primary'>العودة للصفحة الرئيسية</a>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ خطأ: " . $e->getMessage() . "</div>";
}
?>
