/**
 * Institute Placement & Alumni Tracking Portal
 * Chart.js Visualizations Controller
 */

const PortalCharts = {
  instances: {},

  destroyChart(id) {
    if (this.instances[id]) {
      this.instances[id].destroy();
      delete this.instances[id];
    }
  },

  renderAll(chartData) {
    if (!chartData) return;
    this.renderByYear(chartData.by_year);
    this.renderByBranch(chartData.by_branch);
    this.renderCompanyWise(chartData.company_wise);
    this.renderPackageRanges(chartData.package_ranges);
    this.renderPlacedVsUnplaced(chartData.placed_vs_unplaced);
  },

  // 1. Placements by Year (Line / Bar)
  renderByYear(data) {
    const ctx = document.getElementById('chartPlacementsByYear');
    if (!ctx || !data) return;
    this.destroyChart('chartPlacementsByYear');

    const labels = data.map(d => d.placement_year);
    const counts = data.map(d => d.count);
    const avgPackages = data.map(d => d.avg_package);

    this.instances['chartPlacementsByYear'] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Total Offers Placed',
            data: counts,
            backgroundColor: 'rgba(37, 99, 235, 0.8)',
            borderColor: '#2563eb',
            borderWidth: 1,
            borderRadius: 6,
            yAxisID: 'y'
          },
          {
            label: 'Average Package (LPA)',
            data: avgPackages,
            type: 'line',
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            borderWidth: 3,
            fill: false,
            tension: 0.3,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top' },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Number of Students' }
          },
          y1: {
            beginAtZero: true,
            position: 'right',
            grid: { drawOnChartArea: false },
            title: { display: true, text: 'Package (LPA ₹)' }
          }
        }
      }
    });
  },

  // 2. Placements by Branch (Horizontal Bar)
  renderByBranch(data) {
    const ctx = document.getElementById('chartPlacementsByBranch');
    if (!ctx || !data) return;
    this.destroyChart('chartPlacementsByBranch');

    const labels = data.map(d => d.branch);
    const placed = data.map(d => d.placed_students);
    const total = data.map(d => d.total_students);

    this.instances['chartPlacementsByBranch'] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Placed Students',
            data: placed,
            backgroundColor: '#10b981',
            borderRadius: 6
          },
          {
            label: 'Total Students in Branch',
            data: total,
            backgroundColor: '#cbd5e1',
            borderRadius: 6
          }
        ]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top' }
        },
        scales: {
          x: { beginAtZero: true }
        }
      }
    });
  },

  // 3. Company-wise Placements
  renderCompanyWise(data) {
    const ctx = document.getElementById('chartCompanyPlacements');
    if (!ctx || !data) return;
    this.destroyChart('chartCompanyPlacements');

    const labels = data.map(d => d.company_name);
    const counts = data.map(d => d.placed_count);
    const colors = ['#2563eb', '#06b6d4', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'];

    this.instances['chartCompanyPlacements'] = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: counts,
          backgroundColor: colors.slice(0, labels.length)
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  },

  // 4. Salary / Package Comparison Ranges
  renderPackageRanges(ranges) {
    const ctx = document.getElementById('chartPackageRanges');
    if (!ctx || !ranges) return;
    this.destroyChart('chartPackageRanges');

    const labels = Object.keys(ranges);
    const counts = Object.values(ranges);

    this.instances['chartPackageRanges'] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Offers within Bracket',
          data: counts,
          backgroundColor: [
            '#60a5fa',
            '#3b82f6',
            '#1d4ed8',
            '#1e40af',
            '#172554'
          ],
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
      }
    });
  },

  // 5. Placed vs Unplaced Students (Doughnut)
  renderPlacedVsUnplaced(data) {
    const ctx = document.getElementById('chartPlacedVsUnplaced');
    if (!ctx || !data) return;
    this.destroyChart('chartPlacedVsUnplaced');

    this.instances['chartPlacedVsUnplaced'] = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Placed Students', 'Unplaced Students'],
        datasets: [{
          data: [data.placed, data.unplaced],
          backgroundColor: ['#10b981', '#f43f5e'],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        },
        cutout: '70%'
      }
    });
  }
};
