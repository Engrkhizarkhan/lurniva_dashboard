<?php
session_start();
require_once '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo "Unauthorized";
    exit;
}

$school_id   = $_SESSION['admin_id'];
$student_id  = intval($_POST['student_id'] ?? 0);
$period_id   = intval($_POST['period_id'] ?? 0);

// Fetch school details
$school = $conn->query("SELECT * FROM schools WHERE id = $school_id")->fetch_assoc();

// Fetch student details
$student = $conn->query("SELECT * FROM students WHERE id = $student_id AND school_id = $school_id")->fetch_assoc();
if (!$student) {
    echo "<div class='alert alert-danger'>Student not found.</div>";
    exit;
}

// Fetch fee period
$period = $conn->query("SELECT * FROM fee_periods WHERE id = $period_id AND school_id = $school_id")->fetch_assoc();
if (!$period) {
    echo "<div class='alert alert-danger'>Fee Period not found.</div>";
    exit;
}

// Fetch installments
$sql = "SELECT * FROM fee_installments 
        WHERE student_id=$student_id AND fee_period_id=$period_id AND school_id=$school_id 
        ORDER BY installment_number ASC";
$res = $conn->query($sql);

if ($res->num_rows == 0) {
    echo "<div class='alert alert-warning'>No installments created for this student.</div>";
    exit;
}

// Loop each installment -> separate slip
while ($inst = $res->fetch_assoc()) {
?>
<div class="card shadow-sm mb-4" style="page-break-after: always;">
    <div class="card-header text-center bg-primary text-white">
        <?php if (!empty($school['logo'])): ?>
        <img src="uploads/logos/<?= $school['logo'] ?>" height="60"><br>
        <?php endif; ?>
        <h4 class="mt-2"><?= htmlspecialchars($school['school_name']) ?> (<?= $school['school_type'] ?>)</h4>
        <small><?= $school['address'] ?>, <?= $school['city'] ?> | <?= $school['school_email'] ?></small>
    </div>

    <div class="card-body">
        <!-- Student Info -->
        <h6><strong>Student:</strong> <?= $student['full_name'] ?> (Roll: <?= $student['roll_number'] ?>)</h6>
        <h6><strong>Class:</strong> <?= $student['class_grade'] ?></h6>
        <h6><strong>Fee Period:</strong> <?= $period['period_name'] ?> (<?= $period['start_date'] ?> -
            <?= $period['end_date'] ?>)</h6>

        <hr>
        <!-- Installment Info -->
        <h6 class="text-primary">Installment #<?= $inst['installment_number'] ?></h6>
        <table class="table table-bordered table-sm">
            <tr>
                <th>Amount</th>
                <td class="text-right">Rs <?= number_format($inst['amount'], 2) ?></td>
            </tr>
            <tr>
                <th>Due Date</th>
                <td><?= $inst['due_date'] ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?= $inst['status'] ?></td>
            </tr>
        </table>

        <p class="text-muted"><small>Generated on <?= date("Y-m-d") ?></small></p>
    </div>
</div>
<?php
}
?>