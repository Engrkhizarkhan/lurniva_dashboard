<?php require_once 'assets/php/header.php'; ?>
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Student Attendance Report</h2>
        </div>

        <div class="section-body">
            <div id="attendanceReport" class="mt-4 text-center">Loading...</div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("attendance");
    if (el) {
        el.classList.add("active");
    }
});

$(document).ready(function() {
    // Auto-load report for logged in student
    $.ajax({
        url: "ajax/get_attendance_report.php",
        type: "GET",
        success: function(data) {
            $("#attendanceReport").html(data);
        },
        error: function() {
            $("#attendanceReport").html("<p class='text-danger'>Failed to load report</p>");
        }
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>