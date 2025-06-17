<?php
session_start();
if (!isset($_SESSION['userName']) || !isset($_SESSION['role'])) {
  header("Location: login.php");
  exit();
}
$userName = $_SESSION['userName'];
$role = ucfirst($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WeConnect Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-image: url('images/img11.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
    }

    header {
      background-color: rgba(0, 74, 173, 0.85);
      color: white;
      padding: 10px 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    header h1 {
      margin: 0 auto;
      font-size: 24px;
    }

    .profile-card {
      display: flex;
      align-items: center;
      background-color: rgba(255, 255, 255, 0.95);
      color: #004aad;
      padding: 10px 15px;
      border-radius: 10px;
      box-shadow: 0 0 5px rgba(0,0,0,0.2);
      gap: 15px;
    }

    .profile-card img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
    }

    .profile-card .info {
      display: flex;
      flex-direction: column;
      font-size: 14px;
    }

    .logout-btn {
      background-color: #004aad;
      color: white;
      padding: 6px 12px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      font-size: 12px;
      transition: background-color 0.3s ease;
      margin-top: 5px;
      width: fit-content;
    }

    .logout-btn:hover {
      background-color: #003b80;
    }

    h2 {
      color: #004aad;
    }

    .dashboard-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      padding: 30px;
    }

    .card {
      background-color: rgba(255, 255, 255, 0.95);
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
    }

    ul {
      padding-left: 20px;
    }

    a {
      color: #004aad;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    .button {
      margin-top: 15px;
      display: inline-block;
      background-color: #004aad;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      text-align: center;
      font-size: 14px;
      cursor: pointer;
      text-decoration: none;
    }

    .button:hover {
      background-color: #003b80;
    }
  </style>
</head>
<body>

<header>
  <h1>Dear <strong><span><?php echo htmlspecialchars($role); ?></span></strong>,<br>Welcome to WeConnect Dashboard</h1>

  <!-- 👤 Profile section on top-right -->
  <div class="profile-card">
    <!--<img src="images/user-icon.png" alt="User Icon"> -->
    <div class="info">
      <strong>👤 <?php echo htmlspecialchars($userName); ?></strong>
      <span>Role: <?php echo htmlspecialchars($role); ?></span>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </div>
</header>

<div class="dashboard-container">

  <!-- 📅 School Holidays -->
  <div class="card">
    <h2>📅 School Holidays</h2>
    <button onclick="window.location.href='school_calendar.php'" class="button">
      View School Calendar
    </button>
  </div>

<!-- 📚 Study Materials -->
<div class="card">
  <h2>📚 Study Materials</h2>
  <button onclick="window.location.href='study_materials.php'" class="button">
    View Study Materials
  </button>
</div>


  <!-- 📈 Grades -->
  <div class="card">
    <h2>📈 Grades</h2>
    <ul>
      <li>Mathematics: 78%</li>
      <li>English: 85%</li>
      <li>Science: 91%</li>
    </ul>
  </div>

<!-- 🧑‍🏫 Upcoming Meetings -->
<div class="card">
  <h2>🧑‍🏫 Upcoming Meetings</h2>
  <button onclick="window.location.href='meetings.php'" class="button">
    View & Manage Meetings
  </button>
</div>


<!-- 🎉 School Events -->
<div class="card">
  <h2>🎉 School Events</h2>
  <button onclick="window.location.href='school_events.php'" class="button">
    View All School Events
  </button>
</div>


<!-- 📝 Exams / Tests -->
<div class="card">
  <h2>📝 Exams / Tests</h2>
  <button onclick="window.location.href='exams_tests.php'" class="button">
    View Exams & Tests
  </button>
</div>


</div>

</body>
</html>
