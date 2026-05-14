<?php
// check_committee_user.php - للتحقق من مستخدم اللجنة

require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h2>التحقق من مستخدم اللجنة</h2>";
echo "<pre>";

$db = getDB();

// 1. البحث عن المستخدم committee@learnata.edu
$email = 'committee@learnata.edu';
$user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);

if (!$user) {
    echo "❌ المستخدم $email غير موجود في جدول users!\n";
    
    // البحث عن أي مستخدمين من نوع committee
    echo "\n🔍 البحث عن مستخدمين من نوع 'committee':\n";
    $committeeUsers = $db->fetchAll("SELECT id, email, username, user_type, status FROM users WHERE user_type = 'committee' OR email LIKE '%committee%'");
    print_r($committeeUsers);
    
    exit;
}

echo "✅ المستخدم موجود في جدول users:\n";
echo "ID: " . $user['id'] . "\n";
echo "Email: " . $user['email'] . "\n";
echo "Username: " . $user['username'] . "\n";
echo "User Type: " . $user['user_type'] . "\n";
echo "Status: " . $user['status'] . "\n\n";

// 2. البحث في جدول committee_members
$committee = $db->fetchOne(
    "SELECT * FROM committee_members WHERE user_id = ?",
    [$user['id']]
);

if (!$committee) {
    echo "❌ المستخدم غير مرتبط بجدول committee_members!\n";
    
    // عرض كل أعضاء اللجان
    echo "\n🔍 جميع أعضاء اللجان في قاعدة البيانات:\n";
    $allCommittee = $db->fetchAll("SELECT * FROM committee_members");
    print_r($allCommittee);
    
    echo "\n\n💡 الحل: يجب إضافة هذا المستخدم إلى جدول committee_members\n";
    echo "SQL: INSERT INTO committee_members (user_id, committee_name, role, status, created_at) VALUES (?, 'لجنة القبول والتسجيل', 'reviewer', 'active', NOW());\n";
    
} else {
    echo "✅ المستخدم مرتبط بجدول committee_members:\n";
    echo "Committee ID: " . $committee['committee_id'] . "\n";
    echo "Committee Name: " . $committee['committee_name'] . "\n";
    echo "Role: " . $committee['role'] . "\n";
    echo "Status: " . $committee['status'] . "\n\n";
    
    if ($committee['status'] !== 'active') {
        echo "⚠️ تحذير: حالة العضو في اللجنة غير نشطة (" . $committee['status'] . ")\n";
        echo "يجب تحديث الحالة إلى 'active'\n";
    } else {
        echo "✅ كل شيء صحيح! المستخدم يجب أن يتم توجيهه لصفحة اللجنة.\n";
    }
}

echo "</pre>";
?>
