<?php require_once 'assets/php/header.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("apps");
    if (el) {
        el.classList.add("active");
    }
});
</script>

<div class="main-content">
    <section class="section">
        <h2>My Meeting Announcements</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Agenda</th>
                    <th>Date & Time</th>
                    <th>With</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="meetingAnnouncementsTable"></tbody>
        </table>
    </section>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    // Load meetings
    function loadMeetings() {
        $.get("ajax/get_meeting_announcements.php", function(data) {
            $("#meetingAnnouncementsTable").html(data);
            $('[data-toggle="tooltip"]').tooltip();
        });
    }
    loadMeetings();
});
</script>
<?php require_once 'assets/php/footer.php'; ?>