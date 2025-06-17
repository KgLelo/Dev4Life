<?php
require 'connect.php';
$conn = connectToDatabase();

$meetingId = intval($_GET['meeting_id']);
$username = $_GET['user'];
$response = $_GET['response'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = $_POST['reason'];
    sqlsrv_query($conn, "INSERT INTO meeting_responses (meeting_id, user_name, response, reason) VALUES (?, ?, ?, ?)", [
        $meetingId, $username, $response, $reason
    ]);
    echo "Response recorded. Thank you!";
    exit();
}
?>

<form method="POST">
    <h3>Reason for <?= $response ?> (optional):</h3>
    <textarea name="reason"></textarea>
    <br>
    <button type="submit">Submit</button>
</form>
