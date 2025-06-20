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
    }

    // Override register method to include province and school
    public function register() {
        $conn = connectToDatabase();

        if ($this->CheckUser($conn, $this->userName) === true) {
            header("Location: register.html?error=username_exists");
            exit();
        } else {
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
           header("Location: register.html?error=registration_failed");
            exit();
        }

        if (sqlsrv_execute($stmt)) {
            header("Location: register.html?success=teacher_registered");
            exit();
        } else {
            header("Location: register.html?error=registration_failed");
            exit();
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        }
    }
}
