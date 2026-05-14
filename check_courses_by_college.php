<?php
/**
 * check_courses_by_college.php - التحقق من المواد حسب الكلية
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDB();
    
    // جلب الكليات
    $colleges = $db->fetchAll("SELECT * FROM colleges WHERE status = 'active' ORDER BY id");
    
    // جلب المواد
    $courses = $db->fetchAll("SELECT * FROM courses ORDER BY department, level, course_code");
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التحقق من المواد حسب الكلية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background: #f5f5f5; padding: 20px; }
        .college-box { 
            background: white; 
            border-radius: 10px; 
            padding: 20px; 
            margin-bottom: 20px;
            border-right: 4px solid #667eea;
        }
        .course-item {
            padding: 8px 12px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 5px;
            border-right: 3px solid #4caf50;
        }
        .no-courses {
            color: #999;
            font-style: italic;
        }
        .stats-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4"><i class="fas fa-book me-2 text-primary"></i>التحقق من المواد حسب الكلية</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">❌ خطأ: <?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            
            <div class="stats-box">
                <h5>📊 الإحصائيات العامة:</h5>
                <div class="row">
                    <div class="col-md-4">
                        <strong>عدد الكليات:</strong> <?php echo count($colleges); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>عدد المواد الكلي:</strong> <?php echo count($courses); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>متوسط المواد/كلية:</strong> 
                        <?php echo count($colleges) > 0 ? round(count($courses) / count($colleges), 1) : 0; ?>
                    </div>
                </div>
            </div>
            
            <?php 
            // تجميع المواد حسب الكلية (باستخدام عمود college)
            $coursesByCollege = [];
            foreach ($courses as $course) {
                $collegeName = $course['college'] ?? $course['department'] ?? 'غير محدد';
                if (!isset($coursesByCollege[$collegeName])) {
                    $coursesByCollege[$collegeName] = [];
                }
                $coursesByCollege[$collegeName][] = $course;
            }
            
            // عرض كل كلية وموادها
            foreach ($colleges as $college): 
                $collegeName = $college['college_name'];
                // البحث المباشر عن مواد الكلية
                $collegeCourses = $coursesByCollege[$collegeName] ?? [];
            ?>
            <div class="college-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($college['college_code']); ?></span>
                        <h4 class="d-inline-block ms-2"><?php echo htmlspecialchars($collegeName); ?></h4>
                    </div>
                    <span class="badge bg-<?php echo count($collegeCourses) > 0 ? 'success' : 'warning'; ?> rounded-pill">
                        <?php echo count($collegeCourses); ?> مادة
                    </span>
                </div>
                
                <?php if (empty($collegeCourses)): ?>
                    <p class="no-courses">⚠️ لا توجد مواد مضافة لهذه الكلية</p>
                <?php else: ?>
                    <div class="courses-list">
                        <?php foreach (array_slice($collegeCourses, 0, 5) as $course): ?>
                        <div class="course-item">
                            <strong><?php echo htmlspecialchars($course['course_code']); ?></strong> - 
                            <?php echo htmlspecialchars($course['course_name']); ?>
                            <span class="badge bg-info ms-2">مستوى <?php echo $course['level']; ?></span>
                            <span class="badge bg-secondary"><?php echo $course['credits']; ?> ساعات</span>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($collegeCourses) > 5): ?>
                        <p class="text-muted mt-2">... و <?php echo count($collegeCourses) - 5; ?> مواد أخرى</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
            <!-- المواد غير المصنفة -->
            <?php 
            $uncategorized = $coursesByCollege['غير محدد'] ?? [];
            $knownColleges = array_column($colleges, 'college_name');
            foreach ($coursesByCollege as $collegeName => $collegeCourses) {
                if ($collegeName !== 'غير محدد' && !in_array($collegeName, $knownColleges)) {
                    $uncategorized = array_merge($uncategorized, $collegeCourses);
                }
            }
            
            if (!empty($uncategorized)): 
            ?>
            <div class="college-box" style="border-right-color: #ff9800;">
                <h5 class="text-warning">⚠️ مواد غير مرتبطة بكلية معروفة:</h5>
                <p class="text-muted"><?php echo count($uncategorized); ?> مادة</p>
            </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <a href="display_all_users.php" class="btn btn-outline-primary">👥 المستخدمين</a>
            <a href="view_colleges.php" class="btn btn-outline-success">🏛️ الكليات</a>
            <a href="index.php" class="btn btn-outline-dark">🏠 الرئيسية</a>
        </div>
    </div>
</body>
</html>
