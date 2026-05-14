<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

echo "<h2>اختبار تسجيل الدخول</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p>المعرف المدخل: " . htmlspecialchars($identifier) . "</p>";
    
    $db = getDB();
    $auth = new Auth();
    
    // التحقق من وجود المستخدم
    $user = $db->fetchOne("SELECT id, username, email, password, user_type, status FROM users WHERE email = ? OR username = ?", [$identifier, $identifier]);
    
    if ($user) {
        echo "<p>✓ المستخدم موجود</p>";
        echo "<p>ID: " . $user['id'] . "</p>";
        echo "<p>Username: " . $user['username'] . "</p>";
        echo "<p>Email: " . $user['email'] . "</p>";
        echo "<p>Type: " . $user['user_type'] . "</p>";
        echo "<p>Status: " . $user['status'] . "</p>";
        
        // اختبار كلمة المرور
        if (password_verify($password, $user['password'])) {
            echo "<p style='color:green'>✓ كلمة المرور صحيحة</p>";
        } else {
            echo "<p style='color:red'>✗ كلمة المرور غير صحيحة</p>";
            echo "<p>Password hash: " . substr($user['password'], 0, 20) . "...</p>";
        }
    } else {
        echo "<p style='color:red'>✗ المستخدم غير موجود</p>";
    }
}
?>

<form method="POST">
    <input type="text" name="identifier" placeholder="Email/Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Test</button>
</form>
