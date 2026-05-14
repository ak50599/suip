<?php
/**
 * manage_accepted_students.php - إدارة الطلاب المقبولين وإنشاء الأرقام الجامعية
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

/**
 * توليد الرقم الجامعي للطالب
 * Format: YYYYXXXSSSS (4 digits year + 3 digits college + 4 digits sequence)
 */
function generateUniversityId($college_id, $admission_year) {
    $db = getDB();
    
    $college_code = $db->query("SELECT college_code_num FROM college_codes WHERE college_id = ?", [$college_id])->fetch();
    if (!$college_code) {
        throw new Exception("كود الكلية غير موجود");
    }
    
    $code_num = $college_code['college_code_num'];
    
    $sequence = $db->query("SELECT last_sequence FROM college_sequence WHERE college_id = ? AND admission_year = ?", 
        [$college_id, $admission_year])->fetch();
    
    if ($sequence) {
        $new_sequence = $sequence['last_sequence'] + 1;
        $db->execute("UPDATE college_sequence SET last_sequence = ? WHERE college_id = ? AND admission_year = ?",
            [$new_sequence, $college_id, $admission_year]);
    } else {
        $new_sequence = 1;
        $db->execute("INSERT INTO college_sequence (college_id, college_code, admission_year, last_sequence) VALUES (?, ?, ?, ?)",
            [$college_id, $code_num, $admission_year, $new_sequence]);
    }
    
    return $admission_year . $code_num . str_pad($new_sequence, 4, '0', STR_PAD_LEFT);
}

/**
 * إضافة طالب مقبول جديد
 */
function addAcceptedStudent($student_data) {
    try {
        $db = getDB();
        
        $admission_year = $student_data['admission_year'] ?? date('Y');
        $university_id = generateUniversityId($student_data['college_id'], $admission_year);
        
        $default_password = $student_data['phone'] ? substr($student_data['phone'], -4) : rand(1000, 9999);
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        
        $db->execute("INSERT INTO users (username, email, password, user_type, status, created_at) VALUES (?, ?, ?, 'student', 'active', NOW())", 
            [$university_id, $student_data['email'], $hashed_password]);
        $user_id = $db->getConnection()->lastInsertId();
        
        $db->execute("INSERT INTO accepted_students 
            (user_id, university_id, student_name, email, phone, college_id, college_code, admission_year, admission_type, password, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $user_id,
                $university_id,
                $student_data['student_name'],
                $student_data['email'],
                $student_data['phone'],
                $student_data['college_id'],
                $student_data['college_code'] ?? '',
                $admission_year,
                $student_data['admission_type'] ?? 'committee',
                $default_password
            ]);
        
        return [
            'success' => true,
            'university_id' => $university_id,
            'password' => $default_password,
            'message' => 'تم قبول الطالب بنجاح'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * الحصول على جميع الطلاب المقبولين
 */
function getAllAcceptedStudents() {
    $db = getDB();
    return $db->query("SELECT a.*, c.college_name FROM accepted_students a LEFT JOIN colleges c ON a.college_id = c.id ORDER BY a.created_at DESC")->fetchAll();
}

// بدء الإخراج
$db = getDB();
?>
<!DOCTYPE html>
<html dir='rtl' lang='ar'>
<head>
    <meta charset='UTF-8'>
    <title>إدارة الطلاب المقبولين</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css'>
</head>
<body class='p-4'>
<div class='container'>
    <h1>🎓 إدارة الطلاب المقبولين</h1>
    
    <div class='row mt-4'>
        <div class='col-md-6'>
            <div class='card'>
                <div class='card-header bg-success text-white'>
                    <h5>📊 الإحصائيات</h5>
                </div>
                <div class='card-body'>
                    <?php
                    $stats = $db->query("SELECT c.college_name, COUNT(*) as count FROM accepted_students a JOIN colleges c ON a.college_id = c.id GROUP BY a.college_id")->fetchAll();
                    foreach ($stats as $stat) {
                        echo "<p><strong>{$stat['college_name']}:</strong> {$stat['count']} طالب</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class='col-md-6'>
            <div class='card'>
                <div class='card-header bg-primary text-white'>
                    <h5>🆕 إضافة طالب مقبول</h5>
                </div>
                <div class='card-body'>
                    <form method='POST'>
                        <div class='mb-3'>
                            <label>اسم الطالب</label>
                            <input type='text' name='student_name' class='form-control' required>
                        </div>
                        <div class='mb-3'>
                            <label>البريد الإلكتروني</label>
                            <input type='email' name='email' class='form-control' required>
                        </div>
                        <div class='mb-3'>
                            <label>رقم الهاتف</label>
                            <input type='text' name='phone' class='form-control' required>
                        </div>
                        <div class='mb-3'>
                            <label>الكلية</label>
                            <select name='college_id' class='form-control' required>
                                <?php
                                $colleges = $db->query("SELECT * FROM colleges WHERE status = 'active'")->fetchAll();
                                foreach ($colleges as $college) {
                                    echo "<option value='{$college['id']}'>{$college['college_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class='mb-3'>
                            <label>سنة القبول</label>
                            <input type='number' name='admission_year' class='form-control' value='<?php echo date('Y'); ?>'>
                        </div>
                        <button type='submit' name='add_student' class='btn btn-primary'>✅ قبول الطالب</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    if (isset($_POST['add_student'])) {
        $result = addAcceptedStudent([
            'student_name' => $_POST['student_name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'college_id' => $_POST['college_id'],
            'admission_year' => $_POST['admission_year']
        ]);
        
        if ($result['success']) {
            echo "<div class='alert alert-success mt-4'>
                ✅ {$result['message']}<br>
                <strong>الرقم الجامعي:</strong> {$result['university_id']}<br>
                <strong>كلمة المرور الافتراضية:</strong> {$result['password']}
            </div>";
        } else {
            echo "<div class='alert alert-danger mt-4'>❌ خطأ: {$result['error']}</div>";
        }
    }
    ?>
    
    <div class='mt-4'>
        <h3>📋 قائمة الطلاب المقبولين</h3>
        <table class='table table-striped table-bordered'>
            <thead class='table-dark'>
                <tr>
                    <th>#</th>
                    <th>الرقم الجامعي</th>
                    <th>الاسم</th>
                    <th>الكلية</th>
                    <th>البريد</th>
                    <th>كلمة المرور</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $students = getAllAcceptedStudents();
                $counter = 1;
                foreach ($students as $student) {
                    $status = $student['password_changed'] ? '✅ تم تغيير الباسورد' : '⏳ جديد';
                    echo "<tr>
                        <td>{$counter}</td>
                        <td><strong>{$student['university_id']}</strong></td>
                        <td>{$student['student_name']}</td>
                        <td>{$student['college_name']}</td>
                        <td>{$student['email']}</td>
                        <td>{$student['password']}</td>
                        <td>{$status}</td>
                    </tr>";
                    $counter++;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
