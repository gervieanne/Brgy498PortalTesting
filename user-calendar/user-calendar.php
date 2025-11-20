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

// Fetch only upcoming events (today and future)
if (isset($_GET['get_events'])) {
    $today = date('Y-m-d');
    $sql = "SELECT event_id, title, description, event_date, start_time, end_time 
            FROM calendar_events 
            WHERE event_date >= ? 
            ORDER BY event_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
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
    $stmt->close();
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
            <img src="../images/logoutbtn.png" alt="logout" class="logout-logo" id="logoutBtn"/>
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
          
          <!-- View Selector -->
          <div class="view-selector">
            <button class="view-btn active" data-view="month">Month</button>
            <button class="view-btn" data-view="week">Week</button>
            <button class="view-btn" data-view="day">Day</button>
            <button class="view-btn" data-view="year">Year</button>
          </div>
          
          <!-- Month View -->
          <div class="calendar-view" id="monthView">
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
          
          <!-- Week View -->
          <div class="calendar-view" id="weekView" style="display: none;">
            <div class="week-grid" id="weekGrid"></div>
          </div>
          
          <!-- Day View -->
          <div class="calendar-view" id="dayView" style="display: none;">
            <div class="day-grid" id="dayGrid"></div>
          </div>
          
          <!-- Year View -->
          <div class="calendar-view" id="yearView" style="display: none;">
            <div class="year-grid" id="yearGrid"></div>
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
      let currentDate = new Date();
      let currentView = 'month'; // Default view

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
            renderCurrentView();
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

      // View selector functionality
      document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const view = this.getAttribute('data-view');
          switchView(view);
        });
      });
      
      function switchView(view) {
        currentView = view;
        
        // Update active button
        document.querySelectorAll('.view-btn').forEach(btn => {
          btn.classList.remove('active');
          if (btn.getAttribute('data-view') === view) {
            btn.classList.add('active');
          }
        });
        
        // Hide all views
        document.querySelectorAll('.calendar-view').forEach(v => {
          v.style.display = 'none';
        });
        
        // Show selected view
        document.getElementById(view + 'View').style.display = 'block';
        
        // Update header and render
        renderCurrentView();
      }
      
      // Render based on current view
      function renderCurrentView() {
        switch(currentView) {
          case 'day':
            renderDayView();
            break;
          case 'week':
            renderWeekView();
            break;
          case 'month':
            renderMonthView();
            break;
          case 'year':
            renderYearView();
            break;
        }
      }
      
      // Update header based on current view
      function updateHeader() {
        const monthNames = [
          "January", "February", "March", "April", "May", "June",
          "July", "August", "September", "October", "November", "December",
        ];
        
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const day = currentDate.getDate();
        
        switch(currentView) {
          case 'day':
            document.getElementById("monthYear").textContent = 
              `${monthNames[month]} ${day}, ${year}`;
            break;
          case 'week':
            const weekStart = new Date(currentDate);
            weekStart.setDate(currentDate.getDate() - currentDate.getDay());
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            document.getElementById("monthYear").textContent = 
              `${monthNames[weekStart.getMonth()]} ${weekStart.getDate()} - ${monthNames[weekEnd.getMonth()]} ${weekEnd.getDate()}, ${year}`;
            break;
          case 'month':
            document.getElementById("monthYear").textContent = 
              `${monthNames[month]} ${year}`;
            break;
          case 'year':
            document.getElementById("monthYear").textContent = `${year}`;
            break;
        }
      }

      // Month View - FIXED to only show dots on dates with events
      function renderMonthView() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        updateHeader();

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        const calendarDates = document.getElementById("calendarDates");
        calendarDates.innerHTML = "";

        // Previous month's trailing days
        for (let i = firstDay - 1; i >= 0; i--) {
          const dateDiv = document.createElement("div");
          dateDiv.className = "calendar-date other-month";
          dateDiv.textContent = daysInPrevMonth - i;
          calendarDates.appendChild(dateDiv);
        }

        // Current month's days
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        for (let day = 1; day <= daysInMonth; day++) {
          const dateDiv = document.createElement("div");
          dateDiv.className = "calendar-date";

          // Check if today
          if (
            year === today.getFullYear() &&
            month === today.getMonth() &&
            day === today.getDate()
          ) {
            dateDiv.classList.add("today");
          }

          // Create date string for comparison
          const dateString = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
          const dateObj = new Date(dateString + "T00:00:00");
          
          // FIXED: Only show dot if date has events AND is today or in the future
          const hasEvent = events.some((event) => event.date === dateString);
          const isFutureOrToday = dateObj >= today;

          if (hasEvent && isFutureOrToday) {
            dateDiv.classList.add("has-event");
            dateDiv.style.cursor = "pointer";
            dateDiv.onclick = () => filterEventsByDate(dateString);
            dateDiv.title = "Click to view events on this date";
          }

          dateDiv.textContent = day;
          calendarDates.appendChild(dateDiv);
        }

        // Next month's leading days
        const totalCells = calendarDates.children.length;
        const remainingCells = 42 - totalCells;
        for (let day = 1; day <= remainingCells; day++) {
          const dateDiv = document.createElement("div");
          dateDiv.className = "calendar-date other-month";
          dateDiv.textContent = day;
          calendarDates.appendChild(dateDiv);
        }
      }
      
      // Day View
      function renderDayView() {
        updateHeader();
        const dayGrid = document.getElementById('dayGrid');
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const day = currentDate.getDate();
        const dateString = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
        
        const dayEvents = events.filter(e => e.date === dateString);
        const today = new Date();
        const isToday = year === today.getFullYear() && month === today.getMonth() && day === today.getDate();
        
        let html = `<div class="day-view-header ${isToday ? 'today' : ''}">`;
        html += `<h3>${formatDate(dateString)}</h3>`;
        html += `<p>${dayEvents.length} event(s) scheduled</p>`;
        html += `</div>`;
        
        html += `<div class="day-events">`;
        if (dayEvents.length > 0) {
          dayEvents.forEach(event => {
            html += `<div class="day-event-item" onclick="previewAnnouncement(${event.id})">`;
            html += `<div class="event-time">${formatTime(event.startTime)} - ${formatTime(event.endTime)}</div>`;
            html += `<div class="event-title">${event.title}</div>`;
            html += `</div>`;
          });
        } else {
          html += `<div class="no-events-day">No events scheduled for this day</div>`;
        }
        html += `</div>`;
        
        dayGrid.innerHTML = html;
      }
      
      // Week View
      function renderWeekView() {
        updateHeader();
        const weekGrid = document.getElementById('weekGrid');
        const weekStart = new Date(currentDate);
        weekStart.setDate(currentDate.getDate() - currentDate.getDay());
        
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        let html = '<div class="week-days-header">';
        dayNames.forEach(day => {
          html += `<div class="week-day-header">${day}</div>`;
        });
        html += '</div>';
        
        html += '<div class="week-days-content">';
        for (let i = 0; i < 7; i++) {
          const currentDay = new Date(weekStart);
          currentDay.setDate(weekStart.getDate() + i);
          const year = currentDay.getFullYear();
          const month = currentDay.getMonth();
          const day = currentDay.getDate();
          const dateString = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
          
          const dayEvents = events.filter(e => e.date === dateString);
          const today = new Date();
          const isToday = year === today.getFullYear() && month === today.getMonth() && day === today.getDate();
          
          html += `<div class="week-day-cell ${isToday ? 'today' : ''}">`;
          html += `<div class="week-day-number">${day}</div>`;
          if (dayEvents.length > 0) {
            dayEvents.slice(0, 3).forEach(event => {
              html += `<div class="week-event-item" onclick="previewAnnouncement(${event.id})" title="${event.title}">`;
              html += `<span class="event-time-small">${formatTime(event.startTime)}</span> ${event.title}`;
              html += `</div>`;
            });
            if (dayEvents.length > 3) {
              html += `<div class="more-events">+${dayEvents.length - 3} more</div>`;
            }
          }
          html += `</div>`;
        }
        html += '</div>';
        
        weekGrid.innerHTML = html;
      }
      
      // Year View
      function renderYearView() {
        updateHeader();
        const yearGrid = document.getElementById('yearGrid');
        const year = currentDate.getFullYear();
        const monthNames = [
          "January", "February", "March", "April", "May", "June",
          "July", "August", "September", "October", "November", "December",
        ];
        
        let html = '';
        for (let m = 0; m < 12; m++) {
          const monthEvents = events.filter(e => {
            const eventDate = new Date(e.date);
            return eventDate.getFullYear() === year && eventDate.getMonth() === m;
          });
          
          html += `<div class="year-month-cell" onclick="switchToMonth(${m})">`;
          html += `<div class="year-month-name">${monthNames[m]}</div>`;
          html += `<div class="year-month-events">${monthEvents.length} event(s)</div>`;
          html += `</div>`;
        }
        
        yearGrid.innerHTML = html;
      }
      
      function switchToMonth(monthIndex) {
        currentDate.setMonth(monthIndex);
        currentDate.setDate(1);
        switchView('month');
      }

      // Navigation buttons
      document.getElementById("prevMonth").addEventListener("click", () => {
        navigateView('prev');
      });

      document.getElementById("nextMonth").addEventListener("click", () => {
        navigateView('next');
      });
      
      function navigateView(direction) {
        switch(currentView) {
          case 'day':
            if (direction === 'prev') {
              currentDate.setDate(currentDate.getDate() - 1);
            } else {
              currentDate.setDate(currentDate.getDate() + 1);
            }
            break;
          case 'week':
            if (direction === 'prev') {
              currentDate.setDate(currentDate.getDate() - 7);
            } else {
              currentDate.setDate(currentDate.getDate() + 7);
            }
            break;
          case 'month':
            if (direction === 'prev') {
              currentDate.setMonth(currentDate.getMonth() - 1);
            } else {
              currentDate.setMonth(currentDate.getMonth() + 1);
            }
            break;
          case 'year':
            if (direction === 'prev') {
              currentDate.setFullYear(currentDate.getFullYear() - 1);
            } else {
              currentDate.setFullYear(currentDate.getFullYear() + 1);
            }
            break;
        }
        renderCurrentView();
      }

       // filter events by specific date
      function filterEventsByDate(date){
        selectedDate = date;

        document.querySelectorAll('.calendar-date').forEach(dateEl => {
          dateEl.classList.remove('selected-date');
        });

        event.target.classList.add('selected-date');

        renderEvents();

        document.querySelector('.events-section').scrollIntoView({ behavior: 'smooth' });
      }

      function clearDateFilter() {
        selectedDate = null;

        document.querySelectorAll('.calendar-date').forEach(dateEl => {
          dateEl.classList.remove('selected-date');
        });
        
        renderEvents();
      }

      function previewAnnouncement(eventId) {
      const event = events.find(e => e.id == eventId); 
      if (!event) {
        console.warn("Event not found:", eventId);
        alert("Event not found. Events might still be loading.");
        return;
      }
      
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

    // Close modal when clicking outside
    window.onclick = function(event) {
      const previewModal = document.getElementById("eventPreviewModal");
      if (event.target === previewModal) {
        closePreviewModal();
      }
    }

// Make functions globally accessible
window.previewAnnouncement = previewAnnouncement;
window.closePreviewModal = closePreviewModal;
window.filterEventsByDate = filterEventsByDate;
window.clearDateFilter = clearDateFilter;
window.switchToMonth = switchToMonth;

      // Initialize default view
      switchView('month');
      
      // Initial load
      loadEvents();
    </script>
  </body>
</html>

<?php
$conn->close();
?>