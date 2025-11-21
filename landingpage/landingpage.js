// Search functionality
const searchInput = document.getElementById("searchInput");
const tableBody = document.getElementById("tableBody");
const rows = Array.from(tableBody.getElementsByTagName("tr"));

searchInput.addEventListener("input", (e) => {
  const searchTerm = e.target.value.toLowerCase();
  rows.forEach((row) => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? "" : "none";
  });
  updateShowingInfo();
});

// Entries selector
const entriesSelect = document.getElementById("entriesSelect");
entriesSelect.addEventListener("change", (e) => {
  const limit = parseInt(e.target.value);
  rows.forEach((row, index) => {
    row.style.display = index < limit ? "" : "none";
  });
  updateShowingInfo();
});

// Pagination buttons
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");

prevBtn.addEventListener("click", () => {
  console.log("Previous page");
});

nextBtn.addEventListener("click", () => {
  console.log("Next page");
});

function updateShowingInfo() {
  const visibleRows = rows.filter((row) => row.style.display !== "none");
  const showingInfo = document.querySelector(".showing-info");
  showingInfo.textContent = `Showing 1 to ${visibleRows.length} of ${rows.length} entries`;
}

// Edit and Delete button handlers
document.querySelectorAll(".btn-edit").forEach((btn) => {
  btn.addEventListener("click", (e) => {
    const row = e.target.closest("tr");
    console.log("Edit row:", row);
    alert("Edit functionality - will open edit form");
  });
});

document.querySelectorAll(".btn-delete").forEach((btn) => {
  btn.addEventListener("click", (e) => {
    if (confirm("Are you sure you want to delete this resident?")) {
      const row = e.target.closest("tr");
      row.remove();
      updateShowingInfo();
    }
  });
});