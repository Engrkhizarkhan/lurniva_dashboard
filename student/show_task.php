<?php require_once 'assets/php/header.php'; ?>

<style>
#dashboard {
    padding-left: 20px;
    position: relative;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#dashboard i {
    color: #6777ef !important;
}
</style>

<div class="main-content">
    <section class="section">
        <?php echo $_POST['id']?>
        <div class="container my-4">
            <h2>Task Details</h2>
            <div id="taskInfo" class="mb-4">
                <h4 id="taskTitle"></h4>
                <p id="taskDescription"></p>
                <p><strong>Due Date:</strong> <span id="dueDate"></span></p>
                <p><strong>Completion:</strong> <span id="completionPercent"></span>%</p>
                <p><strong>Created At:</strong> <span id="createdAt"></span></p>
            </div>

            <h4>Assigned People</h4>
            <table class="table table-bordered" id="assigneesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Assigned At</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
        $(document).ready(function() {
            // Properly get taskId from PHP POST and put in JS variable as string
            let taskId = "<?php echo isset($_POST['id']) ? intval($_POST['id']) : ''; ?>";

            if (!taskId) {
                alert('Task ID missing.');
                return;
            }

            $.ajax({
                url: 'ajax/show_task.php',
                type: 'GET', // API expects GET with ?id=...
                dataType: 'json',
                data: {
                    id: taskId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        let task = response.task;
                        let assignees = response.assignees;

                        $('#taskTitle').text(task.task_title);
                        $('#taskDescription').text(task.task_description);
                        $('#dueDate').text(task.due_date);
                        $('#completionPercent').text(task.task_completed_percent);
                        $('#createdAt').text(task.created_at);

                        if (assignees.length > 0) {
                            let rows = '';
                            assignees.forEach(function(a, i) {
                                rows += `<tr>
                                    <td>${i + 1}</td>
                                    <td>${a.assigned_to_type}</td>
                                    <td>${a.person_name}</td>
                                    <td>${a.status}</td>
                                    <td>${a.created_at}</td>
                                 </tr>`;
                            });
                            $('#assigneesTable tbody').html(rows);
                        } else {
                            $('#assigneesTable tbody').html(
                                '<tr><td colspan="5" class="text-center">No assignees found</td></tr>'
                            );
                        }
                    } else {
                        alert(response.message || 'Failed to load task');
                        $('#assigneesTable tbody').html(
                            '<tr><td colspan="5" class="text-center">No data</td></tr>');
                    }
                },
                error: function(xhr) {
                    alert('Error fetching task: ' + xhr.responseText);
                    $('#assigneesTable tbody').html(
                        '<tr><td colspan="5" class="text-center">Error loading data</td></tr>');
                }
            });
        });
        </script>

    </section>

    <?php require_once 'assets/php/footer.php'; ?>