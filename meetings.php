<?php
require_once 'connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$conn = connectToDatabase();

$role = strtolower($_SESSION['role'] ?? '');
$userName = $_SESSION['userName'] ?? '';

if (!$userName || !$role) {
    die("<p style='color:red;'>Unauthorized access. Please login.</p>");
}

// === Schedule Meeting Request ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_meeting'])) {
    $teacher = $_POST['teacher'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $topic = $_POST['topic'] ?? '';

    if ($teacher && $date && $time && $topic) {
        $infoStmt = sqlsrv_query($conn, "SELECT school, grade FROM LearnerTable WHERE userName = ?", [$userName]);
        if ($info = sqlsrv_fetch_array($infoStmt, SQLSRV_FETCH_ASSOC)) {
            $school = $info['school'];
            $grade = $info['grade'];

            $stmt = sqlsrv_query($conn, "INSERT INTO meetings 
                (school, grade, requester, role, teacher, topic, date, time, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')", 
                [$school, $grade, $userName, $role, $teacher, $topic, $date, $time]);

            echo $stmt
                ? "<p style='color:green; font-weight:bold;'>✅ Meeting request sent to <strong>$teacher</strong>.</p>"
                : "<p style='color:red;'>❌ Failed to send request.</p>";
        } else {
            echo "<p style='color:red;'>❌ Unable to find school/grade for requester.</p>";
        }
    }
}

// === Teacher Approval/Decline ===
if ($role === 'teacher' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decision'])) {
    $decision = $_POST['decision'];
    $meeting_id = $_POST['meeting_id'];
    $status = $decision === 'approve' ? 'approved' : 'declined';

    sqlsrv_query($conn, "UPDATE meetings SET status = ? WHERE id = ? AND teacher = ?", [
        $status, $meeting_id, $userName
    ]);
}
?>

<!-- MAIN DASHBOARD CONTENT -->
<div style="padding: 20px;">

    <?php if (in_array($role, ['learner', 'parent'])): ?>
    <div style="background:#fff; padding:20px; border-radius:10px; margin-bottom:30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="color:#004aad;">📅 Request a Meeting with a Teacher</h3>
        <form method="post">
            <label for="teacher">Teacher Email</label>
            <input type="text" name="teacher" required placeholder="e.g. johndoe@school.com" style="width:100%; padding:10px; margin-bottom:10px;">

            <label for="date">Date</label>
            <input type="date" name="date" required style="width:100%; padding:10px; margin-bottom:10px;">

            <label for="time">Time</label>
            <input type="time" name="time" required style="width:100%; padding:10px; margin-bottom:10px;">

            <label for="topic">Topic</label>
            <textarea name="topic" rows="4" required style="width:100%; padding:10px; margin-bottom:10px;"></textarea>

            <button type="submit" name="schedule_meeting" style="background:#004aad; color:white; padding:10px 20px; border:none; border-radius:5px;">Send Request</button>
        </form>
    </div>
    <?php endif; ?>

    <div style="background:#fff; padding:20px; border-radius:10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="color:#004aad;">
            <?= $role === 'teacher' ? '📝 Pending Meeting Requests' : '📋 Your Meeting Requests' ?>
        </h3>

        <table style="width:100%; border-collapse: collapse; margin-top:15px;">
            <thead>
                <tr style="background:#004aad; color:white;">
                    <th style="padding:10px;">Requester</th>
                    <th>Role</th>
                    <th>Topic</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <?php if ($role === 'teacher') echo '<th>Action</th>'; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = $role === 'teacher'
                    ? sqlsrv_query($conn, "SELECT * FROM meetings WHERE teacher = ? ORDER BY date DESC", [$userName])
                    : sqlsrv_query($conn, "SELECT * FROM meetings WHERE requester = ? ORDER BY date DESC", [$userName]);

                while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)):
                ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding:8px;"><?= htmlspecialchars($row['requester']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($row['role'])) ?></td>
                    <td><?= htmlspecialchars($row['topic']) ?></td>
                    <td><?= date_format($row['date'], 'Y-m-d') ?></td>
                    <td><?= date_format($row['time'], 'H:i') ?></td>
                    <td><strong><?= ucfirst($row['status']) ?></strong></td>

                    <?php if ($role === 'teacher' && $row['status'] === 'pending'): ?>
                    <td style="text-align:center;">
                        <form method="post" style="display:inline-block;">
                            <input type="hidden" name="meeting_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="decision" value="approve" style="background:green; color:white; padding:5px 10px; border:none; border-radius:4px;">✅ Approve</button>
                        </form>
                        <form method="post" style="display:inline-block;">
                            <input type="hidden" name="meeting_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="decision" value="reject" style="background:red; color:white; padding:5px 10px; border:none; border-radius:4px;">❌ Decline</button>
                        </form>
                    </td>
                    <?php elseif ($role === 'teacher'): ?>
                    <td>-</td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
