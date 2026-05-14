<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

echo "<h2>تشخيص تسجيل الدخول</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p>المعرف: " . htmlspecialchars($identifier) . "</p>";
    
    $auth = new Auth();
    $result = $auth->login($identifier, $password);
    
    echo "<hr><h3>نتيجة Auth::login:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "<p style='color:green'>✓ تسجيل الدخول ناجح</p>";
        echo "<p>Session user_type: " . ($_SESSION['user_type'] ?? 'غير محدد') . "</p>";
        echo "<p>Session admin_role: " . ($_SESSION['admin_role'] ?? 'غير محدد') . "</p>";
    } else {
        echo "<p style='color:red'>✗ فشل: " . $result['message'] . "</p>";
    }
}
?>

<form method="POST">
    <input type="text" name="identifier" placeholder="Email/Username" value="president@learnata.edu"><br><br>
    <input type="password" name="password" placeholder="Password"><br><br>
    <button type="submit">Test Login</button>
</form>
