<?php
// session_start(); // Uncomment if not already active
require 'connect.php';
$conn = connectToDatabase();

$role = strtolower($_SESSION['role']);
$userName = $_SESSION['userName'];
$userSchool = $_SESSION['school'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $id = intval($_POST['id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (in_array($role, ['learner', 'parent'])) {
        if ($action === 'add' && $message !== '') {
            sqlsrv_query($conn, "INSERT INTO testimonials (school, sender_email, role, message, status, created_at) VALUES (?, ?, ?, ?, 'pending', GETDATE())", [$userSchool, $userName, $role, $message]);
        } elseif ($action === 'edit' && $id && $message !== '') {
            sqlsrv_query($conn, "UPDATE testimonials SET message=?, status='pending', edited_at=GETDATE() WHERE id=? AND sender_email=?", [$message, $id, $userName]);
        } elseif ($action === 'delete' && $id) {
            sqlsrv_query($conn, "DELETE FROM testimonials WHERE id=? AND sender_email=?", [$id, $userName]);
        }
    }

    if ($role === 'teacher' && $id) {
        $approver = $userName;
        if ($action === 'approve') {
            sqlsrv_query($conn, "UPDATE testimonials SET status='approved', approved_at=GETDATE(), approved_by=? WHERE id=?", [$approver, $id]);
        } elseif ($action === 'reject') {
            sqlsrv_query($conn, "UPDATE testimonials SET status='rejected', approved_at=GETDATE(), approved_by=? WHERE id=?", [$approver, $id]);
        }
    }

    header("Location: dashboard.php?page=testimonials");
    exit();
}

// Fetch testimonials
if ($role === 'teacher') {
    $stmt = sqlsrv_query($conn, "SELECT * FROM testimonials WHERE school=? ORDER BY created_at DESC", [$userSchool]);
} else {
    $stmt = sqlsrv_query($conn, "SELECT * FROM testimonials WHERE school=? AND (status='approved' OR sender_email=?) ORDER BY created_at DESC", [$userSchool, $userName]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Testimonials</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: url('images/img13.jpg') no-repeat center center fixed;
      background-size: cover;
      margin: 0;
      padding: 40px;
    }
    .container {
      max-width: 900px;
      margin: auto;
      background: rgba(255, 255, 255, 0.97);
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.2);
    }
    h2 {
      color: #004aad;
      text-align: center;
      margin-bottom: 30px;
    }
    form textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #aaa;
      border-radius: 6px;
      resize: vertical;
      margin-bottom: 10px;
    }
    .testimonial {
      border-left: 5px solid #004aad;
      padding: 15px 20px;
      margin-bottom: 25px;
      border-radius: 6px;
      background-color: #f5f5f5;
    }
    .testimonial.approved { background-color: #e6ffed; }
    .testimonial.pending { background-color: #fff9e6; }
    .testimonial.rejected { background-color: #ffe6e6; }

    .meta {
      font-size: 0.9em;
      color: #666;
      margin-bottom: 10px;
    }
    .actions {
      margin-top: 10px;
    }
    .btn {
      background-color: #004aad;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 5px;
      cursor: pointer;
      margin-right: 5px;
    }
    .btn:hover {
      background-color: #00337a;
    }
    .btn-delete {
      background-color: crimson;
    }
    .btn-delete:hover {
      background-color: darkred;
    }
    .btn-approve {
      background-color: #28a745;
    }
    .btn-reject {
      background-color: #dc3545;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>📢 Testimonials - <?= ucfirst($role); ?></h2>

    <?php if (in_array($role, ['learner', 'parent'])): ?>
      <form method="post">
        <textarea name="message" rows="4" placeholder="Share your testimonial..." required></textarea>
        <input type="hidden" name="action" value="add">
        <button class="btn" type="submit">➕ Submit Testimonial</button>
      </form>
      <hr />
    <?php endif; ?>

    <?php if ($role === 'teacher'): ?>
      <p>You can approve or decline pending testimonials from learners and parents at your school.</p>
    <?php endif; ?>

    <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
      <?php
        $status = strtolower($row['status']);
        $isOwner = $row['sender_email'] === $userName;
        $dateCreated = $row['created_at'] ? $row['created_at']->format("Y-m-d H:i") : '';
        $dateEdited = $row['edited_at'] ? $row['edited_at']->format("Y-m-d H:i") : '';
        $dateApproved = $row['approved_at'] ? $row['approved_at']->format("Y-m-d H:i") : '';
      ?>
      <div class="testimonial <?= $status ?>">
        <div class="meta">
          <strong><?= htmlspecialchars($row['sender_email']) ?> (<?= htmlspecialchars($row['role']) ?>)</strong><br>
          Submitted: <?= $dateCreated ?>
          <?php if ($dateEdited): ?><br>Edited: <?= $dateEdited ?><?php endif; ?>
          <?php if ($status !== 'pending' && $role === 'teacher'): ?>
            <br><?= ucfirst($status) ?> by <?= htmlspecialchars($row['approved_by']) ?> on <?= $dateApproved ?>
          <?php endif; ?>
        </div>
        <div class="message"><?= nl2br(htmlspecialchars($row['message'])) ?></div>

        <div class="actions">
          <?php if ($role === 'teacher' && $status === 'pending'): ?>
            <form method="post" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button class="btn btn-approve" type="submit" name="action" value="approve">✅ Approve</button>
            </form>
            <form method="post" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button class="btn btn-reject" type="submit" name="action" value="reject">❌ Decline</button>
            </form>

          <?php elseif ($isOwner && in_array($role, ['learner', 'parent'])): ?>
            <form method="post">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <textarea name="message" rows="3" required><?= htmlspecialchars($row['message']) ?></textarea>
              <button class="btn" type="submit" name="action" value="edit">✏️ Save Edit</button>
              <button class="btn btn-delete" type="submit" name="action" value="delete" onclick="return confirm('Are you sure to delete this testimonial?')">🗑️ Delete</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</body>
</html>
