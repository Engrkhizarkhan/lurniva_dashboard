<?php
session_start();
require_once '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo "Session expired.";
    exit;
}

$school_id   = $_SESSION['admin_id'];
$period_id   = $_POST['period_id']   ?? '';
$class_grade = $_POST['class_name'] ?? ''; // from your JS it's `class_name`
$student_id  = $_POST['student_id']  ?? '';

// Escape helper
function esc($conn, $str) {
    return mysqli_real_escape_string($conn, $str);
}

$period_id   = esc($conn, $period_id);
$class_grade = esc($conn, $class_grade);
$student_id  = esc($conn, $student_id);

// ===============================
// 1. Get Period Information
// ===============================
$period_query = "SELECT * FROM fee_periods WHERE school_id = '$school_id'";
if (!empty($period_id)) {
    $period_query .= " AND id = '$period_id'";
}
$period_result = mysqli_query($conn, $period_query);

if (!$period_result || mysqli_num_rows($period_result) == 0) {
    echo "<div class='alert alert-warning'>No fee periods found.</div>";
    exit;
}

while ($period = mysqli_fetch_assoc($period_result)) {
    $p_id         = $period['id'];
    $p_name       = $period['period_name'];
    $p_start_date = $period['start_date'];
    $p_end_date   = $period['end_date'];

    // ===============================
    // 2. Get Students Without Slips
    // ===============================
    // Fetch all students with their slip (if exists) for this period
$student_query = "
    SELECT s.*, f.id AS slip_id, f.total_amount, f.scholarship_amount, 
           f.net_payable, f.amount_paid, f.payment_status
    FROM students s
    LEFT JOIN fee_slip_details f 
        ON f.student_id = s.id 
        AND f.fee_period_id = '$p_id' 
        AND f.school_id = '$school_id'
    WHERE s.school_id = '$school_id'
";

// Filter by class or student if selected
if (!empty($student_id)) {
    $student_query .= " AND s.id = '$student_id'";
} elseif (!empty($class_grade)) {
    $student_query .= " AND s.class_grade = '$class_grade'";
}

    $student_result = mysqli_query($conn, $student_query);
    if (!$student_result || mysqli_num_rows($student_result) == 0) {
        echo "<div class='alert alert-info'>All students have fee slips for <b>$p_name</b>.</div>";
        continue;
    }

    // ===============================
    // 3. Print Each Student Slip
    // ===============================
    while ($student = mysqli_fetch_assoc($student_result)) {
        $student_class = $student['class_grade'];
        $stu_id        = $student['id'];

        // Fetch school details
        $school_sql = "SELECT * FROM schools WHERE id = $school_id";
        $school_res = mysqli_query($conn, $school_sql);
        $school = mysqli_fetch_assoc($school_res);

        ?>
<div class="section">
    <div class="card shadow-sm">
        <!-- School Header -->
        <div class="card-header text-center bg-primary text-white">
            <?php if (!empty($school['logo'])): ?>
            <img src="uploads/logos/<?= $school['logo'] ?>" height="80"><br>
            <?php endif; ?>
            <h4 class="mt-2 mb-1"><?= htmlspecialchars($school['school_name']) ?> (<?= $school['school_type'] ?>)</h4>
            <p class="mb-0"><?= $school['address'] ?>, <?= $school['city'] ?>, <?= $school['state'] ?>,
                <?= $school['country'] ?></p>
            <small>Email: <?= $school['school_email'] ?></small>
        </div>

        <div class="card-body">
            <!-- Student Info -->
            <div class="row mb-4 border-bottom pb-3">
                <div class="col-md-3">
                    <h6 class="text-dark">Student Information</h6>
                    <p><strong>Name:</strong> <?= $student['full_name'] ?></p>
                    <p><strong>Roll No:</strong> <?= $student['roll_number'] ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-dark">Class & Contact</h6>
                    <p><strong>Class:</strong> <?= $student['class_grade'] ?></p>
                    <p><strong>Email:</strong> <?= $student['email'] ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-dark">Other Details</h6>
                    <p><strong>Phone:</strong> <?= $student['phone'] ?></p>
                    <p><strong>Status:</strong> <?= $student['status'] ? 'Active' : 'Inactive' ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-dark">Fee Details</h6>
                    <p>Fee Slip - <?= htmlspecialchars($p_name) ?></p>
                    <p>Start Date - <?= htmlspecialchars($p_start_date) ?></p>
                    <p>End Date - <?= htmlspecialchars($p_end_date) ?></p>
                </div>
            </div>

            <!-- Class Fee -->
            <h6 class="text-primary mt-4">Class-based Fee Structure</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Fee Component</th>
                            <th class="text-right">Amount (Rs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                                $sql = "SELECT ft.fee_name, cft.rate 
                                        FROM class_fee_types cft
                                        INNER JOIN fee_types ft ON cft.fee_type_id = ft.id
                                        WHERE cft.class_grade = '$student_class' AND cft.school_id = $school_id";
                                $res = mysqli_query($conn, $sql);
                                $x = 0; $i = 1;
                                while ($row = mysqli_fetch_assoc($res)) {
                                    echo "<tr>
                                        <td>$i</td>
                                        <td>{$row['fee_name']}</td>
                                        <td class='text-right'>" . number_format($row['rate'], 2) . "</td>
                                    </tr>";
                                    $x += $row['rate']; $i++;
                                }
                                ?>
                    </tbody>
                </table>
            </div>

            <!-- Student Fee -->
            <h6 class="text-primary mt-4">Student-specific Monthly Fees</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Fee Component</th>
                            <th class="text-right">Amount (Rs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                                $sql = "SELECT ft.fee_name, sfp.base_amount
                                        FROM student_fee_plans sfp
                                        INNER JOIN fee_types ft ON sfp.fee_component = ft.id
                                        WHERE sfp.student_id = $stu_id AND sfp.school_id = $school_id AND sfp.frequency = 'monthly'";
                                $res = mysqli_query($conn, $sql);
                                $y = 0; $j = 1;
                                while ($row = mysqli_fetch_assoc($res)) {
                                    echo "<tr>
                                        <td>$j</td>
                                        <td>{$row['fee_name']}</td>
                                        <td class='text-right'>" . number_format($row['base_amount'], 2) . "</td>
                                    </tr>";
                                    $y += $row['base_amount']; $j++;
                                }
                                ?>
                    </tbody>
                </table>
            </div>

            <!-- Scholarship -->
            <?php
                    $total = $x + $y;
                    $scholarship_total = 0;

                    $sql = "SELECT type, amount FROM scholarships 
                            WHERE student_id = $stu_id AND school_id = $school_id AND status = 'approved'";
                    $res = mysqli_query($conn, $sql);

                    if ($res && mysqli_num_rows($res) > 0): ?>
            <h6 class="text-primary mt-4">Scholarship Details</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Type</th>
                            <th>Value</th>
                            <th class="text-right">Deduction (Rs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                                    while ($row = mysqli_fetch_assoc($res)) {
                                        $type = strtolower($row['type']);
                                        $amount = floatval($row['amount']);
                                        $deduction = ($type === 'percentage') ? ($amount / 100) * $total : $amount;
                                        $scholarship_total += $deduction;

                                        echo "<tr>
                                            <td>$type</td>
                                            <td>$amount</td>
                                            <td class='text-right'>" . number_format($deduction, 2) . "</td>
                                        </tr>";
                                    }
                                    ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Final Summary -->
            <?php
    // Net after scholarship
    $net_fee = $total - $scholarship_total;

    // Fetch student slip for this period (to check payments)
    $slip_sql = "SELECT amount_paid, net_payable, payment_status 
                 FROM fee_slip_details 
                 WHERE student_id = $stu_id 
                   AND fee_period_id = $p_id 
                   AND school_id = $school_id
                 LIMIT 1";
    $slip_res = mysqli_query($conn, $slip_sql);

    $status = "UNPAID";
    $paid = 0;
    $remaining = $net_fee;

    if ($slip_res && mysqli_num_rows($slip_res) > 0) {
        $slip = mysqli_fetch_assoc($slip_res);
        $paid = $slip['amount_paid'];
        $remaining = $net_fee - $paid;

        if ($paid >= $net_fee) {
            $status = "PAID";
            $remaining = 0;
        } elseif ($paid > 0) {
            $status = "PARTIALLY PAID";
        }
    }
?>
            <div class="row mt-4">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th>Total Fee</th>
                            <td class="text-right">Rs <?= number_format($total, 2) ?></td>
                        </tr>
                        <tr>
                            <th class="text-danger">Scholarship Deduction</th>
                            <td class="text-right text-danger">- Rs <?= number_format($scholarship_total, 2) ?></td>
                        </tr>
                        <tr>
                            <th>Net Payable</th>
                            <td class="text-right">Rs <?= number_format($net_fee, 2) ?></td>
                        </tr>
                        <tr>
                            <th>Paid</th>
                            <td class="text-right">Rs <?= number_format($paid, 2) ?></td>
                        </tr>
                        <tr>
                            <th class="<?= ($remaining > 0) ? 'text-danger' : 'text-success' ?>">Remaining</th>
                            <td class="text-right <?= ($remaining > 0) ? 'text-danger' : 'text-success' ?>">
                                Rs <?= number_format($remaining, 2) ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td class="text-right"><b><?= $status ?></b></td>
                        </tr>
                    </table>
                </div>
            </div>


        </div>
    </div>
</div>
<?php
    }
}
?>