<?php require_once 'assets/php/header.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("apps");
    if (el) {
        el.classList.add("active");
    }
});
</script>

<style>
#notice_board {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

.notice-card {
    border-left: 5px solid #6777ef;
    margin-bottom: 15px;
    padding: 15px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.notice-title {
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 5px;
}

.notice-meta {
    font-size: 13px;
    color: #666;
}

.notice-purpose {
    margin-top: 10px;
}
</style>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Faculty Notice Board</h2>
        </div>

        <div class="section-body">
            <div id="facultyNotices">
                <p class="text-muted">Loading notices...</p>
            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    loadNotices();

    function loadNotices() {
        $.ajax({
            url: "ajax/get_faculty_notices.php",
            type: "POST",
            data: {
                action: "getFaculty"
            },
            success: function(res) {
                $("#facultyNotices").html(res);
            },
            error: function() {
                $("#facultyNotices").html(
                    "<div class='alert alert-danger'>Error loading notices</div>");
            }
        });
    }
});
</script>

<?php require_once 'assets/php/footer.php'; ?>