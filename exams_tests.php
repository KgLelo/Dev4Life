<?php
// Session check
if (!isset($_SESSION)) session_start();
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

// Filters and teacher input
$selectedProvince = $_POST['filter_province'] ?? '';
$selectedSchool = $_POST['filter_school'] ?? '';
$selectedGrade = $_POST['filter_grade'] ?? '';
$teacherProvince = $_POST['teacher_province'] ?? '';

// Handle teacher actions
if ($isTeacher && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $subject = $_POST['subject'];
        $date = $_POST['date'];
        $desc = $_POST['description'];
        $grade = $_POST['grade'];
        $schoolID = $_POST['schoolID'];
        sqlsrv_query($conn, "INSERT INTO ExamsTests (subject, examDate, description, grade, schoolID) VALUES (?, ?, ?, ?, ?)", [$subject, $date, $desc, $grade, $schoolID]);
    }
    if (isset($_POST['update'])) {
        $id = $_POST['examID'];
        $subject = $_POST['subject'];
        $date = $_POST['date'];
        $desc = $_POST['description'];
        $grade = $_POST['grade'];
        $schoolID = $_POST['schoolID'];
        sqlsrv_query($conn, "UPDATE ExamsTests SET subject=?, examDate=?, description=?, grade=?, schoolID=? WHERE examID=?", [$subject, $date, $desc, $grade, $schoolID, $id]);
    }
    if (isset($_POST['delete'])) {
        $id = $_POST['examID'];
        sqlsrv_query($conn, "DELETE FROM ExamsTests WHERE examID=?", [$id]);
    }
}

// School list
$schools = [];
if ($selectedProvince) {
    $stmt = sqlsrv_query($conn, "SELECT schoolID, schoolName FROM Schools WHERE province = ?", [$selectedProvince]);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $schools[] = $row;
    }
}

$teacherSchools = [];
if ($teacherProvince) {
    $stmt = sqlsrv_query($conn, "SELECT schoolID, schoolName FROM Schools WHERE province = ?", [$teacherProvince]);
} else {
    $stmt = sqlsrv_query($conn, "SELECT schoolID, schoolName FROM Schools");
}
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $teacherSchools[] = $row;
}

// Query setup
$params = [];
if ($isTeacher) {
    $query = "SELECT et.*, s.schoolName FROM ExamsTests et JOIN Schools s ON et.schoolID = s.schoolID ORDER BY examDate";
} elseif ($selectedProvince && $selectedSchool && $selectedGrade) {
    $query = "SELECT et.*, s.schoolName FROM ExamsTests et JOIN Schools s ON et.schoolID = s.schoolID WHERE s.province = ? AND s.schoolID = ? AND et.grade = ? ORDER BY examDate";
    $params = [$selectedProvince, $selectedSchool, $selectedGrade];
} else {
    $query = "SELECT * FROM ExamsTests WHERE 1=0"; // No filter applied
}
$result = sqlsrv_query($conn, $query, $params);
?>

<div style="max-width:900px; margin:auto; color:#333;">

  <h1 style="color:#004aad; text-align:center; margin-bottom: 30px;">📝 Exams & Tests</h1>

  <?php if (!$isTeacher): ?>
  <div style="background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); margin-bottom:30px;">
    <form method="POST">
      <label style="font-weight:bold;">Select Province:</label>
      <select name="filter_province" onchange="this.form.submit()" required
        style="width:100%; padding:10px; margin:8px 0 15px 0; border:1px solid #aaa; border-radius:6px; font-size:15px;">
        <option value="">-- Choose Province --</option>
        <?php foreach ($provinces as $p): ?>
          <option value="<?= htmlspecialchars($p) ?>" <?= $selectedProvince == $p ? "selected" : "" ?>><?= htmlspecialchars($p) ?></option>
        <?php endforeach; ?>
      </select>

      <?php if ($selectedProvince): ?>
        <label style="font-weight:bold;">Select School:</label>
        <select name="filter_school" onchange="this.form.submit()" required
          style="width:100%; padding:10px; margin:8px 0 15px 0; border:1px solid #aaa; border-radius:6px; font-size:15px;">
          <option value="">-- Choose School --</option>
          <?php foreach ($schools as $s): ?>
            <option value="<?= $s['schoolID'] ?>" <?= $selectedSchool == $s['schoolID'] ? "selected" : "" ?>><?= htmlspecialchars($s['schoolName']) ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <?php if ($selectedSchool): ?>
        <label style="font-weight:bold;">Select Grade:</label>
        <select name="filter_grade" onchange="this.form.submit()" required
          style="width:100%; padding:10px; margin:8px 0 15px 0; border:1px solid #aaa; border-radius:6px; font-size:15px;">
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
  <div style="background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); margin-bottom:30px;">
    <h3 style="color:#004aad; margin-top:0;">➕ Add Exam/Test</h3>
    <form method="POST">
      <input type="text" name="subject" placeholder="Subject" required
        style="width:100%; padding:10px; margin:10px 0; border:1px solid #aaa; border-radius:6px; font-size:15px;">
      <input type="date" name="date" required
        style="width:100%; padding:10px; margin:10px 0; border:1px solid #aaa; border-radius:6px; font-size:15px;">
      <textarea name="description" placeholder="Description" required rows="3"
        style="width:100%; padding:10px; margin:10px 0; border:1px solid #aaa; border-radius:6px; font-size:15px; resize:vertical;"></textarea>

      <select name="grade" required
        style="width:100%; padding:10px; margin:10px 0 20px 0; border:1px solid #aaa; border-radius:6px; font-size:15px;">
        <option value="">-- Select Grade --</option>
        <?php for ($i = 8; $i <= 12; $i++): ?>
          <option value="Grade <?= $i ?>">Grade <?= $i ?></option>
        <?php endfor; ?>
      </select>

      <label style="font-weight:bold;">Select Province:</label>
      <select name="teacher_province" onchange="this.form.submit()" required
        style="width:100%; padding:10px; margin:8px 0 15px 0; border:1px solid #aaa; border-radius:6px; font-size:15px;">
        <option value="">-- Choose Province --</option>
        <?php foreach ($provinces as $p): ?>
          <option value="<?= htmlspecialchars($p) ?>" <?= $teacherProvince == $p ? "selected" : "" ?>><?= htmlspecialchars($p) ?></option>
        <?php endforeach; ?>
      </select>

      <?php if ($teacherProvince): ?>
        <label style="font-weight:bold;">Select School:</label>
        <select name="schoolID" required
          style="width:100%; padding:10px; margin:8px 0 15px 0; border:1px solid #aaa; border-radius:6px; font-size:15px;">
          <option value="">-- Choose School --</option>
          <?php foreach ($teacherSchools as $s): ?>
            <option value="<?= $s['schoolID'] ?>"><?= htmlspecialchars($s['schoolName']) ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <?php if ($teacherProvince): ?>
        <button type="submit" name="add" class="btn"
          style="background:#004aad; color:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold;">Add</button>
      <?php endif; ?>
    </form>
  </div>
  <?php endif; ?>

  <table style="width:100%; border-collapse:collapse; background:#fff; box-shadow:0 0 8px rgba(0,0,0,0.1);">
    <thead>
      <tr style="background:#004aad; color:#fff;">
        <th style="padding:10px; border-bottom:2px solid #00337a;">Date</th>
        <th style="padding:10px; border-bottom:2px solid #00337a;">School</th>
        <th style="padding:10px; border-bottom:2px solid #00337a;">Grade</th>
        <th style="padding:10px; border-bottom:2px solid #00337a;">Subject</th>
        <th style="padding:10px; border-bottom:2px solid #00337a;">Description</th>
        <?php if ($isTeacher): ?><th style="padding:10px; border-bottom:2px solid #00337a;">Actions</th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)): ?>
        <tr>
          <form method="POST" style="margin:0;">
            <td style="padding:10px; border-bottom:1px solid #ddd;">
              <input type="date" name="date" value="<?= $row['examDate']->format('Y-m-d') ?>" <?= !$isTeacher ? 'readonly' : '' ?>
                style="width:130px; padding:6px; border:1px solid #ccc; border-radius:4px;">
            </td>
            <td style="padding:10px; border-bottom:1px solid #ddd;">
              <?php if ($isTeacher): ?>
                <select name="schoolID" required
                  style="padding:6px; border:1px solid #ccc; border-radius:4px; font-size:14px;">
                  <?php foreach ($teacherSchools as $s): ?>
                    <option value="<?= $s['schoolID'] ?>" <?= $s['schoolID'] == $row['schoolID'] ? "selected" : "" ?>><?= htmlspecialchars($s['schoolName']) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php else: ?>
                <?= htmlspecialchars($row['schoolName']) ?>
              <?php endif; ?>
            </td>
            <td style="padding:10px; border-bottom:1px solid #ddd;">
              <?php if ($isTeacher): ?>
                <select name="grade" style="padding:6px; border:1px solid #ccc; border-radius:4px; font-size:14px;">
                  <?php for ($i = 8; $i <= 12; $i++): $g = "Grade $i"; ?>
                    <option value="<?= $g ?>" <?= $g == $row['grade'] ? "selected" : "" ?>><?= $g ?></option>
                  <?php endfor; ?>
                </select>
              <?php else: ?>
                <?= htmlspecialchars($row['grade']) ?>
              <?php endif; ?>
            </td>
            <td style="padding:10px; border-bottom:1px solid #ddd;">
              <input type="text" name="subject" value="<?= htmlspecialchars($row['subject']) ?>" <?= !$isTeacher ? 'readonly' : '' ?>
                style="width:150px; padding:6px; border:1px solid #ccc; border-radius:4px;">
            </td>
            <td style="padding:10px; border-bottom:1px solid #ddd;">
              <textarea name="description" <?= !$isTeacher ? 'readonly' : '' ?> rows="2"
                style="width:250px; padding:6px; border:1px solid #ccc; border-radius:4px; resize:none;"><?= htmlspecialchars($row['description']) ?></textarea>
            </td>
            <?php if ($isTeacher): ?>
              <td style="padding:10px; border-bottom:1px solid #ddd;">
                <input type="hidden" name="examID" value="<?= $row['examID'] ?>">
                <button type="submit" name="update" class="btn"
                  style="background:#004aad; color:#fff; padding:6px 12px; border:none; border-radius:5px; cursor:pointer; margin-bottom:8px; width:100%;">Update</button>
                <button type="submit" name="delete" class="btn"
                  onclick="return confirm('Are you sure you want to delete?')"
                  style="background:#d60000; color:#fff; padding:6px 12px; border:none; border-radius:5px; cursor:pointer; width:100%;">Delete</button>
              </td>
            <?php endif; ?>
          </form>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <a href="dashboard.php" style="display:block; text-align:center; margin:30px auto 0; background:#004aad; color:#fff; padding:10px 25px; border-radius:6px; text-decoration:none; width:fit-content;">← About Us!</a>
</div>
