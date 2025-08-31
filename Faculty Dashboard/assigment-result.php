<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("test");
    if (el) {
        el.classList.add("active");
    }
});
</script>

<?php require_once 'assets/php/header.php'; ?>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Submit Student Results</h2>
        </div>

        <div class="section-body">
            <form id="resultForm">
                <input type="hidden" name="school_id" value="<?php echo $_SESSION['campus_id']; ?>">

                <div class="mb-3">
                    <label>Select Test/Assignment</label>
                    <select name="assignment_id" id="assignment_id" class="form-control" required>
                        <option value="">Select Test/Assignment</option>
                    </select>
                </div>

                <div class="mb-3">
                    <table class="table table-bordered" id="studentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Roll Number</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Due Date</th>
                                <th>Total Marks</th>
                                <th>Marks Obtained</th>
                                <th>Remarks</th>
                                <th>Attachment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="12" class="text-center">Select Test/Assignment to load students</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary">Submit Results</button>
            </form>

            <div id="resultMsg" class="mt-3"></div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {

    // Load assignments for teacher (only those not fully graded)
    function loadAssignments() {
        $.post('ajax/get_pending_assignments.php', {
            teacher_id: <?php echo $_SESSION['admin_id']; ?>
        }, function(data) {
            $('#assignment_id').html(data);
        });
    }
    loadAssignments();

    // Load students table when assignment selected
    $('#assignment_id').change(function() {
        var assignment_id = $(this).val();
        if (assignment_id) {
            $.post('ajax/get_assignment_students.php', {
                assignment_id: assignment_id
            }, function(data) {
                $('#studentsTable tbody').html(data);
            });
        } else {
            $('#studentsTable tbody').html(
                '<tr><td colspan="12" class="text-center">Select Test/Assignment to load students</td></tr>'
            );
        }
    });

    // Submit student results
    $('#resultForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'ajax/submit_student_results.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#resultMsg').html(response);
                $('#assignment_id').val('').change();
            }
        });
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>