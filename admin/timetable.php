<style>
#timetable {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#timetable ul {
    display: block !important;
}

#createTT {
    color: #000;
}
</style>

<?php require_once 'assets/php/header.php';
include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$admin_id = $_SESSION['admin_id']; // admin ID

// Fetch admin settings
$sql = "SELECT timetable_enabled FROM school_settings WHERE person='admin' AND person_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

// 🚨 If Timetable module is disabled
if (!$settings || $settings['timetable_enabled'] == 0) {
    echo "<script>alert('Timetable module is disabled by admin settings.'); window.location.href='logout.php';</script>";
    exit;
}
?>

<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="container mt-4">
            <h3>Create Timetable</h3>
            <form id="timetableForm" method="POST">
                <!-- Assembly & Leave Time -->
                <div class="row mb-3">
                    <div class="col">
                        <label for="assembly_time">Assembly Time</label>
                        <input type="time" id="assembly_time" name="assembly_time" class="form-control" required />
                    </div>
                    <div class="col">
                        <label for="leave_time">Leave Time</label>
                        <input type="time" id="leave_time" name="leave_time" class="form-control" required />
                    </div>
                </div>

                <!-- Finalize Switch -->
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="finalizeSwitch" name="is_finalized" value="1">
                    <label class="form-check-label" for="finalizeSwitch">Finalize Timetable (Lock from editing)</label>
                </div>

                <!-- Half-Day Settings -->
                <div class="border p-3 mb-4 rounded bg-light">
                    <h5>Optional Half-Day Settings</h5>
                    <div class="row mb-2">
                        <div class="col-md-3">
                            <label for="halfDayWeekday">Weekday</label>
                            <select id="halfDayWeekday" class="form-control">
                                <option value="">-- Select --</option>
                                <option>Monday</option>
                                <option>Tuesday</option>
                                <option>Wednesday</option>
                                <option>Thursday</option>
                                <option>Friday</option>
                                <option>Saturday</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="halfDayAssembly">Assembly</label>
                            <input type="time" id="halfDayAssembly" class="form-control" />
                        </div>
                        <div class="col-md-2">
                            <label for="halfDayLeave">Leave</label>
                            <input type="time" id="halfDayLeave" class="form-control" />
                        </div>
                        <div class="col-md-2">
                            <label for="halfDayPeriods">Periods</label>
                            <input type="number" id="halfDayPeriods" class="form-control" min="1" />
                        </div>
                        <div class="col-md-3 d-grid mt-4">
                            <button type="button" id="addHalfDay" class="btn btn-warning">Add Half-Day</button>
                        </div>
                    </div>
                    <div id="halfDayPreview" class="mt-2"></div>
                    <input type="hidden" name="half_day_config" id="half_day_config" />
                </div>

                <!-- Class Sections -->
                <div id="class-sections"></div>
                <button type="button" id="addClassSection" class="btn btn-secondary mb-3">Add Class/Section</button>

                <!-- Preview -->
                <button type="button" id="previewBtn" class="btn btn-info mb-3">Preview Timetable</button>
                <div id="previewContainer" class="mb-4"></div>

                <button type="submit" id="save" class="btn btn-primary">Save Timetable</button>
            </form>
        </div>
    </section>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let classIndex = 0;
let teacherOptionsHtml = '<option value="">Loading...</option>';
const halfDayMap = {};
const weekdays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

$(document).ready(function() {

    // Load teachers from PHP once
    function loadTeachers() {
        $.ajax({
            url: 'ajax/get_teachers.php',
            type: 'POST',
            success: function(data) {
                teacherOptionsHtml = data;
                $('.teacher-dropdown').html(teacherOptionsHtml);
            },
            error: function() {
                teacherOptionsHtml = '<option value="">Error loading teachers</option>';
                $('.teacher-dropdown').html(teacherOptionsHtml);
            }
        });
    }

    loadTeachers();

    // Add new class section
    $('#addClassSection').click(function() {
        $('#class-sections').append(getClassSectionHtml(classIndex));
        $('.teacher-dropdown').last().html(teacherOptionsHtml);
        classIndex++;
    });

    // Add half-day config
    $("#addHalfDay").click(function() {
        const day = $("#halfDayWeekday").val();
        const assembly = $("#halfDayAssembly").val();
        const leave = $("#halfDayLeave").val();
        const periods = $("#halfDayPeriods").val();

        if (!day || !assembly || !leave || !periods) {
            alert("Please fill all half-day fields.");
            return;
        }

        halfDayMap[day] = {
            is_half_day: true,
            assembly_time: assembly,
            leave_time: leave,
            total_periods: parseInt(periods)
        };

        $("#half_day_config").val(JSON.stringify(halfDayMap));

        let html = "<ul class='mb-0'>";
        for (let d in halfDayMap) {
            html +=
                `<li><strong>${d}</strong>: ${halfDayMap[d].assembly_time} - ${halfDayMap[d].leave_time}, Periods: ${halfDayMap[d].total_periods}</li>`;
        }
        html += "</ul>";
        $("#halfDayPreview").html(html);
    });

    // Generate periods when period count changes
    $(document).on('input', '.period-count', function() {
        const index = $(this).data('index');
        const count = parseInt($(this).val());
        const container = $(`#periods-${index}`);
        container.html('');
        for (let i = 1; i <= count; i++) {
            container.append(getPeriodHtml(index, i));
        }
        container.find('.teacher-dropdown').html(teacherOptionsHtml);
    });

    // Preview
    $("#previewBtn").click(function() {
        generatePreview();
    });

    // Submit
    $("#save").click(function(e) {
        e.preventDefault();

        const assembly_time = $("#assembly_time").val();
        const leave_time = $("#leave_time").val();
        const is_finalized = $("#finalizeSwitch").is(":checked") ? 1 : 0;
        const half_day_config = $("#half_day_config").val();

        let all_classes = [];

        $(".class-block").each(function() {
            const index = $(this).data("index");
            const class_name = $(`#class_name_${index}`).val();
            const section = $(`#section_${index}`).val();
            const total_periods = $(`#total_periods_${index}`).val();

            let periods = [];

            $(this).find(".row.mb-2").each(function() {
                const pname = $(this).find(`input[name='period_name[${index}][]']`)
                    .val();
                const start = $(this).find(`input[name='start_time[${index}][]']`)
                    .val();
                const end = $(this).find(`input[name='end_time[${index}][]']`)
                    .val();
                const type = $(this).find(`select[name='period_type[${index}][]']`)
                    .val();
                const teacher = $(this).find(
                    `select[name='teacher_id[${index}][]']`).val();
                const isBreak = $(this).find(`input[name='is_break[${index}][]']`)
                    .is(":checked") ? 1 : 0;

                if (pname && start && end) {
                    periods.push({
                        period_name: pname,
                        start_time: start,
                        end_time: end,
                        period_type: type,
                        teacher_id: teacher,
                        is_break: isBreak
                    });
                }
            });

            if (class_name && section) {
                all_classes.push({
                    class_name: class_name,
                    section: section,
                    total_periods: total_periods,
                    periods: periods
                });
            }
        });

        $.ajax({
            url: "ajax/timetable.php",
            type: "POST",
            data: {
                assembly_time: assembly_time,
                leave_time: leave_time,
                is_finalized: is_finalized,
                half_day_config: half_day_config,
                classes: JSON.stringify(all_classes)
            },
            success: function(response) {
                console.log("Server Response:", response);
                alert(response);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                alert("AJAX Error: " + error);
            }
        });
    });
});

// Helpers
function getClassSectionHtml(index) {
    return `
<div class="border p-3 mb-4 class-block bg-light rounded" data-index="${index}">
  <div class="row">
    <div class="col">
      <label for="class_name_${index}">Class</label>
      <input type="text" id="class_name_${index}" name="class_name[${index}]" class="form-control" required />
    </div>
    <div class="col">
      <label for="section_${index}">Section</label>
      <input type="text" id="section_${index}" name="section[${index}]" class="form-control" required />
    </div>
    <div class="col">
      <label for="total_periods_${index}">Number of Periods</label>
      <input type="number" id="total_periods_${index}" name="total_periods[${index}]" class="form-control period-count" data-index="${index}" required min="1" />
    </div>
  </div>
  <div class="periods mt-3" id="periods-${index}"></div>
</div>`;
}

function getPeriodHtml(classIndex, periodNumber) {
    return `
<div class="row mb-2">
  <div class="col">
    <label>Period ${periodNumber} Name</label>
    <input type="text" name="period_name[${classIndex}][]" class="form-control" required />
  </div>
  <div class="col">
    <label>Start</label>
    <input type="time" name="start_time[${classIndex}][]" class="form-control" required />
  </div>
  <div class="col">
    <label>End</label>
    <input type="time" name="end_time[${classIndex}][]" class="form-control" required />
  </div>
  <div class="col">
    <label>Type</label>
    <select name="period_type[${classIndex}][]" class="form-control">
      <option value="Normal">Normal</option>
      <option value="Lab">Lab</option>
      <option value="Break">Break</option>
      <option value="Sports">Sports</option>
      <option value="Library">Library</option>
    </select>
  </div>
  <div class="col">
    <label>Teacher</label>
    <select name="teacher_id[${classIndex}][]" class="form-control teacher-dropdown">${teacherOptionsHtml}</select>
  </div>
  <div class="col d-flex align-items-end">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_break[${classIndex}][]" value="1">
      <label class="form-check-label">Break</label>
    </div>
  </div>
</div>`;
}

function generatePreview() {
    let previewHTML = "<h5>Timetable Preview</h5><div class='table-responsive'>";

    $(".class-block").each(function() {
        const index = $(this).data("index");
        const className = $(`#class_name_${index}`).val();
        const section = $(`#section_${index}`).val();
        const periodData = [];

        $(this).find(".row.mb-2").each(function() {
            periodData.push({
                pname: $(this).find(`input[name='period_name[${index}][]']`).val(),
                start: $(this).find(`input[name='start_time[${index}][]']`).val(),
                end: $(this).find(`input[name='end_time[${index}][]']`).val(),
                type: $(this).find(`select[name='period_type[${index}][]']`).val(),
                teacher: $(this).find(
                    `select[name='teacher_id[${index}][]'] option:selected`).text(),
                isBreak: $(this).find(`input[name='is_break[${index}][]']`).is(":checked")
            });
        });

        previewHTML += `<h6 class="mt-4">${className} - Section ${section}</h6>`;
        previewHTML += `<table class="table table-bordered text-center"><thead><tr><th>Day</th>`;
        for (let i = 0; i < periodData.length; i++) {
            previewHTML += `<th>Period ${i + 1}</th>`;
        }
        previewHTML += `</tr></thead><tbody>`;

        weekdays.forEach(day => {
            const isHalf = halfDayMap[day];
            const maxPeriods = isHalf ? isHalf.total_periods : periodData.length;

            previewHTML += `<tr><td><strong>${day}</strong></td>`;
            for (let i = 0; i < periodData.length; i++) {
                if (i >= maxPeriods) {
                    previewHTML += `<td class="text-muted">-</td>`;
                } else {
                    const pd = periodData[i];
                    const bg = pd.isBreak ? 'table-warning' : '';
                    previewHTML += `<td class="${bg}">
            <div><strong>${pd.pname || '-'}</strong></div>
            <div>${pd.type || '-'}</div>
            <div>${pd.start || '-'} - ${pd.end || '-'}</div>
            <div>${pd.teacher || '-'}</div>
          </td>`;
                }
            }
            previewHTML += `</tr>`;
        });

        previewHTML += `</tbody></table>`;
    });

    if (Object.keys(halfDayMap).length > 0) {
        previewHTML += `<h5 class="mt-4">Half-Day Configuration</h5><table class="table table-sm table-bordered">
      <thead><tr><th>Weekday</th><th>Assembly</th><th>Leave</th><th>Periods</th></tr></thead><tbody>`;
        for (let d in halfDayMap) {
            previewHTML +=
                `<tr><td>${d}</td><td>${halfDayMap[d].assembly_time}</td><td>${halfDayMap[d].leave_time}</td><td>${halfDayMap[d].total_periods}</td></tr>`;
        }
        previewHTML += `</tbody></table>`;
    }

    previewHTML += "</div>";
    $("#previewContainer").html(previewHTML);
}
</script>


<!-- Optional Script to handle form submission -->
<script>
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    alert("Schedule submitted successfully.");
    // You can add logic to save data or show preview
});
</script>
<?php require_once 'assets/php/footer.php'; ?>