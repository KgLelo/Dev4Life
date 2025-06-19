<?php
require_once 'classes/parent.php';
require_once 'classes/teacher.php';
require_once 'classes/learner.php';

// Collect form data
$role = $_POST['role'];
$fullName = $_POST['fullName'];
$userName = $_POST['userName'];
$password = $_POST['password'];
$phoneNum = $_POST['phoneNum'];
$province = $_POST['province'];
$school = $_POST['schools'];
$grade = intval(substr($_POST['grade'], -2)); // Get first two characters of grade

// Choose table based on role
switch ($role) {
    case "teacher":
        $teacher = new TeacherUser($fullName, $userName, $password, $phoneNum, $province, $school);
        $teacher->register();
        break;
    case "learner":
        $learner = new LearnerUser($fullName, $userName, $password, $phoneNum, $province, $school, $grade);
        $learner->register();
        break;
    case "parent":
        $parent = new ParentUser($fullName, $userName, $password, $phoneNum);
        $parent->register();
        
        break;
    default:
        die("Invalid role selected.");
}

?>
