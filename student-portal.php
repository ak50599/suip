<?php
// index.php - الصفحة الرئيسية - تسجيل دخول الطالب والمفاضلة

require_once './includes/config.php';
require_once './includes/db.php';
require_once './includes/functions.php';
require_once './includes/RateLimiter.php';

$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'ar';
if (!in_array($lang, ['ar', 'en'])) $lang = 'ar';
$_SESSION['lang'] = $lang;

$trans = [
    'ar' => [
        'title' => 'بوابة الطالب والمفاضلة - تسجيل الدخول',
        'student_login' => 'تسجيل دخول',
        'email' => 'البريد الإلكتروني',
        'student_id' => 'الرقم الجامعي',
        'password' => 'كلمة المرور',
        'remember_me' => 'تذكرني',
        'forgot_password' => 'نسيت كلمة المرور؟',
        'login' => 'تسجيل دخول',
        'invalid' => 'البريد الإلكتروني أو الرقم الجامعي أو كلمة المرور غير صحيحة',
        'enter' => 'يرجى إدخال البريد الإلكتروني أو الرقم الجامعي وكلمة المرور',
        'not_registered' => 'أنت مقبول في المفاضلة ولكن لم يتم إنشاء حسابك بعد. يرجى مراجعة إدارة القبول.',
        'rate_limited' => 'تم تجاوز الحد المسموح من المحاولات. يرجى المحاولة لاحقاً.',
    ],
    'en' => [
        'title' => 'Student & Admission Portal - Login',
        'student_login' => 'Login',
        'email' => 'Email Address',
        'student_id' => 'Student ID',
        'password' => 'Password',
        'remember_me' => 'Remember Me',
        'forgot_password' => 'Forgot Password?',
        'login' => 'Login',
        'invalid' => 'Invalid email, student ID, or password',
        'enter' => 'Please enter email or student ID and password',
        'not_registered' => 'You are accepted in the admission but your account has not been created yet. Please contact the admission office.',
        'rate_limited' => 'Too many failed attempts. Please try again later.',
    ]
];
$t = $trans[$lang];                                                                 

$error = '';

// Rate Limiting check
$rateLimiter = new RateLimiter();
$isAllowed = $rateLimiter->isAllowed();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // التحقق من Rate Limiting
    if (!$isAllowed) {
        $error = $t['rate_limited'];
    } elseif (empty($password) || empty($identifier)) {
        $error = $t['enter'];
    } else {
        $db = getDB();
        
        // Check if the identifier belongs to a non-student user
        if (strpos($identifier, '@') !== false) {
            $nonStudentUser = $db->fetchOne(
                "SELECT u.user_type, u.full_name FROM users u WHERE u.email = ? AND u.user_type != 'student' AND u.status = 'active'",
                [$identifier]
            );
        } else {
            $nonStudentUser = $db->fetchOne(
                "SELECT u.user_type, u.full_name FROM users u JOIN students s ON u.id = s.user_id WHERE s.student_id = ? AND u.user_type != 'student' AND u.status = 'active'",
                [$identifier]
            );
        }

        if ($nonStudentUser) {
            $error = $lang === 'ar' 
                ? "هذه الصفحة مخصصة للطلاب فقط. المستخدم '{$nonStudentUser['full_name']}' هو من نوع '{$nonStudentUser['user_type']}'." 
                : "This page is for students only. User '{$nonStudentUser['full_name']}' is of type '{$nonStudentUser['user_type']}'.";
        }
        
        if (strpos($identifier, '@') !== false) {
            $user = $db->fetchOne(
                "SELECT u.*, s.student_id, s.id as student_table_id FROM users u JOIN students s ON u.id = s.user_id WHERE u.email = ? AND u.user_type = 'student' AND u.status = 'active'",
                [$identifier]
            );
        } else {
            $user = $db->fetchOne(
                "SELECT u.*, s.student_id, s.id as student_table_id FROM users u JOIN students s ON u.id = s.user_id WHERE s.student_id = ? AND u.user_type = 'student' AND u.status = 'active'",
                [$identifier]
            );
        }
        if ($user && password_verify($password, $user['password'])) {
            // تسجيل محاولة ناجحة
            $rateLimiter->recordAttempt(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['student_id'] = $user['student_table_id'];
            $_SESSION['student_number'] = $user['student_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['logged_in'] = true;
            $db->execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
            logActivity($user['id'], 'student_login', 'Student login from admission portal');
            redirect(SITE_URL . '/student/dashboard.php');
        } else {
            // تسجيل محاولة فاشلة
            $rateLimiter->recordAttempt(false);
            
            if (strpos($identifier, '@') !== false) {
                $application = $db->fetchOne(
                    "SELECT aa.* FROM admission_applications aa
                     WHERE aa.email = ? AND aa.status = 'accepted'
                     ORDER BY aa.id DESC LIMIT 1",
                    [$identifier]
                );
                
                if ($application) {
                    $error = $t['not_registered'];
                } else {
                    $error = $t['invalid'];
                }
            } else {
                $error = $t['invalid'];
            }
        }
    }
}

$otherLang = $lang === 'ar' ? 'en' : 'ar';
$otherLangText = $lang === 'ar' ? 'English' : 'العربية';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
$align = $lang === 'ar' ? 'right' : 'left';
$margin = $lang === 'ar' ? 'left' : 'right';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?> - <?php echo SITE_SHORT_NAME; ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="./assets/fonts/fonts.css">
    <link rel="stylesheet" href="./assets/css/index.css">
    <link rel="stylesheet" href="./assets/css/global-modern.css?v=1">
    <style>
        :root {
            --font-family: '<?php echo $lang === "ar" ? "Tajawal" : "Poppins"; ?>';
            --margin-pos: <?php echo $margin === 'left' ? '20px' : 'auto'; ?>;
            --margin-pos-alt: <?php echo $margin === 'right' ? '20px' : 'auto'; ?>;
            --align-start: <?php echo $align === 'right' ? '0' : 'auto'; ?>;
            --align-end: <?php echo $align === 'left' ? '0' : 'auto'; ?>;
            --margin-dir: 8px;
        }
        /* تنسيقات شفافة لصندوق تسجيل الدخول */
        .glass-card {
            background: rgba(255, 255, 255, 0.08) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3) !important;
        }
        .glass-card .login-title {
            color: #ffffff !important;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .glass-card .form-group {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 5px 15px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .glass-card .form-group:focus-within {
            border-color: rgba(255, 255, 255, 0.5);
        }
        /* RTL - العربي */
        [dir="rtl"] .glass-card .input-field {
            padding: 10px 35px 10px 10px;
        }
        /* LTR - الإنجليزي */
        [dir="ltr"] .glass-card .input-field {
            padding: 10px 10px 10px 35px;
        }
        .glass-card .input-field {
            background: transparent !important;
            border: none !important;
            color: #ffffff !important;
            height: 50px;
            -webkit-box-shadow: 0 0 0 1000px transparent inset !important;
            box-shadow: 0 0 0 1000px transparent inset !important;
            -webkit-text-fill-color: #ffffff !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        .glass-card .input-field:-webkit-autofill,
        .glass-card .input-field:-webkit-autofill:hover,
        .glass-card .input-field:-webkit-autofill:focus,
        .glass-card .input-field:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px transparent inset !important;
            box-shadow: 0 0 0 1000px transparent inset !important;
            -webkit-text-fill-color: #ffffff !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        .glass-card .input-field::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        .glass-card .input-field:focus {
            background: transparent !important;
            outline: none !important;
        }
        /* موضع الأيقونة RTL */
        [dir="rtl"] .glass-card .input-icon {
            right: 15px !important;
            left: auto !important;
        }
        /* موضع الأيقونة LTR */
        [dir="ltr"] .glass-card .input-icon {
            left: 15px !important;
            right: auto !important;
        }
        .glass-card .input-icon {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        .glass-card .form-options span,
        .glass-card .remember-check span {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        .glass-card .forgot-link {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        .glass-card .forgot-link:hover {
            color: #ffffff !important;
        }
        .glass-card .alert {
            background: rgba(254, 215, 215, 0.9) !important;
        }
        .copyright {
            color: rgba(255, 255, 255, 0.6) !important;
        }
    </style>
</head>
<body>
    <div class="lang-toggle-wrapper">
        <a href="?lang=<?php echo $otherLang; ?>" class="lang-btn-modern">
            <i class="fas fa-globe"></i>
            <span><?php echo $otherLangText; ?></span>
        </a>
        <a href="./admission/apply.php" class="lang-btn-modern" style="background: rgba(102, 126, 234, 0.6);">
            <i class="fas fa-user-plus"></i>
            <span><?php echo $lang === 'ar' ? 'التسجيل على المفاضلة' : 'Apply for Admission'; ?></span>
        </a>
    </div>
    
    <div class="login-wrapper" style="position: relative; z-index: 1;">
        <div class="glass-card glass-card-hover">
            <h1 class="login-title"><?php echo $t['student_login']; ?></h1>
            
            <?php if ($error): ?>
                <div class="alert">
                    <i class="fas fa-exclamation-circle"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <span class="input-icon"><i class="fas fa-user"></i></span>
                    <input type="text" class="input-field" name="identifier" placeholder="<?php echo $lang === 'ar' ? 'البريد الإلكتروني أو الرقم الجامعي' : 'Email or Student ID'; ?>" required>
                </div>
                
                <div class="form-group">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" class="input-field" id="password" name="password" placeholder="<?php echo $t['password']; ?>" required>
                    <button type="button" class="password-toggle-field" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                
                <div class="form-options">
                    <label class="remember-check">
                        <input type="checkbox" name="remember">
                        <span><?php echo $t['remember_me']; ?></span>
                    </label>
                    <a href="./forgot-password.php" class="forgot-link"><?php echo $t['forgot_password']; ?></a>
                </div>
                
                <button type="submit" class="btn-modern btn-primary-modern" style="width: 100%; justify-content: center;">
                    <i class="fas fa-sign-in-alt"></i>
                    <?php echo $t['login']; ?>
                </button>
            </form>
        </div>
    </div>
    
    <div class="copyright">
        © 2026 M.Hamza AlNawaf
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
