<?php
/**
 * create_missing_tables.php - إنشاء الجداول المفقودة
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<!DOCTYPE html>
<html dir=\"rtl\" lang=\"ar\">
<head>
    <meta charset=\"UTF-8\">
    <title>إنشاء الجداول المفقودة</title>
    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css\">
</head>
<body class=\"p-4\">
<div class=\"container\">";

try {
    $db = getDB();
    
    // جدول الكليات
    echo "<h3>إنشاء جدول colleges...</h3>";
    $db->execute("CREATE TABLE IF NOT EXISTS colleges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        college_name VARCHAR(100) NOT NULL,
        college_name_en VARCHAR(100),
        college_code VARCHAR(20) UNIQUE,
        description TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class=\"alert alert-success\">✅ تم إنشاء جدول colleges</div>";
    
    // جدول العمداء
    echo "<h3>إنشاء جدول deans...</h3>";
    $db->execute("CREATE TABLE IF NOT EXISTS deans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        dean_id VARCHAR(20) UNIQUE,
        college VARCHAR(100),
        department VARCHAR(100),
        office_location VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class=\"alert alert-success\">✅ تم إنشاء جدول deans</div>";
    
    // جدول لجنة القبول
    echo "<h3>إنشاء جدول committee_users...</h3>";
    $db->execute("CREATE TABLE IF NOT EXISTS committee_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        committee_email VARCHAR(100),
        role VARCHAR(50) DEFAULT 'member',
        is_active TINYINT(1) DEFAULT 1,
        last_login DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class=\"alert alert-success\">✅ تم إنشاء جدول committee_users</div>";
    
    // جدول الموظفين
    echo "<h3>إنشاء جدول staff...</h3>";
    $db->execute("CREATE TABLE IF NOT EXISTS staff (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        staff_id VARCHAR(20) UNIQUE,
        department VARCHAR(100),
        position VARCHAR(100),
        office_location VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class=\"alert alert-success\">✅ تم إنشاء جدول staff</div>";
    
    // جدول السكرتارية
    echo "<h3>إنشاء جدول dean_secretaries...</h3>";
    $db->execute("CREATE TABLE IF NOT EXISTS dean_secretaries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        secretary_id VARCHAR(20) UNIQUE,
        dean_id INT,
        college VARCHAR(100),
        department VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (dean_id) REFERENCES deans(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class=\"alert alert-success\">✅ تم إنشاء جدول dean_secretaries</div>";
    
    // جدول محاولات الدخول
    echo "<h3>إنشاء جدول login_attempts...</h3>";
    $db->execute("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100),
        ip_address VARCHAR(45),
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('success', 'failed') DEFAULT 'failed'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class=\"alert alert-success\">✅ تم إنشاء جدول login_attempts</div>";
    
    // جدول الطلاب المقبولين
    echo "<h3>إنشاء جدول accepted_students...</h3>";
    $db->execute("CREATE TABLE IF NOT EXISTS accepted_students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        university_id VARCHAR(11) UNIQUE NOT NULL,
        student_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        college_id INT NOT NULL,
        college_code VARCHAR(20),
        admission_year INT NOT NULL,
        admission_type ENUM('committee', 'smart') DEFAULT 'committee',
        password VARCHAR(255) NOT NULL,
        password_changed TINYINT(1) DEFAULT 0,
        welcome_sent TINYINT(1) DEFAULT 0,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE,
        INDEX idx_university_id (university_id),
        INDEX idx_college_year (college_id, admission_year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class=\"alert alert-success\">✅ تم إنشاء جدول accepted_students</div>";
    
    // جدول لحفظ آخر رقم تسلسلي لكل كلية
    echo "<h3>إنشاء جدول college_sequence...</h3>";
    $db->execute("CREATE TABLE IF NOT EXISTS college_sequence (
        id INT AUTO_INCREMENT PRIMARY KEY,
        college_id INT NOT NULL,
        college_code VARCHAR(20) NOT NULL,
        admission_year INT NOT NULL,
        last_sequence INT DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_college_year (college_id, admission_year),
        FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class=\"alert alert-success\">✅ تم إنشاء جدول college_sequence</div>";
    
    // إضافة أكواد الكليات لنظام الترقيم
    echo "<h3>إضافة أكواد الكليات لنظام الترقيم...</h3>";
    $db->execute("CREATE TABLE IF NOT EXISTS college_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        college_id INT NOT NULL UNIQUE,
        college_name VARCHAR(100) NOT NULL,
        college_code_num VARCHAR(3) NOT NULL,
        FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // إضافة البيانات الافتراضية لأكواد الكليات
    $college_codes_data = [
        ['كلية الطب البشري', '100'],
        ['كلية طب الأسنان', '110'],
        ['كلية الصيدلة', '120'],
        ['كلية الهندسة المعلوماتية', '130'],
        ['كلية الهندسة البترولية', '140'],
        ['كلية الهندسة الكيميائية', '150'],
        ['كلية الترجمة واللغات', '160'],
        ['معهد الحاسوب', '170']
    ];
    
    foreach ($college_codes_data as $code_data) {
        try {
            // الحصول على معرف الكلية
            $college = $db->query("SELECT id FROM colleges WHERE college_name LIKE ?", ['%' . $code_data[0] . '%'])->fetch();
            if ($college) {
                $db->execute("INSERT IGNORE INTO college_codes (college_id, college_name, college_code_num) VALUES (?, ?, ?)", 
                    [$college['id'], $code_data[0], $code_data[1]]);
            }
        } catch (Exception $e) {
            // تجاهل الأخطاء
        }
    }
    echo "<div class=\"alert alert-success\">✅ تم إضافة أكواد الكليات</div>";
    
    // إضافة بيانات افتراضية للكليات
    echo "<h3>إضافة بيانات الكليات...</h3>";
    $colleges = [
        ['كلية الطب البشري', 'College of Medicine', 'med'],
        ['كلية طب الأسنان', 'College of Dentistry', 'dent'],
        ['كلية الصيدلة', 'College of Pharmacy', 'pharm'],
        ['كلية الهندسة المعلوماتية', 'College of Information Engineering', 'ite'],
        ['كلية الهندسة البترولية', 'College of Petroleum Engineering', 'petro'],
        ['كلية الهندسة الكيميائية', 'College of Chemical Engineering', 'chem'],
        ['معهد الحاسوب', 'Computer Institute', 'comp'],
        ['كلية الترجمة واللغات', 'College of Translation and Languages', 'lang']
    ];
    
    foreach ($colleges as $college) {
        try {
            $db->execute("INSERT IGNORE INTO colleges (college_name, college_name_en, college_code) VALUES (?, ?, ?)", $college);
        } catch (Exception $e) {
            // تجاهل أخطاء التكرار
        }
    }
    echo "<div class=\"alert alert-success\">✅ تم إضافة بيانات الكليات</div>";
    
    echo "<div class=\"alert alert-success mt-4\"><h4>✅ تم إنشاء جميع الجداول المفقودة بنجاح!</h4></div>";
    echo "<a href=\"display_all_users.php\" class=\"btn btn-primary\">عرض المستخدمين</a>";
    
} catch (Exception $e) {
    echo "<div class=\"alert alert-danger\">❌ خطأ: " . $e->getMessage() . "</div>";
}

echo "
</div>
</body>
</html>";
?>
