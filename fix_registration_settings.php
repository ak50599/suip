<?php
/**
 * fix_registration_settings.php - إضافة الأعمدة المفقودة في جدول registration_settings
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDB();

    // إضافة العمود المفقود updated_by
    try {
        $db->execute("ALTER TABLE registration_settings ADD COLUMN updated_by INT DEFAULT NULL");
        echo '<div class="alert alert-success">✅ تم إضافة عمود updated_by بنجاح!</div>';
    } catch (Exception $e) {
        echo '<div class="alert alert-warning">⚠️ عمود updated_by موجود بالفعل</div>';
    }

    // إضافة العمود المفقود auto_enroll_new_students
    try {
        $db->execute("ALTER TABLE registration_settings ADD COLUMN auto_enroll_new_students TINYINT(1) DEFAULT 1 AFTER semester_id");
        echo '<div class="alert alert-success">✅ تم إضافة عمود auto_enroll_new_students بنجاح!</div>';
    } catch (Exception $e) {
        echo '<div class="alert alert-warning">⚠️ عمود auto_enroll_new_students موجود بالفعل</div>';
    }

    echo '<hr>';
    echo '<a href="admin/president.php" class="btn btn-primary">العودة لصفحة الرئيس</a>';

} catch (Exception $e) {
    echo '<div class="alert alert-danger">❌ خطأ عام: ' . $e->getMessage() . '</div>';
    echo '<a href="admin/president.php" class="btn btn-primary">المحاولة مرة أخرى</a>';
}
