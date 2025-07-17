<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: "Inter", sans-serif;
            background-color: #f8f9fa;
        }
        .container-fluid {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .table thead th {
            background-color: #343a40;
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: #e2e6ea;
        }
        .btn {
            border-radius: 0.5rem;
        }
        .form-control, .form-select {
            border-radius: 0.5rem;
        }
        .input-group-text {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }
        .modal-content {
            border-radius: 1rem;
        }
        .badge {
            padding: 0.5em 0.75em;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .alert-fixed {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            min-width: 300px;
            text-align: center;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <h1 class="mb-4 text-center">Shift Management</h1>

    <!-- Page-level message display -->
    <div id="pageMessage" class="alert alert-fixed d-none" role="alert"></div>

    <div class="row mb-4 align-items-end">
        <div class="col-md-3 mb-3 mb-md-0">
            <label for="filterDate" class="form-label visually-hidden">Filter by Date</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                <input type="date" id="filterDate" class="form-control rounded-end-md" placeholder="Filter by Date">
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <label for="filterProject" class="form-label visually-hidden">Filter by Project</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-building"></i></span>
                <select id="filterProject" class="form-select rounded-end-md">
                    <option value="">All Projects</option>
                    <!-- Options will be populated by JS -->
                </select>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <label for="filterWorker" class="form-label visually-hidden">Filter by Worker</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-users"></i></span>
                <select id="filterWorker" class="form-select rounded-end-md">
                    <option value="">All Workers</option>
                    <!-- Options will be populated by JS -->
                </select>
            </div>
        </div>
        <div class="col-md-3 text-end">
            <button class="btn btn-primary w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#shiftModal">
                <i class="fas fa-plus-circle me-2"></i>Add New Shift
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Worker Name / ID</th>
                    <th>Project / Site</th>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Break (min)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="shiftTableBody">
                <!-- Shift rows will be populated by JS -->
            </tbody>
        </table>
    </div>

    <div id="noShiftsMessage" class="alert alert-info text-center d-none mt-4" role="alert">
        No shifts found matching your criteria.
    </div>

    <!-- Add/Edit Shift Modal -->
    <div class="modal fade" id="shiftModal" tabindex="-1" aria-labelledby="shiftModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shiftModalLabel">Add New Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="shiftForm">
                        <input type="hidden" id="shiftId" name="id">
                        <div class="mb-3">
                            <label for="workerId" class="form-label">Worker Name / ID</label>
                            <select class="form-select" id="workerId" name="worker_id" required>
                                <!-- Options will be populated by JS -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="projectId" class="form-label">Project / Site Name</label>
                            <select class="form-select" id="projectId" name="project_id" required>
                                <!-- Options will be populated by JS -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="shiftDate" class="form-label">Shift Date</label>
                            <input type="date" class="form-control" id="shiftDate" name="shift_date" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="startTime" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="startTime" name="start_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="endTime" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="endTime" name="end_time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="breakTime" class="form-label">Break Time (minutes) <small class="text-muted">(Optional)</small></label>
                            <input type="number" class="form-control" id="breakTime" name="break_time" min="0" placeholder="e.g., 30">
                        </div>
                        <div class="mb-3">
                            <label for="shiftStatus" class="form-label">Shift Status</label>
                            <select class="form-select" id="shiftStatus" name="shift_status" required>
                                <option value="Assigned">Assigned</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div id="formMessage" class="alert d-none mt-3" role="alert"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="saveShiftBtn">Save Shift</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this shift? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Get references to DOM elements
    const shiftTableBody = document.getElementById('shiftTableBody');
    const filterDate = document.getElementById('filterDate');
    const filterProject = document.getElementById('filterProject');
    const filterWorker = document.getElementById('filterWorker');
    const shiftModal = new bootstrap.Modal(document.getElementById('shiftModal'));
    const shiftModalLabel = document.getElementById('shiftModalLabel');
    const shiftForm = document.getElementById('shiftForm');
    const formMessage = document.getElementById('formMessage');
    const saveShiftBtn = document.getElementById('saveShiftBtn');
    const noShiftsMessage = document.getElementById('noShiftsMessage');
    const pageMessage = document.getElementById('pageMessage'); // New element for page-level messages

    // References for the new delete confirmation modal
    const confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    let shiftIdToDelete = null; // Variable to store the ID of the shift to be deleted

    // --- MOCK API DATA STORE (FOR DEMONSTRATION PURPOSES) ---
    // In a real application, this data would come from a database via a server-side API (e.g., PHP).
    let mockShifts = [
        { id: 1, worker_id: 101, project_id: 1, shift_date: '2025-07-01', start_time: '08:00', end_time: '16:00', break_time: 30, shift_status: 'Completed' },
        { id: 2, worker_id: 102, project_id: 2, shift_date: '2025-07-01', start_time: '09:00', end_time: '17:00', break_time: 60, shift_status: 'In Progress' },
        { id: 3, worker_id: 101, project_id: 3, shift_date: '2025-07-02', start_time: '10:00', end_time: '18:00', break_time: null, shift_status: 'Assigned' },
        { id: 4, worker_id: 103, project_id: 1, shift_date: '2025-07-02', start_time: '07:00', end_time: '15:00', break_time: 45, shift_status: 'Cancelled' },
        { id: 5, worker_id: 102, project_id: 3, shift_date: '2025-07-03', start_time: '11:00', end_time: '19:00', break_time: 30, shift_status: 'Assigned' },
    ];

    let mockWorkers = [
        { id: 101, worker_name: 'Alice Smith' },
        { id: 102, worker_name: 'Bob Johnson' },
        { id: 103, worker_name: 'Charlie Brown' },
    ];

    let mockProjects = [
        { id: 1, project_name: 'Downtown Construction' },
        { id: 2, project_name: 'Park Renovation' },
        { id: 3, project_name: 'Bridge Repair' },
    ];

    let nextShiftId = Math.max(...mockShifts.map(s => s.id)) + 1;
    // --- END MOCK API DATA STORE ---

    /**
     * Displays a message at the top of the page (e.g., for delete operations).
     * The message fades out after a few seconds.
     * @param {string} message - The message to display.
     * @param {string} type - The Bootstrap alert type (e.g., 'success', 'danger', 'info').
     */
    function showPageMessage(message, type) {
        pageMessage.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
        pageMessage.classList.add(`alert-${type}`);
        pageMessage.textContent = message;
        setTimeout(() => {
            pageMessage.classList.add('d-none');
        }, 5000); // Hide after 5 seconds
    }

    /**
     * Displays messages within the modal form.
     * @param {string} message - The message to display.
     * @param {string} type - The Bootstrap alert type (e.g., 'success', 'danger').
     */
    function showFormMessage(message, type) {
        formMessage.classList.remove('d-none', 'alert-success', 'alert-danger');
        formMessage.classList.add(`alert-${type}`);
        formMessage.textContent = message;
    }

    /**
     * Fetches and displays shifts, workers, and projects from the (mock) API.
     * This function also handles filtering based on selected criteria.
     */
    async function fetchShifts() {
        const date = filterDate.value;
        const projectId = filterProject.value;
        const workerId = filterWorker.value;

        // --- MOCK API CALL SIMULATION ---
        // In a real app, this would be a fetch() call to your backend.
        // Here, we filter the local mock data.
        let filteredShifts = mockShifts.map(shift => {
            const worker = mockWorkers.find(w => w.id === shift.worker_id);
            const project = mockProjects.find(p => p.id === shift.project_id);
            return {
                ...shift,
                worker_name: worker ? worker.worker_name : 'Unknown Worker',
                project_name: project ? project.project_name : 'Unknown Project'
            };
        }).filter(shift => {
            let matches = true;
            if (date && shift.shift_date !== date) {
                matches = false;
            }
            if (projectId && shift.project_id !== parseInt(projectId)) {
                matches = false;
            }
            if (workerId && shift.worker_id !== parseInt(workerId)) {
                matches = false;
            }
            return matches;
        });

        // Simulate API delay
        await new Promise(resolve => setTimeout(resolve, 300));

        const data = {
            success: true,
            shifts: filteredShifts,
            workers: mockWorkers,
            projects: mockProjects
        };
        // --- END MOCK API CALL SIMULATION ---

        if (data.success) {
            shiftTableBody.innerHTML = ''; // Clear existing table rows
            if (data.shifts && data.shifts.length > 0) {
                noShiftsMessage.classList.add('d-none'); // Hide 'no shifts' message
                data.shifts.forEach(shift => {
                    const row = `
                        <tr>
                            <td>${escapeHTML(shift.id)}</td>
                            <td>${escapeHTML(shift.worker_name)} ${shift.worker_id ? `/ ${escapeHTML(shift.worker_id)}` : ''}</td>
                            <td>${escapeHTML(shift.project_name)}</td>
                            <td>${escapeHTML(shift.shift_date)}</td>
                            <td>${escapeHTML(shift.start_time ? shift.start_time.substring(0, 5) : 'N/A')}</td>
                            <td>${escapeHTML(shift.end_time ? shift.end_time.substring(0, 5) : 'N/A')}</td>
                            <td>${shift.break_time !== null ? escapeHTML(shift.break_time) : 'N/A'}</td>
                            <td><span class="badge ${getShiftStatusBadge(shift.shift_status)}">${escapeHTML(shift.shift_status)}</span></td>
                            <td>
                                <button class="btn btn-sm btn-info me-2 edit-btn"
                                    data-id="${shift.id}"
                                    data-worker-id="${shift.worker_id}"
                                    data-project-id="${shift.project_id}"
                                    data-shift-date="${shift.shift_date}"
                                    data-start-time="${shift.start_time}"
                                    data-end-time="${shift.end_time}"
                                    data-break-time="${shift.break_time !== null ? shift.break_time : ''}"
                                    data-shift-status="${shift.shift_status}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${shift.id}">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </td>
                        </tr>
                    `;
                    shiftTableBody.innerHTML += row;
                });
            } else {
                noShiftsMessage.classList.remove('d-none'); // Show 'no shifts' message
            }

            // Populate workers and projects for filter and form dropdowns only if not already cached
            // The mock data is always available, so we populate these
            populateDropdown('workerId', mockWorkers, 'worker_name');
            populateDropdown('filterWorker', mockWorkers, 'worker_name', 'All Workers');
            populateDropdown('projectId', mockProjects, 'project_name');
            populateDropdown('filterProject', mockProjects, 'project_name', 'All Projects');

            // Re-attach event listeners to newly added buttons using delegation
            addEventListenersToButtons();
        } else {
            showPageMessage(data.message || 'Error fetching shifts.', 'danger');
            console.error('Error fetching shifts:', data.message);
        }
    }

    /**
     * Helper function to escape HTML for display, preventing XSS vulnerabilities.
     * @param {string|number} str - The string or number to escape.
     * @returns {string} The escaped HTML string.
     */
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /**
     * Helper function to get the appropriate Bootstrap badge class based on shift status.
     * @param {string} status - The shift status (e.g., 'Assigned', 'Completed').
     * @returns {string} The Bootstrap badge class.
     */
    function getShiftStatusBadge(status) {
        switch (status) {
            case 'Assigned': return 'bg-primary';
            case 'In Progress': return 'bg-info text-dark'; // Use text-dark for better contrast on light info background
            case 'Completed': return 'bg-success';
            case 'Cancelled': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    /**
     * Helper function to populate a select dropdown element.
     * @param {string} selectId - The ID of the select element.
     * @param {Array<Object>} data - An array of objects to populate the dropdown.
     * @param {string} textKey - The key in each object whose value should be used as the option text.
     * @param {string} [defaultOptionText=null] - Optional text for a default option (e.g., "All Workers").
     */
    function populateDropdown(selectId, data, textKey, defaultOptionText = null) {
        const selectElement = document.getElementById(selectId);
        if (!selectElement) return; // Guard against element not found

        selectElement.innerHTML = ''; // Clear existing options
        if (defaultOptionText) {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = defaultOptionText;
            selectElement.appendChild(defaultOption);
        }
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item[textKey];
            selectElement.appendChild(option);
        });
    }

    // Add/Edit Shift Form Submission
    shiftForm.addEventListener('submit', async function(e) {
        e.preventDefault(); // Prevent default form submission

        const shiftId = document.getElementById('shiftId').value;
        const workerId = document.getElementById('workerId').value;
        const projectId = document.getElementById('projectId').value;
        const shiftDate = document.getElementById('shiftDate').value;
        const startTime = document.getElementById('startTime').value;
        const endTime = document.getElementById('endTime').value;
        const breakTime = document.getElementById('breakTime').value; // Can be empty string
        const shiftStatus = document.getElementById('shiftStatus').value;

        // Basic client-side validation
        if (!workerId || !projectId || !shiftDate || !startTime || !endTime) {
            showFormMessage('Please fill all required fields.', 'danger');
            return;
        }

        const shiftData = {
            worker_id: parseInt(workerId),
            project_id: parseInt(projectId),
            shift_date: shiftDate,
            start_time: startTime,
            end_time: endTime,
            // Convert empty string for break_time to null, otherwise to int
            break_time: breakTime === '' ? null : parseInt(breakTime),
            shift_status: shiftStatus
        };

        // --- MOCK API CALL SIMULATION ---
        // In a real app, this would be a fetch() call to your backend.
        saveShiftBtn.disabled = true; // Disable button to prevent double submission
        await new Promise(resolve => setTimeout(resolve, 500)); // Simulate API delay

        let success = false;
        let message = '';

        if (shiftId) { // It's an update operation
            const index = mockShifts.findIndex(s => s.id === parseInt(shiftId));
            if (index !== -1) {
                mockShifts[index] = { ...mockShifts[index], ...shiftData, id: parseInt(shiftId) }; // Ensure ID is kept
                success = true;
                message = 'Shift updated successfully!';
            } else {
                message = 'Shift not found for update.';
            }
        } else { // It's an add operation
            shiftData.id = nextShiftId++; // Assign a new mock ID
            mockShifts.push(shiftData);
            success = true;
            message = 'Shift added successfully!';
        }
        // --- END MOCK API CALL SIMULATION ---

        if (success) {
            showFormMessage(message, 'success');
            shiftForm.reset(); // Clear form fields
            shiftModal.hide(); // Hide the modal
            fetchShifts(); // Refresh the shifts table
        } else {
            showFormMessage(message, 'danger');
        }
        saveShiftBtn.disabled = false; // Re-enable button
    });

    // Event listeners for filter changes
    filterDate.addEventListener('change', fetchShifts);
    filterProject.addEventListener('change', fetchShifts);
    filterWorker.addEventListener('change', fetchShifts);

    /**
     * Adds event listeners to the table body using event delegation for Edit and Delete buttons.
     * This function is called after the table is populated to ensure buttons are interactive.
     */
    function addEventListenersToButtons() {
        // Remove previous listener to avoid duplicates if called multiple times
        shiftTableBody.removeEventListener('click', handleTableButtonClick);
        // Add a single listener to the table body
        shiftTableBody.addEventListener('click', handleTableButtonClick);
    }

    /**
     * Handles clicks on Edit and Delete buttons within the shift table.
     * @param {Event} event - The click event object.
     */
    async function handleTableButtonClick(event) {
        // Handle Edit button click
        if (event.target.closest('.edit-btn')) {
            const button = event.target.closest('.edit-btn');
            shiftModalLabel.textContent = 'Edit Shift';
            document.getElementById('shiftId').value = button.dataset.id;
            document.getElementById('workerId').value = button.dataset.workerId;
            document.getElementById('projectId').value = button.dataset.projectId;
            document.getElementById('shiftDate').value = button.dataset.shiftDate;
            document.getElementById('startTime').value = button.dataset.startTime ? button.dataset.startTime.substring(0, 5) : ''; // Format for time input
            document.getElementById('endTime').value = button.dataset.endTime ? button.dataset.endTime.substring(0, 5) : '';     // Format for time input
            document.getElementById('breakTime').value = button.dataset.breakTime; // Can be empty string
            document.getElementById('shiftStatus').value = button.dataset.shiftStatus;

            formMessage.classList.add('d-none'); // Hide any previous messages
            shiftModal.show();
        }

        // Handle Delete button click
        if (event.target.closest('.delete-btn')) {
            const button = event.target.closest('.delete-btn');
            shiftIdToDelete = button.dataset.id; // Store the ID
            confirmDeleteModal.show(); // Show the confirmation modal
        }
    }

    // Event listener for the confirm delete button in the confirmation modal
    confirmDeleteBtn.addEventListener('click', async function() {
        if (shiftIdToDelete) {
            // --- MOCK API CALL SIMULATION ---
            // In a real app, this would be a fetch() call to your backend.
            await new Promise(resolve => setTimeout(resolve, 300)); // Simulate API delay

            const initialLength = mockShifts.length;
            mockShifts = mockShifts.filter(s => s.id !== parseInt(shiftIdToDelete));
            let success = mockShifts.length < initialLength;
            let message = success ? 'Shift deleted successfully!' : 'Shift not found for deletion.';
            // --- END MOCK API CALL SIMULATION ---

            if (success) {
                showPageMessage(message, 'success');
                fetchShifts(); // Refresh table
            } else {
                showPageMessage(message, 'danger');
            }
            confirmDeleteModal.hide(); // Hide the confirmation modal
            shiftIdToDelete = null; // Clear the stored ID
        }
    });

    // Reset form when the Add/Edit modal is hidden
    shiftModal._element.addEventListener('hidden.bs.modal', function () {
        shiftForm.reset();
        document.getElementById('shiftId').value = ''; // Clear hidden ID
        shiftModalLabel.textContent = 'Add New Shift';
        formMessage.classList.add('d-none'); // Hide message
    });

    // Initial fetch of shifts when the page loads
    document.addEventListener('DOMContentLoaded', fetchShifts);
</script>

</body>
</html>
