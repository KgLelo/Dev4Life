<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require 'connect.php';
$conn = connectToDatabase();

$userName = $_SESSION['userName'];
$role = strtolower($_SESSION['role']);
$isTeacher = $role === 'teacher';
$message = '';

// === Upload Material ===
if ($isTeacher && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["material"]) && isset($_POST["upload"])) {
    $grade = intval($_POST["grade"]);
    $title = trim($_POST["title"]);
    $fileName = basename($_FILES["material"]["name"]);
    $targetDir = "uploads/";

    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $targetFile = $targetDir . time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);

    if (move_uploaded_file($_FILES["material"]["tmp_name"], $targetFile)) {
        $stmt = sqlsrv_query($conn, "INSERT INTO study_materials (grade, title, filename, uploaded_by) VALUES (?, ?, ?, ?)", 
            [$grade, $title, $targetFile, $userName]);
        $message = $stmt ? "✅ Study material uploaded successfully!" : "❌ Database error occurred.";
    } else {
        $message = "❌ File upload failed.";
    }
}

// === Update Material Title ===
if ($isTeacher && isset($_POST['update']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $stmt = sqlsrv_prepare($conn, "UPDATE study_materials SET title = ? WHERE id = ? AND uploaded_by = ?", [$title, $id, $userName]);
    if ($stmt && sqlsrv_execute($stmt)) {
        header("Location: dashboard.php?page=study_materials");
        exit();
    } else {
        $message = "❌ Failed to update material title.";
    }
}

// === Delete Material ===
if ($isTeacher && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = sqlsrv_query($conn, "SELECT filename FROM study_materials WHERE id = ? AND uploaded_by = ?", [$id, $userName]);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($row && file_exists($row['filename'])) unlink($row['filename']);
    sqlsrv_query($conn, "DELETE FROM study_materials WHERE id = ? AND uploaded_by = ?", [$id, $userName]);
    header("Location: dashboard.php?page=study_materials");
    exit();
}

// === Fetch Materials per Grade ===
$materials = [];
for ($g = 8; $g <= 12; $g++) {
    $stmt = sqlsrv_query($conn, "SELECT * FROM study_materials WHERE grade = ?", [$g]);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $materials[$g][] = $row;
    }
}
?>

<div>
  <h2 style="color: #004aad;">📚 Study Materials</h2>

  <?php if ($message): ?>
    <div style="background:#e0f7fa; border-left: 5px solid #004aad; padding: 10px 15px; margin-bottom: 20px; border-radius: 5px;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if ($isTeacher): ?>
    <div style="background:white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 30px;">
      <h3 style="color: #004aad;">Upload New Material</h3>
      <form method="post" enctype="multipart/form-data">
        <label>
          Grade:
          <select name="grade" required>
            <?php for ($i = 8; $i <= 12; $i++): ?>
              <option value="<?= $i ?>">Grade <?= $i ?></option>
            <?php endfor; ?>
          </select>
        </label><br><br>

        <label>
          Title:
          <input type="text" name="title" placeholder="e.g. Grade 10 Biology Notes" required style="width: 100%;" />
        </label><br><br>

        <label>
          Upload File:
          <input type="file" name="material" accept=".pdf,.doc,.docx" required />
        </label><br><br>

        <button type="submit" name="upload" style="background: #004aad; color: white; padding: 10px 20px; border: none; border-radius: 5px;">Upload</button>
      </form>
    </div>
  <?php endif; ?>

  <?php foreach ($materials as $grade => $list): ?>
    <div style="margin-bottom: 40px;">
      <h3 style="color: #004aad;">📘 Grade <?= $grade ?></h3>
      <?php if (!empty($list)): ?>
        <table style="width:100%; border-collapse: collapse; background:white;">
          <thead style="background:#004aad; color:white;">
            <tr>
              <th style="padding: 8px;">Title</th>
              <th>Uploaded By</th>
              <th>Date</th>
              <th>Download</th>
              <?php if ($isTeacher): ?><th>Actions</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $material): ?>
              <tr style="border-bottom: 1px solid #ccc;">
                <td style="padding: 8px;">
                  <?php if ($isTeacher && $material['uploaded_by'] === $userName): ?>
                    <form method="post" style="display: flex; gap: 10px;">
                      <input type="hidden" name="id" value="<?= $material['id'] ?>">
                      <input type="text" name="title" value="<?= htmlspecialchars($material['title']) ?>" required style="flex:1;">
                      <button type="submit" name="update" style="background:#004aad; border:none; color:white; padding:4px 10px; border-radius:5px;">Update</button>
                    </form>
                  <?php else: ?>
                    <?= htmlspecialchars($material['title']) ?>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($material['uploaded_by']) ?></td>
                <td><?= date_format($material['uploaded_at'], 'Y-m-d') ?></td>
                <td><a href="<?= htmlspecialchars($material['filename']) ?>" download style="color: green; font-weight: bold;">Download</a></td>
                <?php if ($isTeacher): ?>
                  <td>
                    <?php if ($material['uploaded_by'] === $userName): ?>
                      <a href="?delete=<?= $material['id'] ?>" onclick="return confirm('Are you sure you want to delete this file?')" style="color:red; font-weight: bold;">Delete</a>
                    <?php else: ?>
                      &mdash;
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p style="font-style: italic;">No study materials uploaded for Grade <?= $grade ?>.</p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
