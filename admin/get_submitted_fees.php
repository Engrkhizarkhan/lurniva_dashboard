<?php require_once 'assets/php/header.php'; ?>
<?php
session_start();
include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$school_id = $_SESSION['admin_id']; // adjust if using campus_id or student_id

// Fetch school settings
$sql = "SELECT fee_enabled FROM school_settings WHERE person = 'school' AND person_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$settings = $result->fetch_assoc();
$stmt->close();

// 🚨 If fee is disabled
if (!$settings || $settings['fee_enabled'] == 0) {
    echo "<script>alert('Fee module is disabled by school admin.'); window.location.href='logout.php';</script>";
    exit;
}
?>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Submitted Fees</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Search Submitted Fees</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Fee Period</label>
                            <select id="feePeriodSelect" class="form-control">
                                <option value="">-- Select Period --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Student</label>
                            <select id="studentSelect" class="form-control">
                                <option value="">-- Select Student --</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button id="searchBtn" class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="feeTable">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Period</th>
                                    <th>Total Fee</th>
                                    <th>Scholarship</th>
                                    <th>Net Payable</th>
                                    <th>Amount Paid</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="refundForm">
                <div class="modal-header">
                    <h5 class="modal-title">Refund Fee</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="slip_id" id="refundSlipId">
                    <div class="form-group">
                        <label>Refund Amount</label>
                        <input type="number" name="refund_amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Reason</label>
                        <textarea name="refund_reason" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="refund_date" class="form-control" value="<?= date('Y-m-d') ?>"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Submit Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Load fee periods
    $.get("ajax/get_fee_periods.php", function(data) {
        $('#feePeriodSelect').append(data);
    });

    // Load students
    $.getJSON("ajax/get_students.php", function(res) {
        if (res.status === "success") {
            res.data.forEach(s => {
                $('#studentSelect').append(
                    `<option value="${s.id}">${s.name} (${s.class} - Roll #${s.roll})</option>`
                );
            });
        }
    });

    // Search
    $('#searchBtn').on('click', function() {
        let period = $('#feePeriodSelect').val();
        let student = $('#studentSelect').val();

        $.post("ajax/get_submitted_fees.php", {
            period_id: period,
            student_id: student
        }, function(res) {
            $('#feeTable tbody').html(res);
        });
    });

    // Open Refund Modal
    $(document).on('click', '.refundBtn', function() {
        $('#refundSlipId').val($(this).data('id'));
        $('#refundModal').modal('show');
    });

    // Submit Refund
    $('#refundForm').on('submit', function(e) {
        e.preventDefault();
        $.post("ajax/refund_fee.php", $(this).serialize(), function(res) {
            alert(res.message);
            if (res.status === 'success') {
                $('#refundModal').modal('hide');
                $('#searchBtn').click();
            }
        }, 'json');
    });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>