<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("exam");
    if (el) {
        el.classList.add("active");
    }
});
</script>

<?php require_once 'assets/php/header.php'; ?>

<div class="main-content">
    <section class="section">
        <div class="container mt-4">
            <h3 class="mb-4">📘 My Exam Results</h3>

            <!-- Exam Dropdown -->
            <div class="mb-3">
                <label for="examSelect"><b>Select Exam</b></label>
                <select id="examSelect" class="form-control">
                    <option value="">-- Select Exam --</option>
                </select>
            </div>

            <!-- Result Card -->
            <div id="resultCard" style="display:none;">
                <div class="card p-4 shadow-lg border rounded">

                    <!-- School Info -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 id="schoolName"></h3>
                            <p id="schoolAddress"></p>
                            <p id="schoolPhone"></p>
                        </div>
                        <div>
                            <img id="schoolLogo" src="" alt="School Logo" height="100">
                        </div>
                    </div>
                    <hr>

                    <!-- Student + Exam Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><b>Name:</b> <span id="studentName"></span></p>
                            <p><b>Roll No:</b> <span id="rollNumber"></span></p>
                            <p><b>Class:</b> <span id="className"></span></p>
                            <p><b>Gender:</b> <span id="studentGender"></span></p>
                            <p><b>DOB:</b> <span id="studentDOB"></span></p>
                        </div>
                        <div class="col-md-6 text-right">
                            <p><b>Exam:</b> <span id="examName"></span></p>
                            <p><b>Date:</b> <span id="examDate"></span></p>
                            <p><b>Total Marks:</b> <span id="totalMarks"></span></p>
                            <p><b>Obtained Marks:</b> <span id="obtainedMarks"></span></p>
                            <p><b>Percentage:</b> <span id="percentage"></span>%</p>
                            <p><b>Grade:</b> <span id="grade"></span></p>
                        </div>
                    </div>

                    <!-- Marks Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Subject</th>
                                    <th>Total Marks</th>
                                    <th>Obtained Marks</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="marksTable"></tbody>
                        </table>
                    </div>

                    <!-- Final Result -->
                    <div class="text-center mt-4">
                        <h4>Final Result: <span id="resultStatus" class="badge badge-info"></span></h4>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {

    // Load exams in dropdown
    $.get('ajax/get_student_exams.php', function(data) {
        $('#examSelect').html('<option value="">-- Select Exam --</option>' + data);
    });

    // On exam selection
    $('#examSelect').change(function() {
        let exam_name = $(this).val();
        if (!exam_name) {
            $('#resultCard').hide();
            return;
        }

        $.post('ajax/get_exam_result_card.php', {
                exam_name: exam_name
            }, function(response) {
                if (response.status === 'success') {
                    $('#resultCard').show();

                    // School Info
                    $('#schoolName').text(response.school.school_name);
                    $('#schoolAddress').text(response.school.address + ", " + response.school.city);
                    $('#schoolPhone').text("Phone: " + response.school.school_phone);
                    $('#schoolLogo').attr("src", "uploads/logos/" + response.school.logo);

                    // Student Info
                    $('#studentName').text(response.student.full_name);
                    $('#rollNumber').text(response.student.roll_number);
                    $('#className').text(response.student.class_grade + " - " + response.student
                        .section);
                    $('#studentGender').text(response.student.gender);
                    $('#studentDOB').text(response.student.dob);

                    // Exam Info
                    $('#examName').text(response.exam.exam_name);
                    $('#examDate').text(response.exam.exam_date);

                    // Results Table
                    let tbody = "";
                    let total = 0,
                        obtained = 0;
                    $.each(response.results, function(i, item) {
                        tbody += `<tr>
                        <td>${item.period_name}</td>
                        <td>${item.total_marks}</td>
                        <td>${item.marks_obtained}</td>
                        <td>${item.remarks ?? '-'}</td>
                    </tr>`;
                        total += parseInt(item.total_marks);
                        obtained += parseInt(item.marks_obtained);
                    });
                    $('#marksTable').html(tbody);

                    // Totals
                    let percentage = ((obtained / total) * 100).toFixed(2);
                    let grade = percentage >= 90 ? "A+" :
                        percentage >= 80 ? "A" :
                        percentage >= 70 ? "B" :
                        percentage >= 60 ? "C" :
                        percentage >= 50 ? "D" : "F";
                    let status = grade === "F" ? "Fail" : "Pass";

                    $('#totalMarks').text(response.exam.total_marks);
                    $('#obtainedMarks').text(obtained);
                    $('#percentage').text(percentage);
                    $('#grade').text(grade);
                    $('#resultStatus').text(status)
                        .removeClass("badge-info badge-success badge-danger")
                        .addClass(status === "Pass" ? "badge-success" : "badge-danger");

                } else {
                    $('#resultCard').hide();

                    // Show real error message from PHP
                    let msg = response.message ?? "Unknown error occurred";
                    alert("Error: " + msg);

                    // (optional) debug in console
                    console.error("Exam Result Error:", response);
                }
            }, 'json')
            .fail(function(xhr, status, error) {
                // Handle server / JSON parse errors
                console.error("AJAX Error:", status, error, xhr.responseText);
                alert("Request failed: " + error);
            });
    });

});
</script>


<?php require_once 'assets/php/footer.php'; ?>