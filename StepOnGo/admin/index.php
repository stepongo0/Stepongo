<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Portal - StepOnGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            flex-direction: column; /* To stack login forms vertically */
            gap: 20px; /* Space between login forms */
        }
        .portal-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-section {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
            margin: 15px auto; /* Centering each section with some margin */
        }
        .login-section h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8em;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: calc(100% - 20px);
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .login-button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .admin-btn {
            background-color: #007bff;
        }
        .admin-btn:hover {
            background-color: #0056b3;
        }
        .subadmin-btn {
            background-color: #6f42c1; /* Purple color */
        }
        .subadmin-btn:hover {
            background-color: #5a2e9e;
        }
        /* Removed .labour-btn styles */

        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
        }
        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        ul li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="portal-container">
        <h1>Welcome to the StepOnGo Portal</h1>
        <?php
            session_start(); // Start session to access flash messages
            if (isset($_SESSION['login_errors']) && !empty($_SESSION['login_errors'])) {
                echo '<div class="error-message"><ul>';
                foreach ($_SESSION['login_errors'] as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul></div>';
                unset($_SESSION['login_errors']); // Clear errors after displaying
            }
        ?>

        <div class="login-section admin-login-section">
            <h2>Admin Login</h2>
            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="admin_username">Username</label>
                    <input type="text" id="admin_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <input type="password" id="admin_password" name="password" required>
                </div>
                <input type="hidden" name="role" value="admin">
                <button type="submit" class="login-button admin-btn">Login</button>
            </form>
        </div>

        <div class="login-section subadmin-login-section">
            <h2>Sub-Admin Login</h2>
            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="subadmin_username">Username</label>
                    <input type="text" id="subadmin_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="subadmin_password">Password</label>
                    <input type="password" id="subadmin_password" name="password" required>
                </div>
                <input type="hidden" name="role" value="sub_admin">
                <button type="submit" class="login-button subadmin-btn">Login</button>
            </form>
        </div>

        <?php /*
        <div class="login-section labour-login-section">
            <h2>Labour Login</h2>
            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="labour_username">Username</label>
                    <input type="text" id="labour_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="labour_password">Password</label>
                    <input type="password" id="labour_password" name="password" required>
                </div>
                <input type="hidden" name="role" value="labour">
                <button type="submit" class="login-button labour-btn">Login</button>
            </form>
        </div>
        */ ?>

    </div>
</body>
</html>