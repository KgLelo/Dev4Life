<?php
require_once 'connect.php';
$conn = connectToDatabase();

session_start(); // Start session to store user details after login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form was submitted
    $role = $_POST['role'] ?? '';
    $userName = $_POST['userName'] ?? '';
    $password = $_POST['password'] ?? '';

    switch ($role) {
        case "teacher":
            $table = "TeacherTable";
            break;
        case "learner":
            $table = "LearnerTable";
            break;
        case "parent":
            $table = "ParentTable";
            break;
        default:
            die("Invalid role selected.");
    }

    $sql = "SELECT * FROM $table WHERE userName = ?";
    $params = array($userName);
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if (!$stmt) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_execute($stmt) && sqlsrv_fetch($stmt)) {
        $hashedPassword = sqlsrv_get_field($stmt, 3);
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['userName'] = $userName;
            $_SESSION['role'] = $role;
            header("Location: dashboard.php");
            exit();
    } else {
        echo "<p style='color:red;'>❌ Invalid username or password. Please try again.</p>";
    }
} else {
    // If not submitted via POST, show the login form (optional)
    echo "<p style='color:orange;'>Please login using the form.</p>";
}
}
