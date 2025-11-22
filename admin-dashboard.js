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

// Scroll Indicator Functionality
(function() {
  const scrollIndicator = document.getElementById('scrollIndicator');
  if (!scrollIndicator) return;

  // Scroll to bottom section when clicked
  scrollIndicator.addEventListener('click', function() {
    const bottomSection = document.querySelector('.dashboard-bottom-section');
    if (bottomSection) {
      bottomSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });

  // Hide scroll indicator when user scrolls down or when bottom section is visible
  function checkScrollIndicator() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const documentHeight = Math.max(
      document.body.scrollHeight,
      document.body.offsetHeight,
      document.documentElement.clientHeight,
      document.documentElement.scrollHeight,
      document.documentElement.offsetHeight
    );
    const bottomSection = document.querySelector('.dashboard-bottom-section');
    
    // Check if there's actually more content to scroll (at least 300px more)
    const hasMoreContent = documentHeight > (windowHeight + 300);
    
    // More accurate bottom detection - check if we're at or very close to the bottom
    // Account for rounding and browser differences
    const scrollBottom = scrollTop + windowHeight;
    const isAtBottom = scrollBottom >= (documentHeight - 50) || Math.abs(scrollBottom - documentHeight) < 10;
    
    // Check if bottom section is visible or has been scrolled past
    let isBottomVisible = false;
    let isBottomScrolledPast = false;
    if (bottomSection) {
      const rect = bottomSection.getBoundingClientRect();
      isBottomVisible = rect.top < windowHeight && rect.bottom > 0;
      // Check if bottom section has been scrolled past (top is above viewport)
      isBottomScrolledPast = rect.bottom < windowHeight && rect.bottom > -100;
    }
    
    // Hide if: at bottom, bottom section visible/scrolled past, or no more content to scroll
    if (isAtBottom || isBottomVisible || isBottomScrolledPast || !hasMoreContent) {
      scrollIndicator.classList.add('hidden');
    } else {
      scrollIndicator.classList.remove('hidden');
    }
  }

  // Use IntersectionObserver for better detection of bottom section
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      // When bottom section is visible or intersecting, hide the indicator
      if (entry.isIntersecting) {
        scrollIndicator.classList.add('hidden');
      } else {
        // Re-check scroll position when bottom section is not visible
        // But also check if we're at the bottom
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = Math.max(
          document.body.scrollHeight,
          document.body.offsetHeight,
          document.documentElement.clientHeight,
          document.documentElement.scrollHeight,
          document.documentElement.offsetHeight
        );
        const scrollBottom = scrollTop + windowHeight;
        const isAtBottom = scrollBottom >= (documentHeight - 50);
        
        if (!isAtBottom) {
          checkScrollIndicator();
        } else {
          scrollIndicator.classList.add('hidden');
        }
      }
    });
  }, { 
    threshold: 0,
    rootMargin: '0px 0px 0px 0px' // Trigger when any part of bottom section enters viewport
  });

  const bottomSection = document.querySelector('.dashboard-bottom-section');
  if (bottomSection) {
    observer.observe(bottomSection);
  }

  // Check on scroll - immediate check for bottom, throttled for other positions
  let scrollTimeout;
  window.addEventListener('scroll', function() {
    // Immediate check for bottom position (no throttle)
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const documentHeight = Math.max(
      document.body.scrollHeight,
      document.body.offsetHeight,
      document.documentElement.clientHeight,
      document.documentElement.scrollHeight,
      document.documentElement.offsetHeight
    );
    const scrollBottom = scrollTop + windowHeight;
    const isAtBottom = scrollBottom >= (documentHeight - 50);
    
    if (isAtBottom) {
      scrollIndicator.classList.add('hidden');
    } else {
      // Throttled check for other positions
      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(checkScrollIndicator, 50);
    }
  }, { passive: true });
  
  // Check on page load and resize
  window.addEventListener('load', checkScrollIndicator);
  window.addEventListener('resize', checkScrollIndicator);
  
  // Initial check with delay to ensure DOM is ready
  setTimeout(checkScrollIndicator, 500);
})();

// Dashboard Info Cards Functionality
(function() {
  // Update current date
  function updateDate() {
    const dateEl = document.getElementById('currentDate');
    const dayEl = document.getElementById('currentDay');
    
    if (dateEl && dayEl) {
      const now = new Date();
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      const dayOptions = { weekday: 'long' };
      
      dateEl.textContent = now.toLocaleDateString('en-US', options);
      dayEl.textContent = now.toLocaleDateString('en-US', dayOptions);
    }
  }

  // Update system status
  function updateSystemStatus() {
    const statusEl = document.getElementById('systemStatus');
    if (statusEl) {
      // You can add actual system checks here
      statusEl.textContent = 'All systems operational';
    }
  }

  // Update notification count (placeholder - can be connected to real notifications)
  function updateNotifications() {
    const countEl = document.getElementById('notificationCount');
    const badgeEl = document.getElementById('notificationBadge');
    const numberEl = document.getElementById('notificationNumber');
    
    // This is a placeholder - you can fetch real notification count from API
    const notificationCount = 0; // Replace with actual count
    
    if (countEl) {
      if (notificationCount > 0) {
        countEl.textContent = `${notificationCount} new alert${notificationCount > 1 ? 's' : ''}`;
      } else {
        countEl.textContent = 'No new alerts';
      }
    }
    
    if (badgeEl && numberEl) {
      if (notificationCount > 0) {
        badgeEl.style.display = 'flex';
        numberEl.textContent = notificationCount > 99 ? '99+' : notificationCount;
      } else {
        badgeEl.style.display = 'none';
      }
    }
  }

  // Update today's activity
  function updateTodayActivity() {
    const activityEl = document.getElementById('todayActivity');
    if (activityEl) {
      // This can be connected to real activity data
      activityEl.textContent = 'View statistics';
    }
  }

  // Initialize all updates
  updateDate();
  updateSystemStatus();
  updateNotifications();
  updateTodayActivity();
  
  // Update date every minute (in case day changes)
  setInterval(updateDate, 60000);
})();

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

// Stat Cards - All visible with animations
(function() {
  const statCards = document.querySelectorAll('.stat-card-vertical');
  
  // Add floating animation to each card with different delays
  statCards.forEach((card, index) => {
    card.style.animationDelay = `${index * 0.1}s`;
    
    // Add number counting animation
    const numberEl = card.querySelector('.stat-number');
    if (numberEl) {
      const finalNumber = parseInt(numberEl.textContent);
      if (!isNaN(finalNumber)) {
        let currentNumber = 0;
        const increment = finalNumber / 30;
        const duration = 1500;
        const stepTime = duration / 30;
        
        const counter = setInterval(() => {
          currentNumber += increment;
          if (currentNumber >= finalNumber) {
            numberEl.textContent = finalNumber;
            clearInterval(counter);
          } else {
            numberEl.textContent = Math.floor(currentNumber);
          }
        }, stepTime);
      }
    }
  });
})();

// Big Charts Carousel Auto-Rotation (Demographics, Document Requests, etc.)
(function() {
  const bigChartsCarousel = document.querySelector('.big-charts-carousel');
  if (!bigChartsCarousel) return;
  
  const bigCharts = bigChartsCarousel.querySelectorAll('.big-chart');
  const bigChartIndicators = document.querySelectorAll('.big-chart-indicator');
  let currentBigChartIndex = 0;
  const totalBigCharts = bigCharts.length;
  const bigChartRotationInterval = 5000; // 5 seconds per chart
  let bigChartCarouselInterval = null;
  let isBigChartPaused = false;

  function showBigChart(index) {
    // Remove active class from all charts
    bigCharts.forEach((chart, i) => {
      chart.classList.remove('active', 'fade-out');
    });

    // Add fade-out to previous chart
    if (bigCharts[currentBigChartIndex]) {
      bigCharts[currentBigChartIndex].classList.add('fade-out');
    }

    // Small delay before showing next chart for smooth transition
    setTimeout(() => {
      bigCharts.forEach(chart => {
        chart.classList.remove('fade-out');
      });
      
      // Add active class to current chart
      const nextChart = bigCharts[index];
      nextChart.classList.add('active');
      nextChart.classList.remove('fade-out');
      
      // Re-trigger chart animation when it becomes visible
      const canvas = nextChart.querySelector('canvas');
      if (canvas && canvas.chart) {
        // Reset and re-animate the chart
        canvas.chart.update('active');
      }
    }, 150);

    // Update indicators
    bigChartIndicators.forEach((indicator, i) => {
      indicator.classList.toggle('active', i === index);
    });

    currentBigChartIndex = index;
  }

  function nextBigChart() {
    if (!isBigChartPaused) {
      const nextIndex = (currentBigChartIndex + 1) % totalBigCharts;
      showBigChart(nextIndex);
    }
  }

  function startBigChartCarousel() {
    if (bigChartCarouselInterval) {
      clearInterval(bigChartCarouselInterval);
    }
    bigChartCarouselInterval = setInterval(nextBigChart, bigChartRotationInterval);
  }

  function stopBigChartCarousel() {
    if (bigChartCarouselInterval) {
      clearInterval(bigChartCarouselInterval);
      bigChartCarouselInterval = null;
    }
  }

  // Initialize - show first chart
  if (bigCharts.length > 0) {
    showBigChart(0);
  }

  // Start auto-rotation after initial delay
  setTimeout(() => {
    startBigChartCarousel();
  }, 2000);

  // Pause on hover
  const bigChartsPanel = document.querySelector('.big-charts-panel');
  if (bigChartsPanel) {
    bigChartsPanel.addEventListener('mouseenter', () => {
      isBigChartPaused = true;
      stopBigChartCarousel();
    });

    bigChartsPanel.addEventListener('mouseleave', () => {
      isBigChartPaused = false;
      startBigChartCarousel();
    });
  }

  // Manual navigation via indicators
  bigChartIndicators.forEach((indicator, index) => {
    indicator.addEventListener('click', () => {
      showBigChart(index);
      // Restart auto-rotation after manual click
      stopBigChartCarousel();
      setTimeout(() => {
        isBigChartPaused = false;
        startBigChartCarousel();
      }, 500);
    });
  });

  // Arrow button navigation
  const prevBtn = document.getElementById('prevChartBtn');
  const nextBtn = document.getElementById('nextChartBtn');

  function prevBigChart() {
    const prevIndex = (currentBigChartIndex - 1 + totalBigCharts) % totalBigCharts;
    showBigChart(prevIndex);
    // Restart auto-rotation after manual click
    stopBigChartCarousel();
    setTimeout(() => {
      isBigChartPaused = false;
      startBigChartCarousel();
    }, 500);
  }

  function nextBigChartManual() {
    const nextIndex = (currentBigChartIndex + 1) % totalBigCharts;
    showBigChart(nextIndex);
    // Restart auto-rotation after manual click
    stopBigChartCarousel();
    setTimeout(() => {
      isBigChartPaused = false;
      startBigChartCarousel();
    }, 500);
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', prevBigChart);
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', nextBigChartManual);
  }

  // Update button states (always enabled for circular navigation)
  function updateButtonStates() {
    if (prevBtn) {
      prevBtn.disabled = false;
    }
    if (nextBtn) {
      nextBtn.disabled = false;
    }
  }

  // Initialize button states
  updateButtonStates();
})();