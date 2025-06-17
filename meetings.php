<?php
session_start();
if (!isset($_SESSION['userName']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

require 'connect.php';
$conn = connectToDatabase();

$role = strtolower($_SESSION['role']);
$userName = $_SESSION['userName'];
$isTeacher = $role === 'teacher';
$message = "";

// Email and SMS sending functions
function sendEmail($to, $subject, $body) {
    echo "<!-- Email to $to: $subject\n$body -->";
}

function sendSMS($number, $message) {
    echo "<!-- SMS to $number: $message -->";
}

// Create or update meeting
if ($isTeacher && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $meeting_date = $_POST['meeting_date'];
    $meeting_time = $_POST['meeting_time'];
    $link = $_POST['link'];

    $parentName = $_POST['parent_name'];
    $parentEmail = $_POST['parent_email'];
    $parentPhone = $_POST['parent_phone'];

    $learnerName = $_POST['learner_name'];
    $learnerEmail = $_POST['learner_email'];
    $learnerPhone = $_POST['learner_phone'];

    $stmt = sqlsrv_query($conn, "INSERT INTO meetings (title, meeting_date, meeting_time, link, created_by) OUTPUT INSERTED.id VALUES (?, ?, ?, ?, ?)", [
        $title, $meeting_date, $meeting_time, $link, $userName
    ]);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $meetingId = $row['id'];
        $message = "Meeting created and invitations sent.";

        $participants = [
            ['name' => $parentName, 'email' => $parentEmail, 'phone' => $parentPhone],
            ['name' => $learnerName, 'email' => $learnerEmail, 'phone' => $learnerPhone]
        ];

        foreach ($participants as $p) {
            $acceptUrl = "http://yourdomain.com/respond.php?meeting_id=$meetingId&user=" . urlencode($p['name']) . "&response=accepted";
            $declineUrl = "http://yourdomain.com/respond.php?meeting_id=$meetingId&user=" . urlencode($p['name']) . "&response=declined";

            $msg = "You're invited to a meeting:\nTitle: $title\nDate: $meeting_date\nTime: $meeting_time\nLink: $link\nAccept: $acceptUrl\nDecline: $declineUrl";

            sendEmail($p['email'], "Meeting Invite: $title", nl2br($msg));
            sendSMS($p['phone'], $msg);
        }
    } else {
        $message = "Failed to create meeting.";
    }
}

// Handle Accept/Decline from links
if (isset($_GET['meeting_id']) && isset($_GET['user']) && isset($_GET['response'])) {
    $meetingId = intval($_GET['meeting_id']);
    $username = $_GET['user'];
    $response = $_GET['response'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $response === 'accepted') {
        $stmt = sqlsrv_query($conn, "INSERT INTO meeting_responses (meeting_id, user_name, response, reason) VALUES (?, ?, ?, ?)", [
            $meetingId, $username, $response, $reason
        ]);
        $message = $stmt ? "Response recorded. Thank you." : "Failed to save response.";
    }
}

// Delete Meeting
if ($isTeacher && isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    sqlsrv_query($conn, "DELETE FROM meetings WHERE id=?", [$deleteId]);
    header("Location: meetings.php");
    exit();
}

// Fetch meetings
$meetings = [];
$stmt = sqlsrv_query($conn, "SELECT * FROM meetings ORDER BY meeting_date ASC");
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $meetings[] = $row;
    }
} else {
    die("Error loading meetings: " . print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Meetings - WeConnect</title>
    <style>
        body { font-family: Arial; background: #f8f9fa; }
        .container { width: 90%; margin: 2em auto; background: white; padding: 20px; border-radius: 8px; }
        input, select, textarea { width: 100%; margin: 5px 0; padding: 8px; }
        .btn { background: #004aad; color: white; padding: 10px 20px; border: none; margin-top: 10px; }
        .btn:hover { background: #003380; }
        .message { background: #e0ffe0; padding: 10px; margin-bottom: 10px; }
        .meeting { border-top: 1px solid #ccc; margin-top: 20px; padding-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>📅 Meetings</h2>
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($isTeacher): ?>
        <form method="POST">
            <h3>Create Meeting</h3>
            <input type="text" name="title" placeholder="Meeting Title" required>
            <input type="date" name="meeting_date" required>
            <input type="time" name="meeting_time" required>
            <input type="url" name="link" placeholder="Meeting Link" required>

            <h4>Parent Info</h4>
            <input type="text" name="parent_name" placeholder="Parent Full Name" required>
            <input type="email" name="parent_email" placeholder="Parent Email" required>
            <input type="text" name="parent_phone" placeholder="Parent Phone Number" required>

            <h4>Learner Info</h4>
            <input type="text" name="learner_name" placeholder="Learner Full Name" required>
            <input type="email" name="learner_email" placeholder="Learner Email" required>
            <input type="text" name="learner_phone" placeholder="Learner Phone Number" required>

            <button class="btn" type="submit">Send Invitations</button>
        </form>
    <?php endif; ?>

    <?php foreach ($meetings as $m): ?>
        <div class="meeting">
            <h4><?php echo htmlspecialchars($m['title']); ?></h4>
            <p><strong>Date:</strong> <?php echo htmlspecialchars(is_a($m['meeting_date'], 'DateTime') ? $m['meeting_date']->format('Y-m-d') : $m['meeting_date']); ?></p>
            <p><strong>Time:</strong> <?php echo htmlspecialchars(is_a($m['meeting_time'], 'DateTime') ? $m['meeting_time']->format('H:i') : $m['meeting_time']); ?></p>
            <p><strong>Link:</strong> <a href="<?= htmlspecialchars($m['link']) ?>" target="_blank"><?= htmlspecialchars($m['link']) ?></a></p>
            <p><strong>Created by:</strong> <?= htmlspecialchars($m['created_by']) ?></p>

            <?php if (!$isTeacher): ?>
                <form method="post">
                    <input type="hidden" name="meeting_id" value="<?= $m['id'] ?>">
                    <div>
                        <label><input type="radio" name="response" value="accepted" required> Accept</label>
                        <label><input type="radio" name="response" value="declined" required> Decline</label>
                    </div>
                    <textarea name="reason" placeholder="Reason (if declining)..."></textarea>
                    <button class="btn" type="submit">Respond</button>
                </form>
            <?php else: ?>
                <a class="btn" href="?delete=<?= $m['id'] ?>" onclick="return confirm('Delete this meeting?')">Delete</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
