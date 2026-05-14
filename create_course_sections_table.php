<?php
/**
 * create_course_sections_table.php - إنشاء جداول الفئات والقاعات
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDB();
    
    echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head>';
    echo '<meta charset="UTF-8"><title>إنشاء جداول الفئات</title>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">';
    echo '<style>body{padding:20px;}</style>';
    echo '</head><body><div class="container">';
    echo '<h2>🔧 إنشاء جداول الفئات والقاعات</h2>';
    
    // 1. إنشاء جدول القاعات
    echo '<div class="alert alert-info">📦 جاري إنشاء جدول rooms...</div>';
    
    $db->execute("CREATE TABLE IF NOT EXISTS rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_number VARCHAR(50) NOT NULL,
        building VARCHAR(100),
        floor INT,
        capacity INT DEFAULT 30,
        room_type ENUM('classroom', 'lab', 'hall') DEFAULT 'classroom',
        status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // إضافة قاعات افتراضية
    $db->execute("INSERT IGNORE INTO rooms (room_number, building, floor, capacity, room_type) VALUES
        ('قاعة 101', 'المبنى الرئيسي', 1, 30, 'classroom'),
        ('قاعة 102', 'المبنى الرئيسي', 1, 30, 'classroom'),
        ('قاعة 103', 'المبنى الرئيسي', 1, 40, 'classroom'),
        ('قاعة 201', 'المبنى الرئيسي', 2, 30, 'classroom'),
        ('قاعة 202', 'المبنى الرئيسي', 2, 30, 'classroom'),
        ('مختبر 1', 'المبنى الرئيسي', 1, 20, 'lab'),
        ('مختبر 2', 'المبنى الرئيسي', 1, 20, 'lab'),
        ('مختبر 3', 'المبنى الرئيسي', 2, 25, 'lab')");
    
    echo '<div class="alert alert-success">✅ تم إنشاء جدول rooms وإضافة 8 قاعات</div>';
    
    // 2. إنشاء جدول الفئات
    echo '<div class="alert alert-info">📦 جاري إنشاء جدول course_sections...</div>';
    
    $db->execute("CREATE TABLE IF NOT EXISTS course_sections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        section_number VARCHAR(20) NOT NULL,
        section_type ENUM('theory', 'practical') DEFAULT 'theory',
        max_students INT DEFAULT 30,
        day_of_week ENUM('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday') DEFAULT 'Saturday',
        start_time TIME,
        end_time TIME,
        room_id INT,
        professor_id INT,
        semester_id INT NOT NULL,
        status ENUM('active', 'inactive', 'cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_section (course_id, section_number, semester_id),
        KEY course_id (course_id),
        KEY semester_id (semester_id),
        KEY room_id (room_id),
        KEY professor_id (professor_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo '<div class="alert alert-success">✅ تم إنشاء جدول course_sections</div>';
    
    // 3. إنشاء جدول تسجيل الطلاب في الفئات
    echo '<div class="alert alert-info">📦 جاري إنشاء جدول section_enrollments...</div>';
    
    $db->execute("CREATE TABLE IF NOT EXISTS section_enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        section_id INT NOT NULL,
        enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('registered', 'withdrawn', 'completed') DEFAULT 'registered',
        UNIQUE KEY unique_enrollment (student_id, section_id),
        KEY student_id (student_id),
        KEY section_id (section_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo '<div class="alert alert-success">✅ تم إنشاء جدول section_enrollments</div>';
    
    echo '<div class="mt-4">';
    echo '<a href="dean/generate_sections_college.php" class="btn btn-primary btn-lg">🚀 توليد الفئات الآن</a>';
    echo '<a href="dean/sections.php" class="btn btn-success btn-lg ms-2">عرض الفئات</a>';
    echo '</div>';
    
    echo '</div></body></html>';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">❌ خطأ: ' . $e->getMessage() . '</div>';
}
