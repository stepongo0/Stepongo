<?php
session_start(); // Always start the session at the very beginning of the script.

// --- Error Reporting for Development ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Variable Initialization ---
$admin_message = '';
$subadmin_message = '';
$admin_message_type = '';
$subadmin_message_type = '';
$login_successful = false;
$redirect_target_url = ''; // URL to redirect to upon successful login

// --- Handle Admin Login Request ---
if (isset($_POST['admin_login'])) {
    $admin_username = trim($_POST['admin_username'] ?? '');
    $admin_password = trim($_POST['admin_password'] ?? '');

    if ($admin_username === 'admin' && $admin_password === 'password123') {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_type'] = 'admin'; // 'admin' হিসেবে সেট করা হলো
        $_SESSION['username'] = $admin_username;

        $admin_message = 'Login Successfully!';
        $admin_message_type = 'success';
        // *** অ্যাডমিন রিডাইরেক্ট URL এখানে পরিবর্তন করা হয়েছে ***
        $redirect_target_url = 'intro/admin_dashboard.php'; // অ্যাডমিনের জন্য নির্দিষ্ট ড্যাশবোর্ড
        $login_successful = true;
    } else {
        $admin_message = 'Invalid username or password.';
        $admin_message_type = 'error';
    }
}

// --- Handle Sub-Admin Login Request ---
if (isset($_POST['subadmin_login'])) {
    $subadmin_username = trim($_POST['subadmin_username'] ?? '');
    $subadmin_password = trim($_POST['subadmin_password'] ?? '');

    if ($subadmin_username === 'subadmin' && $subadmin_password === 'subpassword456') {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_type'] = 'subadmin'; // 'subadmin' হিসেবে সেট করা হলো
        $_SESSION['username'] = $subadmin_username;

        $subadmin_message = 'Login Successfully!';
        $subadmin_message_type = 'success';
        // *** সাব-অ্যাডমিন রিডাইরেক্ট URL এখানে পরিবর্তন করা হয়েছে ***
        $redirect_target_url = 'intro/subadmin_dashboard.php'; // সাব-অ্যাডমিনের জন্য নির্দিষ্ট ড্যাশবোর্ড
        $login_successful = true;
    } else {
        $subadmin_message = 'Invalid username or password.';
        $subadmin_message_type = 'error';
    }
}

// --- Immediate PHP Redirection upon Successful Login ---
if ($login_successful && !empty($redirect_target_url)) {
    header("Location: " . $redirect_target_url);
    exit(); // Crucial: Stop script execution after sending the header
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Portal - StepOnGo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        /* --- General Body and Container Styling --- */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5; /* Light grey background */
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            height: 100vh; /* Full viewport height */
            margin: 0;
            padding: 20px;
            box-sizing: border-box; /* Include padding/border in element's total width/height */
        }

        .container {
            background: #fff; /* White background for the main content area */
            padding: 40px;
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Subtle shadow */
            width: 100%;
            max-width: 700px; /* Maximum width for the container */
            text-align: center;
            box-sizing: border-box;
        }

        h2 {
            margin-bottom: 30px;
            color: #333; /* Dark grey heading color */
        }

        /* --- Login Sections Layout (using Flexbox) --- */
        .login-sections {
            display: flex; /* Arrange login boxes in a row */
            gap: 30px; /* Space between login boxes */
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
            justify-content: center; /* Center boxes if they don't fill the row */
        }

        .login-section {
            background-color: #f9f9f9; /* Off-white background for each login box */
            padding: 25px;
            border-radius: 6px;
            flex: 1; /* Allows boxes to grow/shrink, taking equal space */
            min-width: 280px; /* Minimum width for each box */
            max-width: calc(50% - 15px); /* Max width: half of container minus half of gap */
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); /* Lighter shadow for inner boxes */
            box-sizing: border-box;
            display: flex;
            flex-direction: column; /* Stack elements vertically inside the box */
            justify-content: space-between; /* Pushes content to top/bottom */
        }

        .login-section h3 {
            color: #555; /* Medium grey heading color */
            margin-top: 0;
            margin-bottom: 25px;
        }

        /* --- Input Field Styling --- */
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            text-align: center; /* Center text input */
            border-radius: 4px;
            border: 1px solid #ccc; /* Light grey border */
            box-sizing: border-box;
            height: 45px;
        }

        .password-field {
            display: flex; /* Use flex for input and toggle icon */
            align-items: center; /* Vertically align items */
            border: 1px solid #ccc;
            border-radius: 4px;
            margin: 10px 0;
            height: 45px;
            box-sizing: border-box;
        }

        .password-field input {
            flex: 1; /* Input takes up remaining space */
            border: none; /* Remove default input border */
            padding: 10px;
            text-align: center;
            height: 100%;
        }

        .password-field input:focus {
            outline: none; /* Remove focus outline */
        }

        .toggle-password {
            padding: 0 10px;
            cursor: pointer;
            color: #555;
            height: 100%;
            display: flex;
            align-items: center;
        }

        /* --- Button Styling --- */
        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            border-radius: 4px;
            color: white; /* White text on buttons */
            font-size: 17px;
            cursor: pointer;
            transition: background-color 0.3s ease; /* Smooth hover effect */
            height: 45px;
            box-sizing: border-box;
        }

        .admin-section button {
            background-color: #007bff; /* Blue for Admin */
        }
        .admin-section button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        .sub-admin-section button {
            background-color: #6f42c1; /* Purple for Sub-Admin */
        }
        .sub-admin-section button:hover {
            background-color: #5a3598; /* Darker purple on hover */
        }

        /* --- Login Message Styling (for success/error) --- */
        .login-message {
            margin-top: 15px;
            padding: 10px;
            font-weight: bold;
            border-radius: 4px;
            display: none; /* Hidden by default, shown by PHP */
            font-size: 0.9em;
        }

        .login-message.success {
            color: #155724; /* Dark green text */
            background-color: #d4edda; /* Light green background */
            border: 1px solid #c3e6cb; /* Green border */
            display: block; /* Make visible */
        }

        .login-message.error {
            color: #721c24; /* Dark red text */
            background-color: #f8d7da; /* Light red background */
            border: 1px solid #f5c6cb; /* Red border */
            display: block; /* Make visible */
        }

        /* --- Responsive Adjustments --- */
        @media (max-width: 992px) {
            .login-section {
                max-width: calc(50% - 15px); /* On medium screens, still two columns */
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            .login-sections {
                flex-direction: column; /* On small screens, stack columns vertically */
                align-items: center; /* Center stacked items */
            }
            .login-section {
                max-width: 100%; /* Each section takes full width */
                min-width: unset; /* Remove min-width constraint */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to the StepOnGo Portal</h2>
        <div class="login-sections">
            <div class="login-section admin-section">
                <h3>Admin Login</h3>
                <form method="POST">
                    <input type="text" name="admin_username" placeholder="Username" required autocomplete="username">
                    <div class="password-field">
                        <input type="password" id="adminPassword" name="admin_password" placeholder="Password" required autocomplete="current-password">
                        <span class="toggle-password" onclick="togglePassword('adminPassword', this)">
                            <i class="fa fa-eye"></i> </span>
                    </div>
                    <button type="submit" name="admin_login">Login</button>

                    <?php
                    // Display admin login message if a login attempt was made
                    if (isset($_POST['admin_login'])):
                    ?>
                        <div class="login-message <?php echo $admin_message_type; ?>">
                            <?php echo $admin_message; ?>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <div class="login-section sub-admin-section">
                <h3>Sub-Admin Login</h3>
                <form method="POST">
                    <input type="text" name="subadmin_username" placeholder="Username" required autocomplete="username">
                    <div class="password-field">
                        <input type="password" id="subAdminPassword" name="subadmin_password" placeholder="Password" required autocomplete="current-password">
                        <span class="toggle-password" onclick="togglePassword('subAdminPassword', this)">
                            <i class="fa fa-eye"></i> </span>
                    </div>
                    <button type="submit" name="subadmin_login">Login</button>

                    <?php
                    // Display sub-admin login message if a login attempt was made
                    if (isset($_POST['subadmin_login'])):
                    ?>
                        <div class="login-message <?php echo $subadmin_message_type; ?>">
                            <?php echo $subadmin_message; ?>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            </div>
    </div>

    <script>
        // JavaScript function to toggle password visibility
        function togglePassword(inputId, toggleElement) {
            const input = document.getElementById(inputId);
            const icon = toggleElement.querySelector('i');

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash'); // Change icon to slashed eye
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye'); // Change icon to eye
            }
        }
    </script>
</body>
</html>
