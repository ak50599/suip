<?php
// logout.php - تسجيل الخروج

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// تحديد صفحة إعادة التوجيه بناءً على نوع المستخدم **قبل** تسجيل الخروج
$redirectUrl = SITE_URL . '/index.php'; // الافتراضي: صفحة تسجيل دخول الطلاب

// حفظ نوع المستخدم في متغير قبل مسح الجلسة
$userType = $_SESSION['user_type'] ?? '';
$deanId = $_SESSION['dean_id'] ?? null;

// المستخدمون الذين يستخدمون admin/login.php (الأدمن، الأستاذ، لجنة القبول)
if (in_array($userType, ['admin', 'professor', 'committee'])) {
    $redirectUrl = SITE_URL . '/admin/login.php';
}

// العمداء والسكرتارية لديهم جلسة خاصة
if ($deanId) {
    $redirectUrl = SITE_URL . '/admin/login.php';
}

// تسجيل الخروج (سيقوم بمسح الجلسة)
$auth->logout();

// إعادة التوجيه إلى صفحة تسجيل الدخول المناسبة
setFlashMessage('تم تسجيل الخروج بنجاح', 'success');
redirect($redirectUrl);
