// Burger Menu Toggle
const burgerMenu = document.getElementById("burgerMenu");
const sidebar = document.querySelector(".sidebar");

burgerMenu.addEventListener("click", function () {
  sidebar.classList.toggle("active");
});

// Close sidebar when clicking on a link
const sidebarLinks = document.querySelectorAll(".sidebar .nav-item");
sidebarLinks.forEach((link) => {
  link.addEventListener("click", function () {
    sidebar.classList.remove("active");
  });
});

// Close sidebar when clicking outside
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

// Announcement Slideshow with Navigation Arrows
document.addEventListener("DOMContentLoaded", function () {
  const cards = document.querySelectorAll(".announcement-card");
  const bullets = document.querySelectorAll(".bullet");
  let currentIndex = 0;
  const interval = 5000; // 5 seconds
  let slideTimer;

  function showSlide(index) {
    // Hide all cards and deactivate all bullets
    cards.forEach((card) => card.classList.remove("active"));
    bullets.forEach((bullet) => bullet.classList.remove("active"));

    // Show current card and activate current bullet
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

  // Add navigation arrows if announcements exist
  if (cards.length > 0) {
    const announcementsList = document.querySelector(".announcements-list");

    const prevArrow = document.createElement("button");
    prevArrow.className = "announcement-nav prev";
    prevArrow.innerHTML = "‹";
    prevArrow.addEventListener("click", () => {
      prevSlide();
      startSlideshow();
    });

    const nextArrow = document.createElement("button");
    nextArrow.className = "announcement-nav next";
    nextArrow.innerHTML = "›";
    nextArrow.addEventListener("click", () => {
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
});

// Initialize clock
document.addEventListener("DOMContentLoaded", () => {
  updateTime();
  setInterval(updateTime, 1000);
});
