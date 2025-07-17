<?php
// dashboard_content.php
// This file contains the content for the Dashboard page.
// It is designed to be included in a main layout file (e.g., dashboard.php).
// All data variables ($active_workers_count, $attendance_rate, etc.)
// are assumed to be fetched and prepared BEFORE this file is included.

// Sample Data Structure (These variables would come from your database queries)
// For demonstration, let's set some dummy values for now.
// In your actual dashboard.php, you'll replace these with real database fetches.

// Overview Stats
$active_workers_count = $active_workers_count ?? 'N/A'; // Example: 248
$attendance_rate = $attendance_rate ?? 'N/A'; // Example: '96.7%'
$monthly_payroll_amount = $monthly_payroll_amount ?? 'N/A'; // Example: '$84,560'
$active_projects_count = $active_projects_count ?? 'N/A'; // Example: 14

// Chart Data (should be JSON encoded arrays from PHP for JavaScript)
$department_chart_data = $department_chart_data ?? ['labels' => [], 'data' => []];
$project_chart_data = $project_chart_data ?? ['labels' => [], 'data' => []];
$attendance_chart_data = $attendance_chart_data ?? ['labels' => [], 'data' => []];

// Recent Activities (should be an array of arrays from PHP)
$recent_activities = $recent_activities ?? [];
// Example structure:
// $recent_activities = [
//     ['type' => 'clockin', 'message' => 'John Smith clocked in at 7:58 AM', 'time' => 'Today, Construction Site A'],
//     ['type' => 'leave', 'message' => 'Maria Garcia requested sick leave', 'time' => 'Yesterday, Needs approval'],
// ];

// Worker Status Table Data (should be an array of arrays from PHP)
$workers_for_dashboard_table = $workers_for_dashboard_table ?? [];
// Example structure:
// $workers_for_dashboard_table = [
//     ['id' => 1, 'name' => 'John Smith', 'department' => 'Construction', 'position' => 'Foreman', 'shift' => 'Day Shift', 'status' => 'On Duty'],
// ];
?>

<h1 class="page-title">
    <i class="fas fa-hard-hat"></i>
    Labour & Company Dashboard
</h1>

---

## Key Performance Indicators

<div class="stats-container">
    <div class="stat-card">
        <div class="stat-icon labour">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($active_workers_count); ?></h3>
            <p>Active Workers</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon attendance">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($attendance_rate); ?></h3>
            <p>Average Attendance</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon payroll">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($monthly_payroll_amount); ?></h3>
            <p>Current Monthly Payroll</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon projects">
            <i class="fas fa-project-diagram"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($active_projects_count); ?></h3>
            <p>Active Projects</p>
        </div>
    </div>
</div>

<div class="labour-grid">
    <div class="dashboard-card">
        <div class="card-header">
            <h3>Worker Distribution by Department</h3>
        </div>
        <div class="chart-container">
            <canvas id="departmentChart"></canvas>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header">
            <h3>Recent System Activities</h3>
        </div>
        <ul class="activities">
            <?php if (!empty($recent_activities)): ?>
                <?php foreach ($recent_activities as $activity): ?>
                    <li class="activity">
                        <div class="activity-icon <?php echo htmlspecialchars($activity['type']); ?>">
                            <?php
                                // Assign appropriate icon based on type
                                $icon_class = 'fas fa-info-circle'; // Default
                                if ($activity['type'] == 'clockin') $icon_class = 'fas fa-clock';
                                elseif ($activity['type'] == 'leave') $icon_class = 'fas fa-file-medical';
                                elseif ($activity['type'] == 'payroll') $icon_class = 'fas fa-money-check';
                                elseif ($activity['type'] == 'new_worker') $icon_class = 'fas fa-user-plus';
                                // Add more conditions for other activity types
                            ?>
                            <i class="<?php echo $icon_class; ?>"></i>
                        </div>
                        <div class="activity-details">
                            <p><?php echo htmlspecialchars($activity['message']); ?></p>
                            <div class="activity-time"><?php echo htmlspecialchars($activity['time']); ?></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="activity" style="justify-content: center; color: #777;">No recent activities to display.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-header">
            <h3>Overall Project Progress</h3>
        </div>
        <div class="chart-container">
            <canvas id="projectChart"></canvas>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header">
            <h3>Weekly Attendance Trend</h3>
        </div>
        <div class="chart-container">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h3>Current Worker Status</h3>
        <a href="?page=workers" class="btn">View All Workers <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Worker ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Shift</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($workers_for_dashboard_table)): ?>
                    <?php foreach ($workers_for_dashboard_table as $worker): ?>
                        <tr>
                            <td>#WRK-<?php echo str_pad($worker['id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($worker['name']); ?></td>
                            <td><?php echo htmlspecialchars($worker['department']); ?></td>
                            <td><?php echo htmlspecialchars($worker['position']); ?></td>
                            <td><?php echo htmlspecialchars($worker['shift']); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                if ($worker['status'] == 'On Duty') $status_class = 'active';
                                elseif ($worker['status'] == 'Sick Leave') $status_class = 'leave';
                                elseif ($worker['status'] == 'Late Arrival') $status_class = 'pending';
                                // Add more status mappings if needed
                                ?>
                                <span class="status <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars($worker['status']); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">No worker data available for display.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- Chart.js Data Initialization ---
    // These variables will receive the JSON encoded data from PHP
    const departmentChartData = <?php echo json_encode($department_chart_data); ?>;
    const projectChartData = <?php echo json_encode($project_chart_data); ?>;
    const attendanceChartData = <?php echo json_encode($attendance_chart_data); ?>;

    // Helper function for default chart options
    function getDefaultChartOptions(titleText) {
        return {
            responsive: true,
            maintainAspectRatio: false, // Important for responsive charts in flexible containers
            plugins: {
                legend: {
                    position: 'top', // Adjust as needed (e.g., 'right' for pie)
                    labels: {
                        font: {
                            size: 10 // Smaller font for legends
                        }
                    }
                },
                title: {
                    display: true,
                    text: titleText,
                    font: {
                        size: 14 // Larger font for chart titles
                    },
                    color: '#333'
                }
            },
            scales: { // Only for bar/line charts
                y: {
                    beginAtZero: true
                }
            }
        };
    }

    // --- Worker Distribution Chart (Pie Chart) ---
    if (departmentChartData && departmentChartData.labels.length > 0) {
        const departmentCtx = document.getElementById('departmentChart');
        new Chart(departmentCtx, {
            type: 'pie',
            data: {
                labels: departmentChartData.labels,
                datasets: [{
                    data: departmentChartData.data,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1', '#fd7e14'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right', // Pie charts often look good with right legends
                        labels: {
                            font: { size: 10 }
                        }
                    },
                    title: {
                        display: false, // Title already in card header
                    }
                }
            }
        });
    } else {
        // Display a message if no data
        const departmentChartDiv = document.getElementById('departmentChart').parentNode;
        departmentChartDiv.innerHTML = '<p style="text-align: center; padding: 20px; color: #777;">No department data available.</p>';
    }

    // --- Project Progress Chart (Bar Chart) ---
    if (projectChartData && projectChartData.labels.length > 0) {
        const projectCtx = document.getElementById('projectChart');
        new Chart(projectCtx, {
            type: 'bar', // Can be 'bar' or 'horizontalBar' for progress
            data: {
                labels: projectChartData.labels,
                datasets: [{
                    label: 'Completion %',
                    data: projectChartData.data,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                ...getDefaultChartOptions('Project Progress'),
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100 // Assuming progress is 0-100%
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    } else {
        const projectChartDiv = document.getElementById('projectChart').parentNode;
        projectChartDiv.innerHTML = '<p style="text-align: center; padding: 20px; color: #777;">No project progress data available.</p>';
    }

    // --- Attendance Overview Chart (Line Chart) ---
    if (attendanceChartData && attendanceChartData.labels.length > 0) {
        const attendanceCtx = document.getElementById('attendanceChart');
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: attendanceChartData.labels,
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: attendanceChartData.data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.3 // Smooth lines
                }]
            },
            options: {
                ...getDefaultChartOptions('Daily Attendance Rate'),
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100 // Assuming attendance is 0-100%
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    } else {
        const attendanceChartDiv = document.getElementById('attendanceChart').parentNode;
        attendanceChartDiv.innerHTML = '<p style="text-align: center; padding: 20px; color: #777;">No attendance trend data available.</p>';
    }
</script>