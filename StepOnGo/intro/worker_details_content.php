<?php
// worker_details_content.php
// This file displays the detailed profile of a specific worker, accessible by HR/Admin.
// It is designed to be included in a main layout file (e.g., dashboard.php).

// IMPORTANT: This page expects a 'id' parameter in the URL (e.g., ?page=worker_details&id=101)
$worker_id_to_display = $_GET['id'] ?? null;

// --- Dummy Worker Data (Replace with real database fetching based on $worker_id_to_display) ---
// In a real application, you'd fetch this from your 'workers' table.
// This is a simplified lookup; in production, use a proper DB query.
$all_available_workers_data = [
    101 => ['id' => 101, 'employee_id' => 'WRK-101', 'name' => 'John Smith', 'department' => 'Construction', 'position' => 'Foreman', 'shift' => 'Day Shift', 'contact' => '9876543210', 'joining_date' => '2020-03-15', 'status' => 'On Duty', 'exit_date' => null, 'email' => 'john.s@example.com', 'address' => '123 Main St, Anytown'],
    102 => ['id' => 102, 'employee_id' => 'EMP-002', 'name' => 'Maria Garcia', 'department' => 'Electrical', 'position' => 'Technician', 'shift' => 'Day Shift', 'contact' => '9876543211', 'joining_date' => '2021-01-20', 'status' => 'Sick Leave', 'exit_date' => null, 'email' => 'maria.g@example.com', 'address' => '456 Oak Ave, Somewhere'],
    103 => ['id' => 103, 'employee_id' => 'LID-A03', 'name' => 'Robert Johnson', 'department' => 'Plumbing', 'position' => 'Senior Technician', 'shift' => 'Night Shift', 'contact' => '9876543212', 'joining_date' => '2018-07-01', 'status' => 'On Duty', 'exit_date' => null, 'email' => 'robert.j@example.com', 'address' => '789 Pine Rd, Nowhere'],
    104 => ['id' => 104, 'employee_id' => 'WRK-104', 'name' => 'Sarah Williams', 'department' => 'HVAC', 'position' => 'Technician', 'shift' => 'Day Shift', 'contact' => '9876543213', 'joining_date' => '2022-09-10', 'status' => 'Late Arrival', 'exit_date' => null, 'email' => 'sarah.w@example.com', 'address' => '101 Elm St, Villagetown'],
    105 => ['id' => 105, 'employee_id' => 'EMP-005', 'name' => 'Michael Brown', 'department' => 'Construction', 'position' => 'Labourer', 'shift' => 'Day Shift', 'contact' => '9876543214', 'joining_date' => '2023-04-22', 'status' => 'On Duty', 'exit_date' => null, 'email' => 'michael.b@example.com', 'address' => '202 Maple Dr, Cityville'],
    106 => ['id' => 106, 'employee_id' => 'WRK-106', 'name' => 'David Lee', 'department' => 'Electrical', 'position' => 'Apprentice', 'shift' => 'Night Shift', 'contact' => '9876543215', 'joining_date' => '2024-02-01', 'status' => 'On Duty', 'exit_date' => null, 'email' => 'david.l@example.com', 'address' => '303 Birch Ln, County'],
    107 => ['id' => 107, 'employee_id' => 'LID-B07', 'name' => 'Priya Sharma', 'department' => 'Plumbing', 'position' => 'Supervisor', 'shift' => 'Day Shift', 'contact' => '9876543216', 'joining_date' => '2019-11-05', 'status' => 'On Duty', 'exit_date' => null, 'email' => 'priya.s@example.com', 'address' => '404 Cedar St, Townsville'],
    108 => ['id' => 108, 'employee_id' => 'WRK-108', 'name' => 'Amit Kumar', 'department' => 'Construction', 'position' => 'Mason', 'shift' => 'Day Shift', 'contact' => '9876543217', 'joining_date' => '2022-01-01', 'status' => 'On Duty', 'exit_date' => null, 'email' => 'amit.k@example.com', 'address' => '505 Willow Way, Suburbia'],
    109 => ['id' => 109, 'employee_id' => 'EMP-009', 'name' => 'Sneha Patil', 'department' => 'HVAC', 'position' => 'Helper', 'shift' => 'Night Shift', 'contact' => '9876543218', 'joining_date' => '2023-08-12', 'status' => 'On Leave', 'exit_date' => null, 'email' => 'sneha.p@example.com', 'address' => '606 Poplar Rd, Hamlet'],
    110 => ['id' => 110, 'employee_id' => 'LID-C10', 'name' => 'Ravi Singh', 'department' => 'Electrical', 'position' => 'Engineer', 'shift' => 'Day Shift', 'contact' => '9876543219', 'joining_date' => '2021-05-30', 'status' => 'On Duty', 'exit_date' => null, 'email' => 'ravi.s@example.com', 'address' => '707 Aspen Blvd, Metropolis'],
    // Worker who has left (for testing)
    111 => ['id' => 111, 'employee_id' => 'OLD-011', 'name' => 'Anjali Devi', 'department' => 'Administrative', 'position' => 'Clerk', 'shift' => 'Day Shift', 'contact' => '9123456789', 'joining_date' => '2017-02-01', 'status' => 'Left Company', 'exit_date' => '2023-12-15', 'email' => 'anjali.d@example.com', 'address' => '808 Old Lane, Pastville'],
];

$worker_data = $all_available_workers_data[$worker_id_to_display] ?? null;

// Check if worker data was found
if (!$worker_data) {
    echo '<div class="dashboard-card" style="text-align: center; padding: 40px; color: #dc3545;">';
    echo '<i class="fas fa-exclamation-circle fa-3x" style="margin-bottom: 20px;"></i>';
    echo '<h2>Worker not found.</h2>';
    echo '<p>The worker ID specified does not exist or is invalid.</p>';
    echo '<a href="?page=workers" class="btn" style="margin-top: 20px;">Back to All Workers</a>';
    echo '</div>';
    exit(); // Stop execution if no worker data
}

// Prepare status class for visual indication
$status_class = '';
$status_icon = '';
if ($worker_data['status'] == 'On Duty') {
    $status_class = 'active';
    $status_icon = 'fas fa-check-circle';
} elseif ($worker_data['status'] == 'On Leave' || $worker_data['status'] == 'Sick Leave') {
    $status_class = 'pending'; // Yellow
    $status_icon = 'fas fa-clock';
} elseif ($worker_data['status'] == 'Late Arrival') {
    $status_class = 'warning'; // Orange
    $status_icon = 'fas fa-exclamation-triangle';
} elseif ($worker_data['status'] == 'Left Company' || $worker_data['status'] == 'Terminated' || $worker_data['status'] == 'Inactive') {
    $status_class = 'leave'; // Red
    $status_icon = 'fas fa-user-times';
}
?>

<h1 class="page-title">
    <i class="fas fa-user-circle"></i>
    Worker Profile: <?php echo htmlspecialchars($worker_data['name']); ?>
</h1>

<div class="dashboard-card profile-card">
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fas fa-user fa-3x"></i>
        </div>
        <div class="profile-title">
            <h2><?php echo htmlspecialchars($worker_data['name']); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($worker_data['position']); ?> at <?php echo htmlspecialchars($worker_data['department']); ?></p>
            <span class="status <?php echo htmlspecialchars($status_class); ?>">
                <i class="<?php echo htmlspecialchars($status_icon); ?>"></i>
                <?php echo htmlspecialchars($worker_data['status']); ?>
            </span>
        </div>
    </div>

    <div class="profile-details">
        <div class="detail-item">
            <strong><i class="fas fa-id-badge icon-sm"></i> Employee ID:</strong>
            <span><?php echo htmlspecialchars($worker_data['employee_id']); ?></span>
        </div>
        <div class="detail-item">
            <strong><i class="fas fa-briefcase icon-sm"></i> Department:</strong>
            <span><?php echo htmlspecialchars($worker_data['department']); ?></span>
        </div>
        <div class="detail-item">
            <strong><i class="fas fa-user-tag icon-sm"></i> Position:</strong>
            <span><?php echo htmlspecialchars($worker_data['position']); ?></span>
        </div>
        <div class="detail-item">
            <strong><i class="fas fa-calendar-alt icon-sm"></i> Joining Date:</strong>
            <span><?php echo date('d M, Y', strtotime($worker_data['joining_date'])); ?></span>
        </div>
        <?php if ($worker_data['status'] == 'Left Company' || $worker_data['status'] == 'Terminated'): ?>
            <div class="detail-item">
                <strong><i class="fas fa-sign-out-alt icon-sm"></i> Exit Date:</strong>
                <span><?php echo $worker_data['exit_date'] ? date('d M, Y', strtotime($worker_data['exit_date'])) : 'N/A'; ?></span>
            </div>
        <?php endif; ?>
        <div class="detail-item">
            <strong><i class="fas fa-clock icon-sm"></i> Shift:</strong>
            <span><?php echo htmlspecialchars($worker_data['shift']); ?></span>
        </div>
        <div class="detail-item">
            <strong><i class="fas fa-phone icon-sm"></i> Contact Number:</strong>
            <span><?php echo htmlspecialchars($worker_data['contact']); ?></span>
        </div>
        <div class="detail-item">
            <strong><i class="fas fa-envelope icon-sm"></i> Email:</strong>
            <span><?php echo htmlspecialchars($worker_data['email'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item full-width">
            <strong><i class="fas fa-map-marker-alt icon-sm"></i> Address:</strong>
            <span><?php echo htmlspecialchars($worker_data['address'] ?? 'N/A'); ?></span>
        </div>
        </div>

    <div class="profile-actions">
        <a href="?page=workers" class="btn" style="background-color: #6c757d;">
            <i class="fas fa-arrow-left"></i> Back to All Workers
        </a>
        <?php if ($worker_data['status'] != 'Left Company' && $worker_data['status'] != 'Terminated'): ?>
            <button class="btn edit-worker-profile-btn" data-worker-id="<?php echo htmlspecialchars($worker_data['id']); ?>">
                <i class="fas fa-edit"></i> Edit Profile
            </button>
            <button class="btn mark-exit-btn" data-worker-id="<?php echo htmlspecialchars($worker_data['id']); ?>" style="background-color: var(--danger);">
                <i class="fas fa-user-minus"></i> Mark as Left
            </button>
        <?php endif; ?>
    </div>
</div>

<style>
/* Add these styles to your main style.css or within a <style> tag */
.profile-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 30px;
    text-align: center;
}

.profile-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 30px;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: var(--primary-light);
    color: var(--primary);
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 15px;
    border: 3px solid var(--primary);
}

.profile-title h2 {
    margin: 0;
    font-size: 2rem;
    color: var(--primary);
}

.profile-title .text-muted {
    color: #6c757d;
    font-size: 1rem;
    margin-top: 5px;
    margin-bottom: 10px;
}

.profile-title .status {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.profile-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px 30px;
    width: 100%;
    max-width: 700px; /* Increased width for more details */
    text-align: left;
    margin-bottom: 30px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-item strong {
    color: #333;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-item span {
    color: #555;
    font-size: 1.1rem;
    font-weight: 500;
}

.detail-item.full-width {
    grid-column: 1 / -1; /* Spans full width for addresses etc. */
}

.icon-sm {
    font-size: 1rem;
    color: var(--primary);
}

.profile-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    justify-content: center;
}

.worker-name-link {
    font-weight: bold;
    color: var(--primary);
    text-decoration: none;
}
.worker-name-link:hover {
    text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .profile-details {
        grid-template-columns: 1fr; /* Stack columns on smaller screens */
    }
}
</style>

<script>
    // --- Edit Worker Profile Button (for worker_details_content.php) ---
    // This button will be on the individual worker profile page.
    document.querySelectorAll('.edit-worker-profile-btn').forEach(button => {
        button.addEventListener('click', function() {
            const workerId = this.dataset.workerId;
            alert((Simulated) Editing full profile for Worker ID: ${workerId}. This would typically open a comprehensive edit form.);
            // In a real application, you'd load a modal or navigate to a dedicated edit page.
        });
    });

    // --- Mark as Left Button (for worker_details_content.php) ---
    document.querySelectorAll('.mark-exit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const workerId = this.dataset.workerId;
            if (confirm(Are you sure you want to mark Worker ID: ${workerId} as 'Left Company'? This will update their status and prompt for an exit date.)) {
                // In a real application, you'd open a modal to confirm the exit date
                // and then send an AJAX request to update the worker's status and exit_date.
                // fetch('process_worker_action.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({
                //         type: 'mark_as_left',
                //         worker_id: workerId,
                //         exit_date: new Date().toISOString().slice(0,10) // Example: current date
                //     })
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if (data.success) {
                //         alert('Success: ' + data.message);
                //         window.location.reload(); // Reload to show updated status
                //     } else {
                //         alert('Error: ' + data.message);
                //     }
                // })
                // .catch(error => console.error('Error:', error));

                alert((Simulated) Worker ID: ${workerId} marked as 'Left Company'. Reloading page.);
                window.location.reload(); // Simulate update
            }
        });
    });
</script>