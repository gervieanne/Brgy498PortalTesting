// Helper function to create animated gradients
function createAnimatedGradient(ctx, color1, color2, x0, y0, x1, y1) {
  const gradient = ctx.createLinearGradient(x0, y0, x1, y1);
  gradient.addColorStop(0, color1);
  gradient.addColorStop(0.5, color2);
  gradient.addColorStop(1, color1);
  return gradient;
}

// Helper function to create pulsing gradient
function createPulsingGradient(ctx, baseColor, x0, y0, x1, y1) {
  const gradient = ctx.createLinearGradient(x0, y0, x1, y1);
  const opacity = 0.3 + Math.sin(Date.now() / 1000) * 0.2;
  gradient.addColorStop(0, baseColor);
  gradient.addColorStop(0.5, baseColor.replace('rgb', 'rgba').replace(')', `, ${0.6 + opacity})`));
  gradient.addColorStop(1, baseColor);
  return gradient;
}

// Helper function to create charts with animations
function createChart(id, type, labels, data, backgroundColor, options = {}) {
  const ctx = document.getElementById(id);
  if (!ctx) return console.warn(`Canvas with ID "${id}" not found.`);
  
  // Use original colors - gradients will be handled by Chart.js plugin if needed
  let processedBackgroundColor = backgroundColor;

  // Enhanced animation configurations for chart data visualization
  const animationConfig = {
    pie: {
      animateRotate: true,
      animateScale: true,
      duration: 2000,
      easing: 'easeOutQuart',
      delay: (context) => {
        return context.dataIndex * 150;
      }
    },
    doughnut: {
      animateRotate: true,
      animateScale: true,
      duration: 2000,
      easing: 'easeOutQuart',
      delay: (context) => {
        return context.dataIndex * 150;
      }
    },
    bar: {
      duration: 2000,
      easing: 'easeOutQuart',
      delay: (context) => {
        return context.dataIndex * 150;
      },
      x: {
        type: 'number',
        duration: 0,
        from: NaN
      },
      y: {
        type: 'number',
        duration: 2000,
        from: (ctx) => ctx.chart.scales.y.getPixelForValue(0),
        easing: 'easeOutQuart'
      }
    },
    line: {
      duration: 2500,
      easing: 'easeInOutQuart',
      x: {
        type: 'number',
        duration: 0,
        from: NaN
      },
      y: {
        type: 'number',
        duration: 2000,
        from: (ctx) => ctx.chart.scales.y.getPixelForValue(0),
        delay: (ctx) => ctx.dataIndex * 200,
        easing: 'easeOutQuart'
      }
    }
  };

  return new Chart(ctx, {
    type,
    data: {
      labels,
      datasets: [
        {
          data,
          backgroundColor: backgroundColor,
          borderColor: options.borderColor || undefined,
          label: options.label || "",
          fill: options.fill || false,
          tension: options.tension || 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      aspectRatio: options.aspectRatio || 2,
      layout: { 
        padding: typeof options.padding === 'object' ? options.padding : (options.padding || 20)
      },
      // Enhanced animation configuration for data visualization
      animation: animationConfig[type] || {
        duration: 2000,
        easing: 'easeOutQuart'
      },
      // Hover animations
      hover: {
        animationDuration: 200,
        mode: 'nearest',
        intersect: true
      },
      // Responsive animations
      transitions: {
        active: {
          animation: {
            duration: 200,
            easing: 'easeOutQuart'
          }
        },
        resize: {
          animation: {
            duration: 300,
            easing: 'easeOutQuart'
          }
        }
      },
      // Interaction settings
      interaction: {
        intersect: false,
        mode: 'index'
      },
      plugins: {
        legend: { 
          display: options.legend ?? false,
          position: 'bottom',
          align: 'center',
          labels: {
            font: {
              family: 'Poppins, sans-serif',
              size: 11,
              weight: '500'
            },
            padding: 12,
            usePointStyle: true,
            pointStyle: 'circle',
            color: '#666',
            boxWidth: 10,
            boxHeight: 10
          },
          title: {
            display: false
          }
        },
        tooltip: { 
          enabled: options.tooltip ?? true,
          backgroundColor: 'rgba(255, 255, 255, 0.98)',
          titleFont: {
            family: 'Poppins, sans-serif',
            size: 13,
            weight: '600'
          },
          bodyFont: {
            family: 'Poppins, sans-serif',
            size: 12,
            weight: '500'
          },
          padding: 12,
          cornerRadius: 8,
          displayColors: true,
          borderColor: 'rgba(0, 0, 0, 0.08)',
          borderWidth: 1,
          titleColor: '#21205d',
          bodyColor: '#333',
          titleSpacing: 6,
          bodySpacing: 4,
          boxPadding: 6,
          animation: {
            duration: 150
          },
          callbacks: {
            label: function(context) {
              let label = context.label || '';
              if (label) {
                label += ': ';
              }
              label += context.parsed.y !== undefined ? context.parsed.y : context.parsed;
              return label;
            }
          }
        },
        datalabels: {
          display: options.showDataLabels ?? true,
          color: options.labelColor || "#21205d",
          font: { 
            weight: "600", 
            size: options.labelSize || 13,
            family: 'Poppins, sans-serif'
          },
          formatter: options.labelFormatter || ((v) => v),
          anchor: options.anchor || undefined,
          align: options.align || undefined,
          offset: options.offset !== undefined ? options.offset : undefined,
          clamp: true,
          clip: false,
        },
      },
      scales: options.scales || undefined,
      cutout: options.cutout || undefined,
    },
    plugins: [ChartDataLabels],
  });
}

// Add staggered animation to chart widgets
function animateChartWidgets() {
  const chartWidgets = document.querySelectorAll('.chart-widget');
  
  chartWidgets.forEach((widget, index) => {
    widget.style.opacity = '0';
    widget.style.transform = 'translateY(30px)';
    
    setTimeout(() => {
      widget.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
      widget.style.opacity = '1';
      widget.style.transform = 'translateY(0)';
    }, index * 150);
  });
}

// Update Quick Stats with animation
function updateQuickStats(documents) {
  const stats = {
    pending: documents.pending || 0,
    processing: documents.processing || 0,
    completed: documents.completed || 0,
    ready: documents.ready || 0
  };

  // Animate numbers
  function animateValue(element, start, end, duration) {
    if (!element) return;
    const startTime = performance.now();
    const range = end - start;
    
    function update(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const current = Math.floor(start + (range * progress));
      element.textContent = current;
      
      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        element.textContent = end;
      }
    }
    
    requestAnimationFrame(update);
  }

  const pendingEl = document.getElementById('pendingCount');
  const processingEl = document.getElementById('processingCount');
  const completedEl = document.getElementById('completedCount');
  const readyEl = document.getElementById('readyCount');

  if (pendingEl) animateValue(pendingEl, 0, stats.pending, 1000);
  if (processingEl) animateValue(processingEl, 0, stats.processing, 1000);
  if (completedEl) animateValue(completedEl, 0, stats.completed, 1000);
  if (readyEl) animateValue(readyEl, 0, stats.ready, 1000);
}

// Fetch dashboard statistics and create charts
async function initializeDashboard() {
  try {
    // Animate chart widgets first
    animateChartWidgets();
    
    // Fetch statistics from the API
    const response = await fetch("get_dashboard_stats.php");
    const statsData = await response.json();

    if (!statsData || statsData.success !== true) {
      console.warn("Failed to fetch dashboard statistics:", statsData);
      return;
    }

    // Update Quick Stats
    if (statsData.documents) {
      updateQuickStats(statsData.documents);
    }

    // Update Quick Stats
    if (statsData.documents) {
      updateQuickStats(statsData.documents);
    }

    // Add slight delay before creating charts to sync with widget animations
    await new Promise(resolve => setTimeout(resolve, 300));

    // Demographics by Street Chart (Pie)
    if (statsData.demographics && statsData.demographics.streets) {
      const streets = statsData.demographics.streets;
      const streetNames = Object.keys(streets);
      const streetCounts = Object.values(streets);

      // Clean, minimal color palette
      const professionalColors = [
        '#21205d',   // Deep blue
        '#3a3995',   // Medium blue
        '#49769F',   // Light blue
        '#6EA2B3',   // Teal blue
        '#7FB3C4',   // Sky blue
        '#8FC4D5',   // Pale blue
        '#9FD4E6'    // Very pale blue
      ];

      createChart(
        "demographicsChart",
        "pie",
        streetNames,
        streetCounts,
        professionalColors,
        {
          legend: true,
          tooltip: true,
          showDataLabels: true,
          labelColor: "#21205d",
          labelSize: 12,
          anchor: "end",
          align: "end",
          offset: 10,
          labelFormatter: (value, ctx) => {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
            const percentage = ((value / total) * 100).toFixed(1);
            // Only show label if segment is large enough (more than 5% of total)
            if (percentage < 5) {
              return null; // Hide labels for very small segments
            }
            return `${value}\n(${percentage}%)`;
          },
          aspectRatio: 1,
          padding: 40,
        }
      );
    }

    const docs = statsData.documents;

    // Document Requests by Type (Bar Chart)
    if (docs.types && Object.keys(docs.types).length > 0) {
      const docTypes = Object.keys(docs.types).map((type) =>
        type.length > 20 ? type.substring(0, 20) + "..." : type
      );
      const docCounts = Object.values(docs.types);

      // Clean, minimal bar colors
      const barColors = docTypes.map((_, index) => {
        const colors = [
          '#21205d',
          '#3a3995',
          '#49769F',
          '#6EA2B3',
          '#7FB3C4',
          '#8FC4D5'
        ];
        return colors[index % colors.length];
      });

      createChart(
        "documentChart",
        "bar",
        docTypes,
        docCounts,
        barColors,
        {
        borderColor: barColors,
        borderWidth: 0,
        borderRadius: 6,
          borderSkipped: false,
          labelColor: "#21205d",
          labelSize: 14,
          anchor: "end",
          align: "top",
          padding: {
            top: 25,
            bottom: 10,
            left: 15,
            right: 15,
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { 
                stepSize: 1, 
                color: "#666", 
                font: { 
                  size: 12,
                  family: 'Poppins, sans-serif',
                  weight: '600'
                },
                padding: 8
              },
              grid: {
                color: 'rgba(0, 0, 0, 0.05)',
                lineWidth: 1,
                drawBorder: false,
                drawTicks: false
              },
              border: {
                display: false
              }
            },
            x: {
              ticks: {
                autoSkip: false,
                color: "#666",
                font: { 
                  size: 11,
                  family: 'Poppins, sans-serif',
                  weight: '600'
                },
                padding: 12,
                maxRotation: 45,
                minRotation: 0
              },
              grid: { 
                display: false,
                drawBorder: false
              },
              border: {
                display: false
              }
            },
          },
        }
      );
    }

    // Pending Applications (Doughnut)
    createChart(
      "pendingChart",
      "doughnut",
      ["Pending", "Processing"],
      [docs.pending, docs.processing],
      [
        '#21205d',
        '#49769F'
      ],
      { 
        borderColor: ['#ffffff', '#ffffff'],
        borderWidth: 2,
        labelSize: 14,
        labelColor: "#21205d",
        labelFormatter: (value, ctx) => {
          const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
          const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
          return `${value}\n${percentage}%`;
        },
        cutout: "65%", 
        aspectRatio: 1,
        legend: true,
        padding: 25
      }
    );

    // Cancelled Requests (Bar Chart)
    createChart(
      "cancelledChart",
      "bar",
      ["Rejected", "In Process"],
      [docs.rejected, docs.processing],
      [
        '#d32f2f',
        '#49769F'
      ],
      {
        borderColor: ['#d32f2f', '#49769F'],
        borderWidth: 0,
        borderRadius: 6,
        borderSkipped: false,
        labelColor: "#21205d",
        labelSize: 15,
        anchor: "end",
        align: "top",
        offset: 5,
        padding: {
          top: 35,
          bottom: 10,
          left: 15,
          right: 15,
        },
        scales: {
          y: { 
            beginAtZero: true, 
            ticks: { 
              stepSize: 1, 
              color: "#666",
              font: {
                family: 'Poppins, sans-serif',
                size: 12,
                weight: '600'
              },
              padding: 8
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)',
              lineWidth: 1,
              drawBorder: false,
              drawTicks: false
            },
            border: {
              display: false
            }
          },
          x: {
            ticks: { 
              font: { 
                size: 12,
                family: 'Poppins, sans-serif',
                weight: '600'
              }, 
              color: "#666",
              padding: 12
            },
            grid: { 
              display: false,
              drawBorder: false
            },
            border: {
              display: false
            }
          },
        },
      }
    );

    // Completed & Ready (Line Chart)
    createChart(
      "completedChart",
      "line",
      ["Completed", "Ready for Pickup"],
      [docs.completed, docs.ready],
      [
        'rgba(33, 32, 93, 0.1)',
        'rgba(110, 162, 179, 0.1)'
      ],
      {
        borderColor: ['#21205d', '#6EA2B3'],
        borderWidth: 2,
        pointBackgroundColor: ['#21205d', '#6EA2B3'],
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointHoverBackgroundColor: ['#21205d', '#6EA2B3'],
        pointHoverBorderWidth: 2,
        fill: true,
        tension: 0.3,
        showDataLabels: false,
        legend: true,
        padding: {
          top: 25,
          bottom: 15,
          left: 15,
          right: 15,
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { 
              font: { 
                size: 12,
                family: 'Poppins, sans-serif',
                weight: '600'
              },
              color: "#666",
              padding: 8
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)',
              lineWidth: 1,
              drawBorder: false,
              drawTicks: false
            },
            border: {
              display: false
            }
          },
          x: {
            ticks: { 
              font: { 
                size: 12,
                family: 'Poppins, sans-serif',
                weight: '600'
              },
              color: "#666",
              padding: 12
            },
            grid: { 
              display: false,
              drawBorder: false
            },
            border: {
              display: false
            }
          },
        },
      }
    );
  } catch (error) {
    console.error("Error loading dashboard:", error);
  }
}

document.addEventListener("DOMContentLoaded", initializeDashboard);