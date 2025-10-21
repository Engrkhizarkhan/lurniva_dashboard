<?php require_once 'assets/php/header.php'; 
require_once 'sass/db_config.php';
$school_id = $_SESSION['admin_id'];
?>

<style>
#fee_type {
    padding-left: 20px;
    background-color: #f0f3ff;
}

#fee_type svg {
    color: #6777ef !important;
}

#fee_type span {
    color: #6777ef !important;
}

#fee_type ul {
    display: block !important;
}

#enroll_scholarship {
    color: #000;
}
</style>
<!-- Main Content -->
<div class="main-content">
    <div class="container">
        <h2>Enroll Scholarship</h2>
        <form id="scholarship_form">

            <!-- Hidden school ID -->
            <input type="hidden" hidden id="school_id" name="school_id" value="<?php echo $_SESSION['admin_id']; ?>">

            <!-- Student Select -->
            <div class="mb-3">
                <label class="">Student</label>
                <select class="form-control" id="student_id" name="student_id" required>
                    <option value="">Select Student</option>
                    <?php
          $students = $conn->query("SELECT id, full_name, class_grade, section, roll_number FROM students WHERE school_id = $school_id");
          while ($row = $students->fetch_assoc()) {
            echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['full_name']).' - '.$row['class_grade'].' '.$row['section'].' (Roll: '.$row['roll_number'].')</option>';
          }
          ?>
                </select>
            </div>

            <!-- Type -->
            <div class="mb-3">
                <label class="">Type</label>
                <select class="form-control" id="type" name="type" required>
                    <option value="percentage">Percentage</option>
                    <option value="fixed">Fixed</option>
                </select>
            </div>

            <!-- Amount -->
            <div class="mb-3">
                <label class="">Amount (<span id="amount_type_label">%</span>)</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                    placeholder="Enter amount" required>
            </div>

            <!-- Reason -->
            <div class="mb-3">
                <label class="">Reason</label>
                <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
            </div>



            <button type="submit" class="btn btn-primary">Save Scholarship</button>
        </form>

        <div id="result" class="mt-3"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// Change label if type is percentage or fixed
$('#type').on('change', function() {
    $('#amount_type_label').text($(this).val() === 'percentage' ? '%' : 'Amount');
});

// Submit form via AJAX
$('#scholarship_form').on('submit', function(e) {
    e.preventDefault();

    var data = {
        school_id: $('#school_id').val(),
        student_id: $('#student_id').val(),
        type: $('#type').val(),
        amount: $('#amount').val(),
        reason: $('#reason').val(),
    };

    $.ajax({
        url: 'ajax/enroll_scholarship.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#result').html(
                    '<div class="alert alert-success">Scholarship saved successfully.</div>');
                $('#scholarship_form')[0].reset();
            } else {
                $('#result').html('<div class="alert alert-danger">Error: ' + response.message +
                    '</div>');
            }
        },
        error: function() {
            $('#result').html('<div class="alert alert-danger">AJAX request failed.</div>');
        }
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>