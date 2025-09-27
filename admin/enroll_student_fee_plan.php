<?php
require_once 'assets/php/header.php';?>
<?php
session_start();
include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$school_id = $_SESSION['admin_id']; // adjust if using campus_id or student_id

// Fetch school settings
$sql = "SELECT fee_enabled FROM school_settings WHERE person = 'school' AND person_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$settings = $result->fetch_assoc();
$stmt->close();

// 🚨 If fee is disabled
if (!$settings || $settings['fee_enabled'] == 0) {
    echo "<script>alert('Fee module is disabled by school admin.'); window.location.href='logout.php';</script>";
    exit;
}
?>

<style>
#fee_type {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#fee_type ul {
    display: block !important;
}

#fee_type svg {
    color: #6777ef !important;
}

#fee_type span {
    color: #6777ef !important;
}

#enroll_student_fee_plan {
    color: #000;
}
</style>
<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Enroll Student Fee Plan</h2>
        </div>
        <form id="student_fee_plan_form">

            <!-- Hidden school ID -->
            <input type="hidden" id="school_id" name="school_id" value="<?php echo $_SESSION['admin_id']; ?>">

            <div class="mb-3">
                <label class="">Student</label>
                <select class="form-control select2" id="student_id" name="student_id" required>
                    <option value="">Select Student</option>
                    <?php
      require_once 'sass/db_config.php';
      session_start();
      $school_id = $_SESSION['admin_id'];

      $students = $conn->query("SELECT id, full_name FROM students WHERE school_id = $school_id ");
      while ($row = $students->fetch_assoc()) {
          echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['full_name']).'</option>';
      }
      ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="">Fee Component</label>
                <select class="form-control select2" id="fee_component" name="fee_component" required>
                    <option value="">Select Fee Type</option>
                    <?php
      $fee_types = $conn->query("SELECT id, fee_name FROM fee_types WHERE school_id = $school_id AND status = 'active'");
      while ($row = $fee_types->fetch_assoc()) {
          echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['fee_name']).'</option>';
      }
      ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="">Base Amount</label>
                <input type="number" class="form-control" id="base_amount" name="base_amount" required>
            </div>

            <div class="mb-3">
                <label class="">Frequency</label>
                <select class="form-control" id="frequency" name="frequency" required>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="one time">One Time</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>


        <div id="result" class="mt-3"></div>
        <hr>
        <h3>All Student Fee Plans</h3>
        <table class="table table-bordered" id="fee_plans_table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Fee Component</th>
                    <th>Base Amount</th>
                    <th>Frequency</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <!-- Hidden update form -->
        <div id="update_form_container" style="display:none;">
            <h3>Update Student Fee Plan</h3>
            <input type="hidden" id="update_id">
            <div class="mb-3">
                <label>Base Amount</label>
                <input type="number" id="update_base_amount" class="form-control">
            </div>
            <div class="mb-3">
                <label>Frequency</label>
                <select id="update_frequency" class="form-control">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="one time">One Time</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select id="update_status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button id="update_btn" class="btn btn-success">Update</button>
        </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    $('#student_fee_plan_form').on('submit', function(e) {
        e.preventDefault(); // prevent normal form submit

        // ✅ Get form data into variables
        var school_id = $('#school_id').val();
        var student_id = $('#student_id').val();
        var fee_component = $('#fee_component').val();
        var base_amount = $('#base_amount').val();
        var frequency = $('#frequency').val();
        var status = $('#status').val();

        // ✅ Send via AJAX POST
        $.ajax({
            url: 'ajax/enroll_student_fee_plan.php',
            method: 'POST',
            data: {
                school_id: school_id,
                student_id: student_id,
                fee_component: fee_component,
                base_amount: base_amount,
                frequency: frequency,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#result').html('<div class="alert alert-success">' + response
                        .message + '</div>');
                    $('#student_fee_plan_form')[0].reset();
                } else {
                    $('#result').html('<div class="alert alert-danger">' + response
                        .message + '</div>');
                }
            },
            error: function() {
                $('#result').html(
                    '<div class="alert alert-danger">AJAX request failed.</div>');
            }
        });
    });
    $(document).ready(function() {

        function loadFeePlans() {
            $.get('ajax/fetch_student_fee_plans.php', function(res) {
                if (res.status === 'success') {
                    let rows = '';
                    res.plans.forEach(plan => {
                        rows += `<tr>
  <td>${plan.id}</td>
  <td>${plan.full_name}</td>
  <td>${plan.fee_component}</td>
  <td>${plan.base_amount}</td>
  <td>${plan.frequency}</td>
  <td>${plan.status}</td>
  <td>
    <button class="btn btn-primary btn-sm edit-plan"
      data-id="${plan.id}"
      data-base_amount="${plan.base_amount}"
      data-frequency="${plan.frequency}"
      data-status="${plan.status}">Edit</button>
    <button class="btn btn-danger btn-sm delete-plan"
      data-id="${plan.id}">Delete</button>
  </td>
</tr>`;

                    });
                    $('#fee_plans_table tbody').html(rows);
                } else {
                    alert(res.message);
                }
            }, 'json');
        }

        loadFeePlans();

        // Open update form
        $(document).on('click', '.edit-plan', function() {
            $('#update_id').val($(this).data('id'));
            $('#update_base_amount').val($(this).data('base_amount'));
            $('#update_frequency').val($(this).data('frequency'));
            $('#update_status').val($(this).data('status'));
            $('#update_form_container').show();
        });

        // Update AJAX
        $('#update_btn').click(function() {
            let id = $('#update_id').val();
            let base_amount = $('#update_base_amount').val();
            let frequency = $('#update_frequency').val();
            let status = $('#update_status').val();

            $.post('ajax/update_student_fee_plan.php', {
                id: id,
                base_amount: base_amount,
                frequency: frequency,
                status: status
            }, function(res) {
                if (res.status === 'success') {
                    alert('Updated!');
                    $('#update_form_container').hide();
                    loadFeePlans();
                } else {
                    alert(res.message);
                }
            }, 'json');
        });

    });
    // DELETE handler
    $(document).on('click', '.delete-plan', function() {
        if (confirm('Are you sure you want to delete this fee plan?')) {
            let id = $(this).data('id');

            $.post('ajax/delete_student_fee_plan.php', {
                id: id
            }, function(res) {
                if (res.status === 'success') {
                    alert('Deleted!');
                    loadFeePlans()
                } else {
                    alert(res.message);
                }
            }, 'json');
        }
    });


});
</script>
<?php require_once 'assets/php/footer.php'; ?>