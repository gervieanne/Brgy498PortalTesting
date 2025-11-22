<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "498portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all officials
$officials = [];
$sql = "SELECT * FROM barangay_officials_db ORDER BY id ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $officials[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barangay 498</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Poppins', sans-serif; background-color: #f5f5f5; overflow-x: hidden; }
/* Bottom section background */
body::after { content: ''; position: fixed; bottom: 0; left: 0; right: 0; height: 33%; background: linear-gradient(to top, #eeeeee 0%, #f5f5f5 100%); z-index: -1; }

/* Animated Background Elements */
.hero::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 50%, rgba(110, 162, 179, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(245, 253, 18, 0.08) 0%, transparent 50%);
  animation: float 20s ease-in-out infinite;
  z-index: 0;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(5deg); }
}

.hero-content { position: relative; z-index: 1; }

/* Floating Shapes */
.floating-shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(110, 162, 179, 0.1);
  animation: floatShape 15s infinite ease-in-out;
  z-index: 0;
}

.floating-shape:nth-child(1) {
  width: 200px;
  height: 200px;
  top: 10%;
  left: 10%;
  animation-delay: 0s;
}

.floating-shape:nth-child(2) {
  width: 150px;
  height: 150px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.floating-shape:nth-child(3) {
  width: 100px;
  height: 100px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

@keyframes floatShape {
  0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.3; }
  33% { transform: translate(30px, -30px) scale(1.1); opacity: 0.5; }
  66% { transform: translate(-20px, 20px) scale(0.9); opacity: 0.4; }
}

/* Navigation */
nav { display: flex; justify-content: space-between; align-items: center; padding: 20px 60px; background: linear-gradient(135deg, #21205d 0%, #2c2b85 100%); position: relative; z-index: 100; box-shadow: 0 2px 10px rgba(33, 32, 93, 0.2); }
.nav-links { display: flex; gap: 35px; list-style: none; }
.nav-links a { color: white; text-decoration: none; font-size: 14px; font-weight: 400; transition: color 0.3s; cursor: pointer; }
.nav-links a:hover { color: #f5fd12; }

.logo { display: flex; align-items: center; gap: 15px; }
.logo img { width: 60px; height: 60px; object-fit: contain; }
.logo p { color: white; font-size: 18px; font-weight: 600; margin: 0; }

/* Hero Section */
.hero { 
  background: linear-gradient(135deg, #21205d 0%, #2c2b85 100%); 
  padding: 120px 20px; 
  color: white; 
  text-align: center; 
  position: relative; 
  overflow: hidden; 
  transition: opacity 0.8s ease, transform 0.8s ease;
  min-height: 90vh;
  display: flex;
  align-items: center;
  justify-content: center;
}
.hero-content { 
  max-width: 800px; 
  margin: 0 auto; 
  position: relative;
  z-index: 2;
}
.hero h1 { 
  font-size: 56px; 
  font-weight: 700; 
  margin-bottom: 25px; 
  line-height: 1.2;
  background: linear-gradient(135deg, #ffffff 0%, #f5fd12 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: fadeInUp 1s ease-out;
  text-shadow: 0 4px 20px rgba(245, 253, 18, 0.3);
}
.hero p { 
  font-size: 18px; 
  line-height: 1.8; 
  color: #f5f5f5; 
  margin-bottom: 40px;
  animation: fadeInUp 1s ease-out 0.2s both;
}

/* Sign In Button */
.signin-btn { 
  background: linear-gradient(135deg, #21205d 0%, #2c2b85 100%); 
  color: white; 
  border: 2px solid #6EA2B3; 
  padding: 18px 45px; 
  font-size: 18px; 
  font-weight: 600; 
  border-radius: 50px; 
  cursor: pointer; 
  transition: all 0.4s ease; 
  box-shadow: 0 4px 20px rgba(33, 32, 93, 0.4);
  position: relative;
  overflow: hidden;
  animation: fadeInUp 1s ease-out 0.4s both;
}
.signin-btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(245, 253, 18, 0.2);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}
.signin-btn:hover::before {
  width: 300px;
  height: 300px;
}
.signin-btn:hover { 
  background: linear-gradient(135deg, #2c2b85 0%, #3a3995 100%); 
  border-color: #f5fd12; 
  color: #f5fd12; 
  transform: scale(1.08) translateY(-2px); 
  box-shadow: 0 10px 35px rgba(245, 253, 18, 0.3);
}
.signin-btn span {
  position: relative;
  z-index: 1;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Features Section */
.features-section {
  background: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
  padding: 80px 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 30px;
  margin-top: 50px;
}

.feature-card {
  background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
  border-radius: 20px;
  padding: 40px 30px;
  text-align: center;
  box-shadow: 0 8px 25px rgba(33, 32, 93, 0.08);
  border: 2px solid rgba(33, 32, 93, 0.05);
  transition: all 0.4s ease;
  position: relative;
  overflow: hidden;
}

.feature-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(110, 162, 179, 0.1), transparent);
  transition: left 0.5s;
}

.feature-card:hover::before {
  left: 100%;
}

.feature-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  background: linear-gradient(135deg, #21205d 0%, #2c2b85 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  color: #f5fd12;
  box-shadow: 0 8px 20px rgba(33, 32, 93, 0.2);
  transition: all 0.4s ease;
}

.feature-card:hover .feature-icon {
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 12px 30px rgba(33, 32, 93, 0.3);
}

.feature-card h3 {
  font-size: 22px;
  font-weight: 600;
  color: #21205d;
  margin-bottom: 12px;
}

.feature-card p {
  font-size: 15px;
  color: #666;
  line-height: 1.6;
}

.feature-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 40px rgba(33, 32, 93, 0.15);
  border-color: #6EA2B3;
}

/* Sign in cards */
.signin-cards { 
  max-width: 900px; 
  margin: 60px auto 100px; 
  display: grid; 
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
  gap: 30px; 
  opacity: 0; 
  transform: translateY(40px); 
  transition: opacity 0.8s ease, transform 0.8s ease; 
  display: none; 
}
.service-card { 
  background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%); 
  border-radius: 20px; 
  overflow: hidden; 
  box-shadow: 0 8px 25px rgba(33, 32, 93, 0.12); 
  border: 2px solid rgba(33, 32, 93, 0.1); 
  transition: all 0.4s ease; 
  text-decoration: none; 
  color: inherit; 
  display: block; 
  text-align: center; 
  padding: 50px 30px;
  position: relative;
}
.service-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #21205d, #6EA2B3, #f5fd12);
  transform: scaleX(0);
  transition: transform 0.4s ease;
}
.service-card:hover::after {
  transform: scaleX(1);
}
.service-card img { 
  width: 70px; 
  height: 70px; 
  object-fit: contain; 
  filter: brightness(0) saturate(100%) invert(28%) sepia(65%) saturate(7500%) hue-rotate(247deg) brightness(93%) contrast(86%); 
  margin-bottom: 20px;
  transition: transform 0.4s ease;
}
.service-card:hover img {
  transform: scale(1.1) rotate(5deg);
}
.service-card h3 { 
  font-size: 22px; 
  font-weight: 600; 
  color: #21205d; 
  margin-bottom: 12px;
  transition: color 0.3s ease;
}
.service-card p { 
  font-size: 15px; 
  color: #666; 
  line-height: 1.6; 
}
.service-card:hover { 
  transform: translateY(-12px) scale(1.02); 
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15); 
  border-color: rgba(33, 32, 93, 0.2); 
  background: linear-gradient(135deg, #ffffff 0%, #f5f6f7 100%); 
}
.service-card:hover h3 { 
  color: #21205d; 
}
.service-card:hover::after {
  display: none;
}

/* Officials container */
#officials-content { max-width: 1200px; margin: 50px auto; opacity: 0; transform: translateY(40px); transition: opacity 0.8s ease, transform 0.8s ease; display: none; }
.officials-header { text-align: center; margin-bottom: 40px; }
.officials-header h1 { font-size: 32px; color: #21205d; font-weight: 700; }
.officials-header p { font-size: 16px; color: #555; margin-top: 8px; }
.officials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; }
.official-card { background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%); border-radius: 15px; overflow: hidden; text-align: center; box-shadow: 0 5px 15px rgba(33, 32, 93, 0.1); border: 2px solid rgba(33, 32, 93, 0.1); padding: 20px; transition: all 0.3s ease; }
.official-card:hover { transform: translateY(-8px) scale(1.03); box-shadow: 0 12px 30px rgba(33, 32, 93, 0.2); border-color: #6EA2B3; }
.official-card img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin-bottom: 15px; border: 3px solid #21205d; }
.official-card h3 { font-size: 18px; color: #21205d; margin-bottom: 5px; font-weight: 600; }
.official-card p { font-size: 14px; color: #666; }

/* Statistics Section */
.stats-section {
  background: linear-gradient(135deg, #21205d 0%, #2c2b85 100%);
  padding: 80px 20px;
  color: white;
  text-align: center;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 40px;
  max-width: 1000px;
  margin: 50px auto 0;
}

.stat-item {
  padding: 30px 20px;
}

.stat-number {
  font-size: 48px;
  font-weight: 700;
  color: #f5fd12;
  margin-bottom: 10px;
  display: block;
}

.stat-label {
  font-size: 16px;
  color: #f5f5f5;
  text-transform: uppercase;
  letter-spacing: 1px;
}

@media(max-width:768px) { 
  nav { padding: 15px 20px; }
  .nav-links { gap: 20px; font-size: 12px; }
  .logo img { width: 50px; height: 50px; }
  .logo p { font-size: 16px; }
  .hero { padding: 80px 20px; min-height: 80vh; }
  .hero h1 { font-size: 36px; } 
  .hero p { font-size: 15px; }
  .features-section { padding: 60px 20px; }
  .features-grid { grid-template-columns: 1fr; gap: 20px; }
  .feature-card { padding: 30px 20px; }
  .feature-icon { width: 60px; height: 60px; font-size: 24px; }
  .stats-section { padding: 60px 20px; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
  .stat-number { font-size: 36px; }
  .stat-label { font-size: 14px; }
  .signin-cards { gap: 20px; margin: 40px auto 60px; }
  .service-card { padding: 40px 20px; }
  .officials-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
}
@media(max-width:480px) { 
  nav { padding: 12px 15px; flex-direction: column; gap: 10px; }
  .nav-links { gap: 15px; font-size: 11px; }
  .logo { gap: 10px; }
  .logo img { width: 40px; height: 40px; }
  .logo p { font-size: 14px; }
  .hero { padding: 60px 15px; min-height: 70vh; }
  .hero h1 { font-size: 28px; margin-bottom: 15px; } 
  .hero p { font-size: 14px; margin-bottom: 25px; }
  .signin-btn { padding: 14px 35px; font-size: 15px; }
  .features-section { padding: 50px 15px; }
  .features-section h2 { font-size: 32px; }
  .features-section > div > p { font-size: 15px; }
  .features-grid { gap: 15px; }
  .feature-card { padding: 25px 15px; }
  .feature-icon { width: 50px; height: 50px; font-size: 20px; }
  .feature-card h3 { font-size: 18px; }
  .feature-card p { font-size: 13px; }
  .stats-section { padding: 50px 15px; }
  .stats-section h2 { font-size: 32px; }
  .stats-grid { grid-template-columns: 1fr; gap: 25px; }
  .stat-number { font-size: 32px; }
  .stat-label { font-size: 13px; }
  .signin-cards { grid-template-columns: 1fr; gap: 15px; margin: 30px auto 50px; } 
  .service-card { padding: 35px 20px; }
  .service-card img { width: 60px; height: 60px; }
  .service-card h3 { font-size: 20px; }
  .service-card p { font-size: 14px; }
  .officials-grid { grid-template-columns: 1fr; gap: 15px; }
  .official-card { padding: 15px; }
  .official-card img { width: 80px; height: 80px; }
  .official-card h3 { font-size: 16px; }
  .official-card p { font-size: 13px; }
} </style>

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
  <div class="floating-shape"></div>
  <div class="floating-shape"></div>
  <div class="floating-shape"></div>
  <div class="hero-content">
    <h1>Barangay Management System</h1>
    <p>Mabuhay! This is the official platform of Barangay 498, Zone 49, District IV, Manila, designed to streamline operations and enhance public service delivery.</p>
    <button class="signin-btn" id="signin-btn"><span>Sign In <i class="fas fa-arrow-right"></i></span></button>
  </div>
</section>

<!-- Features Section -->
<section class="features-section" id="features-section">
  <div style="text-align: center; margin-bottom: 20px;">
    <h2 style="font-size: 42px; font-weight: 700; color: #21205d; margin-bottom: 15px;">Why Choose Our Platform?</h2>
    <p style="font-size: 18px; color: #666; max-width: 600px; margin: 0 auto;">Experience seamless digital governance with our comprehensive management system</p>
  </div>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-file-alt"></i></div>
      <h3>Document Requests</h3>
      <p>Request and track barangay certificates and documents online with ease and transparency.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-bullhorn"></i></div>
      <h3>Announcements</h3>
      <p>Stay updated with the latest barangay news, events, and important community information.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
      <h3>Event Calendar</h3>
      <p>Never miss important barangay events, meetings, and community activities.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-users"></i></div>
      <h3>Officials Directory</h3>
      <p>Connect with your barangay officials and learn about their roles and responsibilities.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
      <h3>Real-time Updates</h3>
      <p>Get instant notifications and updates about your requests and barangay activities.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
      <h3>Secure & Reliable</h3>
      <p>Your data is protected with advanced security measures and reliable infrastructure.</p>
    </div>
  </div>
</section>

<!-- Statistics Section -->
<section class="stats-section" id="stats-section">
  <h2 style="font-size: 42px; font-weight: 700; margin-bottom: 15px;">Our Impact</h2>
  <p style="font-size: 18px; color: #f5f5f5; margin-bottom: 20px;">Serving our community with excellence</p>
  <div class="stats-grid">
    <div class="stat-item">
      <span class="stat-number" data-target="979">0</span>
      <div class="stat-label">Total Residents</div>
    </div>
    <div class="stat-item">
      <span class="stat-number" data-target="848">0</span>
      <div class="stat-label">Registered Voters</div>
    </div>
    <div class="stat-item">
      <span class="stat-number" data-target="100">0</span>
      <div class="stat-label">Documents Processed</div>
    </div>
    <div class="stat-item">
      <span class="stat-number" data-target="24">0</span>
      <div class="stat-label">Hours Support</div>
    </div>
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
    <?php if (!empty($officials)): ?>
      <?php foreach ($officials as $official): ?>
        <div class="official-card">
          <?php 
          $image_path = !empty($official['image_path']) 
            ? '../admin-officials/' . htmlspecialchars($official['image_path'], ENT_QUOTES, 'UTF-8')
            : '../images/barangay-logo.png';
          ?>
          <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($official['name']); ?>">
          <h3><?php echo htmlspecialchars($official['name']); ?></h3>
          <p><?php echo htmlspecialchars($official['position']); ?></p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="official-card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
        <p style="color: #666; font-size: 16px;">No officials information available at the moment.</p>
      </div>
    <?php endif; ?>
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
  document.getElementById('features-section').style.opacity = 0;
  document.getElementById('stats-section').style.opacity = 0;
  heroSection.style.transform = 'translateY(40px)';
  setTimeout(() => {
    heroSection.style.display = 'none';
    document.getElementById('features-section').style.display = 'none';
    document.getElementById('stats-section').style.display = 'none';
    signinCards.style.display = 'grid';
    setTimeout(() => {
      signinCards.style.opacity = 1;
      signinCards.style.transform = 'translateY(0)';
    }, 50);
  }, 800);
});

// Show Officials content
officialsBtn.addEventListener('click', () => {
  heroSection.style.opacity = 0;
  signinCards.style.opacity = 0;
  document.getElementById('features-section').style.opacity = 0;
  document.getElementById('stats-section').style.opacity = 0;
  heroSection.style.transform = 'translateY(40px)';
  signinCards.style.transform = 'translateY(40px)';
  setTimeout(() => {
    heroSection.style.display = 'none';
    signinCards.style.display = 'none';
    document.getElementById('features-section').style.display = 'none';
    document.getElementById('stats-section').style.display = 'none';
    officialsContent.style.display = 'block';
    setTimeout(() => {
      officialsContent.style.opacity = 1;
      officialsContent.style.transform = 'translateY(0)';
    }, 50);
  }, 800);
});

// Return Home - show all sections
homeLink.addEventListener('click', () => {
  signinCards.style.opacity = 0;
  signinCards.style.transform = 'translateY(40px)';
  officialsContent.style.opacity = 0;
  officialsContent.style.transform = 'translateY(40px)';
  setTimeout(() => {
    signinCards.style.display = 'none';
    officialsContent.style.display = 'none';
    heroSection.style.display = 'block';
    document.getElementById('features-section').style.display = 'block';
    document.getElementById('stats-section').style.display = 'block';
    setTimeout(() => {
      heroSection.style.opacity = 1;
      heroSection.style.transform = 'translateY(0)';
      document.getElementById('features-section').style.opacity = 1;
      document.getElementById('stats-section').style.opacity = 1;
    }, 50);
  }, 400);
});

// Animate statistics on scroll
function animateStats() {
  const stats = document.querySelectorAll('.stat-number');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const target = parseInt(entry.target.getAttribute('data-target'));
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
          current += increment;
          if (current >= target) {
            entry.target.textContent = target;
            clearInterval(timer);
          } else {
            entry.target.textContent = Math.floor(current);
          }
        }, 30);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  
  stats.forEach(stat => observer.observe(stat));
}

// Initialize animations
document.addEventListener('DOMContentLoaded', () => {
  animateStats();
  
  // Animate feature cards on scroll
  const featureCards = document.querySelectorAll('.feature-card');
  const cardObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }, index * 100);
        cardObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  
  featureCards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'all 0.6s ease';
    cardObserver.observe(card);
  });
});
</script>

</body>
</html>
