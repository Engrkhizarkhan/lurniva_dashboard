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
<style>
#timetable {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#timetable ul {
    display: block !important;
}

#seeTT {
    color: #000;
}
</style>
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>All Timetables</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Timetable Overview</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="timetable-container">
                        <!-- AJAX content will load here -->
                    </div>
                </div>
                <div class="card-footer text-right">
                    <!-- Optional Pagination -->
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let fullTimetableData = [];

$(document).ready(function() {
    loadTimetables();
});

function loadTimetables() {
    $.ajax({
        url: 'ajax/view_all_time_data.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            fullTimetableData = data;

            if (data.length === 0) {
                $('#timetable-container').html(
                    '<div class="alert alert-warning">No timetables found.</div>');
                return;
            }

            let html = '<table class="table table-bordered table-md">';
            html +=
                '<thead><tr><th>#</th><th>Class</th><th>Section</th><th>Max Periods</th><th>Actions</th></tr></thead><tbody>';

            data.forEach(function(classBlock, index) {
                html += `<tr>
                    <td>${index + 1}</td>
                    <td>${classBlock.class_name}</td>
                    <td>${classBlock.section}</td>
                    <td>${classBlock.max_periods}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="showTimetableDetails(${index})">Detail</button>
                        <button class="btn btn-danger btn-sm ms-1" onclick="deleteTimetable(${classBlock.timing_table_id})">Delete</button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';
            $('#timetable-container').html(html);
        },
        error: function() {
            $('#timetable-container').html(
                '<div class="alert alert-danger">Failed to load timetables.</div>');
        }
    });
}

function showTimetableDetails(index) {
    const classBlock = fullTimetableData[index];
    let html = `<h5>${classBlock.class_name} - ${classBlock.section}</h5>`;
    html += '<table class="table table-bordered"><thead><tr><th>Day</th>';

    for (let p = 1; p <= classBlock.max_periods; p++) {
        html += `<th>Period ${p}</th>`;
    }

    html += '</tr></thead><tbody>';

    classBlock.days.forEach(function(day) {
        html += `<tr><td><strong>${day.name}</strong></td>`;

        for (let p = 1; p <= classBlock.max_periods; p++) {
            if (day.periods.hasOwnProperty(p)) {
                const per = day.periods[p];
                html += `<td>
                    <div><strong>${per.period_name}</strong></div>
                    <div>${per.start_time} - ${per.end_time}</div>
                    <div><small>${per.teacher_name}</small></div>
                    <div><em class="text-muted">(${per.period_type})</em></div>
                </td>`;
            } else {
                html += '<td class="text-muted text-center">--</td>';
            }
        }

        html += '</tr>';
    });

    html += '</tbody></table>';
    $('#timetable-container').html(html);
}

function deleteTimetable(timetableId) {
    if (confirm("Are you sure you want to delete this timetable?")) {
        $.ajax({
            url: 'ajax/delete_timetable.php',
            method: 'POST',
            data: {
                timing_table_id: timetableId
            },
            success: function(response) {
                alert(response);
                loadTimetables(); // Reload after delete
            },
            error: function() {
                alert("Error deleting timetable.");
            }
        });
    }
}
</script>

<?php require_once 'assets/php/footer.php'; ?>