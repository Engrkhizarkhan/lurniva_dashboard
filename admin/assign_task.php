<?php require_once 'assets/php/header.php'; 
include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$admin_id = $_SESSION['admin_id']; // admin ID

// Fetch admin settings
$sql = "SELECT assign_task_enabled FROM school_settings WHERE person='admin' AND person_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

// 🚨 If Assign Task module is disabled
if (!$settings || $settings['assign_task_enabled'] == 0) {
    echo "<script>alert('Assign Task module is disabled by admin settings.'); window.location.href='logout.php';</script>";
    exit;
}
?>

<style>
#app_link {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#apps ul {
    display: block !important;
}

#assign_task {
    color: #000;
}
</style>
<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="container">
            <h4>Assign Task</h4>
            <form id="taskForm">
                <input type="hidden" name="action" value="insert">
                <input type="hidden" name="school_id" value="1">
                <!-- We'll add a hidden input for assignments JSON -->
                <input type="hidden" name="assignments_json" id="assignments_json" value="[]">

                <div class="mb-3">
                    <label>Task Title</label>
                    <input type="text" name="task_title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="task_description" class="form-control" required></textarea>
                </div>

                <div class="mb-3">
                    <label>Assign To</label>
                    <select name="assigned_to_type" id="assigned_to_type" class="form-control">
                        <option value="">Select Type</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                    </select>
                </div>

                <div class="mb-3 d-flex align-items-end">
                    <div style="flex-grow:1;">
                        <label>Person</label>
                        <select name="assigned_to_id" id="personList" class="form-control">
                            <option value="">Select Person</option>
                        </select>
                    </div>
                    <button type="button" id="addPersonBtn" class="btn btn-success ml-2"
                        style="margin-left: 10px; height: 38px;">+</button>
                </div>

                <hr>

                <div class="table-responsive">
                    <table class="table table-striped table-md" id="assignmentTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Assign To</th>
                                <th>Person</th>
                                <th>Created At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows added dynamically -->
                        </tbody>
                    </table>
                </div>

                <div class="mb-3 mt-3">
                    <label>Due Date</label>
                    <input type="date" name="due_date" class="form-control" required>
                </div>

                <button class="btn btn-primary" type="submit">Save Task</button>
            </form>

            <hr>
            <div id="taskList">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Task Name</th>
                            <th>Members</th>
                            <th>Task Status</th>
                            <th>Assign Date</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="editableTaskTable"></tbody>
                </table>

            </div>
        </div>






        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



        <script>
        // Load editable task table
        function loadEditableTaskTable() {
            $.ajax({
                url: 'ajax/get_editable_tasks.php',
                type: 'GET',
                success: function(data) {
                    $('#editableTaskTable').html(data);
                },
                error: function(xhr, status, error) {
                    alert("Error loading editable task table: " + error);
                }
            });
        }
        $(document).on('input change', "input[type='range'][data-task-id]", function() {
            let taskId = $(this).data('task-id');
            let newPercent = $(this).val();
            let $span = $(this).next('span');

            // Update the displayed percentage immediately
            $span.text(newPercent + '%');

            $.ajax({
                url: 'ajax/update_task_completion.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    task_id: taskId,
                    task_completed_percent: newPercent
                },
                success: function(response) {
                    if (response.status !== 'success') {
                        alert(response.message || 'Failed to update completion');
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseText);
                }
            });
        });


        // Call function when needed
        loadEditableTaskTable();

        $(document).ready(function() {
            let assignments = [];

            // Load people on assign_to_type change
            $('#assigned_to_type').on('change', function() {
                const type = $(this).val();
                if (!type) {
                    $('#personList').html('<option value="">Select Person</option>');
                    return;
                }
                $.post('ajax/get_people.php', {
                    type
                }, function(data) {
                    $('#personList').html(data);
                });
            });

            $('#assign_to').on('change', function() {
                const type = $(this).val();
                if (!type) {
                    $('#person').html('<option value="">Select Person</option>');
                    return;
                }
                $.post('ajax/get_people.php', {
                    type
                }, function(data) {
                    $('#person').html(data);
                });
            });
            // Add person to assignments array & table
            $('#addPersonBtn').click(function() {
                const assignToType = $('#assigned_to_type').val();
                const personId = $('#personList').val();
                const personName = $('#personList option:selected').text();

                if (!assignToType) {
                    alert('Please select "Assign To" type first.');
                    return;
                }
                if (!personId) {
                    alert('Please select a person.');
                    return;
                }

                // Prevent duplicates
                if (assignments.some(a => a.assign_to_type === assignToType && a.person_id ===
                        personId)) {
                    alert('This person is already added.');
                    return;
                }

                // Add new assignment with current datetime
                assignments.push({
                    assign_to_type: assignToType,
                    person_id: personId,
                    person_name: personName,
                    created_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
                    status: 'Active'
                });

                renderAssignmentTable();
                updateAssignmentsJson();

                // Reset person select
                $('#personList').val('');
            });

            // Render assignments table
            function renderAssignmentTable() {
                const tbody = $('#assignmentTable tbody');
                tbody.empty();

                assignments.forEach((item, idx) => {
                    const row = `<tr data-index="${idx}">
                    <td>${idx + 1}</td>
                    <td>${capitalize(item.assign_to_type)}</td>
                    <td>${item.person_name}</td>
                    <td>${item.created_at}</td>
                    <td><div class="badge badge-success">${item.status}</div></td>
                    <td><button type="button" class="btn btn-danger btn-sm delete-assignment">Delete</button></td>
                </tr>`;
                    tbody.append(row);
                });
            }

            // Capitalize first letter
            function capitalize(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            }

            // Update hidden JSON before submit
            function updateAssignmentsJson() {
                $('#assignments_json').val(JSON.stringify(assignments));
            }

            // Delete row event
            $(document).on('click', '.delete-assignment', function() {
                const index = $(this).closest('tr').data('index');
                assignments.splice(index, 1);
                renderAssignmentTable();
                updateAssignmentsJson();
            });

            // Submit form via AJAX
            $('#taskForm').on('submit', function(e) {
                e.preventDefault();

                if (assignments.length === 0) {
                    alert('Please add at least one person before saving.');
                    return;
                }

                updateAssignmentsJson();

                $.ajax({
                    url: 'ajax/save_task.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        console.log("Server Response:", response); // Debug
                        if (response.status === 'success') {
                            alert(response.message);
                            $('#taskForm')[0].reset();
                            assignments = [];
                            renderAssignmentTable();
                            updateAssignmentsJson();
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error, xhr.responseText);
                        alert("AJAX Error: " + xhr.responseText);
                    }
                });
            });



        });
        // When delete button clicked
        $(document).on('click', '.delete-task', function() {
            let taskId = $(this).data('id');

            if (!confirm('Are you sure you want to delete this task?')) return;

            $.ajax({
                url: 'ajax/delete_task.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    task_id: taskId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Task deleted successfully!');
                        // Refresh your task list
                        loadTasks();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('AJAX Error: ' + error);
                }
            });
        });
        </script>
    </section>

    <!-- Large Modal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-labelledby="editTaskLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskLabel">Task Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="task_id" value="">

                    <!-- Task Info -->
                    <div class="form-group">
                        <label>Task Title</label>
                        <input type="text" id="task_title" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="task_description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" id="due_date" class="form-control">
                    </div>


                    <button type="button" id="save_task_btn" class="btn btn-success">Save Changes</button>

                    <div class="m-3">
                        <label>Assign To</label>
                        <select name="assigned_to_type" id="assign_to" class="form-control">
                            <option value="">Select Type</option>
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                        </select>
                    </div>

                    <div class="mb-3 d-flex align-items-end">
                        <div style="flex-grow:1;">
                            <label>Person</label>
                            <select name="assigned_to_id" id="person" class="form-control">
                                <option value="">Select Person</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" id="add_person_btn" class="btn btn-primary ms-2" style="height:38px;">
                        +
                    </button>

                    <!-- Assigned People Table -->
                    <h5>Assigned People</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Assign To</th>
                                <th>Person</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="assigned_people_table">
                            <tr>
                                <td colspan="5" class="text-center">No data available</td>
                            </tr>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery AJAX -->
    <script>
    $('#save_task_btn').click(function() {
        let taskId = $('#task_id').val();
        let taskTitle = $('#task_title').val().trim();
        let taskDescription = $('#task_description').val().trim();
        let dueDate = $('#due_date').val();
        let taskCompletedPercent = $('#task_completed_percent').val();

        if (!taskId) {
            alert('Invalid task');
            return;
        }
        if (!taskTitle) {
            alert('Task title cannot be empty');
            return;
        }
        if (!dueDate) {
            alert('Due date is required');
            return;
        }

        $.ajax({
            url: 'ajax/update_task.php',
            type: 'POST',
            dataType: 'json',
            data: {
                task_id: taskId,
                task_title: taskTitle,
                task_description: taskDescription,
                due_date: dueDate,
                task_completed_percent: taskCompletedPercent
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert('Task updated successfully');
                    $('#editTaskModal').modal('hide');
                    // Optionally refresh task list table on page
                    // e.g., reload tasks or refresh the row for this task
                    location.reload();
                } else {
                    alert(response.message || 'Failed to update task');
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    });

    $(document).ready(function() {
        $('#add_person_btn').click(function() {
            let taskId = $('#task_id').val(); // Make sure you have a hidden input with task id in modal
            let assignedToType = $('#assign_to').val();
            let assignedToId = $('#person').val();

            if (!assignedToType) {
                alert('Please select Assign To type');
                return;
            }
            if (!assignedToId) {
                alert('Please select a Person');
                return;
            }
            if (!taskId) {
                alert('Invalid task selected');
                return;
            }

            $.ajax({
                url: 'ajax/add_assignee.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    task_id: taskId,
                    assigned_to_type: assignedToType,
                    assigned_to_id: assignedToId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Person assigned successfully');
                        // Refresh assigned people table by reopening task or update the table row dynamically
                        $('.edit-task-btn[data-task-id="' + taskId + '"]').click();
                        // Reset selects
                        $('#assign_to').val('');
                        $('#person').html('<option value="">Select Person</option>');
                    } else {
                        alert(response.message || 'Failed to assign person');
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseText);
                }
            });
        });
    });

    $(document).on('click', '.edit-task-btn', function() {
        let taskId = $(this).data('task-id');

        $.ajax({
            url: 'ajax/get_single_task.php',
            type: 'GET',
            dataType: 'json',
            data: {
                action: 'get_task',
                task_id: taskId
            },
            success: function(response) {
                if (response.status === 'success') {
                    let t = response.task;
                    let assignees = response.assignees;

                    // Fill form fields
                    $('#task_id').val(t.id);
                    $('#task_title').val(t.task_title);
                    $('#task_description').val(t.task_description);
                    $('#due_date').val(t.due_date);
                    $('#task_completed_percent').val(t.task_completed_percent);
                    $('#percent_display').text(t.task_completed_percent + '%');

                    $('#assign_to').val('');
                    $('#person').val('');

                    // Populate assigned people table
                    let tableHtml = '';
                    if (assignees.length > 0) {
                        assignees.forEach(function(a, i) {
                            tableHtml += `
                            <tr>
                                <td>${i + 1}</td>
                                <td>${a.assigned_to_type}</td>
                                <td>${a.person_name}</td>
                                <td>${a.status}</td>
                                <td>${a.created_at}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm delete-assignee-btn" data-assignee-id="${a.id}">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>`;
                        });
                    } else {
                        tableHtml =
                            '<tr><td colspan="6" class="text-center">No assigned people found</td></tr>';
                    }
                    $('#assigned_people_table').html(tableHtml);

                    // Show modal
                    $('#editTaskModal').modal('show');
                } else {
                    alert(response.message || 'Invalid request');
                }
            },
            error: function(xhr) {
                let errorMsg = 'An unexpected error occurred.';
                try {
                    let res = JSON.parse(xhr.responseText);
                    if (res.message) errorMsg = res.message;
                } catch (e) {
                    errorMsg = xhr.responseText;
                }
                alert(errorMsg);
            }
        });
    });

    // Delete assignee handler
    $(document).on('click', '.delete-assignee-btn', function() {
        let assigneeId = $(this).data('assignee-id');
        if (!confirm('Are you sure you want to remove this assignee?')) return;

        $.ajax({
            url: 'ajax/delete_assignee.php',
            type: 'POST',
            dataType: 'json',
            data: {
                assignee_id: assigneeId
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert('Assignee removed successfully');
                    $('.edit-task-btn[data-task-id="' + response.task_id + '"]').click();
                } else {
                    alert(response.message || 'Unable to delete assignee.');
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    });
    </script>



    <?php require_once 'assets/php/footer.php'; ?>