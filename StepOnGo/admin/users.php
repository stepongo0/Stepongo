<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

include('includes/header.php');
// Include User class to fetch users from DB
// require_once('../classes/user.php');
// $user = new User();
// $users = $user->getAllUsers(); // Assuming this method exists
?>

<main class="content">
    <div class="page-header">
        <h1>User Management</h1>
        <a href="create_user.php" class="btn btn-primary">Add New User</a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>User Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>admin</td>
                    <td>admin@example.com</td>
                    <td>Admin</td>
                    <td>Active</td>
                    <td>
                        <a href="edit_user.php?id=1" class="btn btn-sm btn-info">Edit</a>
                        <a href="delete_user.php?id=1" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>subadmin</td>
                    <td>subadmin@example.com</td>
                    <td>Sub-Admin</td>
                    <td>Active</td>
                    <td>
                        <a href="edit_user.php?id=2" class="btn btn-sm btn-info">Edit</a>
                        <a href="delete_user.php?id=2" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
                 <tr>
                    <td>3</td>
                    <td>labour_john</td>
                    <td>john@example.com</td>
                    <td>Labour</td>
                    <td>Active</td>
                    <td>
                        <a href="edit_user.php?id=3" class="btn btn-sm btn-info">Edit</a>
                        <a href="delete_user.php?id=3" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
                </tbody>
        </table>
    </div>
</main>

<?php include('includes/footer.php'); ?>