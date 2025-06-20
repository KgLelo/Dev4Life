<?php
require_once 'connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = connectToDatabase();

$role = strtolower($_SESSION['role'] ?? '');
$userName = $_SESSION['userName'] ?? '';
$school = $_SESSION['school'] ?? '';
$grade = $_SESSION['grade'] ?? '';

if (!$userName || !$role) {
    die("Unauthorized access. Please login.");
}

// Handle meeting request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_meeting'])) {
    $teacher = $_POST['teacher'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $topic = $_POST['topic'] ?? '';

    if ($teacher && $date && $time && $topic) {
        $stmt = sqlsrv_query($conn, "INSERT INTO meetings 
            (school, grade, requester, role, teacher, topic, date, time, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')", [
                $school, $grade, $userName, $role, $teacher, $topic, $date, $time
        ]);

        echo $stmt
            ? "<p style='color:green;'>✅ Meeting request sent to <strong>$teacher</strong>.</p>"
            : "<p style='color:red;'>❌ Failed to send request.</p>";
    }
}

// Handle approval/decline by teacher
if ($role === 'teacher' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decision'])) {
    $decision = $_POST['decision'];
    $meeting_id = $_POST['meeting_id'];
    $status = $decision === 'approve' ? 'approved' : 'declined';

    sqlsrv_query($conn, "UPDATE meetings SET status = ? WHERE id = ? AND teacher = ?", [
        $status, $meeting_id, $userName
    ]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Meeting</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 30px;
            color: #333;
        }
        .container {
            max-width: 950px;
            margin: auto;
        }
        .form-section, .requests {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        h3 {
            color: #004aad;
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #004aad;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #003088;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        table th {
            background: #004aad;
            color: white;
        }
        .action-buttons form {
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<body>
<div class="container">

<?php if (in_array($role, ['learner', 'parent'])): ?>
    <div class="form-section">
        <h3>📅 Request a Meeting with a Teacher</h3>
        <form method="post">
            <label for="teacher">Teacher Email</label>
            <input type="text" id="teacher" name="teacher" required>

            <label for="date">Date</label>
            <input type="date" id="date" name="date" required>

            <label for="time">Time</label>
            <input type="time" id="time" name="time" required>

            <label for="topic">Meeting Topic</label>
            <textarea id="topic" name="topic" rows="4" required></textarea>

            <button type="submit" name="schedule_meeting">Send Request</button>
        </form>
    </div>
<?php endif; ?>

<div class="requests">
    <h3><?= $role === 'teacher' ? '📝 Pending Meeting Requests' : '📋 Your Meeting Requests' ?></h3>
    <table>
        <tr>
            <th>Requester</th>
            <th>Role</th>
            <th>Topic</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <?php if ($role === 'teacher') echo '<th>Action</th>'; ?>
        </tr>

        <?php
        $query = $role === 'teacher'
            ? sqlsrv_query($conn, "SELECT * FROM meetings WHERE teacher = ? ORDER BY date DESC", [$userName])
            : sqlsrv_query($conn, "SELECT * FROM meetings WHERE requester = ? ORDER BY date DESC", [$userName]);

        while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)):
        ?>
            <tr>
                <td><?= htmlspecialchars($row['requester']) ?></td>
                <td><?= ucfirst(htmlspecialchars($row['role'])) ?></td>
                <td><?= htmlspecialchars($row['topic']) ?></td>
                <td><?= $row['date']->format('Y-m-d') ?></td>
                <td><?= $row['time']->format('H:i') ?></td>
                <td><?= ucfirst($row['status']) ?></td>

                <?php if ($role === 'teacher' && $row['status'] === 'pending'): ?>
                <td class="action-buttons">
                    <form method="post">
                        <input type="hidden" name="meeting_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="decision" value="approve">✅ Approve</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="meeting_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="decision" value="reject">❌ Reject</button>
                    </form>
                </td>
                <?php elseif ($role === 'teacher'): ?>
                <td>-</td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</div>
</body>
</html>
