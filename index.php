<?php
// index.php - الصفحة الرئيسية للموقع

require_once './includes/config.php';
require_once './includes/db.php';
require_once './includes/functions.php';
require_once './includes/lang.php';

// التعامل مع تبديل اللغة
$currentLang = $_SESSION['lang'] ?? 'ar';
if (isset($_GET['lang'])) {
    $newLang = $_GET['lang'] === 'en' ? 'en' : 'ar';
    $_SESSION['lang'] = $newLang;
    $currentLang = $newLang;
}

// تحديد اتجاه الصفحة حسب اللغة
$dir = $currentLang === 'ar' ? 'rtl' : 'ltr';

$db = getDB();

// جلب بيانات الكليات
try {
    $colleges = $db->fetchAll("SELECT DISTINCT * FROM colleges WHERE status = 'active' ORDER BY college_name");
} catch (Exception $e) {
    $colleges = [];
}

// جلب عدد المواد لكل كلية
try {
    $coursesCount = $db->fetchAll(
        "SELECT college, COUNT(*) as count FROM courses WHERE college IS NOT NULL GROUP BY college"
    );
    $coursesCountMap = [];
    foreach ($coursesCount as $c) {
        $coursesCountMap[$c['college']] = $c['count'];
    }
} catch (Exception $e) {
    $coursesCountMap = [];
}

// جلب عدد الساعات لكل كلية (افتراضياً 120 ساعة للكلية)
$creditHours = [];
foreach ($colleges as $college) {
    $creditHours[$college['id']] = 120; // قيمة افتراضية
}

// جلب الإعلانات المنشورة
try {
    $announcements = $db->fetchAll(
        "SELECT * FROM announcements 
         WHERE status = 'published' 
         AND (start_date IS NULL OR start_date <= CURDATE())
         AND (end_date IS NULL OR end_date >= CURDATE())
         ORDER BY created_at DESC 
         LIMIT 5"
    );
} catch (Exception $e) {
    $announcements = [];
}

// بيانات الخدمات
$services = [
    [
        'icon' => 'fa-bus',
        'title' => t('transportation_service'),
        'description' => t('transportation_desc')
    ],
    [
        'icon' => 'fa-home',
        'title' => t('housing_service'),
        'description' => t('housing_desc')
    ]
];

// بيانات التواصل
$contactInfo = [
    'admission' => [
        'title' => t('admission_committee'),
        'phone' => '0981-123-456',
        'email' => 'admission@university.edu',
        'hours' => $currentLang === 'ar' ? 'الأحد - الخميس: 9:00 ص - 3:00 م' : 'Sunday - Thursday: 9:00 AM - 3:00 PM'
    ],
    'support' => [
        'title' => t('consultations'),
        'phone' => '0981-789-012',
        'email' => 'support@university.edu',
        'hours' => $currentLang === 'ar' ? 'الأحد - الخميس: 9:00 ص - 5:00 م' : 'Sunday - Thursday: 9:00 AM - 5:00 PM'
    ]
];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5.3.2 RTL -->
    <?php if ($currentLang === 'ar'): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <?php endif; ?>
    <!-- FontAwesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts - Tajawal -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d3b66;
            --secondary-color: #0a2540;
            --accent-color: #1e5f8e;
            --gold-color: #c9a227;
            --light-bg: #f8f9fa;
            --glass-bg: rgba(255, 255, 255, 0.95);
        }
        
        * {
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
        }
        
        /* Navbar Styling */
        .main-navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 0.8rem 0;
            box-shadow: 0 4px 20px rgba(13, 59, 102, 0.3);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .navbar-brand i {
            color: var(--gold-color);
            margin-left: 0.5rem;
        }
        
        .student-portal-btn {
            background: linear-gradient(135deg, var(--gold-color) 0%, #d4af37 100%);
            color: var(--secondary-color) !important;
            font-weight: 600;
            padding: 0.6rem 1.5rem !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            border: none;
        }
        
        .student-portal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(201, 162, 39, 0.4);
            color: var(--primary-color) !important;
        }
        
        .student-portal-btn i {
            margin-left: 0.5rem;
        }
        
        .admin-portal-btn {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white !important;
            font-weight: 600;
            padding: 0.6rem 1.5rem !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            border: none;
        }
        
        .admin-portal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
            color: white !important;
        }
        
        .admin-portal-btn i {
            margin-left: 0.5rem;
        }
        
        /* Nav Menu Items */
        .nav-menu-center {
            display: flex;
            gap: 0.5rem;
        }
        
        .nav-menu-item {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.6rem 1.2rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-menu-item:hover {
            color: white !important;
            background: rgba(255,255,255,0.1);
        }
        
        .nav-menu-item i {
            margin-left: 0.4rem;
            color: var(--gold-color);
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 50%, var(--accent-color) 100%);
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="0.5"/></svg>');
            background-size: 100px 100px;
            opacity: 0.5;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            color: white;
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .hero-subtitle {
            color: rgba(255,255,255,0.85);
            font-size: 1.2rem;
            font-weight: 400;
        }
        
        /* Section Cards */
        .section-card {
            background: var(--glass-bg);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(13, 59, 102, 0.1);
            border: 1px solid rgba(255,255,255,0.5);
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(13, 59, 102, 0.15);
        }
        
        .section-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .section-icon i {
            font-size: 2rem;
            color: white;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        /* College Cards */
        .college-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(13, 59, 102, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(13, 59, 102, 0.08);
        }
        
        .college-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px rgba(13, 59, 102, 0.2);
        }
        
        .college-card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            padding: 2rem 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .college-card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .college-icon {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            position: relative;
            z-index: 1;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .college-icon i {
            font-size: 2rem;
            color: white;
        }
        
        .college-name {
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .college-code {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }
        
        .college-card-body {
            padding: 1.5rem;
        }
        
        .college-stats {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px dashed #eee;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-color);
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #888;
            margin-top: 0.3rem;
        }
        
        .stat-divider {
            width: 1px;
            height: 40px;
            background: linear-gradient(to bottom, transparent, #ddd, transparent);
        }
        
        .college-desc {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
            text-align: center;
            margin: 0;
        }
        
        .college-card-footer {
            background: #f8f9fa;
            padding: 1rem;
            text-align: center;
            border-top: 1px solid #eee;
        }
        
        .college-status {
            color: #28a745;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .college-status i {
            margin-left: 0.3rem;
        }
        
        /* Colleges Table */
        .college-table {
            width: 100%;
        }
        
        .college-table th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 1rem;
            font-weight: 600;
        }
        
        .college-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .college-table tr:hover td {
            background: #f8f9fa;
        }
        
        .college-badge {
            background: linear-gradient(135deg, var(--gold-color) 0%, #d4af37 100%);
            color: var(--secondary-color);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        /* Services Cards */
        .service-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(13, 59, 102, 0.15);
        }
        
        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .service-icon i {
            font-size: 2.5rem;
            color: white;
        }
        
        .service-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }
        
        .service-desc {
            color: #666;
            line-height: 1.7;
        }
        
        /* Contact Cards */
        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            border-right: 4px solid var(--primary-color);
        }
        
        .contact-card:hover {
            transform: translateX(-5px);
            box-shadow: 0 10px 30px rgba(13, 59, 102, 0.12);
        }
        
        .contact-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
        }
        
        .contact-title i {
            color: var(--gold-color);
            margin-left: 0.5rem;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: #555;
        }
        
        .contact-item i {
            width: 35px;
            height: 35px;
            background: var(--light-bg);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 0.8rem;
            color: var(--primary-color);
        }
        
        /* Announcements */
        .announcement-item {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
            border-right: 3px solid var(--gold-color);
            transition: all 0.3s ease;
        }
        
        .announcement-item:hover {
            transform: translateX(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .announcement-date {
            display: inline-block;
            background: var(--light-bg);
            color: var(--primary-color);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.8rem;
        }
        
        .announcement-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 0.8rem;
        }
        
        .announcement-content {
            color: #555;
            line-height: 1.7;
        }
        
        /* Section Headers */
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-header h2 {
            color: var(--primary-color);
            font-weight: 800;
            font-size: 2rem;
            position: relative;
            display: inline-block;
        }
        
        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, var(--gold-color) 0%, #d4af37 100%);
            border-radius: 2px;
        }
        
        /* Footer */
        .main-footer {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 5rem;
        }
        
        .footer-brand {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .footer-brand i {
            color: var(--gold-color);
            margin-left: 0.5rem;
        }
        
        .footer-desc {
            color: rgba(255,255,255,0.7);
            line-height: 1.8;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 2rem;
            padding-top: 1.5rem;
            text-align: center;
            color: rgba(255,255,255,0.6);
        }
        
        /* Stats Counter */
        .stat-box {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        /* Language Toggle Button */
        .lang-toggle-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(251, 191, 36, 0.5);
            border-radius: 25px;
            padding: 8px 20px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 10px;
        }
        
        .lang-toggle-btn:hover {
            background: rgba(251, 191, 36, 0.3);
            border-color: #fbbf24;
            transform: scale(1.05);
        }
        
        .lang-toggle-btn i {
            margin-right: 5px;
        }
        
        .stat-box:hover {
            transform: scale(1.05);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-weight: 500;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--gold-color) 0%, #d4af37 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .stat-icon i {
            font-size: 1.5rem;
            color: var(--secondary-color);
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .nav-menu-center {
                flex-direction: column;
                gap: 0.3rem;
            }
            
            .navbar-brand {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg main-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-university"></i>
                <?php echo SITE_SHORT_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Center Menu -->
                <ul class="navbar-nav mx-auto nav-menu-center">
                    <li class="nav-item">
                        <a class="nav-link nav-menu-item" href="#university-info">
                            <i class="fas fa-info-circle"></i> <?= t('menu_university_info') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-menu-item" href="#services">
                            <i class="fas fa-cogs"></i> <?= t('menu_services') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-menu-item" href="#contact">
                            <i class="fas fa-phone-alt"></i> <?= t('menu_contact') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-menu-item" href="#announcements">
                            <i class="fas fa-bullhorn"></i> <?= t('menu_announcements') ?>
                        </a>
                    </li>
                </ul>
                
                <!-- Language Toggle Button -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link lang-toggle-btn" href="?lang=<?= $currentLang === 'ar' ? 'en' : 'ar' ?>">
                            <i class="fas fa-globe"></i> <?= t('menu_language') ?>
                        </a>
                    </li>
                </ul>
                
                <!-- Student Portal Button -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link student-portal-btn" href="student-portal.php">
                            <i class="fas fa-user-graduate"></i> <?= t('menu_student_portal') ?>
                        </a>
                    </li>
                </ul>
                
                <!-- Admin Portal Button -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link admin-portal-btn" href="admin/login.php">
                            <i class="fas fa-user-shield"></i> بوابة الإداريين
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-8 text-center text-lg-start">
                    <h1 class="hero-title"><?= t('university_name') ?></h1>
                    <p class="hero-subtitle"><?= t('university_subtitle') ?></p>
                </div>
                <div class="col-lg-4 text-center mt-4 mt-lg-0">
                    <!-- Stats -->
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="stat-number"><?php echo count($colleges); ?></div>
                                <div class="stat-label"><?= t('colleges_count') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="stat-number"><?php echo array_sum($coursesCountMap); ?></div>
                                <div class="stat-label"><?= t('courses_count') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- University Info Section -->
    <section id="university-info" class="py-5">
        <div class="container">
            <div class="section-header">
                <h2><i class="fas fa-info-circle" style="color: var(--gold-color);"></i> معلومات عن الجامعة</h2>
            </div>
            
            <div class="section-card">
                <h3 class="section-title text-center mb-4">
                    <i class="fas fa-building" style="color: var(--gold-color);"></i> الكليات والتخصصات
                </h3>
                
                <?php if (empty($colleges)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted">لا توجد كليات متاحة حالياً</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($colleges as $college): 
                            $courseCount = $coursesCountMap[$college['college_name']] ?? 0;
                            $hours = ($courseCount > 0) ? ($courseCount * 3) : 120; // 3 ساعات لكل مادة
                            $icons = ['fa-user-md', 'fa-tooth', 'fa-pills', 'fa-laptop-code', 'fa-oil-can', 'fa-flask', 'fa-desktop', 'fa-language'];
                            $iconIndex = ($college['id'] - 1) % count($icons);
                            $collegeIcon = $icons[$iconIndex];
                        ?>
                            <div class="col-md-6 col-lg-4 col-xl-3">
                                <div class="college-card h-100">
                                    <div class="college-card-header">
                                        <div class="college-icon">
                                            <i class="fas <?php echo $collegeIcon; ?>"></i>
                                        </div>
                                        <h5 class="college-name"><?= getCollegeName($college['college_code'], $currentLang) ?></h5>
                                        <span class="college-code"><?php echo $college['college_code']; ?></span>
                                    </div>
                                    <div class="college-card-body">
                                        <div class="college-stats">
                                            <div class="stat-item">
                                                <div class="stat-value"><?php echo $courseCount; ?></div>
                                                <div class="stat-label">مادة</div>
                                            </div>
                                            <div class="stat-divider"></div>
                                            <div class="stat-item">
                                                <div class="stat-value"><?php echo $hours; ?></div>
                                                <div class="stat-label">ساعة</div>
                                            </div>
                                        </div>
                                        <?php if (!empty($college['description'])): ?>
                                            <p class="college-desc"><?php echo $college['description']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="college-card-footer">
                                        <span class="college-status">
                                            <i class="fas fa-check-circle"></i> مفتوحة للقبول
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Services Section -->
    <section id="services" class="py-5" style="background: var(--light-bg);">
        <div class="container">
            <div class="section-header">
                <h2><i class="fas fa-cogs" style="color: var(--gold-color);"></i> خدماتنا</h2>
            </div>
            
            <div class="row g-4">
                <?php foreach ($services as $service): ?>
                    <div class="col-md-6">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas <?php echo $service['icon']; ?>"></i>
                            </div>
                            <h4 class="service-title"><?php echo $service['title']; ?></h4>
                            <p class="service-desc"><?php echo $service['description']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="section-header">
                <h2><i class="fas fa-phone-alt" style="color: var(--gold-color);"></i> <?= t('contact_us') ?></h2>
            </div>
            
            <div class="row g-4">
                <?php foreach ($contactInfo as $key => $contact): ?>
                    <div class="col-md-6">
                        <div class="contact-card">
                            <h4 class="contact-title">
                                <i class="fas <?php echo $key === 'admission' ? 'fa-user-tie' : 'fa-headset'; ?>"></i>
                                <?php echo $contact['title']; ?>
                            </h4>
                            
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo $contact['phone']; ?></span>
                            </div>
                            
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo $contact['email']; ?></span>
                            </div>
                            
                            <div class="contact-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo $contact['hours']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Announcements Section -->
    <section id="announcements" class="py-5" style="background: var(--light-bg);">
        <div class="container">
            <div class="section-header">
                <h2><i class="fas fa-bullhorn" style="color: var(--gold-color);"></i> <?= t('menu_announcements') ?></h2>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <?php if (empty($announcements)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bullhorn text-muted mb-3" style="font-size: 3rem;"></i>
                            <p class="text-muted"><?= t('no_announcements') ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($announcements as $ann): ?>
                            <div class="announcement-item">
                                <span class="announcement-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?php echo date('Y-m-d', strtotime($ann['created_at'])); ?>
                                    <?php if ($ann['start_date'] || $ann['end_date']): ?>
                                        (من <?php echo $ann['start_date'] ?: '...'; ?> 
                                        إلى <?php echo $ann['end_date'] ?: '...'; ?>)
                                    <?php endif; ?>
                                </span>
                                <h4 class="announcement-title"><?php echo $ann['title']; ?></h4>
                                <p class="announcement-content"><?php echo nl2br($ann['content']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="footer-brand">
                        <i class="fas fa-university"></i>
                        <?php echo SITE_SHORT_NAME; ?>
                    </div>
                    <p class="footer-desc">
                        <?= t('footer_desc') ?>
                    </p>
                </div>
                <div class="col-lg-6 text-lg-start">
                    <h5 style="color: var(--gold-color); margin-bottom: 1rem;"><?= t('quick_links') ?></h5>
                    <ul class="list-unstyled" style="line-height: 2;">
                        <li><a href="student-portal.php" style="color: rgba(255,255,255,0.7); text-decoration: none;"><i class="fas fa-user-graduate me-2"></i><?= t('menu_student_portal') ?></a></li>
                        <li><a href="admission/apply.php" style="color: rgba(255,255,255,0.7); text-decoration: none;"><i class="fas fa-user-plus me-2"></i><?= t('register_admission') ?></a></li>
                        <li><a href="#university-info" style="color: rgba(255,255,255,0.7); text-decoration: none;"><i class="fas fa-info-circle me-2"></i><?= t('menu_university_info') ?></a></li>
                        <li><a href="#contact" style="color: rgba(255,255,255,0.7); text-decoration: none;"><i class="fas fa-phone-alt me-2"></i><?= t('contact_us') ?></a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. <?= t('all_rights_reserved') ?></p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Smooth Scroll -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
</body>
</html>
