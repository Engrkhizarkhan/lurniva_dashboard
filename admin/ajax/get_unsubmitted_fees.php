<?php
session_start();
require_once '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo "Session expired.";
    exit;
}

$school_id = $_SESSION['admin_id'];

$period_id = $_POST['period_id'] ?? '';
$class_grade = $_POST['class_grade'] ?? '';
$student_id = $_POST['student_id'] ?? '';

// Function to escape input
function esc($conn, $str) {
    return mysqli_real_escape_string($conn, $str);
}

// Escape values
$period_id = esc($conn, $period_id);
$class_grade = esc($conn, $class_grade);
$student_id = esc($conn, $student_id);

// Where school_id
$school_condition = "AND school_id = '$school_id'";

// 1. PERIOD ID only
// 1. PERIOD ID only
if (!empty($period_id) && empty($student_id) && empty($class_grade)) {
    $period_query = "
        SELECT * 
        FROM fee_periods p
        WHERE p.id = '$period_id'
          AND p.school_id = '$school_id'
          AND EXISTS (
              SELECT 1 
              FROM students s
              WHERE s.school_id = p.school_id
                AND NOT EXISTS (
                    SELECT 1 
                    FROM fee_slip_details f
                    WHERE f.fee_period_id = p.id
                      AND f.student_id = s.id
                      AND f.school_id = p.school_id
                )
          )";
}


// 2. STUDENT ID only
elseif (!empty($student_id) && empty($period_id) && empty($class_grade)) {
    $period_query = "SELECT * FROM fee_periods WHERE id NOT IN (
        SELECT fee_period_id FROM fee_slip_details 
        WHERE student_id = '$student_id' AND school_id = '$school_id'
    ) $school_condition";
}

// 3. CLASS GRADE only
elseif (!empty($class_grade) && empty($period_id) && empty($student_id)) {
    $period_query = "SELECT * FROM fee_periods WHERE id NOT IN (
        SELECT fee_period_id FROM fee_slip_details 
        WHERE school_id = '$school_id'
    ) $school_condition";
}

// 4. PERIOD ID + STUDENT ID
elseif (!empty($period_id) && !empty($student_id) && empty($class_grade)) {
    $period_query = "SELECT * FROM fee_periods WHERE id = '$period_id' AND id NOT IN (
        SELECT fee_period_id FROM fee_slip_details 
        WHERE student_id = '$student_id' AND school_id = '$school_id'
    ) $school_condition";
}

// 5. PERIOD ID + CLASS GRADE
elseif (!empty($period_id) && !empty($class_grade) && empty($student_id)) {
    $period_query = "SELECT * FROM fee_periods WHERE id = '$period_id' AND id NOT IN (
        SELECT fee_period_id FROM fee_slip_details 
        WHERE school_id = '$school_id'
    ) $school_condition";
}

// 6. Show all
else {
    $period_query = "SELECT * FROM fee_periods WHERE id NOT IN (
        SELECT fee_period_id FROM fee_slip_details 
        WHERE school_id = '$school_id'
    ) $school_condition";
}

$period_result = mysqli_query($conn, $period_query);

if (mysqli_num_rows($period_result) > 0) {
    while ($period = mysqli_fetch_assoc($period_result)) {
        $p_id = $period['id'];
        $p_name = $period['period_name'];
        $p_start_date = $period['start_date'];
        $p_end_date = $period['end_date'];

        // Build student query inside loop
        $student_query = "SELECT * FROM students WHERE id NOT IN (
            SELECT student_id FROM fee_slip_details 
            WHERE fee_period_id = '$p_id' AND school_id = '$school_id'
        ) AND school_id = '$school_id'";

        if (!empty($student_id)) {
            $student_query .= " AND id = '$student_id'";
        } elseif (!empty($class_grade)) {
            $student_query .= " AND class_grade = '$class_grade'";
        }

        $student_result = mysqli_query($conn, $student_query);

        if (mysqli_num_rows($student_result) > 0) {
            while ($student = mysqli_fetch_assoc($student_result)) {

                
// Fetch all school records
$sql = "SELECT * FROM `schools` WHERE `id`=$school_id";
$result = mysqli_query($conn, $sql);

// Display results
if (mysqli_num_rows($result) > 0) {
   $student_result = mysqli_query($conn, $student_query);

        while ($student = mysqli_fetch_assoc($student_result)) {
            $student_class = $student['class_grade'];
            $student_id = $student['id'];

            $school_sql = "SELECT * FROM schools WHERE id = $school_id";
            $school_res = mysqli_query($conn, $school_sql);
            $school = mysqli_fetch_assoc($school_res);
?>

<div class="section">


    <div class="card shadow-sm">
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
                    <p class="mb-1"><strong>Name:</strong> <?= $student['full_name'] ?></p>
                    <p class="mb-1"><strong>Roll No:</strong> <?= $student['roll_number'] ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-dark">Class & Contact</h6>
                    <p class="mb-1"><strong>Class:</strong> <?= $student['class_grade'] ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= $student['email'] ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-dark">Other Details</h6>
                    <p class="mb-1"><strong>Phone:</strong> <?= $student['phone'] ?></p>
                    <p class="mb-1"><strong>Status:</strong> <?= $student['status'] ? 'Active' : 'Inactive' ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-dark">Fee Details</h6>
                    <p class="mb-1">Fee Slip - <?= htmlspecialchars($p_name) ?></p>
                    <p class="mb-1">Fee Start Date - <?= htmlspecialchars($p_start_date) ?></p>
                    <p class="mb-1">Fee End Data - <?= htmlspecialchars($p_end_date) ?></p>

                </div>
            </div>

            <!-- Class Fee Structure -->
            <h6 class="text-primary mt-4">Class-based Fee Structure</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Fee Component</th>
                            <th class="text-right">Amount (Rs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT fee_types.fee_name, class_fee_types.rate 
                                FROM class_fee_types 
                                INNER JOIN fee_types ON class_fee_types.fee_type_id = fee_types.id 
                                WHERE class_fee_types.class_grade = $student_class AND class_fee_types.school_id = $school_id";
                        $result = mysqli_query($conn, $sql);
                        $x = 0; $i = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
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

            <!-- Student Monthly Fees -->
            <h6 class="text-primary mt-4">Student-specific Monthly Fees</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
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
                                FROM student_fee_plans AS sfp
                                INNER JOIN fee_types AS ft ON sfp.fee_component = ft.id
                                WHERE sfp.student_id = $student_id AND sfp.school_id = $school_id AND sfp.frequency = 'monthly'";
                        $result = mysqli_query($conn, $sql);
                        $y = 0; $j = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
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

            $sql = "SELECT type, amount FROM scholarships WHERE student_id = $student_id AND school_id = $school_id";
            $result = mysqli_query($conn, $sql);
            $scholarship_total = 0;
            if (mysqli_num_rows($result) > 0):
            ?>
            <h6 class="text-primary mt-4">Scholarship Details</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Type</th>
                            <th>Value</th>
                            <th class="text-right">Deduction (Rs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($result)) {
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
            <div class="row mt-4">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th class="text-left">Total Fee</th>
                            <td class="text-right">Rs<?= number_format($total, 2) ?></td>
                        </tr>
                        <tr>
                            <th class="text-left text-danger">Scholarship Deduction</th>
                            <td class="text-right text-danger">- Rs<?= number_format($scholarship_total, 2) ?></td>
                        </tr>
                        <tr>
                            <th class="text-left text-success h5">Net Payable</th>
                            <td class="text-right text-success h5">
                                Rs<?= number_format($total - $scholarship_total, 2) ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>



        </div>
    </div>
</div>

<?php
        }
}}}}}
?>