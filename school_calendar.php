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
      padding: 0;
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

    .back-btn:hover {
      background-color: #e6e6e6;
    }

    .container {
      max-width: 900px;
      margin: 30px auto;
      padding: 20px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
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

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      text-align: center;
      gap: 5px;
    }

    .calendar-grid div {
      padding: 10px;
      border-radius: 5px;
    }

    .calendar-grid .day-name {
      font-weight: bold;
      background-color: #e0e7f4;
    }

    .calendar-grid .day {
      cursor: pointer;
      background-color: #f9f9f9;
    }

    .calendar-grid .holiday-public {
      background-color: #ffdddd;
      border: 1px solid #ff9999;
    }

    .calendar-grid .holiday-school {
      background-color: #ffffcc;
      border: 1px solid #cccc66;
    }

    .calendar-grid .today {
      border: 2px solid #004aad;
    }

    .school-holiday-cards {
      margin-top: 40px;
    }

    .school-holiday {
      background-color: #e6f0fa;
      border-left: 6px solid #004aad;
      padding: 10px 15px;
      margin-bottom: 15px;
      border-radius: 5px;
    }

    .school-holiday h2 {
      margin: 0 0 5px;
      font-size: 1.2em;
      color: #004aad;
    }

    .school-holiday p {
      margin: 0;
      font-size: 1em;
      color: #333;
    }

    .legend {
      margin-top: 15px;
      font-size: 0.95em;
    }

    .legend span {
      display: inline-block;
      width: 15px;
      height: 15px;
      margin-right: 8px;
      border-radius: 3px;
    }

    .legend .public { background-color: #ffdddd; }
    .legend .school { background-color: #ffffcc; }

  </style>
</head>
<body>

<header>
  <h1>📅 School Holidays Calender</h1>
  <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
</header>

<div class="container">
  <div class="calendar-nav">
    <button onclick="changeMonth(-1)">← Previous</button>
    <strong id="monthYearLabel"></strong>
    <button onclick="changeMonth(1)">Next →</button>
  </div>

  <div class="calendar-grid" id="calendar"></div>

  <div class="legend">
    <p><span class="public"></span> Public Holiday &nbsp;&nbsp;&nbsp; <span class="school"></span> School Holiday</p>
  </div>

  <div class="school-holiday-cards">
    <h2>🎓 School Holidays</h2>

    <div class="school-holiday">
      <h2>Winter Break</h2>
      <p>June 15 – July 8</p>
    </div>

    <div class="school-holiday">
      <h2>Spring Recess</h2>
      <p>September 23 – October 2</p>
    </div>

    <div class="school-holiday">
      <h2>Summer Holidays</h2>
      <p>December 10 – January 15</p>
    </div>
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
    { start: "2025-06-15", end: "2025-07-08", name: "Winter Break" },
    { start: "2025-09-23", end: "2025-10-02", name: "Spring Recess" },
    { start: "2025-12-10", end: "2026-01-15", name: "Summer Holidays" }
  ];

  function formatDateKey(date) {
    return date.toISOString().split("T")[0];
  }

  function isSchoolHoliday(dateStr) {
    const date = new Date(dateStr);
    return schoolHolidays.find(h => {
      return new Date(h.start) <= date && date <= new Date(h.end);
    });
  }

  function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    const startDay = firstDay.getDay(); // 0 (Sun) - 6 (Sat)
    const todayStr = formatDateKey(new Date());

    const monthNames = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];

    monthYearLabel.textContent = `${monthNames[month]} ${year}`;
    calendarEl.innerHTML = "";

    const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    dayNames.forEach(d => {
      const dayEl = document.createElement("div");
      dayEl.textContent = d;
      dayEl.classList.add("day-name");
      calendarEl.appendChild(dayEl);
    });

    // Empty blocks before the 1st day
    for (let i = 0; i < startDay; i++) {
      calendarEl.appendChild(document.createElement("div"));
    }

    for (let day = 1; day <= lastDay.getDate(); day++) {
      const date = new Date(year, month, day);
      const dateKey = formatDateKey(date);
      const dayDiv = document.createElement("div");
      dayDiv.classList.add("day");

      // Highlight today
      if (dateKey === todayStr) dayDiv.classList.add("today");

      // Public holiday
      if (publicHolidays[dateKey]) {
        dayDiv.classList.add("holiday-public");
        dayDiv.onclick = () => alert("📍 Public Holiday: " + publicHolidays[dateKey]);
      }

      // School holiday
      const schoolHoliday = isSchoolHoliday(dateKey);
      if (schoolHoliday) {
        dayDiv.classList.add("holiday-school");
        dayDiv.onclick = () => alert("📚 School Holiday: " + schoolHoliday.name);
      }

      dayDiv.textContent = day;
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
