<?php
// dashboard.php

// Start session if not already started
session_start();

// Include database configuration
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // In a production environment, you might log this error and show a generic message
    die("Connection failed: " . $conn->connect_error);
}

// ===============================================================
// Dummy Data (Replace with actual database fetches using prepared statements)
// ===============================================================

// Example: Fetching active worker count (conceptual, not using prepared statement yet)
// $activeWorkersCount = 0;
// $sql = "SELECT COUNT(*) AS count FROM workers WHERE status = 'On Duty'";
// $result = $conn->query($sql);
// if ($result && $result->num_rows > 0) {
//     $row = $result->fetch_assoc();
//     $activeWorkersCount = $row['count'];
// }

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

// Calculate derived stats from dummy data for demonstration
$total_workers = count($workers); // Total workers for demo
$active_workers = count(array_filter($workers, function($worker) {
    return $worker['status'] === 'On Duty';
}));

// Dummy Attendance Rate Calculation
$total_attendance_records = array_sum($attendance);
$attendance_rate = ($total_attendance_records > 0) ? round(($attendance['present'] / $total_attendance_records) * 100, 1) : 0;

// Dummy Monthly Payroll (sum of worker salaries, for demo let's use a fixed value)
$monthly_payroll = 84560; // Example fixed value

$total_active_projects = count($projects);


// Current page detection from URL parameter, default to 'dashboard'
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ===============================================================
// Check if user just logged in to force sidebar open
// This flag should be set in your login success handler
if (isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'] === true) {
    echo '<script>';
    echo 'localStorage.setItem("sidebarState", "open");'; // Force sidebar open on first login to dashboard
    echo '</script>';
    unset($_SESSION['just_logged_in']); // Unset the flag to allow user preference for subsequent visits
}
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
            min-height: 100vh; /* Ensure body takes full viewport height */
            overflow-x: hidden; /* Prevent horizontal scrollbar from sidebar animation */
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: white;
            height: 100vh;
            position: fixed; /* Fixed position so it doesn't scroll with content */
            transition: var(--transition);
            z-index: 100;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            /* Initially hidden by default */
            left: calc(-1 * var(--sidebar-width));
        }

        /* When sidebar has 'active' class, bring it into view */
        .sidebar.active {
            left: 0;
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
            /* Calculate height to fill remaining space, enabling internal scroll */
            height: calc(100vh - var(--header-height));
            overflow-y: auto; /* Allows menu items to scroll if too many */
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
            border-left: 3px solid transparent; /* For active state highlight */
            text-decoration: none; /* Remove underline from links */
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

        /* Main Content Area */
        .main-content {
            flex: 1; /* Takes remaining width */
            /* Initially, it takes full width as sidebar is hidden */
            margin-left: 0;
            transition: var(--transition);
            display: flex; /* Flex container for header and content */
            flex-direction: column;
        }

        /* When sidebar is active, push main content to the right */
        .main-content.pushed {
            margin-left: var(--sidebar-width);
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
            position: sticky; /* Sticky header */
            top: 0;
            z-index: 90;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        /* Single Toggle Button Style */
        .header-left .toggle-btn {
            background: none;
            color: black;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            margin-right: 10px;
            transition: color 0.3s ease;
        }

        .header-left .toggle-btn:hover {
            color: var(--primary);
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

        /* Main Content Area below Header */
        .content {
            flex-grow: 1; /* Allows content to take up remaining height */
            padding: 30px;
            overflow-y: auto; /* Enables scrolling for the main content area */
            -webkit-overflow-scrolling: touch; /* Improves scrolling performance on iOS */
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
            grid-template-columns: 2fr 1fr; /* Example: 2/3 for charts, 1/3 for activities */
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
            height: 300px; /* Fixed height for charts */
            position: relative;
        }

        /* Labour Management Specific Grid (if applicable on other pages) */
        .labour-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Recent Activities List */
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
            border-bottom: none; /* No border for the last item */
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0; /* Prevent icon from shrinking */
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

        /* General Table Styles */
        .table-container {
            overflow-x: auto; /* Allow horizontal scrolling for wide tables */
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

        /* Form Styles (for add/edit pages) */
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            max-width: 800px; /* Limit form width */
            margin: 0 auto; /* Center the form */
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
            flex: 1; /* Distribute columns equally */
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
                grid-template-columns: 1fr; /* Stack columns on smaller screens */
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%); /* Use transform for smooth slide on mobile */
                left: unset; /* Override fixed left for transform to work */
                /* When sidebar is active, add an overlay */
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0; /* Always take full width on mobile, sidebar slides over */
            }

            /* No 'pushed' class needed for main content on mobile as sidebar overlays */
            .main-content.pushed {
                margin-left: 0;
            }

            /* Overlay for mobile when sidebar is active */
            .sidebar.active + .main-content::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5); /* Semi-transparent black overlay */
                z-index: 99; /* Below sidebar, above content */
                cursor: pointer;
            }

            .content {
                padding: 15px; /* Reduce padding on smaller screens */
            }

            .labour-grid {
                grid-template-columns: 1fr; /* Stack labour cards on mobile */
            }

            .form-row {
                flex-direction: column; /* Stack form fields on mobile */
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="mainSidebar">
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

    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-left">
                <button class="toggle-btn" id="sidebarToggleBtn">
                    <i class="fas fa-bars"></i> </button>
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
                    include 'projects_content.php';
                    break;
                // Add more cases for other pages you create content files for
                case 'payroll':
                case 'shifts':
                case 'leave':
                case 'documents':
                case 'assignment':
                case 'safety_labour':
                case 'departments':
                case 'performance':
                case 'finances':
                case 'company_documents':
                case 'safety_company':
                case 'settings':
                case 'roles':
                case 'support':
                    // For pages not yet fully developed, include a generic 'under_development.php'
                    include 'under_development.php';
                    break;
                default:
                    // Fallback to dashboard if a requested page is not found
                    include 'dashboard_content.php';
                    break;
            }
            ?>
        </div>
    </div>

    <script>
        // Get references to DOM elements
        const mainSidebar = document.getElementById('mainSidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebarMenu = document.querySelector('.sidebar-menu');

        // Function to toggle sidebar visibility and main content push
        function toggleSidebar() {
            mainSidebar.classList.toggle('active'); // Add/remove 'active' class on sidebar
            mainContent.classList.toggle('pushed'); // Add/remove 'pushed' class on main content

            // Toggle icon of the button
            const icon = sidebarToggleBtn.querySelector('i');
            if (mainSidebar.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times'); // Or fa-arrow-left, whatever you prefer
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }

            // Save sidebar state to localStorage whenever it's toggled
            if (mainSidebar.classList.contains('active')) {
                localStorage.setItem('sidebarState', 'open');
            } else {
                localStorage.setItem('sidebarState', 'closed');
            }
        }

        // Add event listener to the single hamburger button
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', toggleSidebar);
        }

        // Add event listener for mobile overlay click (to close sidebar)
        // This targets the pseudo-element overlay
        mainContent.addEventListener('click', function(event) {
            // Check if sidebar is active and click was on the overlay (not inside main-content itself)
            if (mainSidebar.classList.contains('active') && window.innerWidth <= 768) {
                // If the click is not inside the sidebar, close it
                if (!mainSidebar.contains(event.target) && event.target === mainContent) {
                    toggleSidebar();
                }
            }
        });


        document.addEventListener('DOMContentLoaded', function() {
            const savedSidebarState = localStorage.getItem('sidebarState');

            // Apply saved state on load
            if (savedSidebarState === 'open') {
                mainSidebar.classList.add('active');
                mainContent.classList.add('pushed');
                sidebarToggleBtn.querySelector('i').classList.remove('fa-bars');
                sidebarToggleBtn.querySelector('i').classList.add('fa-times');
            } else {
                // Ensure initial state for icon if sidebar is closed by default
                sidebarToggleBtn.querySelector('i').classList.remove('fa-times');
                sidebarToggleBtn.querySelector('i').classList.add('fa-bars');
            }

            // --- Sidebar Scroll to Top on Page Load ---
            if (sidebarMenu) {
                sidebarMenu.scrollTop = 0;
            }
            // --- End Sidebar Scroll to Top ---
        });


        // Highlight active menu item based on current URL parameter
        const currentSearchParam = new URLSearchParams(window.location.search).get('page');
        const menuItems = document.querySelectorAll('.sidebar-menu .menu-item');

        menuItems.forEach(item => {
            const itemSearchParam = new URLSearchParams(item.getAttribute('href')).get('page');
            if (itemSearchParam === currentSearchParam) {
                item.classList.add('active'); // Add 'active' class if it matches the current page
            } else {
                item.classList.remove('active'); // Remove 'active' class if it doesn't match
            }
        });


        // Chart Initialization (conditional, only runs if the canvas element exists on the page)
        // Department Distribution Doughnut Chart (for Dashboard)
        if (document.getElementById('departmentChart') && <?php echo json_encode(!empty($department_distribution)); ?>) {
            const deptCtx = document.getElementById('departmentChart').getContext('2d');
            const deptChart = new Chart(deptCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_keys($department_distribution)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($department_distribution)); ?>,
                        backgroundColor: [
                            '#4361ee', // primary
                            '#4cc9f0', // success
                            '#3f37c9', // secondary
                            '#ffb74d', // warning
                            '#f72585'  // danger
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right', // Legend on the right side
                            labels: {
                                color: '#333' // Legend text color
                            }
                        }
                    }
                }
            });
        }

        // Project Progress Bar Chart (for Dashboard)
        if (document.getElementById('projectChart') && <?php echo json_encode(!empty($projects)); ?>) {
            const projectCtx = document.getElementById('projectChart').getContext('2d');
            const projectChart = new Chart(projectCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($projects, 'name')); ?>,
                    datasets: [{
                        label: 'Completion %',
                        data: <?php echo json_encode(array_column($projects, 'progress')); ?>,
                        backgroundColor: '#4361ee' // primary color for bars
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100, // Max value for percentage
                            ticks: {
                                callback: function(value) {
                                    return value + '%'; // Add '%' to y-axis labels
                                },
                                color: '#555' // Y-axis tick color
                            },
                            grid: {
                                color: '#eee' // Y-axis grid lines color
                            }
                        },
                        x: { // Add x-axis configuration for labels if they become too long
                             ticks: {
                                autoSkip: false, // Prevent labels from being skipped
                                maxRotation: 45, // Rotate labels for better readability
                                minRotation: 45,
                                color: '#555'
                            },
                             grid: {
                                display: false // Hide x-axis grid lines for cleaner look
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false // Hide legend for single dataset bar chart
                        }
                    }
                }
            });
        }

        // Attendance Pie Chart (for Dashboard)
        if (document.getElementById('attendanceChart') && <?php echo json_encode(!empty($attendance)); ?>) {
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(attendanceCtx, {
                type: 'pie',
                data: {
                    labels: ['Present', 'Late Arrival', 'Absent', 'On Leave'],
                    datasets: [{
                        data: <?php echo json_encode(array_values($attendance)); ?>,
                        backgroundColor: [
                            '#4cc9f0', // success
                            '#ffb74d', // warning
                            '#f72585', // danger
                            '#3f37c9'  // secondary
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom', // Legend at the bottom
                            labels: {
                                color: '#333' // Legend text color
                            }
                        }
                    }
                }
            });
        }

        // Show/hide add worker form (for Workers page)
        const addWorkerBtn = document.getElementById('addWorkerBtn');
        const addWorkerForm = document.getElementById('addWorkerForm');

        if (addWorkerBtn && addWorkerForm) {
            addWorkerBtn.addEventListener('click', function() {
                // Toggle display style between 'block' and 'none'
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
<?php
// Close database connection to free up resources
$conn->close();
?>