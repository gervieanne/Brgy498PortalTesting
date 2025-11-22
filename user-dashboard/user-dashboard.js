// Burger Menu Toggle (FIXED) - with null checks to prevent errors
function initializeSidebar() {
  const burgerMenu = document.querySelector(".burger-menu");
  const sidebar = document.querySelector(".sidebar");

  if (burgerMenu && sidebar) {
    burgerMenu.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("active");
    });

    // Close sidebar when clicking link (FIXED)
    const sidebarLinks = document.querySelectorAll(".sidebar a");
    sidebarLinks.forEach((link) => {
      link.addEventListener("click", () => sidebar.classList.remove("active"));
    });

    // Close sidebar when clicking outside (FIXED)
    document.addEventListener("click", function (event) {
      if (
        !event.target.closest(".sidebar") &&
        !event.target.closest(".burger-menu")
      ) {
        sidebar.classList.remove("active");
      }
    });
  }
}

// Initialize sidebar when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeSidebar);
} else {
  initializeSidebar();
}

// Live Clock
let timeUpdateInterval = null; // Store interval to prevent multiple intervals

function updateTime() {
  try {
    const timeElement = document.getElementById("time");
    if (timeElement) {
      const now = new Date();
      const hours = now.getHours();
      const minutes = now.getMinutes();
      const seconds = now.getSeconds();
      const ampm = hours >= 12 ? 'PM' : 'AM';
      const displayHours = hours % 12 || 12;
      const timeString = `${displayHours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')} ${ampm}`;
      timeElement.textContent = timeString;
    }
  } catch (error) {
    console.error('Error updating time:', error);
  }
}

// Update User Dashboard Date
function updateUserDate() {
  const dateEl = document.getElementById('userCurrentDate');
  const dayEl = document.getElementById('userCurrentDay');
  
  if (dateEl && dayEl) {
    const now = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const dayOptions = { weekday: 'long' };
    
    dateEl.textContent = now.toLocaleDateString('en-US', options);
    dayEl.textContent = now.toLocaleDateString('en-US', dayOptions);
  }
}

// Initialize date on page load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    updateUserDate();
  });
} else {
  updateUserDate();
}

// Update Recent Activity
function updateRecentActivity() {
  const activityList = document.getElementById('userActivityList');
  if (!activityList) return;

  // Fetch user's recent requests for activity
  fetch('../user-request/get_user_activity.php')
    .then(response => response.json())
    .then(data => {
      if (data.success && data.activities && data.activities.length > 0) {
        activityList.innerHTML = '';
        data.activities.forEach((activity, index) => {
          const activityItem = createActivityItem(activity, index);
          activityList.appendChild(activityItem);
        });
      } else {
        activityList.innerHTML = `
          <div class="activity-item">
            <div class="activity-icon">
              <i class="fas fa-info-circle"></i>
            </div>
            <div class="activity-content">
              <p class="activity-text">No recent activity</p>
              <span class="activity-time">Start by making a document request</span>
            </div>
          </div>
        `;
      }
    })
    .catch(error => {
      console.error('Error fetching activity:', error);
      activityList.innerHTML = `
        <div class="activity-item">
          <div class="activity-icon">
            <i class="fas fa-exclamation-circle"></i>
          </div>
          <div class="activity-content">
            <p class="activity-text">Unable to load activity</p>
            <span class="activity-time">Please refresh the page</span>
          </div>
        </div>
      `;
    });
}

function createActivityItem(activity, index) {
  const item = document.createElement('div');
  item.className = 'activity-item';
  item.style.animationDelay = `${(index + 1) * 0.1}s`;

  const iconMap = {
    'pending': 'fa-clock',
    'processing': 'fa-spinner',
    'ready': 'fa-check-double',
    'completed': 'fa-check-circle',
    'rejected': 'fa-times-circle',
    'submitted': 'fa-file-alt'
  };

  const icon = iconMap[activity.status] || 'fa-file-alt';
  const statusText = activity.status.charAt(0).toUpperCase() + activity.status.slice(1);

  item.innerHTML = `
    <div class="activity-icon">
      <i class="fas ${icon}"></i>
    </div>
    <div class="activity-content">
      <p class="activity-text">${activity.message || `Document request ${statusText.toLowerCase()}`}</p>
      <span class="activity-time">${activity.time_ago || 'Recently'}</span>
    </div>
  `;

  return item;
}

// Initialize activity on page load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    updateRecentActivity();
    // Refresh activity every 30 seconds
    setInterval(updateRecentActivity, 30000);
  });
} else {
  updateRecentActivity();
  // Refresh activity every 30 seconds
  setInterval(updateRecentActivity, 30000);
}
  
  // Update every minute (in case day changes)
  setInterval(updateUserDate, 60000);

// Announcement Modal Functions
function openAnnouncementModal(announcementData) {
  const modal = document.getElementById("announcementModal");
  const modalTitle = document.getElementById("modalAnnouncementTitle");
  const modalMessage = document.getElementById("modalAnnouncementMessage");
  const modalMedia = document.getElementById("modalAnnouncementMedia");
  const modalDate = document.getElementById("modalAnnouncementDate");
  const modalTime = document.getElementById("modalAnnouncementTime");

  if (!modal) return;

  // Set title and message
  modalTitle.textContent = announcementData.title || "Announcement";
  modalMessage.textContent = announcementData.message || "";

  // Clear previous media
  modalMedia.innerHTML = "";

  // Add image if available
  if (announcementData.image_path) {
    const img = document.createElement("img");
    img.src = announcementData.image_path;
    img.alt = announcementData.title || "Announcement Image";
    img.style.maxWidth = "100%";
    img.style.height = "auto";
    img.style.borderRadius = "8px";
    img.style.marginTop = "15px";
    modalMedia.appendChild(img);
  }

  // Add video if available
  if (announcementData.video_path) {
    const video = document.createElement("video");
    video.src = announcementData.video_path;
    video.controls = true;
    video.style.maxWidth = "100%";
    video.style.height = "auto";
    video.style.borderRadius = "8px";
    video.style.marginTop = "15px";
    modalMedia.appendChild(video);
  }

  // Set date and time
  modalDate.textContent = announcementData.date;
  modalTime.textContent = announcementData.time;

  // Show modal
  modal.classList.add('active');
  document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeAnnouncementModal() {
  const modal = document.getElementById("announcementModal");
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = ''; // Restore scrolling
  }
}

// Announcement Slideshow with Navigation Arrows and Modal
document.addEventListener("DOMContentLoaded", function () {
  // Small delay to ensure DOM is fully ready
  setTimeout(function() {
    try {
      initializeAnnouncementSlideshow();
    } catch (error) {
      console.error('Error initializing announcement slideshow:', error);
    }
  }, 100);
});

function initializeAnnouncementSlideshow() {
  try {
    const cards = document.querySelectorAll(".announcement-card");
    const bullets = document.querySelectorAll(".bullet");
    let currentIndex = 0;
    const interval = 5000; // 5 seconds
    let slideTimer;

    if (cards.length === 0) {
      return; // No cards, exit silently
    }

    function showSlide(index) {
      if (cards.length === 0) return;
      
      try {
        // Remove active class from all cards
        cards.forEach((card) => {
          card.classList.remove("active");
        });
        
        // Remove active class from all bullets
        bullets.forEach((bullet) => {
          bullet.classList.remove("active");
        });
        
        // Show the selected card
        if (cards[index]) {
          cards[index].classList.add("active");
        }
        
        // Activate the corresponding bullet
        if (bullets[index]) {
          bullets[index].classList.add("active");
        }
      } catch (error) {
        console.error('Error showing slide:', error);
      }
    }

    function nextSlide() {
      if (cards.length === 0) return;
      try {
        currentIndex = (currentIndex + 1) % cards.length;
        showSlide(currentIndex);
      } catch (error) {
        console.error('Error in nextSlide:', error);
      }
    }

    function prevSlide() {
      if (cards.length === 0) return;
      try {
        currentIndex = (currentIndex - 1 + cards.length) % cards.length;
        showSlide(currentIndex);
      } catch (error) {
        console.error('Error in prevSlide:', error);
      }
    }

    function startSlideshow() {
      if (cards.length <= 1) return;
      try {
        if (slideTimer) clearInterval(slideTimer);
        slideTimer = setInterval(nextSlide, interval);
      } catch (error) {
        console.error('Error starting slideshow:', error);
      }
    }

    // Bullet Click Event
    bullets.forEach((bullet, index) => {
      try {
        bullet.addEventListener("click", () => {
          currentIndex = index;
          showSlide(currentIndex);
          startSlideshow();
        });
      } catch (error) {
        console.error('Error adding bullet click handler:', error);
      }
    });

    // Announcement Card Click to Open Modal
    cards.forEach((card) => {
      try {
        card.addEventListener("click", function () {
          let decodedMessage = "";
          try {
            decodedMessage = JSON.parse(this.dataset.message);
          } catch (e) {
            decodedMessage = this.dataset.message;
          }

          const announcementData = {
            title: this.dataset.title,
            message: decodedMessage,
            image_path: this.dataset.imagePath || "",
            video_path: this.dataset.videoPath || "",
            date: this.dataset.date,
            time: this.dataset.time
          };

          openAnnouncementModal(announcementData);
        });
      } catch (error) {
        console.error('Error adding card click handler:', error);
      }
    });

    // Store functions in a way that's accessible to event handlers
    window.announcementNavigation = {
      prevSlide: prevSlide,
      nextSlide: nextSlide,
      startSlideshow: startSlideshow
    };
    
    // Add navigation arrows functionality if announcements exist and there's more than one
    if (cards.length > 1) {
      try {
        // Get arrows from HTML (they're now in the PHP)
        const prevArrow = document.getElementById("prevAnnouncementBtn");
        const nextArrow = document.getElementById("nextAnnouncementBtn");
        
        if (prevArrow && nextArrow) {
          // Remove any existing event listeners
          const prevArrowClone = prevArrow.cloneNode(true);
          const nextArrowClone = nextArrow.cloneNode(true);
          prevArrow.parentNode.replaceChild(prevArrowClone, prevArrow);
          nextArrow.parentNode.replaceChild(nextArrowClone, nextArrow);
          
          // Add click handlers with direct function references
          prevArrowClone.onclick = function(e) {
            e.stopPropagation();
            e.preventDefault();
            prevSlide();
            startSlideshow();
            return false;
          };
          
          nextArrowClone.onclick = function(e) {
            e.stopPropagation();
            e.preventDefault();
            nextSlide();
            startSlideshow();
            return false;
          };
          
          // Also add event listeners as backup
          prevArrowClone.addEventListener("click", function(e) {
            e.stopPropagation();
            e.preventDefault();
            prevSlide();
            startSlideshow();
          }, true);
          
          nextArrowClone.addEventListener("click", function(e) {
            e.stopPropagation();
            e.preventDefault();
            nextSlide();
            startSlideshow();
          }, true);
          
          // Ensure arrows are visible and clickable
          prevArrowClone.style.display = 'flex';
          nextArrowClone.style.display = 'flex';
          prevArrowClone.style.visibility = 'visible';
          nextArrowClone.style.visibility = 'visible';
          prevArrowClone.style.opacity = '1';
          nextArrowClone.style.opacity = '1';
          prevArrowClone.style.pointerEvents = 'auto';
          nextArrowClone.style.pointerEvents = 'auto';
          prevArrowClone.style.zIndex = '10001';
          nextArrowClone.style.zIndex = '10001';
          
          startSlideshow();
        }
      } catch (error) {
        console.error('Error setting up arrows:', error);
      }
    } else {
      // Hide arrows if only one or no announcements
      try {
        const prevArrow = document.getElementById("prevAnnouncementBtn");
        const nextArrow = document.getElementById("nextAnnouncementBtn");
        if (prevArrow) prevArrow.style.display = 'none';
        if (nextArrow) nextArrow.style.display = 'none';
      } catch (error) {
        // Ignore errors when hiding arrows
      }
    }

    // Pause slideshow on hover
    const announcementsSection = document.querySelector(".announcements-section");
    if (announcementsSection) {
      try {
        announcementsSection.addEventListener("mouseenter", () => {
          if (slideTimer) clearInterval(slideTimer);
        });
        announcementsSection.addEventListener("mouseleave", () => {
          if (cards.length > 1) {
            startSlideshow();
          }
        });
      } catch (error) {
        console.error('Error setting up hover handlers:', error);
      }
    }
  } catch (error) {
    console.error('Error initializing announcement slideshow:', error);
    // Don't break the page if slideshow fails
  }
}

// Initialize clock - multiple methods to ensure it works
(function initializeClock() {
  function startClock() {
    try {
      // Clear any existing interval first
      if (timeUpdateInterval) {
        clearInterval(timeUpdateInterval);
        timeUpdateInterval = null;
      }
      
      // Update immediately
      updateTime();
      
      // Update every second
      timeUpdateInterval = setInterval(function() {
        updateTime();
      }, 1000);
    } catch (error) {
      console.error('Error starting clock:', error);
    }
  }
  
  // Try multiple initialization methods
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      startClock();
    });
  } else {
    // DOM is already ready
    startClock();
  }
  
  // Also try after a small delay as fallback
  setTimeout(function() {
    try {
      const timeEl = document.getElementById("time");
      if (timeEl) {
        // Check if it's still stuck at default time
        const currentText = timeEl.textContent.trim();
        if (currentText === "12:00:00 AM" || currentText === "12:00:00 PM" || currentText === "") {
          // Clear any existing interval
          if (timeUpdateInterval) {
            clearInterval(timeUpdateInterval);
            timeUpdateInterval = null;
          }
          // Start fresh
          updateTime();
          timeUpdateInterval = setInterval(updateTime, 1000);
        }
      }
    } catch (error) {
      console.error('Error in clock fallback:', error);
    }
  }, 1000);
  
  // Final fallback after 2 seconds
  setTimeout(function() {
    try {
      const timeEl = document.getElementById("time");
      if (timeEl) {
        const currentText = timeEl.textContent.trim();
        if (currentText === "12:00:00 AM" || currentText === "12:00:00 PM" || currentText === "") {
          if (timeUpdateInterval) {
            clearInterval(timeUpdateInterval);
            timeUpdateInterval = null;
          }
          updateTime();
          timeUpdateInterval = setInterval(updateTime, 1000);
        }
      }
    } catch (error) {
      console.error('Error in clock final fallback:', error);
    }
  }, 2000);
})();

// Modal close button events
document.addEventListener("DOMContentLoaded", function() {
  const closeModalBtn = document.querySelector('.close-modal-btn');
  const closeBottomBtn = document.querySelector('.modal-close-bottom-btn');
  const modal = document.getElementById('announcementModal');

  if (closeModalBtn) {
    closeModalBtn.addEventListener('click', closeAnnouncementModal);
  }
  if (closeBottomBtn) {
    closeBottomBtn.addEventListener('click', closeAnnouncementModal);
  }

  // Close modal when clicking outside content
  if (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) closeAnnouncementModal();
    });
  }

  // Close modal with Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAnnouncementModal();
  });
});
