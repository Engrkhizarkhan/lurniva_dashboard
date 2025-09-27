<?php require_once 'assets/php/header.php'; 
include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$school_id = $_SESSION['admin_id'];

$sql = "SELECT exam_enabled FROM school_settings WHERE person='admin' AND person_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

if (!$settings || $settings['exam_enabled'] == 0) {
    echo "<script>alert('Exam module is disabled by school admin.'); window.location.href='logout.php';</script>";
    exit;
}
?>


<style>
#timetable {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#timetable ul {
    display: block !important;
}

#createAE {
    color: #000;
}
</style>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h4>Create Paper / Date Sheet</h4>
        </div>

        <form id="dateSheetForm">
            <div class="card p-4 mb-4">
                <div class="form-group">
                    <label>Exam Name</label>
                    <select class="form-control" name="exam_id" id="examSelect" required>
                        <option value="">Select Exam</option>
                    </select>
                </div>


                <div class="form-group">
                    <label>Class</label>
                    <select class="form-control" name="class_name" id="classSelect" required>
                        <option value="">Select Class</option>
                    </select>
                </div>

                <div id="dateSheetTableContainer" style="display:none;">
                    <h5 class="mt-4">Schedule</h5>
                    <table class="table table-bordered" id="dateSheetTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Day</th>
                                <th>Subject</th>
                                <th>Total Marks</th>
                                <th><button type="button" id="addRow" class="btn btn-success btn-sm">+</button></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save Date Sheet</button>
            </div>
        </form>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // ✅ Load exams in dropdown
    function loadExamsDropdown() {
        $.get("ajax/get_exams_school.php", function(res) {
            if (res.status == "success") {
                $("#examSelect").empty().append("<option value=''>Select Exam</option>");
                res.data.forEach(exam => {
                    $("#examSelect").append(
                        `<option value="${exam.id}">${exam.exam_name} (Total: ${exam.total_marks})</option>`
                    );
                });
            }
        }, "json");
    }

    // call on page load
    loadExamsDropdown();

    // ✅ Load classes via AJAX
    $.ajax({
        url: 'ajax/get_classes.php',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                res.data.forEach(c => $('#classSelect').append(
                    `<option value="${c}">${c}</option>`));
            }
        }
    });

    // ✅ Show table when class selected
    $('#classSelect').change(function() {
        let selectedClass = $(this).val();
        if (selectedClass) {
            $('#dateSheetTableContainer').show();
            $('#dateSheetTable tbody').empty(); // Clear previous rows
        } else {
            $('#dateSheetTableContainer').hide();
        }
    });

    // ✅ Add new row
    $('#addRow').click(function() {
        let className = $('#classSelect').val();
        if (!className) {
            alert('Please select a class first!');
            return;
        }

        let row = `<tr>
    <td><input type="date" class="form-control exam-date" required></td>
    <td><input type="time" class="form-control exam-time" required></td>
    <td><input type="text" class="form-control exam-day" readonly></td>
    <td>
      <select class="form-control subjectSelect" required>
        <option value="">Select Subject</option>
      </select>
    </td>
    <td><input type="number" class="form-control total-marks" min="0" required></td>
    <td><button type="button" class="btn btn-danger btn-sm removeRow">x</button></td>
</tr>`;

        $('#dateSheetTable tbody').append(row);
    });

    // ✅ Remove row
    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
    });

    // ✅ Auto-set day when date changes
    $(document).on('change', '.exam-date', function() {
        let dateVal = $(this).val();
        let day = new Date(dateVal).toLocaleDateString('en-US', {
            weekday: 'long'
        });
        $(this).closest('tr').find('.exam-day').val(day);
    });

    // ✅ Load subjects dynamically based on class
    $(document).on('focus', '.subjectSelect', function() {
        let className = $('#classSelect').val();
        let $select = $(this);
        if ($select.children('option').length === 1) { // only default option
            $.ajax({
                url: 'ajax/get_subjects.php',
                type: 'POST',
                data: {
                    class_name: className
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        res.data.forEach(sub => {
                            $select.append(
                                `<option value="${sub.id}">${sub.period_name}</option>`
                            );
                        });
                    }
                }
            });
        }
    });

    // ✅ Submit form
    $('#dateSheetForm').submit(function(e) {
        e.preventDefault();

        let rows = [];
        $('#dateSheetTable tbody tr').each(function() {
            rows.push({
                exam_date: $(this).find('.exam-date').val(),
                exam_time: $(this).find('.exam-time').val(),
                subject_id: $(this).find('.subjectSelect').val(),
                total_marks: $(this).find('.total-marks').val()
            });
        });


        $.ajax({
            url: 'ajax/save_date_sheet.php',
            type: 'POST',
            data: {
                exam_id: $('#examSelect').val(),
                class_name: $('#classSelect').val(),
                rows: rows
            },
            dataType: 'json',
            success: function(res) {
                alert(res.message || 'Saved successfully!');
                if (res.status === 'success') location.reload();
            },
            error: function(err) {
                console.error(err.responseText);
                alert("Error saving date sheet.");
            }
        });
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>