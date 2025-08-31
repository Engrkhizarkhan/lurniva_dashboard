<?php require_once 'assets/php/header.php'; ?>
<style>

#leaved {
    padding-left: 20px;
    position: relative;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#leaved svg {
    color: #6777ef !important;
}

#leaved span {
    color: #6777ef !important;
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

    </section>
</div>
<?php require_once 'assets/php/footer.php'; ?>