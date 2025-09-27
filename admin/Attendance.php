<?php
require_once 'assets/php/header.php';
require_once 'sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo "Unauthorized";
    exit;
}

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
#attendanceData {
    padding-left: 20px;
    background-color: #f0f3ff !important;
}

#attendanceData svg {
    color: #6777ef !important;
}

#attendanceData span {
    color: #6777ef !important;
}
</style>

<div class="main-content">
    <section class="section">
        <div class="section-body container">

            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="date" class="form-control" id="attendanceDate" value="<?= date('Y-m-d') ?>" readonly>
                </div>
            </div>

            <form id="attendanceForm">
                <input type="hidden" name="attendance_date" value="<?= date('Y-m-d') ?>">

                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i data-feather="check-square" class="me-2"></i>Mark Teacher Attendance</h4>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="bg-primary text-white text-center">
                                    <tr>
                                        <th>#</th>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
          $stmt = $conn->prepare("SELECT id, full_name, phone, photo FROM faculty WHERE campus_id = ?");
          $stmt->bind_param("i", $school_id);
          $stmt->execute();
          $result = $stmt->get_result();
          $count = 1;

          while ($row = $result->fetch_assoc()) {
              $fid = $row['id'];
              $photo = !empty($row['photo']) ? "../Faculty Dashboard/uploads/{$row['photo']}" : "assets/img/default-user.png";
              echo "
              <tr class='align-middle'>
                <td class='text-center'>{$count}</td>
                <td class='text-center'>
                  <img src='{$photo}' class='rounded-circle' width='40' height='40' alt='photo'>
                </td>
                <td>{$row['full_name']}</td>
                <td>{$row['phone']}</td>
                <td>
                  <input type='hidden' name='faculty_ids[]' value='{$fid}'>
                  <div class='d-flex flex-wrap gap-2'>
                    <div class='custom-control custom-radio custom-control-inline'>
                      <input type='radio' id='present_{$fid}' name='status_{$fid}' value='Present' class='custom-control-input' checked>
                      <label class='custom-control-label' for='present_{$fid}'>Present</label>
                    </div>
                    <div class='custom-control custom-radio custom-control-inline'>
                      <input type='radio' id='absent_{$fid}' name='status_{$fid}' value='Absent' class='custom-control-input'>
                      <label class='custom-control-label' for='absent_{$fid}'>Absent</label>
                    </div>
                    <div class='custom-control custom-radio custom-control-inline'>
                      <input type='radio' id='leave_{$fid}' name='status_{$fid}' value='Leave' class='custom-control-input'>
                      <label class='custom-control-label' for='leave_{$fid}'>Leave</label>
                    </div>
                  </div>
                </td>
              </tr>";
              $count++;
          }
          ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-success">
                            <i data-feather="send"></i> Submit Attendance
                        </button>
                    </div>
                </div>

            </form>

            <div id="message"></div>
        </div>
    </section>
</div>

<script>
$('#attendanceForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'ajax/save_faculty_attendance.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            $('#message').html(`<div class="alert alert-success mt-3">${response}</div>`);
        },
        error: function() {
            $('#message').html(`<div class="alert alert-danger mt-3">Something went wrong.</div>`);
        }
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>