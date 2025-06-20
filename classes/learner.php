<?php
require_once(__DIR__ . '/teacher.php');

class  LearnerUser extends TeacherUser {
    private $grade;
    protected $table = "LearnerTable";

    public function __construct($fullName, $userName, $password, $phoneNum, $province, $school, $grade) {
        $this->fullName = $fullName;
        $this->userName = $userName;
        $this->password = password_hash($password, PASSWORD_DEFAULT); // Secure password hash
        $this->phoneNum = $phoneNum;
        $this->province = $province;
        $this->school = $school;
        $this->grade = $grade;
        
    }

    // Override register method to include grade
    public function register() {
        $conn = connectToDatabase();
        // Check if the user already exists in any of the tables
        if ($this->CheckUser($conn, $this->userName) === true) {
            header("Location: register.html?error=username_exists");
            exit();
        } else {
            $sql = "INSERT INTO {$this->table} (fullName, userName, password, phoneNum, province, school, grade) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = array(
            $this->fullName,
            $this->userName,
            $this->password,
            $this->phoneNum,
            $this->province,
            $this->school,
            $this->grade
        );

        $stmt = sqlsrv_prepare($conn, $sql, $params);

        if ($stmt === false) {
            header("Location: register.html?error=registration_failed");
            exit();
        }

        if (sqlsrv_execute($stmt)) {
            header("Location: register.html?success=learner_registered");
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
