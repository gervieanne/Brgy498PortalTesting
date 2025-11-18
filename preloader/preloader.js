// Show preloader when page loads
window.addEventListener("load", function () {
  const preloader = document.getElementById("preloader");
  if (preloader) {
    setTimeout(() => {
      preloader.classList.add("hidden");
    }, 800);
  }
});

// Show preloader when clicking links
document.addEventListener("click", function (e) {
  const link = e.target.closest("a");

  // Check if it's the logout button - if so, don't show preloader
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn && (e.target === logoutBtn || e.target.closest("#logoutBtn"))) {
    return; // Don't show preloader for logout button
  }

  // Check if link exists and is a valid navigation link
  if (link && link.href) {
    const href = link.getAttribute("href");

    // Don't show preloader for:
    // - Hash links (e.g., #step1, #section2)
    // - Links that open in new tab
    // - JavaScript void links
    // - Empty or null hrefs
    if (
      !href ||
      href.startsWith("#") ||
      href.startsWith("javascript:") ||
      link.target === "_blank"
    ) {
      return; // Don't show preloader for these links
    }

    // Only show preloader for actual page navigation
    const preloader = document.getElementById("preloader");
    if (preloader) {
      preloader.classList.remove("hidden");
    }
  }
});
