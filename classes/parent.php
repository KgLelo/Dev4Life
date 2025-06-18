<?php
require_once(__DIR__ . '/../connect.php');

class ParentUser {
    private $fullName;
    private $userName;
    private $password;
    private $phoneNum;
    private $table = "ParentTable";

    public function __construct($fullName, $userName, $password, $phoneNum) {
        $this->fullName = $fullName;
        $this->userName = $userName;
        $this->password = password_hash($password, PASSWORD_DEFAULT); // Secure password hash
        $this->phoneNum = $phoneNum;
        $this->CheckUser(); // Check if user already exists
    }

    public function CheckUser() {
        $conn = connectToDatabase();

        // Use $this->table and curly braces in double quotes
        $checkSql = "SELECT * FROM {$this->table} WHERE userName = ?";
        $checkParams = array($this->userName);
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

        sqlsrv_free_stmt($checkStmt);
        sqlsrv_close($conn);
    }

    public function register() {
        $conn = connectToDatabase();

        // Use curly braces for property in double quotes
        $sql = "INSERT INTO {$this->table} (fullName, userName, password, phoneNum) VALUES (?, ?, ?, ?)";
        $params = array(
            $this->fullName,
            $this->userName,
            $this->password,
            $this->phoneNum,
        );

        $stmt = sqlsrv_prepare($conn, $sql, $params);

        if ($stmt === false) {
            // Handle error
            return array('success' => false, 'error' => sqlsrv_errors());
        }

        if (sqlsrv_execute($stmt)) {
            // Registration successful
            echo "<p style='color:green;'>✅ Parent registered successfully. Please login.</p>";
            header("Location: login.html");
            exit();
        } else {
            die("Error registering parent: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
    }
}
