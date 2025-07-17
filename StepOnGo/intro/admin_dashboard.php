<?php
// dashboard.php

// Database configuration (example) - REMEMBER TO REPLACE WITH YOUR ACTUAL DATABASE CREDENTIALS
$db_host = 'localhost';
$db_name = 'stepongo_new_db';
$db_user = 'root';
$db_pass = "";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from database (example data in this demo)
// In a real application, this data would come from your database
$workers = [
    ['id' => 1, 'name' => 'John Smith', 'department' => 'Construction', 'position' => 'Foreman', 'shift' => 'Day Shift', 'status' => 'On Duty'],
    ['id' => 2, 'name' => 'Maria Garcia', 'department' => 'Electrical', 'position' => 'Technician', 'shift' => 'Day Shift', 'status' => 'Sick Leave'],
    ['id' => 3, 'name' => 'Robert Johnson', 'department' => 'Plumbing', 'position' => 'Senior Technician', 'shift' => 'Night Shift', 'status' => 'On Duty'],
    ['id' => 4, 'name' => 'Sarah Williams', 'department' => 'HVAC', 'position' => 'Technician', 'shift' => 'Day Shift', 'status' => 'Late Arrival'],
    ['id' => 5, 'name' => 'Michael Brown', 'department' => 'Construction', 'position' => 'Laborer', 'shift' => 'Swing Shift', 'status' => 'On Duty']
];

$projects = [
    ['name' => 'Downtown Tower', 'progress' => 75, 'budget' => 1250000, 'workers' => 42],
    ['name' => 'Riverfront Plaza', 'progress' => 42, 'budget' => 890000, 'workers' => 28],
    ['name' => 'Westside Mall', 'progress' => 88, 'budget' => 2150000, 'workers' => 65],
    ['name' => 'Harbor View', 'progress' => 60, 'budget' => 1750000, 'workers' => 38],
    ['name' => 'Central Park', 'progress' => 35, 'budget' => 950000, 'workers' => 24]
];

$attendance = [
    'present' => 92,
    'late' => 5,
    'absent' => 2,
    'leave' => 1
];

$department_distribution = [
    'Construction' => 85,
    'Electrical' => 42,
    'Plumbing' => 38,
    'HVAC' => 35,
    'Landscaping' => 48
];

// Current page detection
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkForce Pro | PHP Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #ffb74d;
            --danger: #f72585;
            --dark: #1e1e2c;
            --light: #f8f9fa;
            --sidebar-width: 250px;
            --header-height: 70px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f0f2f5;
            color: #333;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: white;
            height: 100vh;
            position: fixed;
            transition: var(--transition);
            z-index: 100;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            left: 0; /* Default position */
        }

        .sidebar.hidden { /* New class to hide sidebar */
            left: calc(-1 * var(--sidebar-width)); /* Moves sidebar completely off-screen to the left */
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            height: var(--header-height);
        }

        .sidebar-header h2 {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .sidebar-header h2 span {
            color: var(--success);
        }

        .sidebar-menu {
            padding: 15px 0;
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
        }

        .menu-section {
            padding: 10px 20px;
            color: #aaa;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.95rem;
            border-left: 3px solid transparent;
            text-decoration: none;
            color: white;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.1);
            border-left: 3px solid var(--primary);
        }

        .menu-item i {
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: var(--transition);
        }

        .main-content.expanded { /* New class for expanded main content */
            margin-left: 0;
        }

        /* Header Styles */
        .header {
            height: var(--header-height);
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-left .toggle-btn {
            background-color: red; /* Red background for the left hamburger menu */
            color: white; /* White icon for the left hamburger menu */
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            margin-right: 10px; /* Space between buttons if both are visible (though they won't be) */
        }

        .header-left .toggle-btn.hidden { /* Hides specific toggle button */
            display: none;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .notification, .user-profile {
            position: relative;
            cursor: pointer;
        }

        .notification i, .user-profile img {
            font-size: 1.3rem;
            color: #555;
        }

        .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Logic for showing/hiding buttons based on sidebar state */
        /* When sidebar is visible (default state for body) */
        body:not(.sidebar-hidden) .header-left #showSidebarBtn {
            display: none; /* Hide the black toggle button */
        }
        body:not(.sidebar-hidden) .header-left #sidebarToggleBtn {
            display: block; /* Show the red toggle button */
        }

        /* When sidebar is hidden */
        body.sidebar-hidden .header-left #showSidebarBtn {
            display: block; /* Show the black toggle button */
            color: black; /* Black icon for the right hamburger menu */
            background: none; /* No background for the black hamburger menu */
            padding: 0; /* Remove padding for this button */
            border-radius: 0; /* Remove border-radius */
        }
        body.sidebar-hidden .header-left #sidebarToggleBtn {
            display: none; /* Hide the red toggle button */
        }


        /* Content Area */
        .content {
            padding: 30px;
        }

        .page-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.labour { background: rgba(67, 97, 238, 0.2); color: var(--primary); }
        .stat-icon.attendance { background: rgba(76, 201, 240, 0.2); color: var(--success); }
        .stat-icon.payroll { background: rgba(255, 183, 77, 0.2); color: var(--warning); }
        .stat-icon.projects { background: rgba(63, 55, 201, 0.2); color: var(--secondary); }

        .stat-info h3 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #777;
            font-size: 0.9rem;
        }

        /* Charts and Tables */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-header h3 {
            font-size: 1.1rem;
            color: var(--dark);
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Labour Management */
        .labour-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Recent Activities */
        .activities {
            list-style: none;
        }

        .activity {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .activity:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-icon.clockin { background: rgba(76, 201, 240, 0.2); color: var(--success); }
        .activity-icon.leave { background: rgba(255, 183, 77, 0.2); color: var(--warning); }
        .activity-icon.payroll { background: rgba(63, 55, 201, 0.2); color: var(--secondary); }

        .activity-details {
            flex: 1;
        }

        .activity-details p {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .activity-time {
            color: #999;
            font-size: 0.8rem;
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            color: #666;
            font-weight: 600;
            font-size: 0.9rem;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status.active {
            background: rgba(76, 201, 240, 0.2);
            color: var(--success);
        }

        .status.pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status.leave {
            background: rgba(247, 37, 133, 0.2);
            color: var(--danger);
        }

        /* Form Styles */
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-col {
            flex: 1;
        }

        .btn {
            padding: 12px 25px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: var(--secondary);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                left: unset; /* Override left: 0; for mobile */
            }

            .sidebar.hidden { /* On mobile, if active, it's shown, otherwise hidden */
                transform: translateX(-100%);
            }

            .sidebar.active { /* This class is used by your existing JS toggle logic */
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.expanded { /* Still expands fully */
                margin-left: 0;
            }

            .content {
                padding: 15px;
            }

            .labour-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            /* On smaller screens, only one toggle button is needed */
            body.sidebar-hidden .header-left #showSidebarBtn {
                display: none; /* Hide the black button on small screens (red one is sufficient) */
            }

            body:not(.sidebar-hidden) .header-left #sidebarToggleBtn {
                display: block; /* Always show the red button on small screens when sidebar is open */
            }
            body.sidebar-hidden .header-left #sidebarToggleBtn {
                display: block; /* Also show the red button when sidebar is closed on small screens */
                color: var(--dark); /* Make it dark/black */
                background: none; /* Remove background */
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-hard-hat"></i>
            <h2>WorkForce <span>Pro</span></h2>
        </div>
        <div class="sidebar-menu">
            <a href="?page=dashboard" class="menu-item <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>

            <div class="menu-section">Labour Management</div>
            <a href="?page=workers" class="menu-item <?php echo $current_page == 'workers' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Workers</span>
            </a>
            <a href="?page=attendance" class="menu-item <?php echo $current_page == 'attendance' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Attendance</span>
            </a>
            <a href="?page=payroll" class="menu-item <?php echo $current_page == 'payroll' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Payroll</span>
            </a>
            <a href="?page=shifts" class="menu-item <?php echo $current_page == 'shifts' ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i>
                <span>Shifts</span>
            </a>
            <a href="?page=leave" class="menu-item <?php echo $current_page == 'leave' ? 'active' : ''; ?>">
                <i class="fas fa-file-medical"></i>
                <span>Leave Management</span>
            </a>
            <a href="?page=documents" class="menu-item <?php echo $current_page == 'documents' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>Documents Panel</span>
            </a>
            <a href="?page=assignment" class="menu-item <?php echo $current_page == 'assignment' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>All Labour Assignment</span>
            </a>
            <a href="?page=safety_labour" class="menu-item <?php echo $current_page == 'safety_labour' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i>
                <span>Safety Guidelines</span>
            </a>

            <div class="menu-section">Company Management</div>
            <a href="?page=departments" class="menu-item <?php echo $current_page == 'departments' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span>Departments</span>
            </a>
            <a href="?page=projects" class="menu-item <?php echo $current_page == 'projects' ? 'active' : ''; ?>">
                <i class="fas fa-project-diagram"></i>
                <span>Projects</span>
            </a>
            <a href="?page=performance" class="menu-item <?php echo $current_page == 'performance' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Performance</span>
            </a>
            <a href="?page=finances" class="menu-item <?php echo $current_page == 'finances' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Finances</span>
            </a>
            <a href="?page=company_documents" class="menu-item <?php echo $current_page == 'company_documents' ? 'active' : ''; ?>">
                <i class="fas fa-folder"></i>
                <span>Documents</span>
            </a>
            <a href="?page=safety_company" class="menu-item <?php echo $current_page == 'safety_company' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i>
                <span>Safety Guidelines</span>
            </a>

            <div class="menu-section">Settings</div>
            <a href="?page=settings" class="menu-item <?php echo $current_page == 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>System Settings</span>
            </a>
            <a href="?page=roles" class="menu-item <?php echo $current_page == 'roles' ? 'active' : ''; ?>">
                <i class="fas fa-user-shield"></i>
                <span>Roles & Permissions</span>
            </a>
            <a href="?page=support" class="menu-item <?php echo $current_page == 'support' ? 'active' : ''; ?>">
                <i class="fas fa-question-circle"></i>
                <span>Support</span>
            </a>

            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <button class="toggle-btn" id="sidebarToggleBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <button class="toggle-btn hidden" id="showSidebarBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="header-right">
                <div class="notification">
                    <i class="fas fa-bell"></i>
                    <span class="badge">7</span>
                </div>
                <div class="user-profile">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Admin">
                    <span>Admin User</span>
                </div>
            </div>
        </div>

        <div class="content">
            <?php
            // Include the content file based on the current page
            switch ($current_page) {
                case 'dashboard':
                    include 'dashboard_content.php';
                    break;
                case 'workers':
                    include 'workers_content.php';
                    break;
                case 'attendance':
                    include 'attendance_content.php';
                    break;
                case 'projects':
                    include 'project_content_dev.php';
                    break;
                // Add more cases for other pages you create content files for
                case 'payroll':
                     include 'payroll.php';
                    break;
                case 'shifts':
                     include 'shifts.php';
                    break;

                case 'leave':
                     include 'leave.php';
                    break;
                case 'documents':
                     include 'documents.php';
                    break;
                case 'assignment':
                     include 'assignment.php';
                    break;
                case 'safety_labour':
                     include 'safety_labour.php';
                    break;
                case 'departments':
                     include 'departments.php';
                    break;
                case 'performance':
                     include 'performance.php';
                    break;
                case 'finances':
                     include 'finances.php';
                    break;
                case 'company_documents':
                     include 'company_documents.php';
                    break;
                case 'safety_company':
                     include 'safety_company.php';
                    break;
                case 'settings':
                     include 'settings.php';
                    break;
                case 'roles':
                     include 'roles.php';
                    break;
                case 'support':
                    include 'support.php'; // Use the generic 'under development' page
                    break;
                
            }
            ?>
        </div>
    </div>

    <script>
        // Select elements
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const showSidebarBtn = document.getElementById('showSidebarBtn');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const body = document.body;

        // Function to toggle sidebar visibility and main content expansion
        function toggleSidebar() {
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('expanded');
            body.classList.toggle('sidebar-hidden'); // Adds/removes a class on the body
        }

        // Event listener for the red hamburger menu (initially visible)
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', toggleSidebar);
        }

        // Event listener for the black hamburger menu (visible when sidebar is hidden)
        if (showSidebarBtn) {
            showSidebarBtn.addEventListener('click', toggleSidebar);
        }

        // Update active menu item based on current URL parameter
        const currentSearchParam = new URLSearchParams(window.location.search).get('page');
        const menuItems = document.querySelectorAll('.sidebar-menu .menu-item');

        menuItems.forEach(item => {
            // Extract the 'page' parameter from the item's href
            const itemSearchParam = new URLSearchParams(item.getAttribute('href')).get('page');
            if (itemSearchParam === currentSearchParam) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });


        // Chart Initialization - only run if the canvas element exists on the page
        // (This prevents errors when switching between pages without charts)

        // Department Distribution Chart (Dashboard Only)
        if (document.getElementById('departmentChart')) {
            const deptCtx = document.getElementById('departmentChart').getContext('2d');
            const deptChart = new Chart(deptCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_keys($department_distribution)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($department_distribution)); ?>,
                        backgroundColor: [
                            '#4361ee',
                            '#4cc9f0',
                            '#3f37c9',
                            '#ffb74d',
                            '#f72585'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }

        // Project Progress Chart (Dashboard Only)
        if (document.getElementById('projectChart')) {
            const projectCtx = document.getElementById('projectChart').getContext('2d');
            const projectChart = new Chart(projectCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($projects, 'name')); ?>,
                    datasets: [{
                        label: 'Completion %',
                        data: <?php echo json_encode(array_column($projects, 'progress')); ?>,
                        backgroundColor: '#4361ee'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Attendance Chart (Dashboard Only)
        if (document.getElementById('attendanceChart')) {
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(attendanceCtx, {
                type: 'pie',
                data: {
                    labels: ['Present', 'Late Arrival', 'Absent', 'On Leave'],
                    datasets: [{
                        data: <?php echo json_encode(array_values($attendance)); ?>,
                        backgroundColor: [
                            '#4cc9f0',
                            '#ffb74d',
                            '#f72585',
                            '#3f37c9'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Show/hide add worker form (Workers Page Only)
        const addWorkerBtn = document.getElementById('addWorkerBtn');
        const addWorkerForm = document.getElementById('addWorkerForm');

        if (addWorkerBtn && addWorkerForm) {
            addWorkerBtn.addEventListener('click', function() {
                // Toggle display style
                if (addWorkerForm.style.display === 'none' || addWorkerForm.style.display === '') {
                    addWorkerForm.style.display = 'block';
                } else {
                    addWorkerForm.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
