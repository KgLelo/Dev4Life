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
        $this->CheckUser(); // Check if user already exists
    }

    // Override register method to include grade
    public function register() {
        $conn = connectToDatabase();

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
            return array('success' => false, 'error' => sqlsrv_errors());
        }

        if (sqlsrv_execute($stmt)) {
            echo "<p style='color:green;'>✅ Learner registered successfully. Please login.</p>";
            header("Location: login.html");
            exit();
        } else {
            die("Error registering learner: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
    }
    
}
