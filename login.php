<?php

require_once 'connect.php';
$conn = connectToDatabase();

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userName = $_POST['userName'] ?? '';
    $password = $_POST['password'] ?? '';

    // Array of tables and their roles
    $tables = [
        'TeacherTable' => 'teacher',
        'LearnerTable' => 'learner',
        'ParentTable'  => 'parent'
    ];

    $found = false;
    foreach ($tables as $table => $role) {
        $sql = "SELECT * FROM $table WHERE userName = ?";
        $params = array($userName);
        $stmt = sqlsrv_prepare($conn, $sql, $params);

        if (!$stmt) {
            continue; // Skip this table if prepare fails
        }

        if (sqlsrv_execute($stmt) && sqlsrv_fetch($stmt)) {
            $hashedPassword = sqlsrv_get_field($stmt, 3);
            if (password_verify($password, $hashedPassword)) {
                $_SESSION['userName'] = $userName;
                $_SESSION['role'] = $role;
                header("Location: dashboard.php");
                exit();
            } else {
                $found = true; // Username found, but password incorrect
                break;
            }
        }
    }

    if ($found) {
        echo "<p style='color:red;'>❌ Invalid password. Please try again.</p>";
    } else {
        echo "<p style='color:red;'>❌ Username not found. Please try again.</p>";
    }
} else {
    echo "<p style='color:orange;'>Please login using the form.</p>";
}
