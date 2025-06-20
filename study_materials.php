<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userName']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

require 'connect.php';
$conn = connectToDatabase();

$userName = $_SESSION['userName'];
$role = strtolower($_SESSION['role']);
$isTeacher = $role === 'teacher';
$message = '';

// Upload
if ($isTeacher && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["material"])) {
    $grade = intval($_POST["grade"]);
    $title = $_POST["title"];
    $fileName = basename($_FILES["material"]["name"]);
    $targetDir = "uploads/";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $targetFile = $targetDir . time() . "_" . $fileName;

    if (move_uploaded_file($_FILES["material"]["tmp_name"], $targetFile)) {
        $stmt = sqlsrv_query($conn, "INSERT INTO study_materials (grade, title, filename, uploaded_by) VALUES (?, ?, ?, ?)", 
            [$grade, $title, $targetFile, $userName]);
        $message = $stmt ? "✅ Study material uploaded successfully!" : "❌ Database error.";
    } else {
        $message = "❌ File upload failed.";
    }
}

// Delete
if ($isTeacher && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = sqlsrv_query($conn, "SELECT filename FROM study_materials WHERE id = ?", [$id]);
    $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
    if ($row && file_exists($row['filename'])) {
        unlink($row['filename']);
    }
    sqlsrv_query($conn, "DELETE FROM study_materials WHERE id = ?", [$id]);
    header("Location: study_materials.php");
    exit();
}

// Fetch materials
$materials = [];
for ($g = 8; $g <= 12; $g++) {
    $stmt = sqlsrv_query($conn, "SELECT * FROM study_materials WHERE grade = ?", [$g]);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $materials[$g][] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Study Materials</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6fb;
            margin: 0;
            padding: 20px;
            background-image: url('images/img12.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
    
        }
        h1, h2, h3 {
            color: #004aad;
            text-align: center;
        }
        .container {
            max-width: 1100px;
            margin: auto;
        }
        .upload-form, .materials {
            background: #fff;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #004aad;
            color: #fff;
            padding: 10px 15px;
            margin-top: 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #00357a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        a.download-link, a.delete-link {
            text-decoration: none;
            color: #004aad;
        }
        a.delete-link:hover {
            color: red;
        }
        .message {
            padding: 10px;
            background: #e0f7fa;
            color: #004aad;
            border: 1px solid #004aad;
            margin-top: 10px;
            border-radius: 5px;
        }
        .back-link {
            display: block;
            width: fit-content;
            margin: 20px auto 0;
            text-align: center;
            padding: 10px 20px;
            background: #004aad;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>📚 Study Materials</h1>

    <?php if ($isTeacher): ?>
    <div class="upload-form">
        <h2>Upload New Study Material</h2>
        <?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label for="grade">Select Grade:</label>
            <select name="grade" required>
                <?php for ($i = 8; $i <= 12; $i++): ?>
                    <option value="<?php echo $i; ?>">Grade <?php echo $i; ?></option>
                <?php endfor; ?>
            </select>

            <label for="title">Material Title:</label>
            <input type="text" name="title" required placeholder="e.g. Grade 10 Geography Summary">

            <label for="material">Choose File:</label>
            <input type="file" name="material" accept=".pdf,.doc,.docx" required>

            <button type="submit">Upload Material</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="materials">
        <h2>Available Materials</h2>
        <?php foreach ($materials as $grade => $list): ?>
            <div class="grade-block">
                <h3>📘 Grade <?php echo $grade; ?></h3>
                <?php if (!empty($list)): ?>
                    <table>
                        <tr>
                            <th>Title</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                            <th>Download</th>
                            <?php if ($isTeacher): ?><th>Action</th><?php endif; ?>
                        </tr>
                        <?php foreach ($list as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['title']); ?></td>
                            <td><?php echo htmlspecialchars($material['uploaded_by']); ?></td>
                            <td><?php echo date_format($material['uploaded_at'], 'Y-m-d'); ?></td>
                            <td><a class="download-link" href="<?php echo htmlspecialchars($material['filename']); ?>" download>Download</a></td>
                            <?php if ($isTeacher): ?>
                                <td><a class="delete-link" href="?delete=<?php echo $material['id']; ?>" onclick="return confirm('Are you sure to delete this material?')">Delete</a></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>No materials uploaded yet for Grade <?php echo $grade; ?>.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="dashboard.php" class="back-link">← About Us</a>
</div>

</body>
</html>
