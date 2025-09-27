<?php
require_once 'assets/php/header.php';
require_once 'sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo "Unauthorized";
    exit;
}
$campus_id = $_SESSION['admin_id'];
$school_id = $_SESSION['admin_id'];

// Fetch school settings
$sql = "SELECT attendance_enabled FROM school_settings WHERE person = 'admin' AND person_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$settings = $result->fetch_assoc();
$stmt->close();

// 🚨 If attendance is disabled
if (!$settings || $settings['attendance_enabled'] == 0) {
    echo "<script>alert('Attendance module is disabled by school admin.'); window.location.href='logout.php';</script>";
    exit;
}
?>
<style>
#faculty_attendance {
    padding-left: 20px;
    background-color: #f0f3ff !important;
}

#faculty_attendance svg {
    color: #6777ef !important;
}

#faculty_attendance span {
    color: #6777ef !important;
}
</style>

<!-- ✅ Load jQuery FIRST -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="main-content">
    <section class="section">
        <div class="section-body container">

            <div class="card shadow-sm">
                <div class="card-header">
                    <h4><i data-feather="calendar"></i> Faculty Attendance Report</h4>
                </div>
                <div class="card-body">

                    <!-- Faculty Select -->
                    <div class="form-group">
                        <label for="facultySelect">Select Faculty</label>
                        <select id="facultySelect" class="form-control select2" style="width:100%">
                            <option value="">-- Choose Faculty --</option>
                            <?php
                            $stmt = $conn->prepare("SELECT id, full_name FROM faculty WHERE campus_id = ?");
                            $stmt->bind_param("i", $campus_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['full_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Attendance Table Will Show Here -->
                    <div id="attendanceTable"></div>

                </div>
            </div>

        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // ✅ Initialize Select2
    $('#facultySelect').select2();

    // ✅ Event listener for Select2
    $('#facultySelect').on('select2:select change', function() {
        let facultyId = $(this).val();
        console.log("Selected Faculty ID:", facultyId); // Debug
        if (!facultyId) return;

        // ✅ Load attendance via AJAX
        $.ajax({
            url: 'ajax/get_faculty_attendance.php',
            type: 'POST',
            data: {
                faculty_id: facultyId
            },
            beforeSend: function() {
                $('#attendanceTable').html(
                    '<div class="alert alert-info">Loading...</div>');
            },
            success: function(response) {
                $('#attendanceTable').html(response);
            },
            error: function() {
                $('#attendanceTable').html(
                    '<div class="alert alert-danger">Error fetching data</div>');
            }
        });
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>