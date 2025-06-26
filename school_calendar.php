<?php
if (!isset($_SESSION)) session_start();
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

<div style="padding: 20px;">
  <h2 style="color: #004aad; margin-bottom: 15px;">📅 School Calendar</h2>

  <div style="margin-bottom: 20px; text-align: center;">
    <button onclick="changeMonth(-1)" style="padding: 8px 16px; margin-right: 10px; background: #004aad; color: #fff; border: none; border-radius: 5px;">← Previous</button>
    <strong id="monthYearLabel" style="font-size: 18px; color: #333;"></strong>
    <button onclick="changeMonth(1)" style="padding: 8px 16px; margin-left: 10px; background: #004aad; color: #fff; border: none; border-radius: 5px;">Next →</button>
  </div>

  <div id="calendar" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; background: #fff; padding: 10px; border-radius: 8px;"></div>

  <div style="text-align: center; margin-top: 20px; font-size: 14px;">
    <span style="display: inline-block; width: 15px; height: 15px; background: #ffdddd; margin-right: 5px; border-radius: 3px;"></span> Public Holiday &nbsp;&nbsp;
    <span style="display: inline-block; width: 15px; height: 15px; background: #fffcc9; margin-right: 5px; border-radius: 3px;"></span> School Holiday &nbsp;&nbsp;
    <span style="display: inline-block; width: 15px; height: 15px; background: #e0ffe0; margin-right: 5px; border-radius: 3px;"></span> School Event &nbsp;&nbsp;
    <span style="display: inline-block; width: 15px; height: 15px; background: #cce5ff; border: 1px solid #004aad; margin-right: 5px; border-radius: 3px;"></span> Current Date
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
      dayEl.style.fontWeight = 'bold';
      dayEl.style.background = '#e1e7f8';
      dayEl.style.color = '#004aad';
      dayEl.style.borderRadius = '6px';
      dayEl.style.padding = '10px';
      calendarEl.appendChild(dayEl);
    });

    for (let i = 0; i < startDay; i++) {
      const empty = document.createElement("div");
      calendarEl.appendChild(empty);
    }

    for (let day = 1; day <= lastDay.getDate(); day++) {
      const date = new Date(year, month, day);
      const dateKey = formatLocalDateKey(date);
      const dayDiv = document.createElement("div");
      dayDiv.textContent = day;
      dayDiv.style.padding = '10px';
      dayDiv.style.border = '1px solid #ddd';
      dayDiv.style.borderRadius = '6px';
      dayDiv.style.cursor = 'pointer';
      dayDiv.style.transition = 'background 0.3s ease';

      if (dateKey === todayStr) {
        dayDiv.style.border = '2px solid #004aad';
        dayDiv.style.background = '#cce5ff';
        dayDiv.style.fontWeight = 'bold';
      }

      if (publicHolidays[dateKey]) {
        dayDiv.style.background = '#ffdddd';
        dayDiv.style.border = '2px solid #ff5555';
        dayDiv.title = publicHolidays[dateKey];
        dayDiv.onclick = () => alert("📍 Public Holiday: " + publicHolidays[dateKey]);
      }

      const schoolHoliday = isSchoolHoliday(dateKey);
      if (schoolHoliday) {
        dayDiv.style.background = '#fffcc9';
        dayDiv.style.border = '2px solid #cccc66';
        dayDiv.title = schoolHoliday.name;
        dayDiv.onclick = () => alert("📚 School Holiday: " + schoolHoliday.name);
      }

      const event = schoolEvents.find(e => e.date === dateKey);
      if (event) {
        dayDiv.style.background = '#e0ffe0';
        dayDiv.style.border = '2px solid #33cc33';
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
