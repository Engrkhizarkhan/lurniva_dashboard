<?php require_once 'assets/php/header.php';?>
<?php

include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$school_id = $_SESSION['admin_id']; // adjust if using campus_id/student_id

$sql = "SELECT meeting_enabled FROM school_settings WHERE person='admin' AND person_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

// 🚨 If meeting module is disabled
if (!$settings || $settings['meeting_enabled'] == 0) {
    echo "<script>alert('Meeting module is disabled by school admin.'); window.location.href='logout.php';</script>";
    exit;
}
?>
<?php
require_once 'sass/db_config.php';

$school_id = $_SESSION['admin_id'];

// Fetch meeting requests
$sql = "SELECT * FROM meeting_requests WHERE school_id = '$school_id' AND status!='approved' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
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

#meeting_requests {
    color: #000;
}
</style>

<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Meeting Requests</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Agenda</th>
                                        <th>Requested By</th>
                                        <th>With</th>
                                        <th>Date Requested</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['title']); ?></td>
                                        <td><?= htmlspecialchars($row['agenda']); ?></td>
                                        <td><?= htmlspecialchars($row['requested_by']); ?> (ID:
                                            <?= $row['requester_id']; ?>)
                                        </td>
                                        <td><?= htmlspecialchars($row['with_meeting']); ?> (ID:
                                            <?= $row['id_meeter']; ?>)</td>
                                        <td><?= htmlspecialchars($row['created_at']); ?></td>
                                        <td>
                                            <?php if($row['status'] == 'pending'): ?>
                                            <div class="badge badge-warning">Pending</div>
                                            <?php elseif($row['status'] == 'approved'): ?>
                                            <div class="badge badge-success">Accepted</div>
                                            <?php else: ?>
                                            <div class="badge badge-danger">Rejected</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['status'] == 'pending'): ?>
                                            <button class="btn btn-success btn-sm accept-btn"
                                                data-id="<?= $row['id']; ?>"
                                                data-title="<?= htmlspecialchars($row['title']); ?>"
                                                data-agenda="<?= htmlspecialchars($row['agenda']); ?>"
                                                data-requested="<?= $row['requester_id']; ?>"
                                                data-with="<?= $row['id_meeter']; ?>">
                                                Accept
                                            </button>
                                            <button class="btn btn-danger btn-sm reject-btn"
                                                data-id="<?= $row['id']; ?>">Reject</button>
                                            <?php else: ?>
                                            <span>-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
</div>
<!-- Modal for Meeting Date & Time -->
<div class="modal fade" id="meetingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="meetingForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Meeting</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="requestId">
                    <input type="hidden" name="person_one" id="personOne">
                    <input type="hidden" name="person_two" id="personTwo">

                    <div class="form-group">
                        <label>Meeting Title</label>
                        <input type="text" class="form-control" name="title" id="meetingTitle" readonly>
                    </div>

                    <div class="form-group">
                        <label>Agenda</label>
                        <textarea class="form-control" name="agenda" id="meetingAgenda" readonly></textarea>
                    </div>

                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" class="form-control" name="meeting_date" required>
                    </div>

                    <div class="form-group">
                        <label>Time</label>
                        <input type="time" class="form-control" name="meeting_time" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Meeting</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Open Modal with Data
    $('.accept-btn').on('click', function() {
        $('#requestId').val($(this).data('id'));
        $('#meetingTitle').val($(this).data('title'));
        $('#meetingAgenda').val($(this).data('agenda'));
        $('#personOne').val($(this).data('requested'));
        $('#personTwo').val($(this).data('with'));
        $('#meetingModal').modal('show');
    });

    // Reject Meeting
    $('.reject-btn').on('click', function() {
        let requestId = $(this).data('id');
        if (confirm("Are you sure you want to reject this request?")) {
            $.post("ajax/reject_meeting.php", {
                id: requestId
            }, function(response) {
                location.reload();
            });
        }
    });

    // Submit Meeting Form
    $('#meetingForm').on('submit', function(e) {
        e.preventDefault();
        $.post("ajax/accept_meeting.php", $(this).serialize(), function(response) {
            alert(response);
            $('#meetingModal').modal('hide');
            location.reload();
        });
    });
});
</script>
<?php require_once 'assets/php/footer.php';?>