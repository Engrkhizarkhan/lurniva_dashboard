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
        <div class="container mt-4">
            <h3>My Assignments & Results</h3>
            <table class="table table-bordered" id="assignmentsTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Teacher</th>
                        <th>Due Date</th>
                        <th>Total Marks</th>
                        <th>Result</th>
                        <th>Remarks</th>
                        <th>Attachments</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    loadAssignments();

    function loadAssignments() {
        $.ajax({
            url: 'ajax/get_student_assignments.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                let tableBody = $("#assignmentsTable tbody");
                tableBody.empty();

                if (response.status === 'success' && response.data.length > 0) {
                    $.each(response.data, function(index, item) {
                        let resultMarks = item.marks_obtained !== null ?
                            `${item.marks_obtained} / ${item.total_marks}` :
                            `<span class="badge bg-warning">Pending</span>`;

                        let remarks = item.remarks ? item.remarks : "-";

                        let attachmentLinks = "";
                        if (item.attachment) {
                            attachmentLinks +=
                                `<a href="../Faculty Dashboard/uploads/assignment/${item.attachment}" target="_blank">Assignment</a>`;
                        }
                        if (item.result_attachment) {
                            attachmentLinks +=
                                ` | <a href="../Faculty Dashboard/uploads/results/${item.result_attachment}" target="_blank">Result File</a>`;
                        }

                        tableBody.append(`
                            <tr>
                                <td>${item.title}</td>
                                <td>${item.type}</td>
                                <td>${item.teacher_name}</td>
                                <td>${item.due_date}</td>
                                <td>${item.total_marks}</td>
                                <td>${resultMarks}</td>
                                <td>${remarks}</td>
                                <td>${attachmentLinks}</td>
                            </tr>
                        `);
                    });
                } else {
                    tableBody.html(
                        `<tr><td colspan="8" class="text-center">No assignments found</td></tr>`
                    );
                }
            }
        });
    }
});
</script>

<?php require_once 'assets/php/footer.php'; ?>