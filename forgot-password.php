<?php
// forgot-password.php - صفحة استعادة كلمة المرور

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    redirectByUserType();
}

// دعم تعدد اللغات
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'ar';
if (!in_array($lang, ['ar', 'en'])) $lang = 'ar';
$_SESSION['lang'] = $lang;

$trans = [
    'ar' => [
        'title' => 'استعادة كلمة المرور',
        'forgot_title' => 'نسيت كلمة المرور؟',
        'subtitle' => 'لا تقلق! أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور',
        'email' => 'البريد الإلكتروني',
        'send_link' => 'إرسال رابط التعيين',
        'back_to_login' => 'العودة إلى تسجيل الدخول',
        'error_security' => 'خطأ في التحقق من الأمان.',
        'error_email' => 'يرجى إدخال بريد إلكتروني صحيح',
        'success_message' => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني',
        'footer' => 'جميع الحقوق محفوظة',
    ],
    'en' => [
        'title' => 'Forgot Password',
        'forgot_title' => 'Forgot Password?',
        'subtitle' => "Don't worry! Enter your email and we'll send you a link to reset your password",
        'email' => 'Email Address',
        'send_link' => 'Send Reset Link',
        'back_to_login' => 'Back to Login',
        'error_security' => 'Security verification error.',
        'error_email' => 'Please enter a valid email',
        'success_message' => 'Password reset link has been sent to your email',
        'footer' => 'All rights reserved',
    ]
];
$t = $trans[$lang];
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = $t['error_security'];
    } else {
        $email = cleanInput($_POST['email'] ?? '');
        
        if (empty($email) || !isValidEmail($email)) {
            $error = $t['error_email'];
        } else {
            $result = $auth->resetPassword($email);
            
            if ($result['success']) {
                $message = $t['success_message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?> - <?php echo SITE_SHORT_NAME; ?></title>
    
    <?php if ($dir === 'rtl'): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <?php endif; ?>
    <link rel="stylesheet" href="./assets/fonts/fonts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global-modern.css?v=1">
</head>
<body style="background: linear-gradient(-45deg, #0d3b66, #0a2540, #1a3a5c, #0a1f35); background-size: 400% 400%; animation: gradientBG 15s ease infinite; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Tajawal', sans-serif; position: relative; overflow-x: hidden;">
    
    <style>
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 200, 255, 0.1) 0%, transparent 40%);
            z-index: 0;
        }
        .forgot-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 50px 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.15);
            width: 100%;
            max-width: 450px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .forgot-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: white;
            font-size: 2rem;
        }
        .forgot-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .forgot-subtitle {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 30px;
            font-size: 1rem;
        }
        .forgot-form .form-control {
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .forgot-form .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        .forgot-form .form-control:focus {
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .forgot-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .forgot-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .back-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            color: #ffffff;
            text-decoration: underline;
        }
        .alert-custom {
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
        }
        .alert-success-custom {
            background: rgba(198, 246, 213, 0.9);
            color: #22543d;
        }
        .alert-danger-custom {
            background: rgba(254, 215, 215, 0.9);
            color: #742a2a;
        }
    </style>
    
    <!-- Language Toggle Button -->
    <?php
    $otherLang = $lang === 'ar' ? 'en' : 'ar';
    $otherLangText = $lang === 'ar' ? 'English' : 'العربية';
    ?>
    <div style="position: fixed; top: 20px; <?php echo $dir === 'rtl' ? 'left' : 'right'; ?>: 20px; z-index: 9999;">
        <a href="?lang=<?php echo $otherLang; ?>" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: #fff; padding: 10px 20px; border-radius: 25px; text-decoration: none; border: 1px solid rgba(255,255,255,0.3); font-weight: 500; transition: all 0.3s ease; display: inline-block;">
            <i class="fas fa-globe me-2"></i><?php echo $otherLangText; ?>
        </a>
    </div>
    
    <div class="forgot-card">
            <div class="forgot-logo">
                <i class="fas fa-key"></i>
            </div>
            
            <h1 class="forgot-title"><?php echo $t['forgot_title']; ?></h1>
            <p class="forgot-subtitle"><?php echo $t['subtitle']; ?></p>
            
            <?php if ($message): ?>
                <div class="alert-custom alert-success-custom" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert-custom alert-danger-custom" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form class="forgot-form" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                
                <div class="mb-3">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="<?php echo $t['email']; ?>" required>
                </div>
                
                <button type="submit" class="forgot-btn">
                    <i class="fas fa-paper-plane me-2"></i>
                    <?php echo $t['send_link']; ?>
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <a href="index.php<?php echo $lang === 'en' ? '?lang=en' : ''; ?>" class="back-link text-decoration-none">
                    <i class="fas fa-<?php echo $dir === 'rtl' ? 'arrow-right' : 'arrow-left'; ?> me-1"></i>
                    <?php echo $t['back_to_login']; ?>
                </a>
            </div>
        
        <p style="margin-top: 30px; color: rgba(255, 255, 255, 0.6); font-size: 0.85rem;">
            <?php echo $t['footer']; ?> &copy; <?php echo date('Y'); ?> M.Hamza AlNawaf
        </p>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
