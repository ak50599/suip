<?php
/**
 * debug_courses.php - فحص مباشر للمواد
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDB();
    
    echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head>';
    echo '<meta charset="UTF-8"><title>فحص المواد</title>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">';
    echo '<style>body{padding:20px;}</style>';
    echo '</head><body><div class="container">';
    echo '<h2>🔍 فحص مباشر للمواد</h2>';
    
    // عرض هيكل جدول courses
    echo '<h4 class="mt-4">📋 هيكل جدول courses:</h4>';
    $columns = $db->fetchAll("SHOW COLUMNS FROM courses");
    echo '<table class="table table-sm table-bordered">';
    echo '<tr class="table-dark"><th>العمود</th><th>النوع</th></tr>';
    foreach ($columns as $col) {
        echo '<tr><td>' . $col['Field'] . '</td><td>' . $col['Type'] . '</td></tr>';
    }
    echo '</table>';
    
    // عرض قيم college الفريدة
    echo '<h4 class="mt-4">📊 قيم college الفريدة:</h4>';
    $values = $db->fetchAll("SELECT college, COUNT(*) as count FROM courses GROUP BY college ORDER BY count DESC");
    echo '<table class="table table-bordered">';
    echo '<tr class="table-dark"><th>الكلية</th><th>العدد</th></tr>';
    foreach ($values as $row) {
        echo '<tr><td>' . htmlspecialchars($row['college'] ?? 'NULL') . '</td><td>' . $row['count'] . '</td></tr>';
    }
    echo '</table>';
    
    // عرض أسماء الكليات في جدول colleges
    echo '<h4 class="mt-4">🏛️ أسماء الكليات:</h4>';
    $colleges = $db->fetchAll("SELECT college_name FROM colleges");
    echo '<ul>';
    foreach ($colleges as $c) {
        echo '<li>' . htmlspecialchars($c['college_name']) . '</li>';
    }
    echo '</ul>';
    
    // عينة من المواد
    echo '<h4 class="mt-4">📚 عينة من المواد (أول 10):</h4>';
    $courses = $db->fetchAll("SELECT course_code, course_name, college, department, level FROM courses LIMIT 10");
    echo '<table class="table table-sm table-bordered">';
    echo '<tr class="table-dark"><th>الكود</th><th>الاسم</th><th>college</th><th>department</th><th>المستوى</th></tr>';
    foreach ($courses as $course) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($course['course_code']) . '</td>';
        echo '<td>' . htmlspecialchars($course['course_name']) . '</td>';
        echo '<td>' . htmlspecialchars($course['college'] ?? 'NULL') . '</td>';
        echo '<td>' . htmlspecialchars($course['department'] ?? 'NULL') . '</td>';
        echo '<td>' . $course['level'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '</div></body></html>';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">❌ خطأ: ' . $e->getMessage() . '</div>';
}
