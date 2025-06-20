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
        
    }

    public function CheckUser($conn, $userName) {
    $allTables = [
        'TeacherTable' => 'teacher',
        'LearnerTable' => 'learner',
        'ParentTable'  => 'parent'
    ];

    foreach ($allTables as $current => $role) {
        $checkSql = "SELECT * FROM $current WHERE userName = ?";
        $checkParams = array($userName);
        $checkStmt = sqlsrv_prepare($conn, $checkSql, $checkParams);

        if (!$checkStmt) {
            sqlsrv_close($conn);
            return false;
        }

        if (sqlsrv_execute($checkStmt)) {
            if (sqlsrv_fetch($checkStmt)) {
                // User already exists
                sqlsrv_free_stmt($checkStmt);
                sqlsrv_close($conn);
                return true;
            }
        }

        sqlsrv_free_stmt($checkStmt);
    }
    return false; // Not found
}

    public function register() {
        $conn = connectToDatabase();

        // Check if the user already exists in any of the tables
       if ($this->CheckUser($conn, $this->userName) === true) {
            header("Location: register.html?error=username_exists");
            exit();
        } else {
        // Prepare the SQL statement to insert the new parent user
            $sql = "INSERT INTO {$this->table} (fullName, userName, password, phoneNum) VALUES (?, ?, ?, ?)";
        $params = array(
            $this->fullName,
            $this->userName,
            $this->password,
            $this->phoneNum,
        );

        $stmt = sqlsrv_prepare($conn, $sql, $params);

        if ($stmt === false) {
                header("Location: register.html?error=registration_failed");
                exit();
        }

        if (sqlsrv_execute($stmt)) {
            // Registration successful
            header("Location: register.html?success=parent_registered");
            exit();
        } else {
            die("Error registering parent: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        }
        
    }
}
