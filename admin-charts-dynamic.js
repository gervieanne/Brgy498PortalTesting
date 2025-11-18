// Helper function to create charts with animations
function createChart(id, type, labels, data, backgroundColor, options = {}) {
  const ctx = document.getElementById(id);
  if (!ctx) return console.warn(`Canvas with ID "${id}" not found.`);

  // Animation configurations based on chart type
  const animationConfig = {
    pie: {
      animateRotate: true,
      animateScale: true,
      duration: 1500,
      easing: 'easeInOutQuart'
    },
    doughnut: {
      animateRotate: true,
      animateScale: true,
      duration: 1500,
      easing: 'easeInOutQuart'
    },
    bar: {
      duration: 1200,
      easing: 'easeOutBounce',
      delay: (context) => {
        return context.dataIndex * 100;
      }
    },
    line: {
      duration: 1500,
      easing: 'easeInOutCubic',
      x: {
        type: 'number',
        duration: 800,
        from: NaN,
        delay: 200
      },
      y: {
        type: 'number',
        duration: 800,
        from: (ctx) => ctx.chart.scales.y.getPixelForValue(0),
        delay: 200
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
          backgroundColor,
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
      layout: { padding: options.padding || 20 },
      // Add animation configuration
      animation: animationConfig[type] || {
        duration: 1000,
        easing: 'easeInOutQuart'
      },
      // Add hover animations
      hover: {
        animationDuration: 400
      },
      // Add responsive animations
      transitions: {
        active: {
          animation: {
            duration: 400
          }
        }
      },
      plugins: {
        legend: { 
          display: options.legend ?? false,
          labels: {
            font: {
              family: 'Poppins'
            }
          }
        },
        tooltip: { 
          enabled: options.tooltip ?? true,
          backgroundColor: 'rgba(33, 32, 93, 0.9)',
          titleFont: {
            family: 'Poppins',
            size: 14
          },
          bodyFont: {
            family: 'Poppins',
            size: 13
          },
          padding: 12,
          cornerRadius: 8,
          displayColors: true,
          animation: {
            duration: 300
          }
        },
        datalabels: {
          display: options.showDataLabels ?? true,
          color: options.labelColor || "white",
          font: { 
            weight: "bold", 
            size: options.labelSize || 14,
            family: 'Poppins'
          },
          formatter: options.labelFormatter || ((v) => v),
          anchor: options.anchor || undefined,
          align: options.align || undefined,
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

    // Add slight delay before creating charts to sync with widget animations
    await new Promise(resolve => setTimeout(resolve, 300));

    // Demographics by Street Chart (Pie)
    if (statsData.demographics && statsData.demographics.streets) {
      const streets = statsData.demographics.streets;
      const streetNames = Object.keys(streets);
      const streetCounts = Object.values(streets);

      createChart(
        "demographicsChart",
        "pie",
        streetNames,
        streetCounts,
        ["#001D39", "#0A4174", "#49769F", "#4E8EA2", "#6EA2B3", "#7FB3C4", "#8FC4D5"],
        {
          legend: true,
          tooltip: true,
          showDataLabels: true,
          labelColor: "white",
          labelSize: 14,
          labelFormatter: (value) => value,
          aspectRatio: 1,
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

      createChart(
        "documentChart",
        "bar",
        docTypes,
        docCounts,
        ["#001D39", "#0A4174", "#49769F", "#4E8EA2", "#6EA2B3", "#7FB3C4"],
        {
          labelColor: "#21205d",
          labelSize: 12,
          anchor: "end",
          align: "top",
          padding: {
            top: 20,
            bottom: 5,
            left: 10,
            right: 10,
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { 
                stepSize: 1, 
                color: "#21205d", 
                font: { 
                  size: 11,
                  family: 'Poppins'
                } 
              },
              grid: {
                color: 'rgba(0, 0, 0, 0.05)'
              }
            },
            x: {
              ticks: {
                autoSkip: false,
                color: "#21205d",
                font: { 
                  size: 10,
                  family: 'Poppins'
                },
                padding: 10,
                maxRotation: 45,
              },
              grid: { display: false },
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
      ["#001D39", "#49769F"],
      { 
        labelSize: 16, 
        cutout: "30%", 
        aspectRatio: 1,
        legend: true
      }
    );

    // Cancelled Requests (Bar Chart)
    createChart(
      "cancelledChart",
      "bar",
      ["Rejected", "In Process"],
      [docs.rejected, docs.processing],
      ["#c6000044", "rgba(10, 65, 116, 0.1)"],
      {
        borderColor: "#0A4174",
        fill: true,
        tension: 0.3,
        labelColor: "#21205d",
        labelSize: 14,
        anchor: "end",
        align: "top",
        scales: {
          y: { 
            beginAtZero: true, 
            ticks: { 
              stepSize: 1, 
              color: "#21205d",
              font: {
                family: 'Poppins'
              }
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            ticks: { 
              font: { 
                size: 12,
                family: 'Poppins'
              }, 
              color: "#21205d" 
            },
            grid: { display: false },
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
      ["rgba(10, 65, 116, 0.1)"],
      {
        borderColor: "#0A4174",
        fill: true,
        tension: 0.3,
        showDataLabels: false,
        legend: true,
        padding: {
          top: 20,
          bottom: 10,
          left: 10,
          right: 10,
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { 
              font: { 
                size: 10,
                family: 'Poppins'
              },
              color: "#21205d"
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            ticks: { 
              font: { 
                size: 10,
                family: 'Poppins'
              },
              color: "#21205d"
            },
            grid: { display: false },
          },
        },
      }
    );
  } catch (error) {
    console.error("Error loading dashboard:", error);
  }
}

document.addEventListener("DOMContentLoaded", initializeDashboard);