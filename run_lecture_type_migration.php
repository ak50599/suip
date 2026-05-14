<?php
// run_lecture_type_migration.php - تشغيل ترحيل نوع المحاضرة وجداول التسجيل

require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h2>تحديث نظام الحضور الذكي - إضافة نوع المحاضرة والتسجيل</h2>";
echo "<hr>";

try {
    $db = getDB();
    
    // قراءة ملف الترحيل
    $migrationFile = __DIR__ . '/database/migrations/add_lecture_type_and_registrations.sql';
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
            // تجاهل الأخطاء المتوقعة (مثل الأعمدة الموجودة)
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate column') === false) {
                echo "⚠️ تحذير: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "<hr>";
    echo "<div class='alert alert-success'>✅ تم تحديث نظام الحضور بنجاح!</div>";
    echo "<a href='index.php' class='btn btn-primary'>العودة للصفحة الرئيسية</a>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ خطأ: " . $e->getMessage() . "</div>";
}
?>
