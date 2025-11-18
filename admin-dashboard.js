const burgerMenu = document.getElementById("burgerMenu");
const sidebar = document.getElementById("sidebar");

burgerMenu.addEventListener("click", function (e) {
  e.stopPropagation();
  sidebar.classList.toggle("active");
});

// Close sidebar when clicking outside
document.addEventListener("click", function (event) {
  if (!sidebar.contains(event.target) && !burgerMenu.contains(event.target)) {
    sidebar.classList.remove("active");
  }
});

// Close sidebar when clicking any link
document.querySelectorAll(".sidebar a").forEach((link) => {
  link.addEventListener("click", () => {
    sidebar.classList.remove("active");
  });
});

// Real-time clock (updates every second)
(function () {
  function formatAMPM(date) {
    let hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, "0");
    const seconds = String(date.getSeconds()).padStart(2, "0");
    const ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    return `${hours}:${minutes}:${seconds} ${ampm}`;
  }

  function updateClock() {
    const el = document.getElementById("clock");
    if (!el) return;
    el.textContent = formatAMPM(new Date());
  }

  updateClock();
  setInterval(updateClock, 1000);
})();
