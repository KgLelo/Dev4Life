<?php
session_start();
if (!isset($_SESSION['userName']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

require 'connect.php';
$conn = connectToDatabase();

$role = strtolower($_SESSION['role']);
$isTeacher = $role === 'teacher';

// Fetch distinct provinces
$provinceResult = sqlsrv_query($conn, "SELECT DISTINCT province FROM Schools ORDER BY province");
$provinces = [];
while ($row = sqlsrv_fetch_array($provinceResult, SQLSRV_FETCH_ASSOC)) {
    $provinces[] = $row['province'];
}

// User filters
$selectedProvince = $_POST['filter_province'] ?? '';
$selectedSchool = $_POST['filter_school'] ?? '';
$selectedGrade = $_POST['filter_grade'] ?? '';

// For teacher province/school selection
$teacherProvince = $_POST['teacher_province'] ?? '';

// Handle Teacher Actions
if ($isTeacher && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $subject = $_POST['subject'];
        $date = $_POST['date'];
        $desc = $_POST['description'];
        $grade = $_POST['grade'];
        $schoolID = $_POST['schoolID'];
        $sql = "INSERT INTO ExamsTests (subject, examDate, description, grade, schoolID) VALUES (?, ?, ?, ?, ?)";
        sqlsrv_query($conn, $sql, [$subject, $date, $desc, $grade, $schoolID]);
    }

    if (isset($_POST['update'])) {
        $id = $_POST['examID'];
        $subject = $_POST['subject'];
        $date = $_POST['date'];
        $desc = $_POST['description'];
        $grade = $_POST['grade'];
        $schoolID = $_POST['schoolID'];
        $sql = "UPDATE ExamsTests SET subject=?, examDate=?, description=?, grade=?, schoolID=? WHERE examID=?";
        sqlsrv_query($conn, $sql, [$subject, $date, $desc, $grade, $schoolID, $id]);
    }

    if (isset($_POST['delete'])) {
        $id = $_POST['examID'];
        $sql = "DELETE FROM ExamsTests WHERE examID=?";
        sqlsrv_query($conn, $sql, [$id]);
    }
}

// Fetch schools under selected province
$schools = [];
if ($selectedProvince) {
    $stmt = sqlsrv_query($conn, "SELECT schoolID, schoolName FROM Schools WHERE province = ?", [$selectedProvince]);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $schools[] = $row;
    }
}

// Fetch schools under teacher province
$teacherSchools = [];
if ($teacherProvince) {
    $stmt = sqlsrv_query($conn, "SELECT schoolID, schoolName FROM Schools WHERE province = ?", [$teacherProvince]);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $teacherSchools[] = $row;
    }
}

// Fetch exams/tests
$params = [];
if ($isTeacher) {
    $query = "SELECT et.*, s.schoolName FROM ExamsTests et JOIN Schools s ON et.schoolID = s.schoolID ORDER BY examDate";
} elseif ($selectedProvince && $selectedSchool && $selectedGrade) {
    $query = "SELECT et.*, s.schoolName FROM ExamsTests et JOIN Schools s ON et.schoolID = s.schoolID WHERE s.province = ? AND s.schoolID = ? AND et.grade = ? ORDER BY examDate";
    $params = [$selectedProvince, $selectedSchool, $selectedGrade];
} else {
    $query = "SELECT * FROM ExamsTests WHERE 1=0";
}
$result = sqlsrv_query($conn, $query, $params);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exams & Tests</title>
    <style>
        body { font-family: Arial; background-color: #f4f6fb; padding: 30px; }
        h1 { color: #004aad; text-align: center; }
        table { width: 95%; margin: 20px auto; border-collapse: collapse; background: #fff; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; }
        .form-box, .filters { max-width: 700px; margin: 20px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #aaa; border-radius: 5px; }
        .btn { background: #004aad; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #003b80; }
        .actions form { display: inline-block; }
        .back-link { display: block; text-align: center; margin-top: 20px; text-decoration: none; background: #004aad; color: white; padding: 10px 20px; border-radius: 5px; width: fit-content; margin-left: auto; margin-right: auto; }
    </style>
</head>
<body>

<h1>📝 Exams & Tests</h1>

<?php if (!$isTeacher): ?>
<div class="filters">
    <form method="POST">
        <label><strong>Select Province:</strong></label>
        <select name="filter_province" onchange="this.form.submit()" required>
            <option value="">-- Choose Province --</option>
            <?php foreach ($provinces as $p): ?>
                <option value="<?= $p ?>" <?= $selectedProvince == $p ? "selected" : "" ?>><?= $p ?></option>
            <?php endforeach; ?>
        </select>

        <?php if ($selectedProvince): ?>
            <label><strong>Select School:</strong></label>
            <select name="filter_school" onchange="this.form.submit()" required>
                <option value="">-- Choose School --</option>
                <?php foreach ($schools as $s): ?>
                    <option value="<?= $s['schoolID'] ?>" <?= $selectedSchool == $s['schoolID'] ? "selected" : "" ?>><?= $s['schoolName'] ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ($selectedSchool): ?>
            <label><strong>Select Grade:</strong></label>
            <select name="filter_grade" onchange="this.form.submit()" required>
                <option value="">-- Choose Grade --</option>
                <?php for ($i = 8; $i <= 12; $i++): ?>
                    <?php $grade = "Grade $i"; ?>
                    <option value="<?= $grade ?>" <?= $selectedGrade === $grade ? "selected" : "" ?>><?= $grade ?></option>
                <?php endfor; ?>
            </select>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>

<?php if ($isTeacher): ?>
<div class="form-box">
    <h3>Add Exam/Test</h3>
    <form method="POST">
        <input type="text" name="subject" placeholder="Subject" required>
        <input type="date" name="date" required>
        <textarea name="description" placeholder="Description" required></textarea>

        <select name="grade" required>
            <option value="">-- Select Grade --</option>
            <?php for ($i = 8; $i <= 12; $i++): ?>
                <option value="Grade <?= $i ?>">Grade <?= $i ?></option>
            <?php endfor; ?>
        </select>

        <label><strong>Select Province:</strong></label>
        <select name="teacher_province" onchange="this.form.submit()" required>
            <option value="">-- Choose Province --</option>
            <?php foreach ($provinces as $p): ?>
                <option value="<?= $p ?>" <?= $teacherProvince == $p ? "selected" : "" ?>><?= $p ?></option>
            <?php endforeach; ?>
        </select>

        <?php if ($teacherProvince): ?>
            <label><strong>Select School:</strong></label>
            <select name="schoolID" required>
                <option value="">-- Choose School --</option>
                <?php foreach ($teacherSchools as $s): ?>
                    <option value="<?= $s['schoolID'] ?>"><?= $s['schoolName'] ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ($teacherProvince): ?>
            <button type="submit" name="add" class="btn">Add</button>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>

<table>
    <tr>
        <th>Date</th>
        <th>School</th>
        <th>Grade</th>
        <th>Subject</th>
        <th>Description</th>
        <?php if ($isTeacher): ?><th>Actions</th><?php endif; ?>
    </tr>
    <?php while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)): ?>
    <tr>
        <form method="POST">
            <td><input type="date" name="date" value="<?= $row['examDate']->format('Y-m-d') ?>" <?= !$isTeacher ? 'readonly' : '' ?>></td>
            <td><?= htmlspecialchars($row['schoolName']) ?></td>
            <td>
                <?php if ($isTeacher): ?>
                    <select name="grade">
                        <?php for ($i = 8; $i <= 12; $i++): $g = "Grade $i"; ?>
                            <option value="<?= $g ?>" <?= $g == $row['grade'] ? "selected" : "" ?>><?= $g ?></option>
                        <?php endfor; ?>
                    </select>
                <?php else: ?>
                    <?= htmlspecialchars($row['grade']) ?>
                <?php endif; ?>
            </td>
            <td><input type="text" name="subject" value="<?= htmlspecialchars($row['subject']) ?>" <?= !$isTeacher ? 'readonly' : '' ?>></td>
            <td><textarea name="description" <?= !$isTeacher ? 'readonly' : '' ?>><?= htmlspecialchars($row['description']) ?></textarea></td>
            <?php if ($isTeacher): ?>
            <td>
                <input type="hidden" name="examID" value="<?= $row['examID'] ?>">
                <input type="hidden" name="schoolID" value="<?= $row['schoolID'] ?>">
                <button type="submit" name="update" class="btn">Update</button>
                <button type="submit" name="delete" class="btn" onclick="return confirm('Are you sure?')">Delete</button>
            </td>
            <?php endif; ?>
        </form>
    </tr>
    <?php endwhile; ?>
</table>

<a href="dashboard.php" class="back-link">← Back to Dashboard</a>

</body>
</html>
