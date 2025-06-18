<?php
require_once(__DIR__ . '/parent.php');

class TeacherUser extends ParentUser {
    private $province;
    private $school;
    protected $table = "TeacherTable";

    public function __construct($fullName, $userName, $password, $phoneNum, $province, $school) {
        $this->fullName = $fullName;
        $this->userName = $userName;
        $this->password = password_hash($password, PASSWORD_DEFAULT); // Secure password hash
        $this->phoneNum = $phoneNum;
        $this->province = $province;
        $this->school = $school;
        $this->CheckUser(); // Check if user already exists
    }

    // Override register method to include province and school
    public function register() {
        $conn = connectToDatabase();

        $sql = "INSERT INTO {$this->table} (fullName, userName, password, phoneNum, province, school) VALUES (?, ?, ?, ?, ?, ?)";
        $params = array(
            $this->fullName,
            $this->userName,
            $this->password,
            $this->phoneNum,
            $this->province,
            $this->school
        );

        $stmt = sqlsrv_prepare($conn, $sql, $params);

        if ($stmt === false) {
            return array('success' => false, 'error' => sqlsrv_errors());
        }

        if (sqlsrv_execute($stmt)) {
            echo "<p style='color:green;'>✅ Teacher registered successfully. Please login.</p>";
            header("Location: login.html");
            exit();
        } else {
            die("Error registering teacher: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
    }
}
