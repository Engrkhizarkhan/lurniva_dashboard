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
            <h1>Installment Fee Slips</h1>
        </div>

        <div class="section-body">
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
            </div>

            <button id="print" class="btn btn-success mb-3">🖨️ Print</button>

            <div id="printArea">
                <div id="installmentSlips"></div>
            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Load students
    $.getJSON("ajax/get_students.php", function(res) {
        if (res.status === 'success') {
            res.data.forEach(s => {
                $('#studentSelect').append(
                    `<option value="${s.id}">${s.name} (${s.class})</option>`);
            });
        }
    });

    // Load fee periods
    $.get("ajax/get_fee_periods.php", function(data) {
        $('#feePeriodSelect').append(data);
    });

    // Fetch slips on filter change
    $('#feePeriodSelect, #studentSelect').on('change', function() {
        let period = $('#feePeriodSelect').val();
        let student = $('#studentSelect').val();
        if (period && student) {
            $.post("ajax/get_installment_slips.php", {
                period_id: period,
                student_id: student
            }, function(res) {
                $('#installmentSlips').html(res);
            });
        }
    });

    // Print
    $('#print').click(function() {
        window.print();
    });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>