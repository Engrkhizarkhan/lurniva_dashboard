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
            <h2>View Submitted Results</h2>
        </div>

        <div class="section-body">
            <div class="mb-3">
                <label>Select Assignment/Test</label>
                <select id="assignmentSelect" class="form-control">
                    <option value="">Select Assignment/Test</option>
                </select>
            </div>

            <hr>

            <div class="table-responsive">
                <table class="table table-bordered" id="resultsTable">
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
                            <td colspan="12">Select an assignment to view results</td>
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

    // Load assignments that have submitted results
    function loadAssignments() {
        $.post('ajax/get_submitted_assignments.php', {}, function(data) {
            $('#assignmentSelect').html('<option value="">Select Assignment/Test</option>' + data);
        });
    }

    loadAssignments();

    $('#assignmentSelect').change(function() {
        var assignment_id = $(this).val();
        if (!assignment_id) {
            $('#resultsTable tbody').html(
                '<tr><td colspan="12">Select an assignment to view results</td></tr>');
            return;
        }

        $.post('ajax/get_submitted_results.php', {
            assignment_id: assignment_id
        }, function(data) {
            $('#resultsTable tbody').html(data);
        });
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>