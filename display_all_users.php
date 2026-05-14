<?php
// display_all_users.php - عرض معلومات تسجيل الدخول لجميع المستخدمين
// يجلب البيانات مباشرة من قاعدة البيانات في كل مرة

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();

// جلب جميع المستخدمين الأساسيين
$missingTables = [];
try {
    $users = $db->fetchAll(
        "SELECT u.id, u.username, u.password, u.email, u.full_name, u.user_type, u.status, u.created_at
         FROM users u
         ORDER BY u.user_type, u.full_name"
    );
} catch (Exception $e) {
    echo '<div class="alert alert-danger">❌ خطأ في جلب المستخدمين: ' . $e->getMessage() . '</div>';
    echo '<div class="alert alert-info">💡 قم بتشغيل <a href="install_complete_database.php">تثبيت قاعدة البيانات</a></div>';
    exit;
}

// جلب بيانات الطلاب (معالجة خطأ الجدول المفقود)
try {
    $students = $db->fetchAll("SELECT user_id, student_id, major, level, gpa FROM students");
    $studentMap = [];
    foreach ($students as $s) {
        $studentMap[$s['user_id']] = $s;
    }
} catch (Exception $e) {
    $studentMap = [];
    $missingTables[] = 'students';
}

// جلب بيانات الأساتذة (معالجة خطأ الجدول المفقود)
try {
    $professors = $db->fetchAll("SELECT user_id, professor_id, department FROM professors");
    $professorMap = [];
    foreach ($professors as $p) {
        $professorMap[$p['user_id']] = $p;
    }
    
    // جلب الكلية لكل أستاذ من جدول professor_courses المرتبط بالمواد
    $professorCourses = $db->fetchAll(
        "SELECT DISTINCT pc.professor_id, c.college 
         FROM professor_courses pc 
         JOIN courses c ON pc.course_id = c.id"
    );
    $professorCollegeMap = [];
    foreach ($professorCourses as $pc) {
        $professorCollegeMap[$pc['professor_id']] = $pc['college'];
    }
} catch (Exception $e) {
    $professorMap = [];
    $professorCollegeMap = [];
    $missingTables[] = 'professors';
}

// جلب بيانات العمداء (معالجة خطأ الجدول المفقود)
try {
    $deans = $db->fetchAll("SELECT d.user_id, d.dean_id, d.college, d.department
                           FROM deans d");
    $deanMap = [];
    foreach ($deans as $d) {
        $deanMap[$d['user_id']] = $d;
    }
} catch (Exception $e) {
    $deanMap = [];
}

// إنشاء حسابات العمداء تلقائياً إذا لم تكن موجودة
try {
    $colleges = $db->fetchAll("SELECT * FROM colleges WHERE status = 'active' ORDER BY college_name");
    $defaultPassword = 'dean123';
    $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
    
    foreach ($colleges as $index => $college) {
        $collegeName = $college['college_name'];
        $deanUsername = 'dean_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $college['college_code'] ?? 'college' . ($index + 1)));
        $deanEmail = $deanUsername . '@learnata.edu';
        $deanFullName = 'عميد ' . $collegeName;
        
        // التحقق من عدم وجود عميد لهذه الكلية
        $existingDean = $db->fetchOne("SELECT id FROM deans WHERE college = ?", [$collegeName]);
        
        if (!$existingDean) {
            try {
                // التحقق من عدم وجود المستخدم مسبقاً
                $existingUser = $db->fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$deanUsername, $deanEmail]);
                
                if ($existingUser) {
                    $userId = $existingUser['id'];
                    // إضافة العميد فقط
                    $db->execute("
                        INSERT INTO deans (user_id, dean_id, college, department, office_location)
                        VALUES (?, ?, ?, ?, ?)
                    ", [$userId, 'DEAN' . date('Y') . str_pad($index + 1, 3, '0', STR_PAD_LEFT), $collegeName, 'قسم ' . $collegeName, 'مبنى الإدارة - الطابق ' . ($index + 1)]);
                } else {
                    // إضافة المستخدم والعميد
                    $userId = $db->insert("
                        INSERT INTO users (username, email, password, full_name, phone, user_type, status)
                        VALUES (?, ?, ?, ?, ?, 'admin', 'active')
                    ", [$deanUsername, $deanEmail, $passwordHash, $deanFullName, '0991000' . ($index + 1)]);
                    
                    $db->execute("
                        INSERT INTO deans (user_id, dean_id, college, department, office_location)
                        VALUES (?, ?, ?, ?, ?)
                    ", [$userId, 'DEAN' . date('Y') . str_pad($index + 1, 3, '0', STR_PAD_LEFT), $collegeName, 'قسم ' . $collegeName, 'مبنى الإدارة - الطابق ' . ($index + 1)]);
                }
                
                // إعادة تحميل بيانات العمداء
                $deans = $db->fetchAll("SELECT d.user_id, d.dean_id, d.college, d.department FROM deans d");
                $deanMap = [];
                foreach ($deans as $d) {
                    $deanMap[$d['user_id']] = $d;
                }
            } catch (Exception $e) {
                // تجاهل أخطاء التكرار
            }
        }
    }
} catch (Exception $e) {
    // تجاهل إذا كان جدول الكليات غير موجود
}

// جلب بيانات سكرتارية العميد (معالجة خطأ الجدول المفقود)
try {
    $secretaries = $db->fetchAll("SELECT user_id, secretary_id, dean_id FROM dean_secretaries");
    $secretaryMap = [];
    foreach ($secretaries as $s) {
        $secretaryMap[$s['user_id']] = $s;
    }
} catch (Exception $e) {
    $secretaryMap = [];
}

// جلب بيانات Committee (معالجة خطأ الجدول المفقود)
try {
    $committee = $db->fetchAll("SELECT user_id, committee_id FROM committee");
    $committeeMap = [];
    foreach ($committee as $c) {
        $committeeMap[$c['user_id']] = $c;
    }
} catch (Exception $e) {
    $committeeMap = [];
}

// جلب بيانات Staff (معالجة خطأ الجدول المفقود)
try {
    $staff = $db->fetchAll("SELECT user_id, staff_id, position FROM staff");
    $staffMap = [];
    foreach ($staff as $s) {
        $staffMap[$s['user_id']] = $s;
    }
} catch (Exception $e) {
    $staffMap = [];
}

// دمج البيانات
$allUsers = [];
foreach ($users as $user) {
    $userId = $user['id'];
    $userData = [
        'id' => $userId,
        'username' => $user['username'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'user_type' => $user['user_type'],
        'status' => $user['status'],
        'password' => $user['password'],
        'created_at' => $user['created_at'],
        'extra_info' => []
    ];
    
    // إضافة معلومات إضافية حسب النوع
    if (isset($studentMap[$userId])) {
        $s = $studentMap[$userId];
        $userData['extra_info'] = [
            'ID' => $s['student_id'],
            'التخصص' => $s['major'],
            'المستوى' => $s['level'],
            'المعدل' => $s['gpa']
        ];
    } elseif (isset($professorMap[$userId])) {
        $p = $professorMap[$userId];
        $college = $professorCollegeMap[$p['professor_id']] ?? 'غير محدد';
        $userData['extra_info'] = [
            'ID' => $p['professor_id'],
            'الكلية' => $college,
            'القسم' => $p['department'] ?? 'غير محدد'
        ];
    } elseif (isset($deanMap[$userId])) {
        $d = $deanMap[$userId];
        $userData['extra_info'] = [
            'Dean ID' => $d['dean_id'],
            'الكلية' => $d['college_name'] ?? $d['college']
        ];
    } elseif (isset($secretaryMap[$userId])) {
        $s = $secretaryMap[$userId];
        $userData['extra_info'] = [
            'Secretary ID' => $s['secretary_id']
        ];
    } elseif (isset($committeeMap[$userId])) {
        $c = $committeeMap[$userId];
        $userData['extra_info'] = [
            'Committee ID' => $c['committee_id']
        ];
    } elseif (isset($staffMap[$userId])) {
        $s = $staffMap[$userId];
        $userData['extra_info'] = [
            'Staff ID' => $s['staff_id'],
            'المنصب' => $s['position']
        ];
    }
    
    $allUsers[] = $userData;
}

// إحصائيات
$stats = [
    'total' => count($allUsers),
    'student' => 0,
    'professor' => 0,
    'admin' => 0,
    'super_admin' => 0,
    'dean' => 0,
    'committee' => 0,
    'staff' => 0,
    'secretary' => 0,
    'other' => 0
];

foreach ($allUsers as $user) {
    $type = $user['user_type'];
    if (isset($stats[$type])) {
        $stats[$type]++;
    } else {
        $stats['other']++;
    }
}

// فصل المستخدمين حسب النوع
$studentsList = array_filter($allUsers, fn($u) => $u['user_type'] === 'student');
$professorsList = array_filter($allUsers, fn($u) => $u['user_type'] === 'professor');
$adminsList = array_filter($allUsers, fn($u) => in_array($u['user_type'], ['admin', 'super_admin']));
$deansList = array_filter($allUsers, fn($u) => isset($deanMap[$u['id']]));
$othersList = array_filter($allUsers, fn($u) => !in_array($u['user_type'], ['student', 'professor', 'admin', 'super_admin']) && !isset($deanMap[$u['id']]));

// تعريف كلمات المرور الافتراضية حسب نوع المستخدم
function getDefaultPassword($userType, $deanMap, $userId) {
    if (isset($deanMap[$userId])) return 'dean123';
    if ($userType === 'student') return 'student123';
    if ($userType === 'admin' || $userType === 'super_admin') return 'password';
    if ($userType === 'professor') return 'professor123';
    return 'password';
}

// تعريف صفحات التوجيه لكل نوع مستخدم
function getRedirectPage($userType, $deanMap, $userId, $email) {
    // التوجيه بناءً على البريد الإلكتروني للمستخدمين الإداريين المحددين
    switch ($email) {
        case 'president@learnata.edu':
            return '/admin/president.php';
        case 'admin@learnata.edu':
            return '/admin/dashboard.php';
        case 'committee@learnata.edu':
            return '/committee/dashboard.php';
        case 'dean_phar@learnata.edu':
        case 'dean_lang@learnata.edu':
        case 'dean_iteng@learnata.edu':
        case 'dean_med@learnata.edu':
        case 'dean_peteng@learnata.edu':
        case 'dean_chemeng@learnata.edu':
        case 'dean_dent@learnata.edu':
        case 'dean_comp@learnata.edu':
            return '/dean/dashboard.php';
    }
    
    // التوجيه الافتراضي بناءً على نوع المستخدم
    switch ($userType) {
        case 'student':
            return '/student/dashboard.php';
        case 'admin':
            return '/admin/dashboard.php';
        case 'professor':
            return '/professor/dashboard.php';
        case 'dean':
            return '/dean/dashboard.php';
        case 'committee':
            return '/committee/dashboard.php';
        default:
            return '/admin/dashboard.php';
    }
}

$loginUrl = SITE_URL . '/student-portal.php';
$adminLoginUrl = SITE_URL . '/admin/login.php';

// ألوان لكل نوع مستخدم
$typeColors = [
    'student' => ['bg' => '#4caf50', 'border' => '#4caf50'],
    'professor' => ['bg' => '#ff9800', 'border' => '#ff9800'],
    'admin' => ['bg' => '#f44336', 'border' => '#f44336'],
    'super_admin' => ['bg' => '#9c27b0', 'border' => '#9c27b0'],
    'dean' => ['bg' => '#2196f3', 'border' => '#2196f3'],
    'committee' => ['bg' => '#795548', 'border' => '#795548'],
    'staff' => ['bg' => '#607d8b', 'border' => '#607d8b'],
    'secretary' => ['bg' => '#009688', 'border' => '#009688'],
    'other' => ['bg' => '#757575', 'border' => '#757575']
];

$typeLabels = [
    'student' => 'طالب',
    'professor' => 'أستاذ',
    'admin' => 'إداري',
    'super_admin' => 'مدير النظام',
    'committee' => 'عضو لجنة',
    'staff' => 'موظف',
    'secretary' => 'سكرتير',
    'dean' => 'عميد',
    'other' => 'آخر'
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">
    <title>جميع المستخدمين - معلومات تسجيل الدخول</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-box:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .user-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-right: 5px solid;
            transition: all 0.3s;
        }
        .user-card:hover {
            transform: translateX(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .section-title {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin: 30px 0 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-right: 5px solid #667eea;
        }
        .badge-custom {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .info-label {
            color: #6c757d;
            font-size: 0.8rem;
            margin-bottom: 4px;
        }
        .info-value {
            font-weight: 600;
            color: #212529;
            font-family: monospace;
        }
        .password-box {
            background: #fff3cd;
            border: 2px dashed #ffc107;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }
        .last-updated {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
        }
        .no-print { 
            @media print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php if (!empty($missingTables)): ?>
        <div class="alert alert-warning mt-3 no-print">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>⚠️ بعض الجداول غير موجودة:</h5>
            <ul class="mb-2">
                <?php foreach ($missingTables as $table): ?>
                <li>جدول <?php echo $table; ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="install_complete_database.php" class="btn btn-warning btn-sm">
                <i class="fas fa-wrench me-2"></i>إصلاح الجداول المفقودة
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Header -->
        <div class="header-card no-print">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-3"><i class="fas fa-users me-2"></i>جميع المستخدمين في النظام</h2>
                    <p class="mb-0">صفحة تجلب البيانات مباشرة من قاعدة البيانات | تحديث تلقائي كل 30 ثانية</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button onclick="window.print()" class="btn btn-light btn-lg">
                        <i class="fas fa-print me-2"></i>طباعة
                    </button>
                    <button onclick="location.reload()" class="btn btn-outline-light btn-lg ms-2">
                        <i class="fas fa-sync me-2"></i>تحديث
                    </button>
                </div>
            </div>
        </div>

        <!-- Login Info -->
        <!-- Login URLs -->
        <div class="row mb-4">
            <!-- رابط الطلاب -->
            <div class="col-md-4">
                <div class="stat-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="mb-2"><i class="fas fa-graduation-cap fa-2x"></i></div>
                    <div class="info-value" style="color: white; font-size: 0.85rem;">
                        <a href="<?php echo $loginUrl; ?>" target="_blank" class="text-white"><?php echo $loginUrl; ?></a>
                    </div>
                    <small>تسجيل دخول الطلاب</small>
                </div>
            </div>
            <!-- رابط الإداريين -->
            <div class="col-md-4">
                <div class="stat-box" style="background: linear-gradient(135deg, #f44336 0%, #e91e63 100%); color: white;">
                    <div class="mb-2"><i class="fas fa-user-shield fa-2x"></i></div>
                    <div class="info-value" style="color: white; font-size: 0.85rem;">
                        <a href="<?php echo $adminLoginUrl; ?>" target="_blank" class="text-white"><?php echo $adminLoginUrl; ?></a>
                    </div>
                    <small>تسجيل دخول الإداريين</small>
                </div>
            </div>
            <!-- اختبار تسجيل دخول -->
            <div class="col-md-4">
                <div class="stat-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <div class="mb-2"><i class="fas fa-flask fa-2x"></i></div>
                    <div class="info-value" style="color: white; font-size: 0.85rem;">
                        <a href="admin/test_professor_login.php" target="_blank" class="text-white">اختبار تسجيل الدخول</a>
                    </div>
                    <small>للتحقق من الأستاذ الجديد</small>
                </div>
            </div>
        </div>

        <!-- حساب لجنة القبول والتسجيل -->
        <div class="alert alert-info no-print mb-4" style="border-right: 4px solid #795548;">
            <h5 class="mb-3"><i class="fas fa-clipboard-check me-2" style="color: #795548;"></i>حساب لجنة القبول والتسجيل</h5>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <strong>البريد الإلكتروني:</strong><br>
                    <span class="text-primary">committee@learnata.edu</span>
                </div>
                <div class="col-md-4 mb-2">
                    <strong>كلمة المرور:</strong><br>
                    <span class="text-danger">password</span>
                </div>
                <div class="col-md-4 mb-2">
                    <strong>رابط الدخول:</strong><br>
                    <a href="<?php echo $adminLoginUrl; ?>" target="_blank"><?php echo $adminLoginUrl; ?></a>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid no-print">
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="text-muted">الإجمالي</div>
            </div>
            <div class="stat-box" style="border-top: 4px solid #4caf50;">
                <div class="stat-number" style="color: #4caf50;"><?php echo $stats['student']; ?></div>
                <div class="text-muted">الطلاب</div>
            </div>
            <div class="stat-box" style="border-top: 4px solid #ff9800;">
                <div class="stat-number" style="color: #ff9800;"><?php echo $stats['professor']; ?></div>
                <div class="text-muted">الأساتذة</div>
            </div>
            <div class="stat-box" style="border-top: 4px solid #f44336;">
                <div class="stat-number" style="color: #f44336;"><?php echo $stats['admin'] + $stats['super_admin']; ?></div>
                <div class="text-muted">الإداريون</div>
            </div>
            <div class="stat-box" style="border-top: 4px solid #2196f3;">
                <div class="stat-number" style="color: #2196f3;"><?php echo count($deansList); ?></div>
                <div class="text-muted">العمداء</div>
            </div>
            <div class="stat-box" style="border-top: 4px solid #795548;">
                <div class="stat-number" style="color: #795548;"><?php echo $stats['committee']; ?></div>
                <div class="text-muted">اللجان</div>
            </div>
            <div class="stat-box" style="border-top: 4px solid #607d8b;">
                <div class="stat-number" style="color: #607d8b;"><?php echo $stats['staff']; ?></div>
                <div class="text-muted">الموظفون</div>
            </div>
        </div>

        <!-- All Users List -->
        <div class="section-title">
            <h4 class="mb-0"><i class="fas fa-list me-2"></i>قائمة جميع المستخدمين (<?php echo count($allUsers); ?>)</h4>
        </div>

        <?php foreach ($allUsers as $user): 
            $color = $typeColors[$user['user_type']] ?? $typeColors['other'];
            $label = $typeLabels[$user['user_type']] ?? $user['user_type'];
        ?>
        <div class="user-card" style="border-right-color: <?php echo $color['border']; ?>">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h5 class="mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <span class="badge-custom" style="background: <?php echo $color['bg']; ?>; color: white;">
                        <i class="fas fa-user me-1"></i><?php echo $label; ?>
                    </span>
                    <?php if ($user['status'] !== 'active'): ?>
                        <span class="badge bg-secondary">غير نشط</span>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-user me-1"></i>اسم المستخدم</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-envelope me-1"></i>البريد</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <?php foreach ($user['extra_info'] as $key => $value): ?>
                            <?php if ($value): ?>
                            <div class="info-item">
                                <div class="info-label"><?php echo $key; ?></div>
                                <div class="info-value"><?php echo htmlspecialchars($value); ?></div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="password-box" style="background: #d4edda; border: 2px dashed #28a745;">
                        <div class="text-muted mb-1">كلمة المرور المشفرة (من قاعدة البيانات)</div>
                        <div class="info-value" style="color: #155724; font-size: 0.7rem; font-weight: bold; word-break: break-all;">
                            <?php 
                            echo htmlspecialchars($user['password']); 
                            ?>
                        </div>
                        <small class="text-muted" style="font-size: 10px;">🔒 كلمة المرور مشفرة ولا يمكن فك تشفيرها</small>
                    </div>
                    <div class="password-box mt-2" style="background: #fff3cd; border: 2px dashed #ffc107;">
                        <div class="text-muted mb-1">كلمة المرور الافتراضية</div>
                        <div class="info-value" style="color: #856404; font-size: 1rem; font-weight: bold; word-break: break-all;">
                            <?php 
                            $plainPassword = getDefaultPassword($user['user_type'], $deanMap, $user['id']);
                            echo htmlspecialchars($plainPassword); 
                            ?>
                        </div>
                        <small class="text-muted" style="font-size: 10px;">⚠️ قد لا تكون دقيقة إذا تم تغييرها</small>
                    </div>
                    <div class="password-box mt-2" style="background: #e2e3e5; border: 2px dashed #6c757d;">
                        <div class="text-muted mb-1">صفحة التوجيه</div>
                        <div class="info-value" style="color: #495057; font-size: 0.85rem; font-weight: bold; word-break: break-all;">
                            <?php 
                            $redirectPage = getRedirectPage($user['user_type'], $deanMap, $user['id'], $user['email']);
                            echo htmlspecialchars($redirectPage); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Footer -->
        <div class="text-center mt-5 mb-3 text-muted no-print">
            <hr>
            <p>تم إنشاء هذا التقرير في: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>صفحة تعرض البيانات مباشرة من قاعدة البيانات</p>
        </div>
    </div>

    <div class="last-updated no-print">
        <i class="fas fa-clock me-2"></i>آخر تحديث: <?php echo date('H:i:s'); ?>
    </div>
</body>
</html>
