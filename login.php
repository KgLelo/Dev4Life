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
                // Username found, but password incorrect
                header("Location: login.html?error=invalid_password");
                exit();
            }
        }
    }

    // Username not found
    header("Location: login.html?error=user_not_found");
    exit();
}
