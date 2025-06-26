<?php
require 'connect.php';
$conn = connectToDatabase();

$role      = strtolower($_SESSION['role']);
$userName  = $_SESSION['userName'];
$school    = $_SESSION['school'] ?? '';

// Handle Form Actions (Teacher Only)
if ($role === 'teacher') {
    if (isset($_POST['add'])) {
        $title   = trim($_POST['title']);
        $content = trim($_POST['content']);
        if ($title !== '' && $content !== '') {
            $sql = "INSERT INTO news (title, content, school, created_by) VALUES (?, ?, ?, ?)";
            $stmt = sqlsrv_prepare($conn, $sql, [$title, $content, $school, $userName]);
            sqlsrv_execute($stmt);
        }
    }
    if (isset($_POST['update'])) {
        $id      = intval($_POST['id']);
        $title   = trim($_POST['title']);
        $content = trim($_POST['content']);
        if ($id > 0 && $title !== '' && $content !== '') {
            $sql = "UPDATE news SET title = ?, content = ? WHERE id = ? AND created_by = ?";
            $stmt = sqlsrv_prepare($conn, $sql, [$title, $content, $id, $userName]);
            sqlsrv_execute($stmt);
        }
    }
    if (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($id > 0) {
            $sql = "DELETE FROM news WHERE id = ? AND created_by = ?";
            $stmt = sqlsrv_prepare($conn, $sql, [$id, $userName]);
            sqlsrv_execute($stmt);
        }
    }
}

// Fetch news for current school
$query = "SELECT * FROM news WHERE school = ? ORDER BY created_at DESC";
$stmt = sqlsrv_query($conn, $query, [$school]);
?>

<div style="max-width:900px; margin:auto;">

    <h2 style="color:#004aad; margin-bottom: 25px;">📢 School News & Announcements</h2>

    <?php if ($role === 'teacher'): ?>
        <form method="POST" style="background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.05); margin-bottom:30px;">
            <h3 style="margin-top:0; color:#004aad;">📝 Add New Announcement</h3>
            <input
                type="text"
                name="title"
                placeholder="News Title"
                required
                style="width:100%; padding:10px; margin:8px 0 15px 0; border:1px solid #ccc; border-radius:6px; font-size:15px;"
            />
            <textarea
                name="content"
                rows="4"
                placeholder="News Content..."
                required
                style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:6px; font-size:15px; resize:vertical;"
            ></textarea>
            <button
                type="submit"
                name="add"
                style="background:#004aad; color:#fff; padding:10px 20px; border:none; border-radius:4px; cursor:pointer; font-weight:bold;"
            >
                Publish News
            </button>
        </form>
    <?php endif; ?>

    <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
        <div style="background:#fff; padding:20px; margin-bottom:20px; border-left:5px solid #004aad; border-radius:6px; box-shadow:0 1px 4px rgba(0,0,0,0.08);">
            <h3 style="margin-top:0; margin-bottom:10px; color:#222;"><?= htmlspecialchars($row['title']) ?></h3>
            <p style="margin-bottom:10px; line-height:1.6; white-space: pre-wrap;"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
            <small style="color:#666;">
                📅 <?= $row['created_at']->format('Y-m-d H:i') ?> | 👤 <?= htmlspecialchars($row['created_by']) ?>
            </small>

            <?php if ($role === 'teacher' && $row['created_by'] === $userName): ?>
                <form method="POST" style="margin-top:15px; background:#f9f9f9; padding:15px; border-radius:5px;">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                    <input
                        type="text"
                        name="title"
                        value="<?= htmlspecialchars($row['title']) ?>"
                        required
                        style="width:100%; padding:10px; margin-bottom:12px; border:1px solid #ccc; border-radius:6px; font-size:15px;"
                    />
                    <textarea
                        name="content"
                        rows="3"
                        required
                        style="width:100%; padding:10px; margin-bottom:12px; border:1px solid #ccc; border-radius:6px; font-size:15px; resize:vertical;"
                    ><?= htmlspecialchars($row['content']) ?></textarea>
                    <button
                        type="submit"
                        name="update"
                        style="background:#004aad; color:#fff; padding:8px 16px; border:none; border-radius:5px; cursor:pointer; font-weight:bold;"
                    >
                        Update
                    </button>
                    <button
                        type="submit"
                        name="delete"
                        onclick="return confirm('Delete this announcement?')"
                        style="background:#d60000; color:#fff; padding:8px 16px; border:none; border-radius:5px; cursor:pointer; font-weight:bold; margin-left:8px;"
                    >
                        Delete
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

</div>
