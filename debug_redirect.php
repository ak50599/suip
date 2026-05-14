<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

echo "<h2>اختبار تسجيل الدخول والتوجيه</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p>المعرف: " . htmlspecialchars($identifier) . "</p>";
    
    $auth = new Auth();
    $result = $auth->login($identifier, $password);
    
    echo "<hr><h3>نتيجة تسجيل الدخول:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "<h3>معلومات الجلسة:</h3>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        // تحديد التوجيه
        $userType = $_SESSION['user_type'] ?? '';
        $role = $_SESSION['admin_role'] ?? '';
        
        echo "<h3>تحديد التوجيه:</h3>";
        echo "<p>user_type: $userType</p>";
        echo "<p>admin_role: $role</p>";
        echo "<p>committee_id: " . ($_SESSION['committee_id'] ?? 'غير موجود') . "</p>";
        echo "<p>committee_user: " . (isset($_SESSION['committee_user']) ? 'نعم' : 'لا') . "</p>";
        
        // منطق التوجيه
        if (isset($_SESSION['committee_id']) && !empty($_SESSION['committee_id'])) {
            echo "<p style='color:green'><b>سيتم التوجيه إلى: committee/dashboard.php</b> (بسبب committee_id)</p>";
        } elseif ($userType === 'committee') {
            echo "<p style='color:green'><b>سيتم التوجيه إلى: committee/dashboard.php</b> (بسبب user_type = committee)</p>";
        } elseif ($userType === 'admin' && $role === 'president') {
            echo "<p style='color:orange'><b>سيتم التوجيه إلى: admin/president.php</b></p>";
        } elseif ($userType === 'admin') {
            echo "<p style='color:orange'><b>سيتم التوجيه إلى: admin/dashboard.php</b></p>";
        } else {
            echo "<p style='color:red'><b>التوجيه الافتراضي: admin/dashboard.php</b></p>";
        }
        
        // رابط للاختبار
        echo "<hr><p><a href='committee/dashboard.php'>→ اذهب إلى صفحة اللجنة</a></p>";
        echo "<p><a href='admin/president.php'>→ اذهب إلى صفحة الرئيس</a></p>";
    }
}
?>

<form method="POST">
    <input type="text" name="identifier" placeholder="Email/Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Test Login</button>
</form>

<p><a href="admin/login.php">← الصفحة الرئيسية للتسجيل</a></p>
