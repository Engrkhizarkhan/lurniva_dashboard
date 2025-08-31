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
            <h2>Submit Exam Results</h2>
        </div>

        <div class="section-body">
            <form id="examResultForm">
                <input type="hidden" name="school_id" value="<?php echo $_SESSION['campus_id']; ?>">

                <div class="mb-3">
                    <label>Select Exam</label>
                    <select name="exam_id" id="exam_id" class="form-control" required>
                        <option value="">Select Exam</option>
                    </select>
                </div>

                <div class="mb-3">
                    <table class="table table-bordered" id="examStudentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Roll Number</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Exam</th>
                                <th>Exam Date</th>
                                <th>Total Marks</th>
                                <th>Marks Obtained</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="text-center">Select Exam to load students</td>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <button type="submit" class="btn btn-primary">Submit Exam Results</button>
            </form>

            <div id="examResultMsg" class="mt-3"></div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {

    // ✅ Load exams via AJAX (only active exams not fully graded yet)
    function loadExams() {
        $.post('ajax/get_pending_exams.php', {
            teacher_id: <?php echo $_SESSION['admin_id']; ?>
        }, function(data) {
            $('#exam_id').html(data);
        });
    }
    loadExams();

    // ✅ Load students for exam 
    $('#exam_id').change(function() {
        var exam_id = $(this).val();
        if (exam_id) {
            $.post('ajax/get_exam_students.php', {
                exam_id: exam_id
            }, function(data) {
                $('#examStudentsTable tbody').html(data);
            });
        } else {
            $('#examStudentsTable tbody').html(
                '<tr><td colspan="9" class="text-center">Select Exam to load students</td></tr>'
            );
        }
    });

    // ✅ Submit results
    $('#examResultForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'ajax/submit_exam_results.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#examResultMsg').html(response);
                $('#exam_id').val('').change();
            }
        });
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>