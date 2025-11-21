<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barangay 498</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Poppins', sans-serif; background-color: #f5f5f5; }

/* Navigation */
nav { display: flex; justify-content: space-between; align-items: center; padding: 20px 60px; background-color: #4a4a8e; position: relative; z-index: 100; }
.nav-links { display: flex; gap: 35px; list-style: none; }
.nav-links a { color: white; text-decoration: none; font-size: 14px; font-weight: 400; transition: color 0.3s; cursor: pointer; }
.nav-links a:hover { color: #f5fd12; }

.logo { display: flex; align-items: center; gap: 15px; }
.logo img { width: 60px; height: 60px; object-fit: contain; }
.logo p { color: white; font-size: 18px; font-weight: 600; margin: 0; }

/* Hero Section */
.hero { background: linear-gradient(135deg, #4a4a8e 0%, #3d3d7a 100%); padding: 100px 20px; color: white; text-align: center; position: relative; overflow: hidden; transition: opacity 0.8s ease, transform 0.8s ease; }
.hero-content { max-width: 700px; margin: 0 auto; }
.hero h1 { font-size: 48px; font-weight: 700; margin-bottom: 20px; line-height: 1.3; }
.hero p { font-size: 16px; line-height: 1.7; color: #f5f5f5; margin-bottom: 30px; }

/* Sign In Button */
.signin-btn { background-color: #3d3d7a; color: white; border: none; padding: 15px 30px; font-size: 16px; font-weight: 600; border-radius: 50px; cursor: pointer; transition: all 0.3s ease; }
.signin-btn:hover { background-color: #2d2d5a; transform: scale(1.05) rotate(-1deg); box-shadow: 0 8px 20px rgba(0,0,0,0.3); }

/* Sign in cards */
.signin-cards { max-width: 800px; margin: 50px auto 80px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; opacity: 0; transform: translateY(40px); transition: opacity 0.8s ease, transform 0.8s ease; display: none; }
.service-card { background-color: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; text-decoration: none; color: inherit; display: block; text-align: center; padding: 40px 20px; }
.service-card img { width: 60px; height: 60px; object-fit: contain; filter: brightness(0) saturate(100%) invert(28%) sepia(65%) saturate(7500%) hue-rotate(247deg) brightness(93%) contrast(86%); margin-bottom: 15px; }
.service-card h3 { font-size: 18px; font-weight: 600; color: #2d2d5a; margin-bottom: 8px; }
.service-card p { font-size: 14px; color: #666; line-height: 1.5; }
.service-card:hover { transform: translateY(-10px) scale(1.05); box-shadow: 0 15px 30px rgba(0,0,0,0.2); }

/* Officials container */
#officials-content { max-width: 1200px; margin: 50px auto; opacity: 0; transform: translateY(40px); transition: opacity 0.8s ease, transform 0.8s ease; display: none; }
.officials-header { text-align: center; margin-bottom: 40px; }
.officials-header h1 { font-size: 32px; color: #2d2d5a; }
.officials-header p { font-size: 16px; color: #555; margin-top: 8px; }
.officials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; }
.official-card { background: white; border-radius: 15px; overflow: hidden; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); padding: 20px; transition: transform 0.3s, box-shadow 0.3s; }
.official-card:hover { transform: translateY(-8px) scale(1.03); box-shadow: 0 12px 30px rgba(0,0,0,0.2); }
.official-card img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin-bottom: 15px; }
.official-card h3 { font-size: 18px; color: #2d2d5a; margin-bottom: 5px; }
.official-card p { font-size: 14px; color: #666; }

@media(max-width:768px) { .hero h1 { font-size: 36px; } .hero p { font-size: 15px; } }
@media(max-width:480px) { .hero h1 { font-size:28px; } .signin-cards { grid-template-columns: 1fr; } .officials-grid { grid-template-columns: 1fr; } } </style>

</head>
<body>

<!-- Navigation -->

<nav>
  <ul class="nav-links">
    <li><a id="home-link">Home</a></li>
    <li><a id="officials-btn">Officials</a></li>
  </ul>
  <div class="logo">
    <img src="../images/barangay-logo.png" alt="Barangay Logo">
    <p>Barangay 498</p>
  </div>
</nav>

<!-- Hero Section -->

<section class="hero" id="hero-section">
  <div class="hero-content">
    <h1>Barangay Management System</h1>
    <p>Mabuhay! This is the official platform of Barangay 498, Zone 49, District IV, Manila, designed to streamline operations and enhance public service delivery.</p>
    <button class="signin-btn" id="signin-btn">Sign In</button>
  </div>
</section>

<!-- Sign In Cards -->

<section class="signin-cards" id="signin-cards">
  <a href="../user-login/user-login.php" class="service-card">
    <img src="../images/user-logo.png" alt="User Logo">
    <h3>Sign In as User</h3>
    <p>Access Resident Dashboard and Services</p>
  </a>
  <a href="../admin-login/admin-login.php" class="service-card">
    <img src="../images/admin-logo.png" alt="Admin Logo">
    <h3>Sign In as Admin</h3>
    <p>Access Admin Dashboard and Management Tools</p>
  </a>
</section>

<!-- Officials Content (Embedded) -->

<section id="officials-content">
  <div class="officials-header">
    <h1>Barangay Officials</h1>
    <p>Meet Our Dedicated Community Leaders</p>
  </div>
  <div class="officials-grid">
    <div class="official-card">
      <img src="../images/sample-official1.jpg" alt="Official 1">
      <h3>Juan Dela Cruz</h3>
      <p>Barangay Captain</p>
    </div>
    <div class="official-card">
      <img src="../images/sample-official2.jpg" alt="Official 2">
      <h3>Maria Santos</h3>
      <p>Secretary</p>
    </div>
    <div class="official-card">
      <img src="../images/sample-official3.jpg" alt="Official 3">
      <h3>Pedro Reyes</h3>
      <p>Treasurer</p>
    </div>
    <!-- Add more officials here as needed -->
  </div>
</section>

<script>
// Elements
const signinBtn = document.getElementById('signin-btn');
const heroSection = document.getElementById('hero-section');
const signinCards = document.getElementById('signin-cards');
const homeLink = document.getElementById('home-link');
const officialsBtn = document.getElementById('officials-btn');
const officialsContent = document.getElementById('officials-content');

// Show Sign In cards
signinBtn.addEventListener('click', () => {
  heroSection.style.opacity = 0;
  heroSection.style.transform = 'translateY(40px)';
  setTimeout(() => {
    heroSection.style.display = 'none';
    signinCards.style.display = 'grid';
    setTimeout(() => {
      signinCards.style.opacity = 1;
      signinCards.style.transform = 'translateY(0)';
    }, 50);
  }, 800);
});

// Return Home from Sign In or Officials
homeLink.addEventListener('click', () => {
  signinCards.style.opacity = 0;
  signinCards.style.transform = 'translateY(40px)';
  officialsContent.style.opacity = 0;
  officialsContent.style.transform = 'translateY(40px)';
  setTimeout(() => {
    signinCards.style.display = 'none';
    officialsContent.style.display = 'none';
    heroSection.style.display = 'block';
    setTimeout(() => {
      heroSection.style.opacity = 1;
      heroSection.style.transform = 'translateY(0)';
    }, 50);
  }, 400);
});

// Show Officials content
officialsBtn.addEventListener('click', () => {
  heroSection.style.opacity = 0;
  signinCards.style.opacity = 0;
  heroSection.style.transform = 'translateY(40px)';
  signinCards.style.transform = 'translateY(40px)';
  setTimeout(() => {
    heroSection.style.display = 'none';
    signinCards.style.display = 'none';
    officialsContent.style.display = 'block';
    setTimeout(() => {
      officialsContent.style.opacity = 1;
      officialsContent.style.transform = 'translateY(0)';
    }, 50);
  }, 800);
});
</script>

</body>
</html>
