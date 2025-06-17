<?php
require_once 'connect.php';
$conn = connectToDatabase();

// Collect form data
$role = $_POST['role'];
$fullName = $_POST['fullName'];
$userName = $_POST['userName'];
$password = $_POST['password'];
$phoneNum = $_POST['phoneNum'];
$address = $_POST['address'];

// Choose table based on role
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

// Check if user already exists
$checkSql = "SELECT * FROM $table WHERE userName = ?";
$checkParams = array($userName);
$checkStmt = sqlsrv_prepare($conn, $checkSql, $checkParams);

if (!$checkStmt) {
    die(print_r(sqlsrv_errors(), true));
}

if (sqlsrv_execute($checkStmt)) {
    if (sqlsrv_fetch($checkStmt)) {
        // User already exists
        echo "<p style='color:red;'>❌ Username already exists. Please login.</p>";
        exit();
    }
} else {
    die(print_r(sqlsrv_errors(), true));
}

// Insert new user
$insertSql = "INSERT INTO $table (fullName, userName, password, phoneNum, address) VALUES (?, ?, ?, ?, ?)";
$params = array($fullName, $userName, $password, $phoneNum, $address);
$insertStmt = sqlsrv_prepare($conn, $insertSql, $params);

if (!$insertStmt) {
    die(print_r(sqlsrv_errors(), true));
}

if (sqlsrv_execute($insertStmt)) {
    header("Location: login.html");
    exit();
} else {
    die(print_r(sqlsrv_errors(), true));
}
?>
