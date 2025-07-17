<?php
// payroll_content.php
// This file contains the content for the Payroll Management page.
// It is designed to be included in a main layout file (e.g., dashboard.php).

// In a real application, you would fetch payroll data from your database here.
// For demonstration, we'll use some sample data.
// This data would typically involve joining with your workers table
// to get worker names and other details.

// Sample Payroll Data (replace with database fetching)
// Added a unique transaction_id for easier targeting for payment actions
$payroll_transactions = [
    ['transaction_id' => 'PAY-001', 'worker_id' => 'WRK-101', 'name' => 'John Smith', 'pay_date' => '2024-06-30', 'amount' => 1850.00, 'status' => 'Paid', 'period' => 'June 2024'],
    ['transaction_id' => 'PAY-002', 'worker_id' => 'EMP-002', 'name' => 'Maria Garcia', 'pay_date' => '2024-06-30', 'amount' => 1620.50, 'status' => 'Paid', 'period' => 'June 2024'],
    ['transaction_id' => 'PAY-003', 'worker_id' => 'LID-A03', 'name' => 'Robert Johnson', 'pay_date' => '2024-06-30', 'amount' => 2100.00, 'status' => 'Paid', 'period' => 'June 2024'],
    ['transaction_id' => 'PAY-004', 'worker_id' => 'WRK-104', 'name' => 'Sarah Williams', 'pay_date' => '2024-06-30', 'amount' => 1700.00, 'status' => 'Paid', 'period' => 'June 2024'],
    ['transaction_id' => 'PAY-005', 'worker_id' => 'EMP-005', 'name' => 'Michael Brown', 'pay_date' => '2024-06-30', 'amount' => 1550.00, 'status' => 'Paid', 'period' => 'June 2024'],
    ['transaction_id' => 'PAY-006', 'worker_id' => 'WRK-106', 'name' => 'David Lee', 'pay_date' => '2024-06-30', 'amount' => 1900.00, 'status' => 'Pending', 'period' => 'June 2024'],
    ['transaction_id' => 'PAY-007', 'worker_id' => 'LID-B07', 'name' => 'Priya Sharma', 'pay_date' => '2024-06-30', 'amount' => 1780.00, 'status' => 'Pending', 'period' => 'June 2024'],
    ['transaction_id' => 'PAY-008', 'worker_id' => 'WRK-108', 'name' => 'Amit Kumar', 'pay_date' => '2024-06-30', 'amount' => 1600.00, 'status' => 'Pending', 'period' => 'June 2024'],
    ['transaction_id' => 'PAY-009', 'worker_id' => 'EMP-009', 'name' => 'Sneha Patil', 'pay_date' => '2024-06-30', 'amount' => 1750.00, 'status' => 'Failed', 'period' => 'June 2024'],
];

// Sample Payroll Metrics (replace with aggregated data from DB)
$total_monthly_payroll = array_sum(array_column($payroll_transactions, 'amount'));
$paid_transactions = count(array_filter($payroll_transactions, fn($t) => $t['status'] == 'Paid'));
$pending_transactions = count(array_filter($payroll_transactions, fn($t) => $t['status'] == 'Pending'));
$failed_transactions = count(array_filter($payroll_transactions, fn($t) => $t['status'] == 'Failed'));

// --- Search Logic ---
$filtered_transactions = $payroll_transactions; // Start with all transactions
$search_query = ''; // Initialize search_query

if (isset($_GET['search_payroll']) && $_GET['search_payroll'] != '') {
    $search_query = trim($_GET['search_payroll']);
    $filtered_transactions = array_filter($payroll_transactions, function($transaction) use ($search_query) {
        // Search by Worker ID or Name (case-insensitive)
        return stripos($transaction['worker_id'], $search_query) !== false ||
               stripos($transaction['name'], $search_query) !== false;
    });
}

// Get pending payments for the "Process All Pending" view
$pending_payments_list = array_filter($payroll_transactions, fn($t) => $t['status'] == 'Pending');
?>

<h1 class="page-title">
    <i class="fas fa-money-bill-wave"></i>
    Payroll Management
</h1>

---

## Payroll Overview

<div class="stats-container" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-icon payroll">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3>$<?php echo number_format($total_monthly_payroll, 2); ?></h3>
            <p>Total Monthly Payroll</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon attendance" style="background: rgba(40, 167, 69, 0.2); color: #28a745;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $paid_transactions; ?></h3>
            <p>Transactions Paid</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon projects" style="background: rgba(255, 193, 7, 0.2); color: #ffc107;">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $pending_transactions; ?></h3>
            <p>Transactions Pending</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon labour" style="background: rgba(220, 53, 69, 0.2); color: #dc3545;">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $failed_transactions; ?></h3>
            <p>Transactions Failed</p>
        </div>
    </div>
</div>

---

## Payroll Actions & Search

<div class="dashboard-card" style="margin-bottom: 30px;">
    <div class="card-header" style="flex-wrap: wrap; gap: 15px;">
        <h3 style="margin-bottom: 0;">Payroll Operations</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if (!empty($pending_payments_list)): // Only show if there are pending payments ?>
                <button class="btn" id="processAllPendingBtn" style="min-width: 220px; background-color: var(--success);">
                    <i class="fas fa-money-check-alt"></i> Process All Pending (<?php echo count($pending_payments_list); ?>)
                </button>
            <?php else: ?>
                <button class="btn" style="min-width: 220px; background-color: #6c757d; cursor: not-allowed;" disabled>
                    <i class="fas fa-money-check-alt"></i> No Pending Payments
                </button>
            <?php endif; ?>
            <button class="btn" id="processPayrollBtn" style="min-width: 180px;">
                <i class="fas fa-plus-circle"></i> Add New Payroll
            </button>
        </div>
    </div>
    <form method="GET" action="" style="display: flex; gap: 15px; align-items: flex-end; margin-top: 20px; flex-wrap: wrap;">
        <input type="hidden" name="page" value="payroll"> <div class="form-group" style="flex-grow: 1; min-width: 250px; margin-bottom: 0;">
            <label for="searchPayroll" style="font-size: 0.9rem; color: #666;">Search by Labour ID or Name</label>
            <input type="text" id="searchPayroll" name="search_payroll" class="form-control"
                   placeholder="e.g., WRK-101 or John Smith"
                   value="<?php echo htmlspecialchars($search_query); ?>">
        </div>
        <button type="submit" class="btn" style="height: 44px;">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if ($search_query != ''): ?>
            <a href="?page=payroll" class="btn" style="background-color: #6c757d; height: 44px;">
                <i class="fas fa-redo"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

---

## Process New Payroll Entry

<div class="form-container" id="processPayrollForm" style="display: none; margin-top: 30px; border-left: 5px solid var(--primary);">
    <h2 style="margin-bottom: 25px; color: var(--primary);">
        <i class="fas fa-cash-register"></i> New Payroll Entry
    </h2>
    <form method="POST">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="payrollWorkerSelect">Select Worker</label>
                    <select id="payrollWorkerSelect" class="form-control" required>
                        <option value="">-- Select Worker --</option>
                        <option value="WRK-101">#WRK-101 - John Smith</option>
                        <option value="EMP-002">#EMP-002 - Maria Garcia</option>
                        <option value="LID-A03">#LID-A03 - Robert Johnson</option>
                        <option value="WRK-104">#WRK-104 - Sarah Williams</option>
                        <option value="EMP-005">#EMP-005 - Michael Brown</option>
                    </select>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="payDate">Payment Date</label>
                    <input type="date" id="payDate" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="payrollAmount">Amount ($)</label>
                    <input type="number" step="0.01" id="payrollAmount" class="form-control" placeholder="e.g., 1850.00" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="payPeriod">Pay Period</label>
                    <input type="text" id="payPeriod" class="form-control" placeholder="e.g., June 2024" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="payrollStatus">Status</label>
            <select id="payrollStatus" class="form-control" required>
                <option value="Paid">Paid</option>
                <option value="Pending">Pending</option>
                <option value="Failed">Failed</option>
                <option value="Processing">Processing</option>
            </select>
        </div>

        <div class="form-group">
            <label for="payrollNotes">Notes (Optional)</label>
            <textarea id="payrollNotes" class="form-control" rows="3" placeholder="Add any relevant notes about this payroll entry..."></textarea>
        </div>

        <button type="submit" class="btn btn-block">
            <i class="fas fa-save"></i> Record Payroll Entry
        </button>
    </form>
</div>

---

## Recent Payroll Transactions

<div class="dashboard-card" style="margin-top: 30px;">
    <div class="card-header">
        <h3>All Payroll Transactions <?php echo ($search_query != '') ? 'for: "' . htmlspecialchars($search_query) . '"' : ''; ?></h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Labour ID</th>
                    <th>Worker Name</th>
                    <th>Pay Date</th>
                    <th>Pay Period</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filtered_transactions)): ?>
                    <?php foreach ($filtered_transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['worker_id']); ?></td>
                            <td><?php echo $transaction['name']; ?></td>
                            <td><?php echo $transaction['pay_date']; ?></td>
                            <td><?php echo $transaction['period']; ?></td>
                            <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                if ($transaction['status'] == 'Paid') $status_class = 'active'; // Using active for success
                                elseif ($transaction['status'] == 'Pending' || $transaction['status'] == 'Processing') $status_class = 'pending';
                                elseif ($transaction['status'] == 'Failed') $status_class = 'leave'; // Using leave for danger
                                ?>
                                <span class="status <?php echo $status_class; ?>"><?php echo $transaction['status']; ?></span>
                            </td>
                            <td>
                                <?php if ($transaction['status'] == 'Pending'): ?>
                                    <button class="btn pay-now-btn"
                                            data-transaction-id="<?php echo htmlspecialchars($transaction['transaction_id']); ?>"
                                            style="padding: 5px 10px; font-size: 0.8rem; background-color: var(--success);">
                                        <i class="fas fa-money-bill-wave"></i> Pay Now
                                    </button>
                                <?php else: ?>
                                    <span style="color: #6c757d; font-size: 0.8rem;">Paid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">No payroll transactions found <?php echo ($search_query != '') ? ' matching "' . htmlspecialchars($search_query) . '"' : ''; ?>.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="processAllPendingModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i> Confirm Pending Payments</h2>
            <button class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <p>The following payments are currently *Pending*. Do you wish to process all of them?</p>
            <div class="table-container" style="max-height: 300px; overflow-y: auto; margin-top: 20px; border: 1px solid #eee; border-radius: 5px;">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Labour ID</th>
                            <th>Worker Name</th>
                            <th>Amount</th>
                            <th>Pay Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pending_payments_list)): ?>
                            <?php foreach ($pending_payments_list as $transaction): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['worker_id']); ?></td>
                                    <td><?php echo $transaction['name']; ?></td>
                                    <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                                    <td><?php echo $transaction['period']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 15px;">No pending payments to display.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p style="margin-top: 20px; font-weight: bold;">Total Pending Amount: $<?php
                echo number_format(array_sum(array_column($pending_payments_list, 'amount')), 2);
            ?></p>
        </div>
        <div class="modal-footer">
            <button class="btn cancel-all-btn" style="background-color: #6c757d;">Cancel</button>
            <button class="btn confirm-all-btn" style="background-color: var(--success);">
                <i class="fas fa-check-circle"></i> Confirm Pay All
            </button>
        </div>
    </div>
</div>

<style>
/* Basic Modal Overlay Styles - Add this to your main CSS file (style.css) if you don't have it */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-width: 700px;
    position: relative;
    max-height: 90vh; /* Limit height for scrollable content */
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: var(--primary);
}

.close-modal-btn {
    background: none;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    color: #aaa;
    line-height: 1;
}

.close-modal-btn:hover {
    color: #555;
}

.modal-body {
    flex-grow: 1;
    overflow-y: auto; /* Make body scrollable if content overflows */
    padding-right: 10px; /* For scrollbar */
}

.modal-body p {
    margin-bottom: 15px;
}

.modal-footer {
    border-top: 1px solid #eee;
    padding-top: 15px;
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>

<script>
    // JavaScript to show/hide the "Process New Payroll" form
    const processPayrollBtn = document.getElementById('processPayrollBtn');
    const processPayrollForm = document.getElementById('processPayrollForm');

    if (processPayrollBtn && processPayrollForm) {
        processPayrollBtn.addEventListener('click', function() {
            // Toggle visibility
            if (processPayrollForm.style.display === 'none' || processPayrollForm.style.display === '') {
                processPayrollForm.style.display = 'block';
                // Optional: Scroll to the form
                processPayrollForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                processPayrollForm.style.display = 'none';
            }
        });
    }

    // Auto-fill current date for the payDate input
    const payDateInput = document.getElementById('payDate');
    if (payDateInput) {
        const today = new Date();
        const year = today.getFullYear();
        let month = today.getMonth() + 1;
        let day = today.getDate();

        if (day < 10) day = '0' + day;
        if (month < 10) month = '0' + month;

        payDateInput.value = ${year}-${month}-${day};
    }

    // Auto-fill current month and year for Pay Period (optional)
    const payPeriodInput = document.getElementById('payPeriod');
    if (payPeriodInput) {
        const today = new Date();
        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        const currentMonthName = monthNames[today.getMonth()];
        const currentYear = today.getFullYear();
        payPeriodInput.value = ${currentMonthName} ${currentYear};
    }

    // --- Payment Functionality (Frontend Simulation) ---

    // Function to handle individual "Pay Now" button click
    document.querySelectorAll('.pay-now-btn').forEach(button => {
        button.addEventListener('click', function() {
            const transactionId = this.dataset.transactionId;
            if (confirm(Are you sure you want to process payment for Transaction ID: ${transactionId}?)) {
                // In a real application, you would send an AJAX request here
                // Example:
                // fetch('process_payment.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ transaction_id: transactionId, type: 'single' })
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if (data.success) {
                //         alert(Payment for ${transactionId} processed successfully!);
                //         window.location.reload(); // Reload to reflect changes
                //     } else {
                //         alert(`Failed to process payment for ${transactionId}: ` + data.message);
                //     }
                // })
                // .catch(error => console.error('Error:', error));

                alert((Simulated) Payment processed for ${transactionId}. Reloading page.);
                // For demonstration, simply reload the page.
                // In a real app, update the table row directly or reload after successful AJAX.
                window.location.reload();
            }
        });
    });

    // --- Process All Pending Payments Modal Logic ---
    const processAllPendingBtn = document.getElementById('processAllPendingBtn');
    const processAllPendingModal = document.getElementById('processAllPendingModal');
    const closeModalBtn = processAllPendingModal.querySelector('.close-modal-btn');
    const cancelAllBtn = processAllPendingModal.querySelector('.cancel-all-btn');
    const confirmAllBtn = processAllPendingModal.querySelector('.confirm-all-btn');

    if (processAllPendingBtn) { // Ensure the button exists before adding listener
        processAllPendingBtn.addEventListener('click', function() {
            processAllPendingModal.style.display = 'flex'; // Show the modal
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            processAllPendingModal.style.display = 'none'; // Hide the modal
        });
    }

    if (cancelAllBtn) {
        cancelAllBtn.addEventListener('click', function() {
            processAllPendingModal.style.display = 'none'; // Hide the modal
        });
    }

    if (confirmAllBtn) {
        confirmAllBtn.addEventListener('click', function() {
            if (confirm('Are you absolutely sure you want to process ALL listed pending payments?')) {
                // In a real application, send an AJAX request here to process all pending payments
                // Example:
                // fetch('process_payment.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ type: 'all_pending' })
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if (data.success) {
                //         alert(All pending payments processed successfully!);
                //         window.location.reload(); // Reload to reflect changes
                //     } else {
                //         alert(`Failed to process all pending payments: ` + data.message);
                //     }
                // })
                // .catch(error => console.error('Error:', error));

                alert('(Simulated) All listed pending payments processed. Reloading page.');
                window.location.reload();
            }
        });
    }

    // Close modal if user clicks outside of it
    window.addEventListener('click', function(event) {
        if (event.target == processAllPendingModal) {
            processAllPendingModal.style.display = 'none';
        }
    });
</script>