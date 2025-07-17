<?php
// attendance_content.php
// This file contains the content for the Attendance Management page.
// It is designed to be included in a main layout file (e.g., dashboard.php).

// In a real application, you would fetch attendance data from your database here.
// For demonstration, we'll use some sample data with alphanumeric IDs.
// This $attendance_records array should ideally be populated from your database
// in your main dashboard.php file before this content file is included,
// or fetched directly here if this page handles its own data retrieval.

// Sample attendance data (replace with database fetching)
$attendance_records = [
    ['id' => 1, 'worker_id' => 'WRK-101', 'name' => 'John Smith', 'date' => '2025-07-01', 'status' => 'Present', 'clock_in' => '08:00 AM', 'clock_out' => '05:00 PM', 'department' => 'Construction'],
    ['id' => 2, 'worker_id' => 'EMP-002', 'name' => 'Maria Garcia', 'date' => '2025-07-01', 'status' => 'Present', 'clock_in' => '08:05 AM', 'clock_out' => '05:10 PM', 'department' => 'Electrical'],
    ['id' => 3, 'worker_id' => 'LID-A03', 'name' => 'Robert Johnson', 'date' => '2025-07-01', 'status' => 'Leave', 'clock_in' => '-', 'clock_out' => '-', 'department' => 'Plumbing'],
    ['id' => 4, 'worker_id' => 'WRK-104', 'name' => 'Sarah Williams', 'date' => '2025-07-01', 'status' => 'Late', 'clock_in' => '08:30 AM', 'clock_out' => '05:00 PM', 'department' => 'HVAC'],
    ['id' => 5, 'worker_id' => 'EMP-005', 'name' => 'Michael Brown', 'date' => '2025-07-01', 'status' => 'Present', 'clock_in' => '07:55 AM', 'clock_out' => '04:58 PM', 'department' => 'Construction'],
    ['id' => 6, 'worker_id' => 'WRK-101', 'name' => 'John Smith', 'date' => '2025-07-02', 'status' => 'Present', 'clock_in' => '08:02 AM', 'clock_out' => '05:02 PM', 'department' => 'Construction'],
    ['id' => 7, 'worker_id' => 'EMP-002', 'name' => 'Maria Garcia', 'date' => '2025-07-02', 'status' => 'Absent', 'clock_in' => '-', 'clock_out' => '-', 'department' => 'Electrical'],
    ['id' => 8, 'worker_id' => 'LID-A03', 'name' => 'Robert Johnson', 'date' => '2025-07-02', 'status' => 'Present', 'clock_in' => '07:59 AM', 'clock_out' => '05:01 PM', 'department' => 'Plumbing'],
];

// --- Search Logic ---
$filtered_records = $attendance_records; // Start with all records
$search_id = ''; // Initialize search_id
if (isset($_GET['search_worker_id']) && $_GET['search_worker_id'] != '') {
    // Trim and sanitize the search input
    $search_id = trim($_GET['search_worker_id']);
    // Filter records: use stripos for case-insensitive partial match
    $filtered_records = array_filter($attendance_records, function($record) use ($search_id) {
        // Check if the search ID is found anywhere in the worker_id string (case-insensitive)
        return stripos($record['worker_id'], $search_id) !== false;
    });
}
?>

---

<h1 class="page-title">
    <i class="fas fa-calendar-check"></i>
    Attendance Management
</h1>

---

### Search and Add Panel

<div class="dashboard-card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3>Search Attendance</h3>
        <button class="btn" id="addAttendanceBtn">Add New Record</button>
    </div>
    <form method="GET" action="" style="display: flex; gap: 15px; align-items: flex-end;">
        <input type="hidden" name="page" value="attendance"> <div class="form-group" style="flex-grow: 1; margin-bottom: 0;">
            <label for="searchWorkerId">Labour ID</label>
            <input type="text" id="searchWorkerId" name="search_worker_id" class="form-control" placeholder="Enter Labour ID (e.g., WRK-101 or EMP-002)" value="<?php echo htmlspecialchars($search_id); ?>">
        </div>
        <button type="submit" class="btn">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if ($search_id != ''): // Only show clear button if a search was performed ?>
            <a href="?page=attendance" class="btn" style="background-color: #6c757d;">
                Clear Search
            </a>
        <?php endif; ?>
    </form>
</div>

---

### Add New Attendance Record Form

<div class="form-container" id="addAttendanceForm" style="display: none; margin-top: 30px;">
    <h2 style="margin-bottom: 20px;">Add New Attendance Record</h2>
    <form method="POST">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="workerSelect">Select Worker</label>
                    <select id="workerSelect" class="form-control" required>
                        <option value="">-- Select Worker --</option>
                        <option value="WRK-101">#WRK-101 - John Smith (Construction)</option>
                        <option value="EMP-002">#EMP-002 - Maria Garcia (Electrical)</option>
                        <option value="LID-A03">#LID-A03 - Robert Johnson (Plumbing)</option>
                        <option value="WRK-104">#WRK-104 - Sarah Williams (HVAC)</option>
                        <option value="EMP-005">#EMP-005 - Michael Brown (Construction)</option>
                    </select>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="attendanceDate">Date</label>
                    <input type="date" id="attendanceDate" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="clockInTime">Clock In Time</label>
                    <input type="time" id="clockInTime" class="form-control">
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="clockOutTime">Clock Out Time</label>
                    <input type="time" id="clockOutTime" class="form-control">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="attendanceStatus">Status</label>
            <select id="attendanceStatus" class="form-control" required>
                <option value="Present">Present</option>
                <option value="Late">Late Arrival</option>
                <option value="Absent">Absent</option>
                <option value="Leave">On Leave</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Notes (Optional)</label>
            <textarea id="notes" class="form-control" rows="3" placeholder="Add any relevant notes about this attendance record..."></textarea>
        </div>

        <button type="submit" class="btn btn-block">Save Attendance Record</button>
    </form>
</div>

---

### Current Attendance Records

<div class="dashboard-card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>Attendance Overview <?php echo ($search_id != '') ? 'for Labour ID: ' . htmlspecialchars($search_id) : ''; ?></h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Labour ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Hours</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filtered_records)): ?>
                    <?php foreach ($filtered_records as $record): ?>
                        <tr>
                            <td><?php echo $record['date']; ?></td>
                            <td><?php echo htmlspecialchars($record['worker_id']); ?></td>
                            <td><?php echo $record['name']; ?></td>
                            <td><?php echo $record['department']; ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                if ($record['status'] == 'Present') $status_class = 'active';
                                elseif ($record['status'] == 'Late') $status_class = 'pending';
                                elseif ($record['status'] == 'Leave' || $record['status'] == 'Absent') $status_class = 'leave';
                                ?>
                                <span class="status <?php echo $status_class; ?>"><?php echo $record['status']; ?></span>
                            </td>
                            <td><?php echo $record['clock_in']; ?></td>
                            <td><?php echo $record['clock_out']; ?></td>
                            <td>
                                <?php
                                // Simple calculation for demonstration; actual calculation should handle dates/times properly
                                if ($record['clock_in'] != '-' && $record['clock_out'] != '-') {
                                    $in_time = strtotime($record['clock_in']);
                                    $out_time = strtotime($record['clock_out']);
                                    $diff_seconds = $out_time - $in_time;
                                    $hours = round($diff_seconds / 3600, 1);
                                    echo $hours . ' hrs';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">No attendance records found <?php echo ($search_id != '') ? ' for Labour ID: "' . htmlspecialchars($search_id) . '"' : ''; ?>.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // JavaScript to show/hide the "Add New Attendance Record" form
    const addAttendanceBtn = document.getElementById('addAttendanceBtn');
    const addAttendanceForm = document.getElementById('addAttendanceForm');

    if (addAttendanceBtn && addAttendanceForm) {
        addAttendanceBtn.addEventListener('click', function() {
            if (addAttendanceForm.style.display === 'none' || addAttendanceForm.style.display === '') {
                addAttendanceForm.style.display = 'block';
            } else {
                addAttendanceForm.style.display = 'none';
            }
        });
    }

    // Optional: Auto-fill current date for the attendanceDate input
    const attendanceDateInput = document.getElementById('attendanceDate');
    if (attendanceDateInput) {
        const today = new Date();
        const yyyy = today.getFullYear();
        let mm = today.getMonth() + 1; // Months start at 0!
        let dd = today.getDate();

        if (dd < 10) dd = '0' + dd;
        if (mm < 10) mm = '0' + mm;

        attendanceDateInput.value = ${yyyy}-${mm}-${dd};
    }
</script>