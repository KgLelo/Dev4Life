<?php
// session_start();
if (!isset($_SESSION['userName']) || !isset($_SESSION['role'])) {
    header("Location: login.html");
    exit();
}

require 'connect.php';
$conn = connectToDatabase();

$role = strtolower($_SESSION['role']);
$userName = $_SESSION['userName'];
$school = $_SESSION['school'] ?? '';

// === Handle Form Actions (Teacher Only) ===
if ($role === 'teacher') {
    // Add News
    if (isset($_POST['add'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $sql = "INSERT INTO news (title, content, school, created_by) VALUES (?, ?, ?, ?)";
        $stmt = sqlsrv_prepare($conn, $sql, [$title, $content, $school, $userName]);
        sqlsrv_execute($stmt);
    }

    // Update News
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $sql = "UPDATE news SET title = ?, content = ? WHERE id = ? AND created_by = ?";
        $stmt = sqlsrv_prepare($conn, $sql, [$title, $content, $id, $userName]);
        sqlsrv_execute($stmt);
    }

    // Delete News
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $sql = "DELETE FROM news WHERE id = ? AND created_by = ?";
        $stmt = sqlsrv_prepare($conn, $sql, [$id, $userName]);
        sqlsrv_execute($stmt);
    }
}

// === Fetch News for Current School ===
$query = "SELECT * FROM news WHERE school = ? ORDER BY created_at DESC";
$stmt = sqlsrv_query($conn, $query, [$school]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WeConnect - News</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f4f4;
            padding: 30px;
            color: #333;
        }

        h2 {
            color: #004aad;
            margin-bottom: 20px;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        textarea {
            resize: vertical;
        }

        button {
            background: #004aad;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #00337a;
        }

        .news-box {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #004aad;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }

        .news-box h3 {
            margin: 0 0 10px;
            color: #222;
        }

        .news-box p {
            margin: 0 0 10px;
            line-height: 1.6;
        }

        .news-box small {
            color: #666;
        }

        a.delete-btn {
            color: #d60000;
            font-weight: bold;
            margin-left: 10px;
            text-decoration: none;
        }

        a.delete-btn:hover {
            text-decoration: underline;
        }

        .edit-form {
            margin-top: 15px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }

        .edit-form input[type="text"],
        .edit-form textarea {
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

    <h2>📢 School News & Announcements</h2>

    <?php if ($role === 'teacher'): ?>
        <form method="POST">
            <h3>📝 Add New Announcement</h3>
            <input type="text" name="title" placeholder="News Title" required />
            <textarea name="content" placeholder="News Content..." rows="4" required></textarea>
            <button type="submit" name="add">Publish News</button>
        </form>
    <?php endif; ?>

    <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
        <div class="news-box">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
            <small>📅 <?= $row['created_at']->format('Y-m-d H:i') ?> | 👤 <?= $row['created_by'] ?></small>

            <?php if ($role === 'teacher' && $row['created_by'] === $userName): ?>
                <form method="POST" class="edit-form">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                    <input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>" required />
                    <textarea name="content" rows="3" required><?= htmlspecialchars($row['content']) ?></textarea>
                    <button type="submit" name="update">Update</button>
                    <a href="?delete=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this news item?')">Delete</a>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

</body>
</html>
