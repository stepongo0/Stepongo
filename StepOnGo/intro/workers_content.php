<?php
// workers_content.php
// This file contains the content for the Worker Management page (All Workers List).
// It is designed to be included in a main layout file (e.g., dashboard.php).

// --- Sample Worker Data (Replace with database fetching) ---
// In a real application, you'd fetch this data from your 'workers' table.
$all_workers = [
    ['id' => 101, 'employee_id' => 'WRK-101', 'name' => 'John Smith', 'department' => 'Construction', 'position' => 'Foreman', 'shift' => 'Day Shift', 'contact' => '9876543210', 'status' => 'On Duty', 'joining_date' => '2020-03-15', 'exit_date' => null],
    ['id' => 102, 'employee_id' => 'EMP-002', 'name' => 'Maria Garcia', 'department' => 'Electrical', 'position' => 'Technician', 'shift' => 'Day Shift', 'contact' => '9876543211', 'status' => 'Sick Leave', 'joining_date' => '2021-01-20', 'exit_date' => null],
    ['id' => 103, 'employee_id' => 'LID-A03', 'name' => 'Robert Johnson', 'department' => 'Plumbing', 'position' => 'Senior Technician', 'shift' => 'Night Shift', 'contact' => '9876543212', 'status' => 'On Duty', 'joining_date' => '2018-07-01', 'exit_date' => null],
    ['id' => 104, 'employee_id' => 'WRK-104', 'name' => 'Sarah Williams', 'department' => 'HVAC', 'position' => 'Technician', 'shift' => 'Day Shift', 'contact' => '9876543213', 'status' => 'Late Arrival', 'joining_date' => '2022-09-10', 'exit_date' => null],
    ['id' => 105, 'employee_id' => 'EMP-005', 'name' => 'Michael Brown', 'department' => 'Construction', 'position' => 'Labourer', 'shift' => 'Day Shift', 'contact' => '9876543214', 'status' => 'On Duty', 'joining_date' => '2023-04-22', 'exit_date' => null],
    ['id' => 106, 'employee_id' => 'WRK-106', 'name' => 'David Lee', 'department' => 'Electrical', 'position' => 'Apprentice', 'shift' => 'Night Shift', 'contact' => '9876543215', 'status' => 'On Duty', 'joining_date' => '2024-02-01', 'exit_date' => null],
    ['id' => 107, 'employee_id' => 'LID-B07', 'name' => 'Priya Sharma', 'department' => 'Plumbing', 'position' => 'Supervisor', 'shift' => 'Day Shift', 'contact' => '9876543216', 'status' => 'On Duty', 'joining_date' => '2019-11-05', 'exit_date' => null],
    ['id' => 108, 'employee_id' => 'WRK-108', 'name' => 'Amit Kumar', 'department' => 'Construction', 'position' => 'Mason', 'shift' => 'Day Shift', 'contact' => '9876543217', 'status' => 'On Duty', 'joining_date' => '2022-01-01', 'exit_date' => null],
    ['id' => 109, 'employee_id' => 'EMP-009', 'name' => 'Sneha Patil', 'department' => 'HVAC', 'position' => 'Helper', 'shift' => 'Night Shift', 'contact' => '9876543218', 'status' => 'On Leave', 'joining_date' => '2023-08-12', 'exit_date' => null],
    ['id' => 110, 'employee_id' => 'LID-C10', 'name' => 'Ravi Singh', 'department' => 'Electrical', 'position' => 'Engineer', 'shift' => 'Day Shift', 'contact' => '9876543219', 'status' => 'On Duty', 'joining_date' => '2021-05-30', 'exit_date' => null],
    // Worker who has left (for testing)
    ['id' => 111, 'employee_id' => 'OLD-011', 'name' => 'Anjali Devi', 'department' => 'Administrative', 'position' => 'Clerk', 'shift' => 'Day Shift', 'contact' => '9123456789', 'status' => 'Left Company', 'joining_date' => '2017-02-01', 'exit_date' => '2023-12-15'],
];

// --- Worker Metrics (Replace with aggregated data from DB) ---
$total_workers = count($all_workers);
$on_duty_workers = count(array_filter($all_workers, fn($w) => $w['status'] == 'On Duty'));
$on_leave_workers = count(array_filter($all_workers, fn($w) => $w['status'] == 'Sick Leave' || $w['status'] == 'On Leave'));
$left_workers = count(array_filter($all_workers, fn($w) => $w['status'] == 'Left Company' || $w['status'] == 'Terminated'));
$departments = array_unique(array_column($all_workers, 'department'));

// --- Search/Filter Logic ---
$filtered_workers = $all_workers; // Start with all workers
$search_query = $_GET['search_workers'] ?? '';
$department_filter = $_GET['department_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

if (!empty($search_query)) {
    $search_query = strtolower(trim($search_query));
    $filtered_workers = array_filter($filtered_workers, function($worker) use ($search_query) {
        return stripos($worker['employee_id'], $search_query) !== false ||
               stripos($worker['name'], $search_query) !== false ||
               stripos($worker['contact'], $search_query) !== false;
    });
}

if (!empty($department_filter)) {
    $filtered_workers = array_filter($filtered_workers, fn($worker) => $worker['department'] == $department_filter);
}

if (!empty($status_filter)) {
    $filtered_workers = array_filter($filtered_workers, fn($worker) => $worker['status'] == $status_filter);
}
?>

<h1 class="page-title">
    <i class="fas fa-users-cog"></i>
    Worker Management
</h1>

---

## Worker Overview

<div class="stats-container" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-icon labour">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($total_workers); ?></h3>
            <p>Total Workers</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon attendance" style="background: rgba(40, 167, 69, 0.2); color: #28a745;">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($on_duty_workers); ?></h3>
            <p>Workers On Duty</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon leave" style="background: rgba(255, 193, 7, 0.2); color: #ffc107;">
            <i class="fas fa-user-times"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($on_leave_workers); ?></h3>
            <p>Workers On Leave</p>
        </div>
    </div>
     <div class="stat-card">
        <div class="stat-icon payroll" style="background: rgba(220, 53, 69, 0.2); color: #dc3545;">
            <i class="fas fa-user-minus"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($left_workers); ?></h3>
            <p>Workers Left</p>
        </div>
    </div>
</div>

---

## Add New Worker & Filter Options

<div class="dashboard-card" style="margin-bottom: 30px;">
    <div class="card-header" style="flex-wrap: wrap; gap: 15px;">
        <h3 style="margin-bottom: 0;">Worker Operations</h3>
        <button class="btn" id="addNewWorkerBtn" style="min-width: 180px;">
            <i class="fas fa-user-plus"></i> Add New Worker
        </button>
    </div>
    <form method="GET" action="" style="display: flex; gap: 15px; align-items: flex-end; margin-top: 20px; flex-wrap: wrap;">
        <input type="hidden" name="page" value="workers"> <div class="form-group" style="flex-grow: 1; min-width: 200px; margin-bottom: 0;">
            <label for="searchWorkers" style="font-size: 0.9rem; color: #666;">Search by ID, Name, or Contact</label>
            <input type="text" id="searchWorkers" name="search_workers" class="form-control"
                   placeholder="e.g., WRK-101 or John"
                   value="<?php echo htmlspecialchars($search_query); ?>">
        </div>

        <div class="form-group" style="min-width: 150px; margin-bottom: 0;">
            <label for="departmentFilter" style="font-size: 0.9rem; color: #666;">Department</label>
            <select id="departmentFilter" name="department_filter" class="form-control">
                <option value="">All Departments</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept); ?>"
                        <?php echo ($department_filter == $dept) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="min-width: 150px; margin-bottom: 0;">
            <label for="statusFilter" style="font-size: 0.9rem; color: #666;">Status</label>
            <select id="statusFilter" name="status_filter" class="form-control">
                <option value="">All Statuses</option>
                <option value="On Duty" <?php echo ($status_filter == 'On Duty') ? 'selected' : ''; ?>>On Duty</option>
                <option value="Sick Leave" <?php echo ($status_filter == 'Sick Leave') ? 'selected' : ''; ?>>Sick Leave</option>
                <option value="On Leave" <?php echo ($status_filter == 'On Leave') ? 'selected' : ''; ?>>On Leave</option>
                <option value="Late Arrival" <?php echo ($status_filter == 'Late Arrival') ? 'selected' : ''; ?>>Late Arrival</option>
                <option value="Left Company" <?php echo ($status_filter == 'Left Company') ? 'selected' : ''; ?>>Left Company</option>
                <option value="Terminated" <?php echo ($status_filter == 'Terminated') ? 'selected' : ''; ?>>Terminated</option>
                <option value="Inactive" <?php echo ($status_filter == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>

        <button type="submit" class="btn" style="height: 44px;">
            <i class="fas fa-filter"></i> Filter
        </button>
        <?php if (!empty($search_query) || !empty($department_filter) || !empty($status_filter)): ?>
            <a href="?page=workers" class="btn" style="background-color: #6c757d; height: 44px;">
                <i class="fas fa-redo"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

---

## Add New Worker Entry

<div class="form-container" id="addNewWorkerForm" style="display: none; margin-top: 30px; border-left: 5px solid var(--primary);">
    <h2 style="margin-bottom: 25px; color: var(--primary);">
        <i class="fas fa-user-plus"></i> New Worker Details
    </h2>
    <form id="workerEntryForm" method="POST">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="workerName">Worker Name</label>
                    <input type="text" id="workerName" name="worker_name" class="form-control" placeholder="e.g., Rahul Sharma" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="employeeID">Employee ID (Optional, auto-generated if empty)</label>
                    <input type="text" id="employeeID" name="employee_id" class="form-control" placeholder="e.g., WRK-123">
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department" class="form-control" required>
                        <option value="">-- Select Department --</option>
                        <option value="Construction">Construction</option>
                        <option value="Electrical">Electrical</option>
                        <option value="Plumbing">Plumbing</option>
                        <option value="HVAC">HVAC</option>
                        <option value="Administrative">Administrative</option>
                        </select>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position" class="form-control" placeholder="e.g., Labourer, Foreman" required>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="shift">Shift</label>
                    <select id="shift" name="shift" class="form-control" required>
                        <option value="">-- Select Shift --</option>
                        <option value="Day Shift">Day Shift</option>
                        <option value="Night Shift">Night Shift</option>
                    </select>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="contactNumber">Contact Number</label>
                    <input type="tel" id="contactNumber" name="contact_number" class="form-control" placeholder="e.g., 9876543210" required>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="joiningDate">Joining Date</label>
                    <input type="date" id="joiningDate" name="joining_date" class="form-control" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="workerStatus">Initial Status</label>
                    <select id="workerStatus" name="worker_status" class="form-control" required>
                        <option value="On Duty">On Duty</option>
                        <option value="On Leave">On Leave</option>
                        <option value="Inactive">Inactive</option>
                        </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-block">
            <i class="fas fa-save"></i> Save Worker Details
        </button>
    </form>
</div>

---

## All Workers List

<div class="dashboard-card" style="margin-top: 30px;">
    <div class="card-header">
        <h3>All Workers <?php echo (!empty($search_query) || !empty($department_filter) || !empty($status_filter)) ? ' (Filtered View)' : ''; ?></h3>
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
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filtered_workers)): ?>
                    <?php foreach ($filtered_workers as $worker): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($worker['employee_id']); ?></td>
                            <td>
                                <a href="?page=worker_details&id=<?php echo htmlspecialchars($worker['id']); ?>" class="worker-name-link">
                                    <?php echo htmlspecialchars($worker['name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($worker['department']); ?></td>
                            <td><?php echo htmlspecialchars($worker['position']); ?></td>
                            <td><?php echo htmlspecialchars($worker['shift']); ?></td>
                            <td><?php echo htmlspecialchars($worker['contact']); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                if ($worker['status'] == 'On Duty') $status_class = 'active';
                                elseif ($worker['status'] == 'Sick Leave' || $worker['status'] == 'On Leave') $status_class = 'pending'; // Changed to pending for yellow
                                elseif ($worker['status'] == 'Late Arrival') $status_class = 'warning'; // New class for orange
                                elseif ($worker['status'] == 'Left Company' || $worker['status'] == 'Terminated' || $worker['status'] == 'Inactive') $status_class = 'leave'; // Red for inactive/left
                                ?>
                                <span class="status <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars($worker['status']); ?></span>
                            </td>
                            <td>
                                <a href="?page=worker_details&id=<?php echo htmlspecialchars($worker['id']); ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background-color: var(--primary);">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <button class="btn edit-worker-btn" data-worker-id="<?php echo htmlspecialchars($worker['id']); ?>" style="padding: 5px 10px; font-size: 0.8rem;">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn delete-worker-btn" data-worker-id="<?php echo htmlspecialchars($worker['id']); ?>" style="padding: 5px 10px; font-size: 0.8rem; background: var(--danger);">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">No workers found matching your criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // JavaScript to show/hide the "Add New Worker" form
    const addNewWorkerBtn = document.getElementById('addNewWorkerBtn');
    const addNewWorkerForm = document.getElementById('addNewWorkerForm');
    const joiningDateInput = document.getElementById('joiningDate');

    // Set today's date for joining date input by default
    if (joiningDateInput) {
        const today = new Date();
        const year = today.getFullYear();
        let month = today.getMonth() + 1;
        let day = today.getDate();

        if (month < 10) month = '0' + month;
        if (day < 10) day = '0' + day;

        joiningDateInput.value = ${year}-${month}-${day};
    }


    if (addNewWorkerBtn && addNewWorkerForm) {
        addNewWorkerBtn.addEventListener('click', function() {
            // Toggle visibility
            if (addNewWorkerForm.style.display === 'none' || addNewWorkerForm.style.display === '') {
                addNewWorkerForm.style.display = 'block';
                // Optional: Scroll to the form
                addNewWorkerForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                addNewWorkerForm.style.display = 'none';
            }
        });
    }

    // --- Add New Worker Form Submission (AJAX Simulation) ---
    const workerEntryForm = document.getElementById('workerEntryForm');
    if (workerEntryForm) {
        workerEntryForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const formData = new FormData(workerEntryForm);
            const workerData = {};
            for (const [key, value] of formData.entries()) {
                workerData[key] = value;
            }
            workerData.type = 'add_worker'; // Action type for backend

            // Simulate employee_id generation if not provided
            if (!workerData.employee_id) {
                workerData.employee_id = 'WRK-' + Math.floor(Math.random() * 9000 + 1000); // Simple random ID like WRK-1234
            }

            // In a real application, send this data via AJAX to a backend script
            // Example using Fetch API:
            // fetch('process_worker_action.php', { // You'll create this file
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //     },
            //     body: JSON.stringify(workerData),
            // })
            // .then(response => response.json())
            // .then(data => {
            //     if (data.success) {
            //         alert('Success: ' + data.message);
            //         workerEntryForm.reset(); // Clear form
            //         window.location.reload(); // Reload to show new worker
            //     } else {
            //         alert('Error: ' + data.message);
            //     }
            // })
            // .catch((error) => {
            //     console.error('Network Error:', error);
            //     alert('A network error occurred while adding worker. Please try again.');
            // });

            alert('(Simulated) Adding New Worker: ' + JSON.stringify(workerData, null, 2));
            workerEntryForm.reset(); // Clear the form
            window.location.reload(); // Reload the page to simulate data update
        });
    }

    // --- Edit Worker Button (AJAX Simulation for later) ---
    document.querySelectorAll('.edit-worker-btn').forEach(button => {
        button.addEventListener('click', function() {
            const workerId = this.dataset.workerId;
            alert((Simulated) Edit Worker ID: ${workerId}. In a real app, this would load a modal/form to edit details.);
            // In a real application, you'd fetch worker details by ID and populate a modal/form
        });
    });

    // --- Delete Worker Button (AJAX Simulation) ---
    document.querySelectorAll('.delete-worker-btn').forEach(button => {
        button.addEventListener('click', function() {
            const workerId = this.dataset.workerId;
            if (confirm(Are you sure you want to delete Worker ID: ${workerId}? This action cannot be undone.)) {
                // In a real application, send AJAX request to delete
                // fetch('process_worker_action.php', { // You'll create this file
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ type: 'delete_worker', worker_id: workerId })
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if (data.success) {
                //         alert('Success: ' + data.message);
                //         window.location.reload();
                //     } else {
                //         alert('Error: ' + data.message);
                //     }
                // })
                // .catch(error => console.error('Error:', error));

                alert((Simulated) Worker ID: ${workerId} deleted. Reloading page.);
                window.location.reload(); // Reload to simulate removal
            }
        });
    });
</script>