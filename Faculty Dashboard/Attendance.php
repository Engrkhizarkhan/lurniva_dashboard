<?php require_once 'assets/php/header.php'; ?>
<style>
#Attendance {
    padding-left: 20px;
    position: relative;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#Attendance svg {
    color: #6777ef !important;
}

#Attendance span {
    color: #6777ef !important;
}
</style>

<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Test / Assignment</h2>
        </div>

        <div class="section-body">
            <div class="form-group">
                <label for="classSelect">Select Class:</label>
                <select id="classSelect" class="form-control">
                </select>
            </div>
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="Date" name="date" id="date" class="form-control">
            </div>

            <table class="table table-bordered" id="attendanceTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center">Please select a class</td>
                    </tr>
                </tbody>
            </table>

            <button id="saveAttendance" class="btn btn-success d-none">Save Attendance</button>


            <div id="result" class="mt-3"></div>
            <hr>
            <h3>All Tests / Assignments</h3>
            <div id="allTasks"></div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    let today = new Date().toISOString().split('T')[0];
    document.getElementById("date").value = today;
    // Load classes on page load
    $.ajax({
        url: "ajax/teacher_classes.php", // your 2nd PHP snippet
        type: "GET",
        success: function(data) {
            $("#classSelect").html(data);
        }
    });

    // When class changes, load unmarked students
    $("#classSelect").change(function() {
        let class_id = $(this).val();
        if (class_id === "") {
            $("#attendanceTable tbody").html(
                "<tr><td colspan='5' class='text-center'>Please select a class</td></tr>");
            $("#saveAttendance").addClass("d-none");
            return;
        }

        $.ajax({
            url: "ajax/get_class_students.php", // your 1st PHP snippet
            type: "GET",
            data: {
                class_id: class_id
            },
            success: function(data) {
                $("#attendanceTable tbody").html(data);
                $("#saveAttendance").removeClass("d-none");
            }
        });
    });

    // Save attendance
    $("#saveAttendance").click(function() {
        let class_id = $("#classSelect").val();
        let date = $("#date").val();

        // get all checked radios as array
        let formData = $("#attendanceTable input[type=radio]:checked").serializeArray();

        // convert to object: { student_id: status }
        let statuses = {};
        formData.forEach(item => {
            // item.name looks like "status[5]"
            let match = item.name.match(/status\[(\d+)\]/);
            if (match) {
                statuses[match[1]] = item.value;
            }
        });

        $.ajax({
            url: "ajax/save_attendance.php",
            type: "POST",
            data: {
                class_id: class_id,
                attendanceDate: date,
                status: statuses
            },
            success: function(res) {
                alert(res);
                $("#classSelect").trigger("change");
            }
        });
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>