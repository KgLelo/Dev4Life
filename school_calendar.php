<?php
//session_start();
if (!isset($_SESSION['userName']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
require 'connect.php';
$conn = connectToDatabase();

// Fetch all school events
$schoolEvents = [];
$sql = "SELECT eventTitle, eventDate FROM SchoolEvents";
$stmt = sqlsrv_query($conn, $sql);
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $eventDate = $row['eventDate'];
    if ($eventDate instanceof DateTime) {
        $schoolEvents[] = [
            'date' => $eventDate->format('Y-m-d'),
            'title' => $row['eventTitle']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>School Calendar - WeConnect</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f8fb;
      margin: 0;
      padding: 20px;
      background-image: url('images/img12.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
    }

    header {
      background-color: #004aad;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px;
    }

    header h1 {
      margin: 0;
    }

    .back-btn {
      background-color: #fff;
      color: #004aad;
      padding: 10px 15px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .container {
      max-width: 900px;
      margin: 30px auto;
      padding: 20px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      text-align: center;
      gap: 5px;
    }

    .calendar-grid div {
      padding: 10px;
      border-radius: 5px;
      position: relative;
    }

    .day-name {
      font-weight: bold;
      background-color: #e0e7f4;
    }

    .day {
      cursor: pointer;
      background-color: #f9f9f9;
      border: 1px solid #ddd;
    }

    .holiday-public {
      background-color: #ffdddd;
      border: 2px solid #ff5555;
    }

    .holiday-school {
      background-color: #fffcc9;
      border: 2px solid #cccc66;
    }

    .holiday-event {
      background-color: #e0ffe0;
      border: 2px solid #33cc33;
    }

    .today {
      border: 3px solid #004aad;
      background-color: #cce5ff;
    }

    .legend span {
      display: inline-block;
      width: 15px;
      height: 15px;
      margin-right: 8px;
      border-radius: 3px;
    }

    .public { background-color: #ffdddd; }
    .school { background-color: #fffcc9; }
    .event { background-color: #e0ffe0; }
    .today-mark { background-color: #cce5ff; border: 1px solid #004aad; }

    .legend {
      margin-top: 15px;
      font-size: 0.95em;
    }

    .calendar-nav {
      text-align: center;
      margin-bottom: 20px;
    }

    .calendar-nav button {
      padding: 8px 15px;
      margin: 0 10px;
      background-color: #004aad;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .calendar-nav button:hover {
      background-color: #003579;
    }

    .back-btn:hover {
      background-color: #e6e6e6;
    }
  </style>
</head>
<body>

<header>
  <h1>📅 School Holidays & Events Calendar</h1>
  <a href="dashboard.php" class="back-btn">← About Us!</a>
</header>

<div class="container">
  <div class="calendar-nav">
    <button onclick="changeMonth(-1)">← Previous</button>
    <strong id="monthYearLabel"></strong>
    <button onclick="changeMonth(1)">Next →</button>
  </div>

  <div class="calendar-grid" id="calendar"></div>

  <div class="legend">
    <p>
      <span class="public"></span> Public Holiday &nbsp;&nbsp;
      <span class="school"></span> School Holiday &nbsp;&nbsp;
      <span class="event"></span> School Event &nbsp;&nbsp;
      <span class="today-mark"></span> Today
    </p>
  </div>
</div>

<script>
  const calendarEl = document.getElementById("calendar");
  const monthYearLabel = document.getElementById("monthYearLabel");
  let currentDate = new Date();

  const publicHolidays = {
    "2025-01-01": "New Year's Day",
    "2025-03-21": "Human Rights Day",
    "2025-04-27": "Freedom Day",
    "2025-05-01": "Workers’ Day",
    "2025-06-16": "Youth Day",
    "2025-08-09": "Women’s Day",
    "2025-09-24": "Heritage Day",
    "2025-12-16": "Day of Reconciliation",
    "2025-12-25": "Christmas Day",
    "2025-12-26": "Day of Goodwill"
  };

  const schoolHolidays = [
    { start: "2025-03-28", end: "2025-04-08", name: "Autumn Holidays" },
    { start: "2025-06-27", end: "2025-07-22", name: "Winter Break" },
    { start: "2025-10-03", end: "2025-10-13", name: "Spring Recess" },
    { start: "2025-12-10", end: "2026-01-15", name: "Summer Holidays" }
  ];

  const schoolEvents = <?= json_encode($schoolEvents); ?>;

  function formatLocalDateKey(date) {
    return date.toLocaleDateString('en-CA'); // YYYY-MM-DD
  }

  function isSchoolHoliday(dateStr) {
    const date = new Date(dateStr);
    return schoolHolidays.find(h => new Date(h.start) <= date && date <= new Date(h.end));
  }

  function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const todayStr = formatLocalDateKey(new Date());

    const startDay = (firstDay.getDay() + 6) % 7; // start Monday
    const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
    monthYearLabel.textContent = `${monthNames[month]} ${year}`;
    calendarEl.innerHTML = "";

    const dayNames = ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
    dayNames.forEach(d => {
      const dayEl = document.createElement("div");
      dayEl.textContent = d;
      dayEl.classList.add("day-name");
      calendarEl.appendChild(dayEl);
    });

    for (let i = 0; i < startDay; i++) calendarEl.appendChild(document.createElement("div"));

    for (let day = 1; day <= lastDay.getDate(); day++) {
      const date = new Date(year, month, day);
      const dateKey = formatLocalDateKey(date);
      const dayDiv = document.createElement("div");
      dayDiv.classList.add("day");
      dayDiv.textContent = day;

      if (dateKey === todayStr) dayDiv.classList.add("today");
      if (publicHolidays[dateKey]) {
        dayDiv.classList.add("holiday-public");
        dayDiv.title = publicHolidays[dateKey];
        dayDiv.onclick = () => alert("📍 Public Holiday: " + publicHolidays[dateKey]);
      }

      const schoolHoliday = isSchoolHoliday(dateKey);
      if (schoolHoliday) {
        dayDiv.classList.add("holiday-school");
        dayDiv.title = schoolHoliday.name;
        dayDiv.onclick = () => alert("📚 School Holiday: " + schoolHoliday.name);
      }

      const event = schoolEvents.find(e => e.date === dateKey);
      if (event) {
        dayDiv.classList.add("holiday-event");
        dayDiv.title = event.title;
        dayDiv.onclick = () => alert("🎉 School Event: " + event.title);
      }

      calendarEl.appendChild(dayDiv);
    }
  }

  function changeMonth(offset) {
    currentDate.setMonth(currentDate.getMonth() + offset);
    renderCalendar();
  }

  renderCalendar();
</script>

</body>
</html>
