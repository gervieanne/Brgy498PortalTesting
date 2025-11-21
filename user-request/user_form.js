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

// Text to uppercase automatically
document.addEventListener("DOMContentLoaded", function () {
  const inputs = document.querySelectorAll('input[type="text"], textarea');

  inputs.forEach((input) => {
    input.addEventListener("input", function () {
      this.value = this.value.toUpperCase();
    });
  });
});

// STEP NAVIGATION SYSTEM
document.addEventListener("DOMContentLoaded", function () {
  let currentStep = 0;
  const sections = document.querySelectorAll(".section[data-step]");
  const navItems = document.querySelectorAll(".nav-item");
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const form = document.getElementById("documentRequestForm");
  const previewModal = document.getElementById("previewModal");

  function showStep(step) {
    sections.forEach((section) => {
      section.classList.remove("active");
    });

    sections[step].classList.add("active");

    navItems.forEach((item, index) => {
      item.classList.remove("active");
      if (index === step) {
        item.classList.add("active");
      }
    });

    if (step === 0) {
      prevBtn.style.display = "none";
      nextBtn.style.display = "inline-block";
      nextBtn.textContent = "Next";
    } else if (step === sections.length - 1) {
      prevBtn.style.display = "inline-block";
      nextBtn.style.display = "inline-block";
      nextBtn.textContent = "Done";
    } else {
      prevBtn.style.display = "inline-block";
      nextBtn.style.display = "inline-block";
      nextBtn.textContent = "Next";
    }

    currentStep = step;
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  function validateStep(step) {
    const currentSection = sections[step];
    const requiredFields = currentSection.querySelectorAll("[required]");
    let isValid = true;
    let firstInvalidField = null;

    requiredFields.forEach((field) => {
      if (field.type === "checkbox") {
        if (!field.checked) {
          isValid = false;
          if (!firstInvalidField) firstInvalidField = field;
          field.style.outline = "2px solid #f44336";
        }
      } else if (field.type === "file") {
        if (!field.files || field.files.length === 0) {
          isValid = false;
          if (!firstInvalidField) firstInvalidField = field;
          field.style.border = "2px solid #f44336";
        }
      } else {
        if (!field.value.trim()) {
          isValid = false;
          if (!firstInvalidField) firstInvalidField = field;
          field.style.border = "1px solid #f44336";
        } else {
          field.style.border = "1px solid #ccc";
        }
      }
    });

    if (!isValid) {
      showValidationAlert(
        "Please fill in all required fields before proceeding."
      );
      if (firstInvalidField) {
        firstInvalidField.scrollIntoView({
          behavior: "smooth",
          block: "center",
        });
        firstInvalidField.focus();
      }
    }

    return isValid;
  }

  function showValidationAlert(message) {
    const alert = document.getElementById("validationAlert");
    alert.textContent = message;
    alert.style.display = "block";
    alert.style.animation = "slideDown 0.3s ease";

    setTimeout(() => {
      alert.style.animation = "slideUp 0.3s ease";
      setTimeout(() => {
        alert.style.display = "none";
      }, 300);
    }, 3000);
  }

  function showPreviewModal() {
    const formData = new FormData(form);
    let previewHTML = `
      <h2 style="color: #21205d; margin-bottom: 20px; border-bottom: 2px solid #21205d; padding-bottom: 10px;">
        Review Your Information
      </h2>
    `;

    previewHTML += `
      <div style="margin-bottom: 30px;">
        <h3 style="color: #21205d; font-size: 18px; margin-bottom: 15px;">Personal Information</h3>
        <table style="width: 100%; border-collapse: collapse;">
          <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px; font-weight: 600; width: 40%;">Full Name:</td>
            <td style="padding: 10px;">${formData.get(
              "first_name"
            )} ${formData.get("middle_name")} ${formData.get("last_name")} ${
      formData.get("suffix") || ""
    }</td>
          </tr>
          <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px; font-weight: 600;">Contact Number:</td>
            <td style="padding: 10px;">${formData.get("contact_number")}</td>
          </tr>
          <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px; font-weight: 600;">Date of Birth:</td>
            <td style="padding: 10px;">${formData.get("date_of_birth")}</td>
          </tr>
          <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px; font-weight: 600;">Gender:</td>
            <td style="padding: 10px;">${formData.get("gender")}</td>
          </tr>
        </table>
      </div>
    `;

    previewHTML += `
      <div style="margin-bottom: 30px;">
        <h3 style="color: #21205d; font-size: 18px; margin-bottom: 15px;">Document Request</h3>
        <table style="width: 100%; border-collapse: collapse;">
          <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px; font-weight: 600; width: 40%;">Document Type:</td>
            <td style="padding: 10px;">${formData.get("document_type")}</td>
          </tr>
          <tr style="border-bottom: 1px solid #e0e0e0;">
          <td style="padding: 10px;">${(formData.get("purpose") || "")
          .replace(/\\r\\n/g, "<br>")
          .replace(/\\n/g, "<br>")
          .replace(/\\r/g, "<br>")}</td>
          </tr>
          <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px; font-weight: 600;">Quantity:</td>
            <td style="padding: 10px;">${formData.get("quantity")}</td>
          </tr>
        </table>
      </div>
    `;

    const selfieFile = document.getElementById("selfie_with_id").files[0];
    const idFile = document.getElementById("id_picture").files[0];
    previewHTML += `
      <div style="margin-bottom: 30px;">
        <h3 style="color: #21205d; font-size: 18px; margin-bottom: 15px;">Uploaded Documents</h3>
        <table style="width: 100%; border-collapse: collapse;">
          <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px; font-weight: 600; width: 40%;">Selfie with ID:</td>
            <td style="padding: 10px;">${
              selfieFile ? selfieFile.name : "No file selected"
            }</td>
          </tr>
          <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px; font-weight: 600;">ID Picture:</td>
            <td style="padding: 10px;">${
              idFile ? idFile.name : "No file selected"
            }</td>
          </tr>
        </table>
      </div>
    `;

    previewHTML += `
      <div class="modal-buttons">
        <button type="button" id="closeModalBtn">Go Back</button>
        <button type="button" id="finalSubmitBtn">Confirm & Submit</button>
      </div>
    `;

    document.getElementById("modalContent").innerHTML = previewHTML;
    previewModal.style.display = "block";

    document
      .getElementById("closeModalBtn")
      .addEventListener("click", function () {
        previewModal.style.display = "none";
      });

    document
      .getElementById("finalSubmitBtn")
      .addEventListener("click", function () {
        form.submit();
      });
  }

  prevBtn.addEventListener("click", function () {
    if (currentStep > 0) {
      showStep(currentStep - 1);
    }
  });

  nextBtn.addEventListener("click", function () {
    if (currentStep < sections.length - 1) {
      if (validateStep(currentStep)) {
        showStep(currentStep + 1);
      }
    } else {
      if (validateStep(currentStep)) {
        showPreviewModal();
      }
    }
  });

  navItems.forEach((item, index) => {
    item.addEventListener("click", function (e) {
      e.preventDefault();
      if (index < currentStep) {
        showStep(index);
      } else if (index === currentStep) {
        // Already on this step
      } else {
        if (validateStep(currentStep)) {
          showStep(index);
        }
      }
    });
  });

  document.querySelectorAll("input, select, textarea").forEach((field) => {
    field.addEventListener("input", function () {
      this.style.border = "1px solid #ccc";
      this.style.outline = "none";
    });

    field.addEventListener("change", function () {
      this.style.border = "1px solid #ccc";
      this.style.outline = "none";
    });
  });

  showStep(0);
});

// privacy modal functionality with tooltip

document.addEventListener("DOMContentLoaded", function () {
  // Track if user has viewed the privacy policy
  let privacyPolicyViewed = false;

  // Get elements
  const modal = document.getElementById("privacyModal");
  const openBtn = document.getElementById("openPrivacyModal");
  const closeBtn = document.getElementById("closePrivacyModalBtn");
  const privacyCheckbox = document.getElementById("privacy_agreement");
  const checkboxLabel = document.querySelector(
    'label[for="privacy_agreement"]'
  );

  console.log("Privacy elements found:", {
    modal: !!modal,
    openBtn: !!openBtn,
    closeBtn: !!closeBtn,
    privacyCheckbox: !!privacyCheckbox,
    checkboxLabel: !!checkboxLabel,
  });

  // Only proceed if all elements exist
  if (!privacyCheckbox || !checkboxLabel || !modal || !openBtn || !closeBtn) {
    console.error("Privacy policy elements not found!");
    return;
  }

  // Disable checkbox initially
  privacyCheckbox.disabled = true;
  privacyCheckbox.style.cursor = "not-allowed";
  privacyCheckbox.style.opacity = "0.5";

  // Create tooltip for the Data Privacy Policy link
  let tooltip = document.getElementById("privacy-tooltip");
  if (!tooltip) {
    tooltip = document.createElement("span");
    tooltip.id = "privacy-tooltip";
    tooltip.textContent = "Please read the data privacy policy first.";
    tooltip.style.cssText = `
      position: absolute;
      background: #e74c3c;
      color: white;
      padding: 8px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
      z-index: 1000;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    `;

    // Add arrow to tooltip
    const arrow = document.createElement("span");
    arrow.style.cssText = `
      content: '';
      position: absolute;
      bottom: -5px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 0;
      border-left: 6px solid transparent;
      border-right: 6px solid transparent;
      border-top: 6px solid #e74c3c;
    `;
    tooltip.appendChild(arrow);

    // Position tooltip relative to the link
    openBtn.style.position = "relative";
    openBtn.parentElement.style.position = "relative";
    openBtn.parentElement.insertBefore(tooltip, openBtn);
  }

  // Show tooltip on hover
  openBtn.addEventListener("mouseenter", function () {
    if (!privacyPolicyViewed) {
      const rect = openBtn.getBoundingClientRect();
      const parentRect = openBtn.parentElement.getBoundingClientRect();

      tooltip.style.bottom = "calc(100% + 10px)";
      tooltip.style.left = "50%";
      tooltip.style.transform = "translateX(-50%)";
      tooltip.style.opacity = "1";
    }
  });

  openBtn.addEventListener("mouseleave", function () {
    tooltip.style.opacity = "0";
  });

  // Open modal
  openBtn.addEventListener("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    console.log("Opening privacy modal");
    tooltip.style.opacity = "0"; // Hide tooltip when modal opens
    modal.style.display = "flex";
    modal.classList.add("show");
    document.body.style.overflow = "hidden";
  });

  // Close modal and enable checkbox
  closeBtn.addEventListener("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    console.log("Closing privacy modal");
    modal.style.display = "none";
    modal.classList.remove("show");
    document.body.style.overflow = "";

    // Enable checkbox after viewing
    if (!privacyPolicyViewed) {
      privacyPolicyViewed = true;
      privacyCheckbox.disabled = false;
      privacyCheckbox.style.cursor = "pointer";
      privacyCheckbox.style.opacity = "1";

      // Change tooltip to success message
      tooltip.textContent = "Policy Viewed. You can now agree.";
      tooltip.style.background = "#27ae60";
      const arrow = tooltip.querySelector("span");
      if (arrow) {
        arrow.style.borderTopColor = "#27ae60";
      }

      // Show success tooltip briefly
      tooltip.style.opacity = "1";
      setTimeout(() => {
        tooltip.style.opacity = "0";
      }, 2000);

      console.log("Privacy policy marked as viewed");
    }
  });

  // Close when clicking outside
  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      closeBtn.click();
    }
  });

  // Prevent checkbox from being checked before viewing
  privacyCheckbox.addEventListener("click", function (e) {
    if (!privacyPolicyViewed) {
      e.preventDefault();
      e.stopPropagation();
      alert(
        "Please read the Data Privacy Policy by clicking the link before agreeing."
      );
      this.checked = false;
    }
  });

  // Extra safety - prevent change event
  privacyCheckbox.addEventListener("change", function (e) {
    if (!privacyPolicyViewed) {
      e.preventDefault();
      this.checked = false;
    }
  });
});
