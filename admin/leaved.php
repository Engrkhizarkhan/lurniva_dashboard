<?php
require_once 'assets/php/header.php';
require_once 'sass/db_config.php';

?>

<style>
#Managements {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}
</style>
<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="container mt-4">
            <h3>Pending Faculty Leave Requests</h3>
            <div id="leaveTable">Loading...</div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
        function loadPendingLeaves() {
            $.get('ajax/fetch_pending_leaves.php', function(data) {
                $('#leaveTable').html(data);
            });
        }

        function updateLeaveStatus(id, status) {
            $.post('ajax/update_leave_status.php', {
                id: id,
                status: status
            }, function(response) {
                alert(response.message);
                loadPendingLeaves();
            }, 'json');
        }

        $(document).ready(function() {
            loadPendingLeaves();

            $(document).on('click', '.action-btn', function() {
                const id = $(this).data('id');
                const status = $(this).data('status');
                updateLeaveStatus(id, status);
            });
        });
        </script>
        <!-- Leave History Table -->
        <!-- <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Leave History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>ABC</td>
                                        <td>093</td>
                                        <td>2025-07-01</td>
                                        <td>Sick Leave</td>
                                        <td>2 Days</td>
                                        <td><span class="badge badge-success">Approved</span></td>
                                        <td>Approved by Admin</td>
                                    </tr>
                                    <tr>
                                        <td>XYZ</td>
                                        <td>658</td>
                                        <td>2025-06-15</td>
                                        <td>Emergency Leave</td>
                                        <td>1 Day</td>
                                        <td><span class="badge badge-warning">Pending</span></td>
                                        <td>Waiting for Approval</td>
                                    </tr>
                                    <tr>
                                        <td>ABC</td>
                                        <td>058</td>
                                        <td>2025-05-10</td>
                                        <td>Casual Leave</td>
                                        <td>3 Days</td>
                                        <td><span class="badge badge-danger">Rejected</span></td>
                                        <td>Insufficient Leave Balance</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </section>
</div>
<?php require_once 'assets/php/footer.php'; ?>