<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

// اختبار المستخدم president
$user = $db->fetchOne("SELECT id FROM users WHERE email = ?", ['president@learnata.edu']);

if ($user) {
    echo "<h2>اختبار عضوية اللجنة</h2>";
    echo "<p>User ID: " . $user['id'] . "</p>";
    
    try {
        $committee = $db->fetchOne(
            "SELECT cm.committee_id, c.name as committee_name, cm.role as committee_role 
            FROM committee_members cm 
            LEFT JOIN committees c ON cm.committee_id = c.id 
            WHERE cm.user_id = ? AND cm.status = 'active'",
            [$user['id']]
        );
        
        if ($committee) {
            echo "<p style='color:green'>✓ عضو لجنة!</p>";
            echo "<pre>";
            print_r($committee);
            echo "</pre>";
        } else {
            echo "<p style='color:red'>✗ ليس عضو لجنة</p>";
            
            // التحقق من الجداول
            echo "<h3>التحقق من الجداول:</h3>";
            try {
                $tables = $db->fetchAll("SHOW TABLES");
                echo "<p>الجداول الموجودة:</p>";
                foreach ($tables as $table) {
                    $tableName = array_values($table)[0];
                    if (strpos($tableName, 'committee') !== false) {
                        echo "- $tableName<br>";
                    }
                }
            } catch (Exception $e) {
                echo "<p>خطأ: " . $e->getMessage() . "</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>خطأ: " . $e->getMessage() . "</p>";
    }
}
?>
