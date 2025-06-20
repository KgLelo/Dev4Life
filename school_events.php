<?php
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
$provinceList = [];
$provinceStmt = sqlsrv_query($conn, "SELECT DISTINCT province FROM schools");
while ($row = sqlsrv_fetch_array($provinceStmt, SQLSRV_FETCH_ASSOC)) {
    $provinceList[] = $row['province'];
}

// Get selected province
$selectedProvince = $_POST['province'] ?? '';
$selectedSchool = $_POST['school'] ?? '';

// Fetch schools under selected province
$schoolList = [];
if ($selectedProvince) {
    $schoolStmt = sqlsrv_query($conn, "SELECT schoolName FROM schools WHERE province = ?", [$selectedProvince]);
    while ($row = sqlsrv_fetch_array($schoolStmt, SQLSRV_FETCH_ASSOC)) {
        $schoolList[] = $row['schoolName'];
    }
}

// Handle Add, Update, Delete
if ($isTeacher && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $title = $_POST['title'];
        $date = $_POST['date'];
        $desc = $_POST['description'];
        $province = $_POST['province'];
        $school = $_POST['school'];

        $sql = "INSERT INTO SchoolEvents (eventTitle, eventDate, eventDescription, province, school) VALUES (?, ?, ?, ?, ?)";
        sqlsrv_query($conn, $sql, [$title, $date, $desc, $province, $school]);
    }

    if (isset($_POST['update'])) {
        $id = $_POST['eventID'];
        $title = $_POST['title'];
        $date = $_POST['date'];
        $desc = $_POST['description'];

        $sql = "UPDATE SchoolEvents SET eventTitle=?, eventDate=?, eventDescription=? WHERE eventID=?";
        sqlsrv_query($conn, $sql, [$title, $date, $desc, $id]);
    }

    if (isset($_POST['delete'])) {
        $id = $_POST['eventID'];
        $sql = "DELETE FROM SchoolEvents WHERE eventID=?";
        sqlsrv_query($conn, $sql, [$id]);
    }
}

// Fetch all events
$sql = "SELECT * FROM SchoolEvents ORDER BY eventDate";
$result = sqlsrv_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>School Events - WeConnect</title>
    <style>
        body { font-family: Arial; background-color: #f4f6fb; margin: 0; padding: 30px; background-image: url('images/img12.jpg'); background-size: cover; background-position: center; background-attachment: fixed; }
        h1 { color: #004aad; text-align: center; }
        table { width: 90%; margin: 20px auto; border-collapse: collapse; background: #fff; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        form { display: inline; }
        .form-box { max-width: 600px; margin: 30px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        input[type="text"], input[type="date"], select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #aaa; border-radius: 5px; }
        textarea { width: 100%; padding: 10px; border: 1px solid #aaa; border-radius: 5px; }
        .btn { background: #004aad; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; margin: 5px 2px; }
        .btn:hover { background: #003b80; }
        .actions form { display: inline-block; }
        .back-link { text-align: center; margin-top: 30px; display: block; text-decoration: none; color: white; background-color: #004aad; padding: 10px 20px; width: fit-content; border-radius: 5px; margin-left: auto; margin-right: auto; }
    </style>
</head>
<body>

<h1>🎉 School Events</h1>

<?php if ($isTeacher): ?>
<div class="form-box">
    <h3>Add New Event</h3>
    <form method="POST">
        <label>Select Province</label>
        <select name="province" onchange="this.form.submit()" required>
            <option value="">-- Choose Province --</option>
            <?php foreach ($provinceList as $prov): ?>
                <option value="<?= $prov ?>" <?= $selectedProvince == $prov ? 'selected' : '' ?>><?= $prov ?></option>
            <?php endforeach; ?>
        </select>

        <label>Select School</label>
        <select name="school" required>
            <option value="">-- Choose School --</option>
            <?php foreach ($schoolList as $sch): ?>
                <option value="<?= $sch ?>" <?= $selectedSchool == $sch ? 'selected' : '' ?>><?= $sch ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="title" placeholder="Event Title" required>
        <input type="date" name="date" required>
        <textarea name="description" placeholder="Event Description" required></textarea>
        <button type="submit" name="add" class="btn">Add Event</button>
    </form>
</div>
<?php endif; ?>

<table>
    <tr>
        <th>Date</th>
        <th>Title</th>
        <th>Description</th>
        <th>Province</th>
        <th>School</th>
        <?php if ($isTeacher) echo "<th>Actions</th>"; ?>
    </tr>
    <?php while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)): ?>
    <tr>
        <form method="POST">
            <td><input type="date" name="date" value="<?= $row['eventDate']->format('Y-m-d') ?>" <?= !$isTeacher ? 'readonly' : '' ?>></td>
            <td><input type="text" name="title" value="<?= htmlspecialchars($row['eventTitle']) ?>" <?= !$isTeacher ? 'readonly' : '' ?>></td>
            <td><textarea name="description" <?= !$isTeacher ? 'readonly' : '' ?>><?= htmlspecialchars($row['eventDescription']) ?></textarea></td>
            <td><?= htmlspecialchars($row['province'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['school'] ?? '-') ?></td>
            <?php if ($isTeacher): ?>
            <td class="actions">
                <input type="hidden" name="eventID" value="<?= $row['eventID'] ?>">
                <button type="submit" name="update" class="btn">Update</button>
                <button type="submit" name="delete" class="btn" onclick="return confirm('Are you sure?')">Delete</button>
            </td>
            <?php endif; ?>
        </form>
    </tr>
    <?php endwhile; ?>
</table>

<a href="dashboard.php" class="back-link">← About Us</a>

</body>
</html>
