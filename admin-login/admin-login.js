// Password toggle functionality
const togglePasswordBtn = document.getElementById("togglePassword");
const passwordInput = document.getElementById("passwordInput");

if (togglePasswordBtn && passwordInput) {
  togglePasswordBtn.addEventListener("click", () => {
    const type =
      passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);

    // Toggle eye icon
    const eyeIcon = togglePasswordBtn.querySelector(".eye-icon");
    const eyeOffIcon = togglePasswordBtn.querySelector(".eye-off-icon");

    if (type === "text") {
      eyeIcon.style.display = "none";
      eyeOffIcon.style.display = "block";
    } else {
      eyeIcon.style.display = "block";
      eyeOffIcon.style.display = "none";
    }
  });
}
