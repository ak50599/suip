<?php
// run_president_tables_migration.php - سكريبت ترحيل جداول رئيس الجامعة

require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

echo "<h1>ترحيل جداول رئيس الجامعة</h1>";
echo "<div style='font-family: Arial; direction: rtl; padding: 20px;'>";

try {
    // 1. جدول السكن الجامعي
    echo "<h2>1. إنشاء جدول السكن الجامعي...</h2>";
    $db->execute("
        CREATE TABLE IF NOT EXISTS dormitories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            name_en VARCHAR(100),
            location VARCHAR(200),
            total_capacity INT DEFAULT 0,
            current_occupancy INT DEFAULT 0,
            number_of_rooms INT DEFAULT 0,
            gender ENUM('male', 'female', 'mixed') DEFAULT 'mixed',
            status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ تم إنشاء جدول dormitories بنجاح<br>";

    // جدول الغرف
    $db->execute("
        CREATE TABLE IF NOT EXISTS dormitory_rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            dormitory_id INT NOT NULL,
            room_number VARCHAR(20) NOT NULL,
            capacity INT DEFAULT 2,
            current_occupancy INT DEFAULT 0,
            floor_number INT DEFAULT 1,
            room_type ENUM('single', 'double', 'triple', 'quad') DEFAULT 'double',
            status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
            amenities JSON,
            FOREIGN KEY (dormitory_id) REFERENCES dormitories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ تم إنشاء جدول dormitory_rooms بنجاح<br>";

    // جدول إشغال الغرف
    $db->execute("
        CREATE TABLE IF NOT EXISTS room_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            student_id INT NOT NULL,
            assigned_date DATE NOT NULL,
            status ENUM('active', 'moved_out', 'transferred') DEFAULT 'active',
            notes TEXT,
            FOREIGN KEY (room_id) REFERENCES dormitory_rooms(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            UNIQUE KEY unique_room_student (room_id, student_id, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ تم إنشاء جدول room_assignments بنجاح<br><br>";

    // 2. جدول القاعات الدراسية
    echo "<h2>2. إنشاء جدول القاعات الدراسية...</h2>";
    $db->execute("
        CREATE TABLE IF NOT EXISTS classrooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            building VARCHAR(100) NOT NULL,
            floor_number INT DEFAULT 1,
            capacity INT DEFAULT 30,
            room_type ENUM('lecture', 'lab', 'seminar', 'exam') DEFAULT 'lecture',
            has_projector BOOLEAN DEFAULT FALSE,
            has_computers BOOLEAN DEFAULT FALSE,
            has_ac BOOLEAN DEFAULT FALSE,
            equipment JSON,
            status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ تم إنشاء جدول classrooms بنجاح<br>";

    // جدول حجز القاعات
    $db->execute("
        CREATE TABLE IF NOT EXISTS classroom_bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            classroom_id INT NOT NULL,
            course_id INT,
            professor_id INT,
            semester_id INT NOT NULL,
            day_of_week ENUM('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            booking_type ENUM('regular', 'exam', 'event', 'other') DEFAULT 'regular',
            status ENUM('active', 'cancelled') DEFAULT 'active',
            FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
            FOREIGN KEY (professor_id) REFERENCES professors(id) ON DELETE SET NULL,
            FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ تم إنشاء جدول classroom_bookings بنجاح<br><br>";

    // 3. جدول التقويم الجامعي العام
    echo "<h2>3. إنشاء جدول التقويم الجامعي العام...</h2>";
    $db->execute("
        CREATE TABLE IF NOT EXISTS academic_calendar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            semester_id INT,
            event_type ENUM('semester_start', 'semester_end', 'registration_start', 'registration_end', 
                            'exam_start', 'exam_end', 'holiday', 'event', 'other') NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            start_date DATE NOT NULL,
            end_date DATE,
            is_system_trigger BOOLEAN DEFAULT FALSE COMMENT 'يؤثر على عمل النظام',
            trigger_action ENUM('enable_admission', 'disable_admission', 'enable_registration', 
                              'disable_registration', 'enable_grades', 'disable_grades') DEFAULT NULL,
            status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ تم إنشاء جدول academic_calendar بنجاح<br><br>";

    // 4. جدول اعتماد النتائج النهائية
    echo "<h2>4. إنشاء جدول اعتماد النتائج النهائية...</h2>";
    $db->execute("
        CREATE TABLE IF NOT EXISTS results_approval (
            id INT AUTO_INCREMENT PRIMARY KEY,
            semester_id INT NOT NULL,
            college_id INT,
            approval_status ENUM('draft', 'dean_approved', 'president_approved', 'rejected') DEFAULT 'draft',
            dean_id INT,
            dean_approval_date DATETIME,
            dean_notes TEXT,
            president_id INT,
            president_approval_date DATETIME,
            president_notes TEXT,
            digital_signature VARCHAR(255) COMMENT 'الخاتم الرقمي',
            is_final BOOLEAN DEFAULT FALSE COMMENT 'لا يمكن التراجع بعد الموافقة',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
            FOREIGN KEY (dean_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (president_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ تم إنشاء جدول results_approval بنجاح<br><br>";

    // 5. جدول التنبيهات الحرجة
    echo "<h2>5. إنشاء جدول التنبيهات الحرجة...</h2>";
    $db->execute("
        CREATE TABLE IF NOT EXISTS critical_alerts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            alert_type ENUM('security', 'database', 'system', 'exception_request', 'other') NOT NULL,
            priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            title VARCHAR(200) NOT NULL,
            description TEXT NOT NULL,
            source VARCHAR(100) COMMENT 'مصدر التنبيه (python script, system, etc.)',
            related_id INT,
            related_type VARCHAR(50),
            status ENUM('new', 'acknowledged', 'in_progress', 'resolved', 'dismissed') DEFAULT 'new',
            action_taken TEXT,
            resolved_by INT,
            resolved_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ تم إنشاء جدول critical_alerts بنجاح<br>";

    // جدول طلبات الاستثناءات
    $db->execute("
        CREATE TABLE IF NOT EXISTS exception_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            request_type ENUM('capacity_override', 'deadline_extension', 'grade_adjustment', 'other') NOT NULL,
            requester_id INT NOT NULL,
            requester_role ENUM('dean', 'professor', 'admin') NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT NOT NULL,
            related_id INT,
            related_type VARCHAR(50),
            priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            reviewed_by INT,
            reviewed_at DATETIME,
            review_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ تم إنشاء جدول exception_requests بنجاح<br><br>";

    // 6. جدول سجل العمليات الكلي
    echo "<h2>6. إنشاء جدول سجل العمليات الكلي...</h2>";
    $db->execute("
        CREATE TABLE IF NOT EXISTS global_audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            user_role VARCHAR(50),
            action_type ENUM('grade_modification', 'professor_change', 'admission_rate_change', 
                            'setting_change', 'user_change', 'critical_action', 'other') NOT NULL,
            action_description TEXT NOT NULL,
            target_table VARCHAR(100),
            target_id INT,
            old_value TEXT,
            new_value TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ تم إنشاء جدول global_audit_log بنجاح<br><br>";

    // 7. تحسين جدول المدفوعات
    echo "<h2>7. تحسين جدول المدفوعات...</h2>";
    // التحقق من وجود جدول payments
    $checkPayments = $db->fetchOne("SHOW TABLES LIKE 'payments'");
    if (!$checkPayments) {
        $db->execute("
            CREATE TABLE payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                type ENUM('tuition', 'fees', 'dormitory', 'service', 'other') DEFAULT 'tuition',
                amount_usd DECIMAL(10,2) DEFAULT 0,
                amount_syp DECIMAL(15,2) DEFAULT 0,
                description TEXT,
                semester_id INT,
                status ENUM('pending', 'paid', 'overdue', 'cancelled', 'refunded') DEFAULT 'pending',
                payment_date DATETIME,
                payment_method VARCHAR(50),
                transaction_id VARCHAR(100),
                receipt_url VARCHAR(255),
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
                FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ تم إنشاء جدول payments بنجاح<br>";
    } else {
        // إضافة الأعمدة الجديدة إذا لم تكن موجودة
        try {
            $db->execute("ALTER TABLE payments ADD COLUMN IF NOT EXISTS type ENUM('tuition', 'fees', 'dormitory', 'service', 'other') DEFAULT 'tuition'");
            $db->execute("ALTER TABLE payments ADD COLUMN IF NOT EXISTS amount_usd DECIMAL(10,2) DEFAULT 0");
            $db->execute("ALTER TABLE payments ADD COLUMN IF NOT EXISTS amount_syp DECIMAL(15,2) DEFAULT 0");
            $db->execute("ALTER TABLE payments ADD COLUMN IF NOT EXISTS created_by INT");
            $db->execute("ALTER TABLE payments ADD COLUMN IF NOT EXISTS status ENUM('pending', 'paid', 'overdue', 'cancelled', 'refunded') DEFAULT 'pending'");
            echo "✓ تم تحديث جدول payments بنجاح<br>";
        } catch (Exception $e) {
            echo "⚠ تم تخطي تحديث payments (قد تكون الأعمدة موجودة)<br>";
        }
    }
    echo "<br>";

    echo "<h3 style='color: green;'>✅ تم إنشاء جميع الجداول بنجاح!</h3>";
    echo "<p><a href='admin/president.php'>الانتقال إلى لوحة رئيس الجامعة</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ حدث خطأ أثناء الترحيل:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}

echo "</div>";
