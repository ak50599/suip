<?php
// fix_all_tables.php - إنشاء جميع الجداول المفقودة

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<h2>إصلاح جميع الجداول المفقودة</h2>";
echo "<hr>";

try {
    $db = getDB();
    
    // 1. إضافة عمود last_login لـ committee_users
    echo "<h4>1. إضافة عمود last_login لـ committee_users:</h4>";
    try {
        $db->execute("ALTER TABLE committee_users ADD COLUMN last_login DATETIME NULL");
        echo "✅ تم إضافة عمود last_login<br>";
    } catch (Exception $e) {
        echo "ℹ️ العمود قد يكون موجوداً أو خطأ: " . $e->getMessage() . "<br>";
    }
    
    // 2. إنشاء جدول college_admission_rates
    echo "<h4>2. إنشاء جدول college_admission_rates:</h4>";
    $db->execute("CREATE TABLE IF NOT EXISTS college_admission_rates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        college_name VARCHAR(100) NOT NULL,
        college_name_en VARCHAR(100),
        min_grade DECIMAL(5,2) NOT NULL,
        max_grade DECIMAL(5,2) DEFAULT 100.00,
        capacity INT DEFAULT 0,
        current_accepted_syrian INT DEFAULT 0,
        current_accepted_non_syrian INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ تم إنشاء جدول college_admission_rates<br>";
    
    // 3. إضافة بيانات الكليات
    echo "<h4>3. إضافة بيانات الكليات:</h4>";
    $colleges = [
        ['كلية الطب البشري', 'College of Medicine', 96.00, 100.00, 200],
        ['كلية طب الأسنان', 'College of Dentistry', 90.00, 100.00, 150],
        ['كلية الصيدلة', 'College of Pharmacy', 85.00, 100.00, 180],
        ['كلية الهندسة المعلوماتية', 'College of Information Engineering', 80.00, 100.00, 250],
        ['كلية الهندسة البترولية', 'College of Petroleum Engineering', 75.00, 100.00, 200],
        ['كلية الهندسة الكيميائية', 'College of Chemical Engineering', 70.00, 100.00, 180],
        ['معهد الحاسوب', 'Computer Institute', 60.00, 100.00, 300],
        ['كلية الترجمة واللغات', 'College of Translation and Languages', 60.00, 100.00, 220]
    ];
    
    foreach ($colleges as $college) {
        try {
            $db->execute("INSERT IGNORE INTO college_admission_rates 
                (college_name, college_name_en, min_grade, max_grade, capacity) 
                VALUES (?, ?, ?, ?, ?)", $college);
        } catch (Exception $e) {
            // تجاهل أخطاء التكرار
        }
    }
    echo "✅ تم إضافة بيانات الكليات<br>";
    
    // 4. إنشاء جدول admission_settings
    echo "<h4>4. إنشاء جدول admission_settings:</h4>";
    $db->execute("CREATE TABLE IF NOT EXISTS admission_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        is_open TINYINT(1) DEFAULT 0,
        start_date DATETIME NULL,
        end_date DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // إضافة الإعدادات الافتراضية
    $db->execute("INSERT IGNORE INTO admission_settings (id, is_open, start_date, end_date) 
                  VALUES (1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))");
    echo "✅ تم إنشاء جدول admission_settings<br>";
    
    echo "<hr>";
    echo "<div style='background:#d4edda; padding:15px; border-radius:5px;'>✅ تم إصلاح جميع الجداول بنجاح!</div>";
    echo "<br><a href='committee/dashboard.php' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>اذهب إلى لوحة لجنة القبول</a>";
    
} catch (Exception $e) {
    echo "<div style='color:red; padding:15px; background:#f8d7da; border-radius:5px;'>❌ خطأ: " . $e->getMessage() . "</div>";
}
?>
<style>
body { font-family: Arial; padding: 20px; direction: rtl; }
h4 { color: #333; margin-top: 20px; }
</style>
