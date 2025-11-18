// Clock functionality
const zeroPad = (num) => (num < 10 ? "0" + num : num);

const formatTime = (date) => {
  let hours = date.getHours();
  const minutes = zeroPad(date.getMinutes());
  const seconds = zeroPad(date.getSeconds());
  const ampm = hours >= 12 ? "PM" : "AM";
  hours = hours % 12 || 12;
  return `${zeroPad(hours)}:${minutes}:${seconds} ${ampm}`;
};

const updateClock = () => {
  const clock = document.getElementById("clock");
  if (clock) clock.textContent = formatTime(new Date());
};

setInterval(updateClock, 1000);
updateClock();

// Burger menu functionality
const burgerMenu = document.getElementById("burgerMenu");
const sidebar = document.querySelector(".sidebar");

burgerMenu.addEventListener("click", function () {
  sidebar.classList.toggle("active");
});

// Close sidebar when clicking on a link
const sidebarLinks = document.querySelectorAll(".sidebar li a");
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

// Toggle section functionality (redesigned)
function toggleSection(sectionId) {
  const content = document.getElementById(`${sectionId}-content`);
  const icon = document.getElementById(`${sectionId}-icon`);
  const header = content.previousElementSibling;

  // Toggle content
  content.classList.toggle("show");

  // Toggle icon rotation
  icon.classList.toggle("rotate");

  // Toggle active state on header
  header.classList.toggle("active");
}

// Close sections when clicking outside
document.addEventListener("click", function (event) {
  const sections = document.querySelectorAll(".info-section");

  sections.forEach(function (section) {
    const header = section.querySelector(".section-header");
    const content = section.querySelector(".section-content");
    const icon = section.querySelector(".toggle-icon");

    // If click is outside the section and content is open
    if (!section.contains(event.target) && content.classList.contains("show")) {
      content.classList.remove("show");
      icon.classList.remove("rotate");
      header.classList.remove("active");
    }
  });
});

// FAQ Modal functionality
const faqModal = document.getElementById("faqModal");
const openModalBtn = document.getElementById("openModalbtn");
const closeModalBtn = document.getElementById("closeModalBtn");
const closeFaqBtn = document.getElementById("closeFaqBtn");

// Open modal
if (openModalBtn) {
  openModalBtn.onclick = function () {
    faqModal.classList.add("show");
  };
}

// Close modal - X button
if (closeModalBtn) {
  closeModalBtn.onclick = function () {
    faqModal.classList.remove("show");
  };
}

// Close modal - Close button
if (closeFaqBtn) {
  closeFaqBtn.onclick = function () {
    faqModal.classList.remove("show");
  };
}

// Close modal when clicking outside
window.addEventListener("click", (e) => {
  if (e.target === faqModal) {
    faqModal.classList.remove("show");
  }
});
