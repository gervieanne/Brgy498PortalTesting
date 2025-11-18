<?php
ini_set('session.gc_maxlifetime', 21600);
ini_set('session.cookie_lifetime', 21600);

session_start();
// Include centralized session check
require_once '../includes/session_check.php';

// Get user data safely
$user = getCurrentUser();
$full_name = $user['full_name'];
$username = $user['username'];
$user_id = $user['user_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "498portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all events
if (isset($_GET['get_events'])) {
    $sql = "SELECT event_id, title, description, event_date, start_time, end_time FROM calendar_events ORDER BY event_date ASC";
    $result = $conn->query($sql);
    
    $events = array();
    while($row = $result->fetch_assoc()) {
        $events[] = array(
            'id' => $row['event_id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'date' => $row['event_date'],
            'startTime' => substr($row['start_time'], 0, 5),
            'endTime' => substr($row['end_time'], 0, 5)
        );
    }
    
    echo json_encode($events);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../user-calendar/user-calendar.css" />
    <link rel="stylesheet" href="../preloader/preloader.css" />
    <link rel="stylesheet" href="../logout-modal.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="../user-chatbot/chatbot.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />
    <title>Barangay Calendar</title>
  </head>
  <body>
    <div class="preloader" id="preloader">
      <div class="spinner"></div>
      <p>Loading...</p>
    </div>

    <button class="burger-menu" id="burgerMenu">☰</button>

    <div class="sidebar" id="sidebar">
      <img
        src="../images/barangay-logo.png"
        alt="barangay-logo"
        class="barangay-logo"
      />
      <h1>Barangay Management System</h1>
      <nav class="menu">
        <ul class="menu-items">
          <li><a href="../user-dashboard/user-dashboard.php">Dashboard</a></li>
          <li><a href="../user-profile/user-profile.php">Profile</a></li>
          <li><a href="../user-request/user_request.php">Document Requests</a></li>
          <li><a href="../user-announcement/user-announcement.php">Announcement</a></li>
          <li><a href="../user-calendar/user-calendar.php" class="active">Calendar</a></li>
          <li><a href="../user-officials/user-officials.php">Officials</a></li>
        </ul>
      </nav>
    </div>

    <div class="main-container">
      <div class="header">
        <div class="header-left">
          <h1>BARANGAY CALENDAR</h1>
          <p>Events and Schedules</p>
        </div>
        <div class="header-right">
          <div class="clock" id="clock">12:00:00 AM</div>
          <a href="#" id="logoutBtn">
            <img src="../images/logout.png" alt="logout" class="logout-logo" id="logoutBtn"/>
          </a>
        </div>
      </div>

      <div class="calendar-layout">
        <div class="calendar-section">
          <div class="section-header">Calendar Overview</div>
          <div class="calendar-header">
            <h2 id="monthYear">January 2025</h2>
            <div class="calendar-nav">
              <button id="prevMonth"><</button>
              <button id="nextMonth">></button>
            </div>
          </div>
          <div class="calendar-grid">
            <div class="calendar-days">
              <div class="day-header">Sunday</div>
              <div class="day-header">Monday</div>
              <div class="day-header">Tuesday</div>
              <div class="day-header">Wednesday</div>
              <div class="day-header">Thursday</div>
              <div class="day-header">Friday</div>
              <div class="day-header">Saturday</div>
            </div>
            <div class="calendar-dates" id="calendarDates"></div>
          </div>
        </div>

        <div class="events-section">
          <div class="section-header">
            <span>Incoming Events</span>
          </div>
          <div class="events-list" id="eventsList"></div>
        </div>
      </div>
    </div>

     <!-- preview modal -->
     <div id="eventPreviewModal" class="modal" onclick="closePreviewModal()">
      <div class="modal-content" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h2 id="previewTitle"></h2>
        <button class="close-btn" onclick="closePreviewModal()">&times;</button>
      </div>
      <div class="modal-body">
      <p><strong>Date:</strong> <span id="previewDate"></span></p>
      <p><strong>Time:</strong> <span id="previewTime"></span></p>
      <p><strong>Description:</strong> <span id="previewDescription"></span></p>
    </div>
  </div>
</div>


    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="logout-modal">
      <div class="logout-modal-content">
        <div class="logout-modal-icon">⚠️</div>
        <h2>Confirm Logout</h2>
        <p>Are you sure you want to logout?</p>
        <div class="logout-modal-buttons">
          <button class="logout-btn-cancel" id="cancelLogout">No</button>
          <button class="logout-btn-confirm" id="confirmLogout">Yes</button>
        </div>
      </div>
    </div>

    <!-- chatbot -->
    <div id="chat-toggle">?</div>

    <div id="chatbot">
      <div id="chat-header">Help & FAQs</div>
      <div id="chat-body"></div>
      <div id="quick-questions">
        <div class="quick-scroll">
          <button class="quick-btn">How to request clearance?</button>
          <button class="quick-btn">How to view my profile?</button>
          <button class="quick-btn">How to see pending requests?</button>
          <button class="quick-btn">How to logout?</button>
        </div>
      </div>
      <div id="chat-input-area">
        <input
          id="chat-input"
          type="text"
          placeholder="Type your question..."
        />
        <button id="send-btn">➤</button>
      </div>
    </div>

    <script src="../user-chatbot/chatbot.js"></script>
    <script src="../preloader/preloader.js"></script>
    <script src="../logout-modal.js"></script>
    <script>
      // Events storage
      let events = [];
      let selectedDate = null;

      // Clock
      function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, "0");
        const seconds = String(now.getSeconds()).padStart(2, "0");
        const ampm = hours >= 12 ? "PM" : "AM";
        hours = hours % 12 || 12;
        const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;
        document.getElementById("clock").textContent = timeString;
      }
      setInterval(updateClock, 1000);
      updateClock();

      // Burger Menu
      const burgerMenu = document.getElementById("burgerMenu");
      const sidebar = document.getElementById("sidebar");

      burgerMenu.addEventListener("click", () => {
        sidebar.classList.toggle("active");
      });

      document.addEventListener("click", (e) => {
        if (!sidebar.contains(e.target) && !burgerMenu.contains(e.target)) {
          sidebar.classList.remove("active");
        }
      });

      // Load events from database
      function loadEvents() {
        fetch('user-calendar.php?get_events=1')
          .then(response => response.json())
          .then(data => {
            events = data;
            renderEvents();
            renderCalendar();
          });
      }

      // Format time to 12-hour format
      function formatTime(time24) {
        const [hours, minutes] = time24.split(":");
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? "PM" : "AM";
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes}${ampm}`;
      }

      // Format date
      function formatDate(dateString) {
        const date = new Date(dateString + "T00:00:00");
        const options = { year: "numeric", month: "long", day: "numeric" };
        return date.toLocaleDateString("en-US", options);
      }

     // Render events list
      function renderEvents() {
        const eventsList = document.getElementById("eventsList");
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        let upcomingEvents = events.filter((event) => {
          const eventDate = new Date(event.date + "T00:00:00");
          return eventDate >= today;
        });

        // selected Date for filtering
        if (selectedDate) {
          upcomingEvents = upcomingEvents.filter(event => event.date === selectedDate);
        }

        let headerHTML = '';
        if (selectedDate) {
          headerHTML =  `
          <div class="filter-header">
            <span>Events on ${formatDate(selectedDate)}</span>
            <button class="clear-filter-btn" onclick="clearDateFilter()">Show All Events</button>
            </div>
             `;
        }

        if (upcomingEvents.length === 0) {
           const message = selectedDate 
            ? `No events scheduled for ${formatDate(selectedDate)}` 
            : 'No upcoming events scheduled';
          eventsList.innerHTML = headerHTML + `<div class="no-events">${message}</div>`;
          return;
        }

        eventsList.innerHTML = headerHTML + upcomingEvents
          .map(
          (event) => `
            <div class="event-item" onclick="previewAnnouncement(${event.id})">

              <div class="event-date">${formatDate(event.date)}</div>
              <div class="event-title">${event.title}</div>
              <div class="event-time">(${formatTime(event.startTime)} - ${formatTime(event.endTime)})</div>
            </div>
          `
        )
      .join("");
  }

      // Calendar functionality
      let currentDate = new Date();

      function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        const monthNames = [
          "January", "February", "March", "April", "May", "June",
          "July", "August", "September", "October", "November", "December",
        ];
        document.getElementById("monthYear").textContent = `${monthNames[month]} ${year}`;

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        const calendarDates = document.getElementById("calendarDates");
        calendarDates.innerHTML = "";

        for (let i = firstDay - 1; i >= 0; i--) {
          const dateDiv = document.createElement("div");
          dateDiv.className = "calendar-date other-month";
          dateDiv.textContent = daysInPrevMonth - i;
          calendarDates.appendChild(dateDiv);
        };

        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
          const dateDiv = document.createElement("div");
          dateDiv.className = "calendar-date";

          if (
            year === today.getFullYear() &&
            month === today.getMonth() &&
            day === today.getDate()
          ) {
            dateDiv.classList.add("today");
          }

          const dateString = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
          const hasEvent = events.some((event) => event.date === dateString);
          if (hasEvent) {
            dateDiv.classList.add("has-event");
            dateDiv.style.cursor = "pointer";
            dateDiv.onclick = () => filterEventsByDate(dateString);
            dateDiv.title = "Click to view events on this date"; // to
          }

          dateDiv.textContent = day;
          calendarDates.appendChild(dateDiv);
        }

        const totalCells = calendarDates.children.length;
        const remainingCells = 42 - totalCells;
        for (let day = 1; day <= remainingCells; day++) {
          const dateDiv = document.createElement("div");
          dateDiv.className = "calendar-date other-month";
          dateDiv.textContent = day;
          calendarDates.appendChild(dateDiv);
        }
      }

      document.getElementById("prevMonth").addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
      });

      document.getElementById("nextMonth").addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
      });

       // filter events by specific date
      function filterEventsByDate(date){
        selectedDate = date;

        document.querySelectorAll('.calendar-date').forEach(date => {
          date.classList.remove('selected-date');
        });

        event.target.classList.add('selected-date');

        renderEvents();

        document.querySelector('.events-section').scrollIntoView({ behavior: 'smooth' });
      }

      function clearDateFilter() {
        selectedDate = null;

        document.querySelectorAll('.calendar-date').forEach(date => {
          date.classList.remove('selected-date');
        });
        
        renderEvents();
      }

      // Initial load
      loadEvents();

      function previewAnnouncement(eventId) {
      console.log("Looking for event ID:", eventId);
      console.log("Available events:", events);
      
      const event = events.find(e => e.id == eventId); 
      if (!event) {
        console.warn("Event not found:", eventId);
        console.log("Events array:", events);
        alert("Event not found. Events might still be loading.");
        return;
      }

      console.log("Event found:", event);
      
      document.getElementById("previewTitle").textContent = event.title;
      document.getElementById("previewDate").textContent = formatDate(event.date);
      document.getElementById("previewTime").textContent =
        `${formatTime(event.startTime)} - ${formatTime(event.endTime)}`;

        // for description
        const descriptionElement = document.getElementById("previewDescription");
        if (event.description && event.description.trim() !== '') {
          descriptionElement.textContent = event.description;
          descriptionElement.parentElement.style.display = 'block'; 
        } else {
          descriptionElement.textContent = 'No description provided';
          descriptionElement.parentElement.style.display = 'none';
        }
      document.getElementById("eventPreviewModal").classList.add("show");
    }

    function closePreviewModal() {
      document.getElementById("eventPreviewModal").classList.remove("show");
    }
// Close modal function
function closePreviewModal() {
  document.getElementById("eventPreviewModal").classList.remove("show");
}

// Make functions globally accessible
window.previewAnnouncement = previewAnnouncement;
window.closePreviewModal = closePreviewModal;
window.filterEventsByDate = filterEventsByDate;
window.clearDateFilter = clearDateFilter;

    </script>
  </body>
</html>

<?php
$conn->close();
?>