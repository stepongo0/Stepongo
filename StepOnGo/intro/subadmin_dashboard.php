<?php
session_start();

// চেক করুন ব্যবহারকারী লগ ইন করা আছে কিনা এবং সে সাব-অ্যাডমিন কিনা
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'subadmin') {
    header("Location: ../index.php"); // লগইন পেজে রিডাইরেক্ট করুন
    exit();
}

// সাব-অ্যাডমিনের ইউজারনেম সেশন থেকে নিন
$username = $_SESSION['username'] ?? 'Sub-Admin';

// সেশন থেকে লগইন মেসেজ চেক করুন
$login_message = '';
$login_message_type = '';
if (isset($_SESSION['login_message'])) {
    $login_message = $_SESSION['login_message'];
    $login_message_type = $_SESSION['login_message_type'] ?? 'info'; // ডিফল্ট ধরন
    // মেসেজ দেখানোর পর সেশন থেকে মুছে দিন
    unset($_SESSION['login_message']);
    unset($_SESSION['login_message_type']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সাব-অ্যাডমিন ড্যাশবোর্ড - StepOnGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* আপনার আগের ড্যাশবোর্ডের CSS কোড এখানে পেস্ট করুন */
        /* Import Google Fonts - Poppins */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        /* CSS Variables for easy theme changes */
        :root {
            --primary-color: #007bff; /* Blue */
            --secondary-color: #6f42c1; /* Purple */
            --success-color: #28a745; /* Green */
            --info-color: #17a2b8; /* Cyan */
            --warning-color: #ffc107; /* Yellow */
            --danger-color: #dc3545; /* Red */
            --light-bg: #f8f9fa; /* Light Gray Background */
            --dark-text: #343a40; /* Dark Gray Text */
            --light-text: #6c757d; /* Medium Gray Text */
            --border-color: #dee2e6; /* Light Border */
            --card-bg: #ffffff; /* White Card Background */
            --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); /* Subtle Shadow */
            --border-radius: 0.5rem; /* Rounded Corners */
        }

        /* Base Styles */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-bg);
            color: var(--dark-text);
            line-height: 1.6;
            scroll-behavior: smooth; /* Smooth scrolling for anchor links */
        }

        .dashboard-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensure container takes full viewport height */
        }

        /* --- Header/Navbar Styles --- */
        .header {
            background-color: var(--card-bg);
            padding: 1rem 2rem;
            box-shadow: var(--box-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Allow items to wrap on smaller screens */
            gap: 1rem; /* Space between elements */
            position: sticky; /* Stick to top */
            top: 0;
            width: 100%;
            z-index: 1000; /* Ensure it stays on top of other content */
            box-sizing: border-box; /* Include padding in width calculation */
        }

        .header .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .header .profile-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header .profile-section .notification-icon {
            font-size: 1.3rem;
            color: var(--light-text);
            cursor: pointer;
            position: relative;
            transition: color 0.2s ease;
        }
        .header .profile-section .notification-icon:hover {
            color: var(--primary-color);
        }

        .header .profile-section .notification-icon .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger-color);
            color: white;
            font-size: 0.7rem;
            border-radius: 50%;
            padding: 3px 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 18px; /* Ensure badge is round even with single digit */
            height: 18px;
        }

        .header .profile-section .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }

        .header .profile-section .logout-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: var(--border-radius);
            background-color: transparent; /* No background by default */
            transition: background-color 0.2s ease, transform 0.2s ease;
            text-decoration: none;
            color: var(--danger-color); /* Red for logout icon */
        }
        .header .profile-section .logout-button:hover {
            background-color: rgba(220, 53, 69, 0.1); /* Light red background on hover */
            transform: scale(1.05);
        }
        .header .profile-section .logout-button i {
            font-size: 1.3rem;
        }

        /* --- Main Content Area --- */
        .main-content {
            flex-grow: 1; /* Allows content to take up remaining vertical space */
            padding: 2rem;
        }

        .welcome-heading {
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 2.2rem;
            color: var(--dark-text);
            font-weight: 600;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Responsive grid columns */
            gap: 25px; /* Space between grid items */
            max-width: 1200px; /* Max width for the grid */
            margin: 0 auto; /* Center the grid */
        }

        .dashboard-card {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: left;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 150px; /* Ensure consistent height for all cards */
            cursor: pointer; /* Indicate clickable cards */
            border: 1px solid var(--border-color); /* Light border */
        }

        .dashboard-card:hover {
            transform: translateY(-5px); /* Lift effect on hover */
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1); /* Stronger shadow on hover */
        }

        .dashboard-card .icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color); /* Default icon color */
        }

        .dashboard-card h4 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--dark-text);
        }

        .dashboard-card p {
            font-size: 0.9rem;
            color: var(--light-text);
            margin-bottom: 0;
        }

        /* Card specific icon colors (optional, for visual distinction) */
        .card-1 .icon { color: var(--primary-color); }
        .card-2 .icon { color: var(--success-color); }
        .card-3 .icon { color: var(--warning-color); }
        .card-4 .icon { color: var(--info-color); }
        .card-5 .icon { color: var(--secondary-color); }
        .card-6 .icon { color: var(--danger-color); }
        .card-7 .icon { color: #fd7e14; /* Orange */ }
        .card-8 .icon { color: #6610f2; /* Indigo */ }

        /* --- Footer/Bottom Navbar Styles --- */
        .bottom-navbar {
            background-color: var(--card-bg);
            padding: 0.75rem 1rem;
            box-shadow: 0 -0.125rem 0.25rem rgba(0, 0, 0, 0.075); /* Shadow on top */
            display: flex;
            justify-content: space-around;
            align-items: center;
            position: sticky; /* Make it stick to bottom */
            bottom: 0;
            width: 100%;
            z-index: 1000;
            box-sizing: border-box;
            border-top: 1px solid var(--border-color); /* Subtle top border */
        }

        .bottom-navbar a {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--light-text);
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.2s ease, transform 0.2s ease;
            padding: 5px 0; /* Add padding for touch targets */
            flex: 1; /* Distribute space evenly */
            text-align: center;
        }

        .bottom-navbar a i {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .bottom-navbar a:hover {
            color: var(--primary-color);
            transform: translateY(-2px); /* Slight lift on hover */
        }
        .bottom-navbar a.active {
            color: var(--primary-color); /* Active link color */
            font-weight: 600;
        }

        /* --- Responsive Design --- */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                padding: 1rem;
            }
            .header .profile-section {
                width: 100%;
                justify-content: flex-end; /* Push profile items to the right */
                margin-top: 10px;
            }
            .main-content {
                padding: 1.5rem;
            }
            .welcome-heading {
                font-size: 1.8rem;
                margin-bottom: 2rem;
            }
            .dashboard-grid {
                grid-template-columns: 1fr; /* Stack cards on small screens */
                gap: 20px;
            }
            .dashboard-card {
                padding: 1.5rem;
                min-height: unset; /* Allow height to adjust on smaller cards */
            }
            .bottom-navbar {
                padding: 0.5rem 0;
            }
        }

        @media (max-width: 480px) {
            .header .logo {
                font-size: 1.3rem;
            }
            .welcome-heading {
                font-size: 1.6rem;
            }
            .dashboard-card .icon {
                font-size: 2rem;
            }
            .dashboard-card h4 {
                font-size: 1.1rem;
            }
            .dashboard-card p {
                font-size: 0.8rem;
            }
            .bottom-navbar a {
                font-size: 0.75rem;
            }
            .bottom-navbar a i {
                font-size: 1.1rem;
            }
        }

        /* Message box styling for dashboard */
        .dashboard-message {
            margin: 20px auto;
            padding: 15px;
            font-weight: bold;
            border-radius: var(--border-radius);
            text-align: center;
            max-width: 600px;
            box-shadow: var(--box-shadow);
        }

        .dashboard-message.success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .dashboard-message.error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .dashboard-message.info {
            color: #0c5460;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="header">
            <a href="#" class="logo">StepOnGo</a>
            <div class="profile-section">
                <div class="notification-icon">
                    <i class="fa-solid fa-bell"></i>
                    <span class="badge">3</span>
                </div>
                <img src="https://via.placeholder.com/40/007bff/ffffff?text=U" alt="ইউজার অবতার" class="user-avatar">
                <a href="../logout.php" class="logout-button" title="লগআউট">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </header>

        <main class="main-content">
            <?php
            // লগইন মেসেজ প্রদর্শন করুন যদি থাকে
            if (!empty($login_message)):
            ?>
                <div class="dashboard-message <?php echo $login_message_type; ?>">
                    <?php echo $login_message; ?>
                </div>
            <?php endif; ?>

            <h1 class="welcome-heading">স্বাগতম, <?php echo htmlspecialchars($username); ?> (সাব-অ্যাডমিন)</h1>
            <div class="dashboard-grid">
                <!-- সাব-অ্যাডমিনদের জন্য নির্দিষ্ট কার্ড বা কন্টেন্ট এখানে যোগ করুন -->
                <div class="dashboard-card card-1">
                    <div class="icon"><i class="fa-solid fa-users-line"></i></div>
                    <h4>শ্রমিক উপস্থিতি তালিকা</h4>
                    <p>চেক ইন/চেক-আউট চিহ্নিত করুন এবং উপস্থিতির ইতিহাস দেখুন</p>
                </div>

                <div class="dashboard-card card-2">
                    <div class="icon"><i class="fa-solid fa-sack-dollar"></i></div>
                    <h4>পেমেন্টস</h4>
                    <p>বিলিং, ক্লিয়ারেন্স এবং মজুরি পেমেন্ট ট্র্যাক করুন</p>
                </div>

                <div class="dashboard-card card-3">
                    <div class="icon"><i class="fa-solid fa-list-check"></i></div>
                    <h4>প্রজেক্টস</h4>
                    <p>লাইভ সাইট, টাইমলাইন এবং কন্ট্রাক্টর কার্যকলাপ ট্র্যাক করুন</p>
                </div>

                <!-- আরও সাব-অ্যাডমিন-নির্দিষ্ট কার্ড এখানে যোগ করতে পারেন -->
            </div>
        </main>

        <nav class="bottom-navbar">
            <a href="#" class="active">
                <i class="fa-solid fa-house"></i>
                হোম
            </a>
            <a href="#">
                <i class="fa-solid fa-user-clock"></i>
                উপস্থিতি
            </a>
            <a href="#">
                <i class="fa-solid fa-comments"></i>
                চ্যাট
            </a>
            <a href="C:\xampp\htdocs\step\intro/logout.php"> <i class="fa-solid fa-right-from-bracket"></i> লগআউট </a>
        </nav>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.bottom-navbar a');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    navLinks.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            const dashboardCards = document.querySelectorAll('.dashboard-card');
            dashboardCards.forEach(card => {
                card.addEventListener('click', function() {
                    console.log('কার্ডে ক্লিক করা হয়েছে:', this.querySelector('h4').innerText);
                    // এখানে আপনি কার্ড অনুযায়ী নেভিগেশন যোগ করতে পারেন
                });
            });
        });
    </script>
</body>
</html>
