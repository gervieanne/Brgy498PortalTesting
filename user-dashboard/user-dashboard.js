// Burger Menu Toggle (FIXED)
const burgerMenu = document.querySelector(".burger-menu");
const sidebar = document.querySelector(".sidebar");

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

// Live Clock
function updateTime() {
  const now = new Date();
  const timeString = now.toLocaleTimeString("en-US", { hour12: true });
  document.getElementById("time").textContent = timeString;
}

// Announcement Modal Functions
function openAnnouncementModal(announcementData) {
  const modal = document.getElementById("announcementModal");
  const modalTitle = document.getElementById("modalAnnouncementTitle");
  const modalMessage = document.getElementById("modalAnnouncementMessage");
  const modalMedia = document.getElementById("modalAnnouncementMedia");
  const modalDate = document.getElementById("modalAnnouncementDate");
  const modalTime = document.getElementById("modalAnnouncementTime");

  // Set modal content
  modalTitle.textContent = announcementData.title;

  // Convert markdown-style links to HTML
  let message = announcementData.message;
  message = message.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
  modalMessage.innerHTML = message;

  // Clear previous media
  modalMedia.innerHTML = '';

  // Add image if exists
  if (announcementData.image_path) {
    const img = document.createElement('img');
    img.src = announcementData.image_path;
    img.alt = 'Announcement Image';
    modalMedia.appendChild(img);
  }

  // Add video if exists
  if (announcementData.video_path) {
    const video = document.createElement('video');
    video.controls = true;
    const source = document.createElement('source');
    source.src = announcementData.video_path;
    source.type = 'video/mp4';
    video.appendChild(source);
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
  modal.classList.remove('active');
  document.body.style.overflow = ''; // Restore scrolling
}

// Announcement Slideshow with Navigation Arrows and Modal
document.addEventListener("DOMContentLoaded", function () {
  const cards = document.querySelectorAll(".announcement-card");
  const bullets = document.querySelectorAll(".bullet");
  let currentIndex = 0;
  const interval = 5000; // 5 seconds
  let slideTimer;

  function showSlide(index) {
    cards.forEach((card) => card.classList.remove("active"));
    bullets.forEach((bullet) => bullet.classList.remove("active"));
    if (cards[index]) {
      cards[index].classList.add("active");
      bullets[index].classList.add("active");
    }
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % cards.length;
    showSlide(currentIndex);
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + cards.length) % cards.length;
    showSlide(currentIndex);
  }

  function startSlideshow() {
    if (slideTimer) clearInterval(slideTimer);
    slideTimer = setInterval(nextSlide, interval);
  }

  // Bullet Click Event
  bullets.forEach((bullet, index) => {
    bullet.addEventListener("click", () => {
      currentIndex = index;
      showSlide(currentIndex);
      startSlideshow();
    });
  });

  // ----- FIXED: Announcement Card Click to Open Modal -----
  cards.forEach((card) => {
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
  });

  // Add navigation arrows if announcements exist
  if (cards.length > 0) {
    const announcementsList = document.querySelector(".announcements-list");

    const prevArrow = document.createElement("button");
    prevArrow.className = "announcement-nav prev";
    prevArrow.innerHTML = "‹";
    prevArrow.addEventListener("click", (e) => {
      e.stopPropagation();
      prevSlide();
      startSlideshow();
    });

    const nextArrow = document.createElement("button");
    nextArrow.className = "announcement-nav next";
    nextArrow.innerHTML = "›";
    nextArrow.addEventListener("click", (e) => {
      e.stopPropagation();
      nextSlide();
      startSlideshow();
    });

    announcementsList.appendChild(prevArrow);
    announcementsList.appendChild(nextArrow);

    startSlideshow();
  }

  // Pause slideshow on hover
  const announcementsSection = document.querySelector(".announcements-section");
  if (announcementsSection) {
    announcementsSection.addEventListener("mouseenter", () => {
      if (slideTimer) clearInterval(slideTimer);
    });
    announcementsSection.addEventListener("mouseleave", () => {
      startSlideshow();
    });
  }

  // Modal close button events
  const closeModalBtn = document.querySelector('.close-modal-btn');
  const closeBottomBtn = document.querySelector('.modal-close-bottom-btn');
  const modal = document.getElementById('announcementModal');

  if (closeModalBtn) closeModalBtn.addEventListener('click', closeAnnouncementModal);
  if (closeBottomBtn) closeBottomBtn.addEventListener('click', closeAnnouncementModal);

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

// Initialize clock
document.addEventListener("DOMContentLoaded", () => {
  updateTime();
  setInterval(updateTime, 1000);
});
