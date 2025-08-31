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
            <h2>View Submitted Exam Results</h2>
        </div>

        <div class="section-body">
            <div class="mb-3">
                <label>Select Exam</label>
                <select id="examSelect" class="form-control">
                    <option value="">Select Exam</option>
                </select>
            </div>

            <hr>

            <div class="table-responsive">
                <table class="table table-bordered" id="examResultsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Roll Number</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Exam Name</th>
                            <th>Exam Date</th>
                            <th>Total Marks</th>
                            <th>Marks Obtained</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="10" class="text-center">Select an exam to view results</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {

    // Load submitted exams
    function loadExams() {
        $.post('ajax/get_submitted_exams.php', {}, function(data) {
            $('#examSelect').html('<option value="">Select Exam</option>' + data);
        });
    }

    loadExams();

    // Load exam results when an exam is selected
    $('#examSelect').change(function() {
        var exam_data = $(this).val(); // examName|className (like before)
        if (!exam_data) {
            $('#examResultsTable tbody').html(
                '<tr><td colspan="10" class="text-center">Select an exam to view results</td></tr>'
            );
            return;
        }

        $.post('ajax/get_exam_results.php', {
            exam_id: exam_data
        }, function(data) {
            $('#examResultsTable tbody').html(data);
        });
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>