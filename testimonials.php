<?php
require 'connect.php';
$conn = connectToDatabase();

$role      = strtolower($_SESSION['role']);
$userName  = $_SESSION['userName'];
$userSchool = $_SESSION['school'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $id      = intval($_POST['id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (in_array($role, ['learner', 'parent'])) {
        if ($action === 'add' && $message !== '') {
            sqlsrv_query(
                $conn,
                "INSERT INTO testimonials (school, sender_email, role, message, status, created_at) VALUES (?, ?, ?, ?, 'pending', GETDATE())",
                [$userSchool, $userName, $role, $message]
            );
        } elseif ($action === 'edit' && $id && $message !== '') {
            sqlsrv_query(
                $conn,
                "UPDATE testimonials SET message=?, status='pending', edited_at=GETDATE() WHERE id=? AND sender_email=?",
                [$message, $id, $userName]
            );
        } elseif ($action === 'delete' && $id) {
            sqlsrv_query(
                $conn,
                "DELETE FROM testimonials WHERE id=? AND sender_email=?",
                [$id, $userName]
            );
        }
    }

    if ($role === 'teacher' && $id) {
        $approver = $userName;
        if ($action === 'approve') {
            sqlsrv_query(
                $conn,
                "UPDATE testimonials SET status='approved', approved_at=GETDATE(), approved_by=? WHERE id=?",
                [$approver, $id]
            );
        } elseif ($action === 'reject') {
            sqlsrv_query(
                $conn,
                "UPDATE testimonials SET status='rejected', approved_at=GETDATE(), approved_by=? WHERE id=?",
                [$approver, $id]
            );
        }
    }

    // Redirect to refresh page and avoid resubmission on reload
    header("Location: dashboard.php?page=testimonials");
    exit();
}

// Fetch testimonials
if ($role === 'teacher') {
    $stmt = sqlsrv_query(
        $conn,
        "SELECT * FROM testimonials WHERE school=? ORDER BY created_at DESC",
        [$userSchool]
    );
} else {
    $stmt = sqlsrv_query(
        $conn,
        "SELECT * FROM testimonials WHERE school=? AND (status='approved' OR sender_email=?) ORDER BY created_at DESC",
        [$userSchool, $userName]
    );
}
?>

<div style="max-width:900px; margin:auto;">

    <h2 style="color:#004aad; margin-bottom:20px;">📢 Testimonials - <?= ucfirst(htmlspecialchars($role)); ?></h2>

    <?php if (in_array($role, ['learner', 'parent'])): ?>
        <form method="post" style="margin-bottom:30px;">
            <textarea
                name="message"
                rows="4"
                style="width:100%; padding:10px; border:1px solid #aaa; border-radius:6px; resize:vertical;"
                placeholder="Share your testimonial..."
                required
            ></textarea>
            <input type="hidden" name="action" value="add" />
            <button
                type="submit"
                style="background-color:#004aad; color:white; padding:8px 16px; border:none; border-radius:6px; cursor:pointer; margin-top:10px;"
            >
                ➕ Submit Testimonial
            </button>
        </form>
        <hr />
    <?php endif; ?>

    <?php if ($role === 'teacher'): ?>
        <p style="font-style:italic; color:#555; margin-bottom:25px;">
            You can approve or decline pending testimonials from learners and parents at your school.
        </p>
    <?php endif; ?>

    <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
        $status       = strtolower($row['status']);
        $isOwner      = $row['sender_email'] === $userName;
        $dateCreated  = $row['created_at'] ? $row['created_at']->format("Y-m-d H:i") : '';
        $dateEdited   = $row['edited_at'] ? $row['edited_at']->format("Y-m-d H:i") : '';
        $dateApproved = $row['approved_at'] ? $row['approved_at']->format("Y-m-d H:i") : '';

        // Status colors
        $bgColor = match($status) {
            'approved' => '#e6ffed',
            'pending'  => '#fff9e6',
            'rejected' => '#ffe6e6',
            default    => '#f5f5f5',
        };
        $borderColor = match($status) {
            'approved' => '#28a745',
            'pending'  => '#ffc107',
            'rejected' => '#dc3545',
            default    => '#ccc',
        };
    ?>
        <div
            style="background-color: <?= $bgColor ?>; border-left: 6px solid <?= $borderColor ?>; padding: 15px 20px; margin-bottom: 25px; border-radius: 6px;"
        >
            <div style="font-size: 0.9em; color: #666; margin-bottom: 8px;">
                <strong><?= htmlspecialchars($row['sender_email']) ?></strong> (<?= htmlspecialchars($row['role']) ?>) &nbsp;&bull;&nbsp;
                Submitted: <?= $dateCreated ?>
                <?php if ($dateEdited): ?>
                    &nbsp;&bull;&nbsp; Edited: <?= $dateEdited ?>
                <?php endif; ?>
                <?php if ($status !== 'pending' && $role === 'teacher'): ?>
                    &nbsp;&bull;&nbsp; <?= ucfirst($status) ?> by <?= htmlspecialchars($row['approved_by']) ?> on <?= $dateApproved ?>
                <?php endif; ?>
            </div>

            <div style="white-space: pre-wrap; margin-bottom: 10px;"><?= htmlspecialchars($row['message']) ?></div>

            <div>
                <?php if ($role === 'teacher' && $status === 'pending'): ?>
                    <form method="post" style="display:inline-block; margin-right:10px;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                        <button
                            type="submit"
                            name="action"
                            value="approve"
                            style="background-color:#28a745; color:#fff; border:none; padding:6px 12px; border-radius:5px; cursor:pointer;"
                        >
                            ✅ Approve
                        </button>
                    </form>

                    <form method="post" style="display:inline-block;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                        <button
                            type="submit"
                            name="action"
                            value="reject"
                            style="background-color:#dc3545; color:#fff; border:none; padding:6px 12px; border-radius:5px; cursor:pointer;"
                        >
                            ❌ Decline
                        </button>
                    </form>

                <?php elseif ($isOwner && in_array($role, ['learner', 'parent'])): ?>
                    <form method="post" style="margin-top:10px;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                        <textarea
                            name="message"
                            rows="3"
                            required
                            style="width: 100%; padding: 8px; border: 1px solid #aaa; border-radius: 5px; resize: vertical; margin-bottom: 8px;"
                        ><?= htmlspecialchars($row['message']) ?></textarea>
                        <button
                            type="submit"
                            name="action"
                            value="edit"
                            style="background-color:#004aad; color:#fff; border:none; padding:6px 12px; border-radius:5px; cursor:pointer; margin-right:8px;"
                        >
                            ✏️ Save Edit
                        </button>
                        <button
                            type="submit"
                            name="action"
                            value="delete"
                            onclick="return confirm('Are you sure to delete this testimonial?')"
                            style="background-color:crimson; color:#fff; border:none; padding:6px 12px; border-radius:5px; cursor:pointer;"
                        >
                            🗑️ Delete
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>

</div>
