<?php require_once 'assets/php/header.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("behaviorFormContainer");
    if (el) {
        el.classList.add("active");
    }
});
</script>
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <h2 class="section-title">My Behavior Reports</h2>
            <p class="section-lead">Here you can view all behavior reports assigned to you.</p>

            <div class="row" id="behaviorReportsContainer">
                <!-- Reports will be loaded here -->
            </div>
        </div>
    </section>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {

    // 🔹 Load reports for logged-in student
    function loadMyReports() {
        $.post('ajax/student_behavior.php', { action: 'getMyReports' }, function (res) {
            if (res.status === 'success') {
                let html = '';
                res.data.forEach(r => {
                    html += `<div class="card mb-3 p-3 shadow-sm">
                        <h5>${r.topic}</h5>
                        <p>${r.description}</p>
                        <p><b>Class:</b> ${r.class_name} | <b>Teacher:</b> ${r.teacher_name}</p>
                        <p><b>Deadline:</b> ${r.deadline}</p>`;

                    if (r.attachment) {
                        html += `<p><a href="uploads/behavior/${r.attachment}" target="_blank">📂 View Attachment</a></p>`;
                    }

                    if (r.parent_approval === "yes") {
                        if (r.parent_approved == 1) {
                            html += `<p class="text-success"><b>✔ Approved by Parent</b></p>`;
                        } else {
                            html += `<button class="btn btn-success approveBtn" data-id="${r.id}">Approve</button>`;
                        }
                    }

                    html += `</div>`;
                });

                $('#behaviorReportsContainer').html(html || '<p>No reports found in the last 30 days.</p>');
            } else {
                $('#behaviorReportsContainer').html('<p class="text-danger">' + res.message + '</p>');
            }
        }, 'json');
    }

    // 🔹 Approve button handler
    $(document).on('click', '.approveBtn', function () {
        let id = $(this).data('id');
        if (!confirm("Are you sure you want to approve this report?")) return;

        $.post('ajax/student_behavior.php', { action: 'approveReport', report_id: id }, function (res) {
            alert(res.message);
            if (res.status === 'success') loadMyReports();
        }, 'json');
    });

    // 🔹 Initial load
    loadMyReports();
});
</script>

<?php require_once 'assets/php/footer.php'; ?>
