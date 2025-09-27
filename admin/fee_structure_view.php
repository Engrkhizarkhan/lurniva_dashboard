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

<style>
#fee_type {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#fee_type svg {
    color: #6777ef !important;
}

#fee_type span {
    color: #6777ef !important;
}

#fee_type ul {
    display: block !important;
}

#fee_structure_view {
    color: #000;
}
</style>
<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>All Student Fee Structure</h2>
        </div>
        <div id="feeTable">Loading...</div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $.get("ajax/fetch_student_fee_structure.php", function(data) {
        $("#feeTable").html(data);
    });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>