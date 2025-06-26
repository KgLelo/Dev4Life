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

// Fetch provinces
$provinceList = [];
$provinceStmt = sqlsrv_query($conn, "SELECT DISTINCT province FROM schools");
while ($row = sqlsrv_fetch_array($provinceStmt, SQLSRV_FETCH_ASSOC)) {
    $provinceList[] = $row['province'];
}

// All schools grouped by province
$allSchools = [];
$schoolQuery = sqlsrv_query($conn, "SELECT schoolName, province FROM schools");
while ($row = sqlsrv_fetch_array($schoolQuery, SQLSRV_FETCH_ASSOC)) {
    $allSchools[$row['province']][] = $row['schoolName'];
}

$selectedProvince = $_POST['province'] ?? '';
$selectedSchool = $_POST['school'] ?? '';

// Fetch schools for selected province
$schoolList = [];
if ($selectedProvince) {
    $stmt = sqlsrv_query($conn, "SELECT schoolName FROM schools WHERE province = ?", [$selectedProvince]);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
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
        $province = $_POST['province'];
        $school = $_POST['school'];

        $sql = "UPDATE SchoolEvents SET eventTitle=?, eventDate=?, eventDescription=?, province=?, school=? WHERE eventID=?";
        sqlsrv_query($conn, $sql, [$title, $date, $desc, $province, $school, $id]);
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

<div style="padding: 20px;">
    <h2 style="color:#004aad; text-align:center;">🎉 School Events</h2>

    <?php if ($isTeacher): ?>
    <div style="max-width:700px; margin:auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); margin-bottom:30px;">
        <h3 style="color:#004aad;">➕ Add New Event</h3>
        <form method="POST">
            <label>Select Province</label>
            <select name="province" onchange="this.form.submit()" required style="width:100%; padding:10px; margin-bottom:10px;">
                <option value="">-- Choose Province --</option>
                <?php foreach ($provinceList as $prov): ?>
                    <option value="<?= $prov ?>" <?= $selectedProvince == $prov ? 'selected' : '' ?>><?= $prov ?></option>
                <?php endforeach; ?>
            </select>

            <label>Select School</label>
            <select name="school" required style="width:100%; padding:10px; margin-bottom:10px;">
                <option value="">-- Choose School --</option>
                <?php foreach ($schoolList as $sch): ?>
                    <option value="<?= $sch ?>" <?= $selectedSchool == $sch ? 'selected' : '' ?>><?= $sch ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="title" placeholder="Event Title" required style="width:100%; padding:10px; margin-bottom:10px;">
            <input type="date" name="date" required style="width:100%; padding:10px; margin-bottom:10px;">
            <textarea name="description" placeholder="Event Description" required style="width:100%; padding:10px; margin-bottom:10px;"></textarea>
            <button type="submit" name="add" style="background:#004aad; color:white; padding:10px 20px; border:none; border-radius:5px;">Add Event</button>
        </form>
    </div>
    <?php endif; ?>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; background:#fff; box-shadow:0 2px 10px rgba(0,0,0,0.05); border-radius:8px;">
            <thead>
                <tr style="background:#004aad; color:white;">
                    <th style="padding:10px;">Date</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Province</th>
                    <th>School</th>
                    <?php if ($isTeacher): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <form method="POST">
                        <td><input type="date" name="date" value="<?= $row['eventDate']->format('Y-m-d') ?>" style="padding:6px;"></td>
                        <td><input type="text" name="title" value="<?= htmlspecialchars($row['eventTitle']) ?>" style="padding:6px;"></td>
                        <td><textarea name="description" style="padding:6px;"><?= htmlspecialchars($row['eventDescription']) ?></textarea></td>
                        <td>
                            <select name="province" style="padding:6px;">
                                <option value="">-- Select --</option>
                                <?php foreach ($provinceList as $prov): ?>
                                    <option value="<?= $prov ?>" <?= ($row['province'] == $prov) ? 'selected' : '' ?>><?= $prov ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="school" style="padding:6px;">
                                <option value="">-- Select --</option>
                                <?php
                                $currentProv = $row['province'];
                                $schoolsForProv = $allSchools[$currentProv] ?? [];
                                foreach ($schoolsForProv as $schoolName): ?>
                                    <option value="<?= $schoolName ?>" <?= ($row['school'] == $schoolName) ? 'selected' : '' ?>><?= $schoolName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <?php if ($isTeacher): ?>
                        <td>
                            <input type="hidden" name="eventID" value="<?= $row['eventID'] ?>">
                            <button type="submit" name="update" style="background:#004aad; color:white; border:none; padding:5px 10px; margin:3px; border-radius:5px;">Update</button>
                            <button type="submit" name="delete" onclick="return confirm('Are you sure?')" style="background:red; color:white; border:none; padding:5px 10px; margin:3px; border-radius:5px;">Delete</button>
                        </td>
                        <?php endif; ?>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
