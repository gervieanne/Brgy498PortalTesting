<?php
ini_set('session.gc_maxlifetime', 21600);
ini_set('session.cookie_lifetime', 21600);

session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "498portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-delete past events (runs on every page load)
$today = date('Y-m-d');
$delete_past_sql = "DELETE FROM calendar_events WHERE event_date < ?";
$delete_stmt = $conn->prepare($delete_past_sql);
$delete_stmt->bind_param("s", $today);
$delete_stmt->execute();
$delete_stmt->close();

// Handle Add Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $event_date = $_POST['event_date'];
    $description = $conn->real_escape_string($_POST['description']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    // Prevent adding past events
    if ($event_date < $today) {
        echo json_encode(['success' => false, 'message' => 'Cannot add events in the past']);
        exit();
    }
    
    $sql = "INSERT INTO calendar_events (title, description, event_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $title, $description, $event_date, $start_time, $end_time);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add event']);
    }
    $stmt->close();
    exit();
}

// Handle Delete Event
if (isset($_GET['delete_event'])) {
    $event_id = intval($_GET['delete_event']);
    
    $sql = "DELETE FROM calendar_events WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
    exit();
}

// Fetch only upcoming events (today and future)
if (isset($_GET['get_events'])) {
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
    <link rel="stylesheet" href="../admin-calendar/admin-calendar.css" />
    <link rel="stylesheet" href="../preloader/preloader.css" />
    <link rel="stylesheet" href="../logout-modal.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
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
          <li>
            <a href="../admin-dashboard.php">Dashboard</a>
          </li>
          <li><a href="../admin-officials/admin-officials.php">Officials</a></li>
          <li>
            <a href="../admin-residents-info/admin-residents.php"
              >Residents Information</a
            >
          </li>
          <li>
            <a href="../admin-calendar/admin-calendar.php" class="active"
              >Calendar</a
            >
          </li>
          <li>
            <a href="../admin-document-req/admin-document-requests.php"
              >Document Requests</a
            >
          </li>
          <li>
            <a href="../admin-announcement/admin-announcement.php">Announcement</a>
          </li>
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
            <img
              src="../images/logoutbtn.png"
              alt="logout"
              class="logout-logo"
            />
          </a>
        </div>
      </div>

      <div class="calendar-layout">
        <div class="calendar-section">
          <div class="section-header">Calendar Overview</div>
          <div class="calendar-header">
            <h2 id="monthYear">January 2025</h2>
            <div class="calendar-nav">
              <button id="prevBtn"><</button>
              <button id="nextBtn">></button>
            </div>
          </div>
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
            <button class="add-event-btn" id="addEventBtn">
              + Add Event
            </button>
          </div>
          <div class="events-list" id="eventsList"></div>
        </div>
      </div>
    </div>

    <!-- Add Event Modal -->
    <div id="eventModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Create New Event</h2>
          <button class="close-btn" id="closeModal">&times;</button>
        </div>
        <form id="eventForm">
          <div class="form-group">
            <label for="eventTitle">Event Title</label>
            <input
              type="text"
              id="eventTitle"
              name="title"
              required
              placeholder="Enter event title"
            />
          </div>

          <div class="form-group">
            <label for="eventDescription">Event Description</label>
            <textarea
              id="eventDescription"
              name="description"
              rows="4"
              placeholder="Enter event description"
            ></textarea>
          </div>
          <div class="form-group">
            <label for="eventDate">Event Date</label>
            <input type="date" id="eventDate" name="event_date" required />
          </div>
          <div class="form-group">
            <label>Event Time</label>
            <div class="time-inputs">
              <input
                type="time"
                id="eventStartTime"
                name="start_time"
                required
                placeholder="Start Time"
              />
              <input
                type="time"
                id="eventEndTime"
                name="end_time"
                required
                placeholder="End Time"
              />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" id="cancelBtn">
              Cancel
            </button>
            <button type="submit" class="btn-submit">Create Event</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Preview Modal -->
    <div id="eventPreviewModal" class="modal">
      <div class="modal-content">
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

     <!-- Delete Event Modal -->
     <div id="deleteModal" class="delete-modal">
      <div class="delete-modal-content">
          <div class="delete-modal-icon">⚠️</div>
          <h2>Delete Event</h2>
          <p>Are you sure you want to delete this Event?</p>
        <div class="delete-modal-buttons">
          <button class="delete-btn-cancel" id="cancelDelete">No, Cancel</button>
          <button class="delete-btn-confirm" id="confirmDelete">Yes, Delete</button>  
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

    <script src="../logout-modal.js"></script>
    <script src="../preloader/preloader.js"></script>    

    <script>
    function showPopupMessage(message, type = 'success') {
        let popup = document.getElementById('popupMessage');
        if (!popup) {
          popup = document.createElement('div');
          popup.id = 'popupMessage';
          popup.style.position = 'fixed';
          popup.style.top = '20px';
          popup.style.right = '20px';
          popup.style.padding = '12px 18px';
          popup.style.borderRadius = '8px';
          popup.style.fontSize = '15px';
          popup.style.fontWeight = '500';
          popup.style.color = '#fff';
          popup.style.zIndex = '9999';
          popup.style.transition = 'opacity 0.5s ease';
          popup.style.boxShadow = '0 4px 10px rgba(0,0,0,0.2)';
          document.body.appendChild(popup);
        }

        popup.style.background = type === 'error' ? '#e74c3c' : '#4CAF50';
        popup.textContent = message;
        popup.style.opacity = '1';
        popup.style.display = 'block';

        setTimeout(() => {
          popup.style.opacity = '0';
          setTimeout(() => {
            popup.style.display = 'none';
          }, 500);
        }, 2000);
      }

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
        document.getElementById("clock").textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
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
        fetch('admin-calendar.php?get_events=1')
          .then(response => response.json())
          .then(data => {
            events = data;
            renderEvents();
            renderCurrentView();
          });
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

      // Modal functionality
      const modal = document.getElementById("eventModal");
      const addEventBtn = document.getElementById("addEventBtn");
      const closeModal = document.getElementById("closeModal");
      const cancelBtn = document.getElementById("cancelBtn");
      const eventForm = document.getElementById("eventForm");

      addEventBtn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        modal.classList.add("show");
        eventForm.reset();
        
        // Set minimum date to today
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, "0");
        const day = String(today.getDate()).padStart(2, "0");
        const todayString = `${year}-${month}-${day}`;
        
        const dateInput = document.getElementById("eventDate");
        dateInput.value = todayString;
        dateInput.min = todayString; // Prevent selecting past dates
      });

      closeModal.addEventListener("click", (e) => {
        e.preventDefault();
        modal.classList.remove("show");
      });

      cancelBtn.addEventListener("click", (e) => {
        e.preventDefault();
        modal.classList.remove("show");
      });

      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
          modal.classList.remove("show");
        }
      });

      // Form submission
      eventForm.addEventListener("submit", function (e) {
      e.preventDefault();
      
      const formData = new FormData(eventForm);
      formData.append('add_event', '1');

      fetch('admin-calendar.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showPopupMessage('Event created successfully!', 'success');
          modal.classList.remove("show");
          eventForm.reset();
          loadEvents();
        } else {
          showPopupMessage(data.message || 'Error creating event.', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showPopupMessage('An unexpected error occurred.', 'error');
      });
    });


      // Delete event
      let deleteEventId = null; 

      function deleteEvent(eventId) {
        deleteEventId = eventId; 
        document.getElementById('deleteModal').style.display = 'block'; 
      }

      function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        deleteEventId = null; 
      }

      document.getElementById('cancelDelete').addEventListener('click', function() {
        closeDeleteModal();
      });

      document.getElementById('confirmDelete').addEventListener('click', function() {
        if (!deleteEventId) return; 

        fetch(`admin-calendar.php?delete_event=${deleteEventId}`)
    .then(response => response.json())
    .then(data => {
      closeDeleteModal(); 

      if (data.success) {
        showPopupMessage('Event deleted successfully!', 'success');
        if (typeof loadEvents === 'function') loadEvents(); 
      } else {
        showPopupMessage('Error deleting event.', 'error');
      }
    })
    .catch(error => {
      console.error('Error deleting event:', error);
      closeDeleteModal();
      showPopupMessage('An unexpected error occurred.', 'error');
    });
});

window.deleteEvent = deleteEvent;



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

        // Filter by selected date
        if (selectedDate) {
          upcomingEvents = upcomingEvents.filter(event => event.date === selectedDate);
        }

        let headerHTML = '';
        if (selectedDate) {
          headerHTML = `
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
          .map(event => `
            <div class="event-item" onclick="previewAnnouncement(${event.id})">
              <button class="delete-event-btn" onclick="event.stopPropagation(); deleteEvent(${event.id})">×</button>
              <div class="event-date">${formatDate(event.date)}</div>
              <div class="event-title">${event.title}</div>
              <div class="event-time">(${formatTime(event.startTime)} - ${formatTime(event.endTime)})</div>
            </div>
          `)
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
      
      // Month View (original calendar functionality)
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
      
      // Make functions globally accessible
      window.switchToMonth = switchToMonth;
      window.previewAnnouncement = previewAnnouncement;

      // Navigation buttons
      document.getElementById("prevBtn").addEventListener("click", () => {
        navigateView('prev');
      });

      document.getElementById("nextBtn").addEventListener("click", () => {
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

      // Filter events by specific date
      function filterEventsByDate(date) {
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

      // Preview announcement
      function previewAnnouncement(eventId) {
        const event = events.find(e => e.id == eventId);
        if (!event) {
          showPopupMessage("Event not found. Events might still be loading.", "error");
           return;
        }

        document.getElementById("previewTitle").textContent = event.title;
        document.getElementById("previewDate").textContent = formatDate(event.date);
        document.getElementById("previewTime").textContent =
          `${formatTime(event.startTime)} - ${formatTime(event.endTime)}`;

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
        const eventModal = document.getElementById("eventModal");

        if (event.target === previewModal) {
          closePreviewModal();
        }

        if (event.target === eventModal) {
          eventModal.classList.remove("show");
        }
      }

      // Make functions globally accessible
      window.previewAnnouncement = previewAnnouncement;
      window.closePreviewModal = closePreviewModal;
      window.filterEventsByDate = filterEventsByDate;
      window.clearDateFilter = clearDateFilter;

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