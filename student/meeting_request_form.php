<?php require_once 'assets/php/header.php'; ?>
<style>
#dairyFormContainer {
    padding: 20px;
    background-color: #f7f8fc;
    border-radius: 10px;
    margin-bottom: 30px;
}
</style>

<div class="main-content">
    <section class="section">

        <!-- Meeting Request Form -->
        <div id="dairyFormContainer">
            <h2>Meeting Request</h2>
            <form id="meetingForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="insert">
                <input type="hidden" name="id" id="edit_id" value="">

                <!-- With Meeting -->
                <div class="form-group">
                    <label>With Meeting</label>
                    <select class="form-control" name="with_meeting" id="with_meeting" required>
                        <option value="">Select</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>

                <!-- Student Dropdown -->
                <div class="form-group" id="studentSelectDiv" style="display:none;">
                    <label>Select Teacher</label>
                    <select class="form-control" name="id_meeter" id="student_id">
                    </select>
                </div>

                <!-- Hidden field for Admin ID -->
                <input type="hidden" name="id_meeter_admin" id="admin_id_hidden" value="<?= $_SESSION['school_id']; ?>">

                <!-- Title -->
                <div class="form-group">
                    <label>Meeting Title</label>
                    <input type="text" class="form-control" name="title" id="title" required>
                </div>

                <!-- Agenda -->
                <div class="form-group">
                    <label>Agenda</label>
                    <textarea class="form-control" name="agenda" id="agenda" rows="3" required></textarea>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary">Save Request</button>
            </form>
        </div>

        <!-- Meeting List -->
        <div id="allMeetingsContainer">
            <h3>All Meeting Requests</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Agenda</th>
                        <th>With</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="meetingsTableBody"></tbody>
            </table>
        </div>

    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("apps");
    if (el) {
        el.classList.add("active");
    }
});

$(document).ready(function() {

    // Load meeting requests
    function loadMeetings() {
        $.get("ajax/meeting_crud.php", {
            action: "fetch"
        }, function(data) {
            $("#meetingsTableBody").html(data);
        });
    }
    loadMeetings();

    // Show student dropdown if meeting with student
    $("#with_meeting").change(function() {
        if ($(this).val() === "teacher") {
            $("#studentSelectDiv").show();
            $.post("ajax/get_my_teacher.php", {}, function(data) {
                $("#student_id").html(data);
            });
        } else {
            $("#studentSelectDiv").hide();
        }
    });

    // Submit form via AJAX
    $("#meetingForm").submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        // If meeting with admin
        if ($("#with_meeting").val() === "admin") {
            formData.set("id_meeter", $("#admin_id_hidden").val());
        }

        // Decide whether to insert or update
        if ($("#edit_id").val()) {
            formData.set("action", "update");
        } else {
            formData.set("action", "insert");
        }

        $.ajax({
            url: "ajax/meeting_crud.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert(response);
                $("#meetingForm")[0].reset();
                $("#studentSelectDiv").hide();
                $("#edit_id").val("");
                loadMeetings();
            }
        });
    });


    // Edit meeting
    $(document).on("click", ".edit-meeting", function() {
        let id = $(this).data("id");
        $.get("ajax/meeting_crud.php", {
            action: "get",
            id: id
        }, function(data) {
            let meeting = JSON.parse(data);
            $("#edit_id").val(meeting.id);
            $("#with_meeting").val(meeting.with_meeting).trigger("change");
            if (meeting.with_meeting === "teacher") {
                $("#studentSelectDiv").show();

                // Delay to ensure options are loaded
                setTimeout(() => {
                    let studentOption = $('#student_id option[value="' + meeting
                        .id_meeter + '"]');
                    if (studentOption.length) {
                        studentOption.prop('selected', true);
                        $('#student_id').trigger('change');
                    }
                }, 500);
            }
            $("#title").val(meeting.title);
            $("#agenda").val(meeting.agenda);
        });
    });

    // Delete meeting
    $(document).on("click", ".delete-meeting", function() {
        if (!confirm("Are you sure you want to delete this meeting?")) return;
        let id = $(this).data("id");
        $.post("ajax/meeting_crud.php", {
            action: "delete",
            id: id
        }, function(response) {
            alert(response);
            loadMeetings();
        });
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>