<?php
session_start();
require_once '../sass/db_config.php';

$admin_id = $_SESSION['admin_id'] ?? 0;

// Get all fee types for the school
$feeTypes = [];
$feeTypeQuery = $conn->query("SELECT id, fee_name FROM fee_types WHERE school_id = $admin_id");
while ($row = $feeTypeQuery->fetch_assoc()) {
    $feeTypes[$row['id']] = $row['fee_name'];
}
$feeTypeIds = array_keys($feeTypes);

// Start HTML table
echo "<table class='table table-bordered table-striped'>";
echo "<thead><tr class='table-dark'>";
echo "<th>Student Name</th><th>Class</th><th>Roll No</th>";
foreach ($feeTypes as $feeName) {
    echo "<th>$feeName</th>";
}
echo "<th>Total</th><th>Scholarship</th><th>Final Payable</th>";
echo "</tr></thead><tbody>";

$grandTotal = 0;
$grandPayable = 0;

// Get students grouped by class
$studentQuery = $conn->query("SELECT * FROM students WHERE school_id = $admin_id ORDER BY class_grade, full_name");
while ($student = $studentQuery->fetch_assoc()) {
    $studentId = $student['id'];
    $class = $student['class_grade'];
    $studentTotal = 0;

    // Initialize fee rows
    $feeRow = array_fill_keys($feeTypeIds, 0);

    // Class Fee Structure
    $classFeeQuery = $conn->query("SELECT * FROM class_fee_types WHERE school_id = $admin_id AND class_grade = '{$class}'");
    while ($cf = $classFeeQuery->fetch_assoc()) {
        $feeRow[$cf['fee_type_id']] = $cf['rate'];
    }

    // Student-specific Fee Plan overrides
    $studentFeeQuery = $conn->query("SELECT * FROM student_fee_plans WHERE school_id = $admin_id AND student_id = $studentId AND status = 'active'");
    while ($sf = $studentFeeQuery->fetch_assoc()) {
        $feeRow[$sf['fee_component']] = $sf['base_amount'];
    }

    // Calculate Total
    foreach ($feeTypeIds as $fid) {
        $studentTotal += $feeRow[$fid];
    }

    // Fetch Scholarship (if any)
    $scholarshipQuery = $conn->query("SELECT * FROM scholarships WHERE school_id = $admin_id AND student_id = $studentId AND status = 'approved' ORDER BY created_at DESC LIMIT 1");
    $scholarshipAmount = 0;
    $scholarshipLabel = '-';

    if ($scholarshipQuery->num_rows > 0) {
        $s = $scholarshipQuery->fetch_assoc();
        if ($s['type'] === 'percentage') {
            $scholarshipAmount = round(($studentTotal * $s['amount']) / 100);
            $scholarshipLabel = $s['amount'] . '%';
        } else {
            $scholarshipAmount = $s['amount'];
            $scholarshipLabel = number_format($s['amount']);
        }
    }

    $finalAmount = max(0, $studentTotal - $scholarshipAmount);

    echo "<tr>";
    echo "<td>{$student['full_name']}</td>";
    echo "<td>{$student['class_grade']}</td>";
    echo "<td>{$student['roll_number']}</td>";

    foreach ($feeTypeIds as $fid) {
        echo "<td>" . number_format($feeRow[$fid]) . "</td>";
    }

    echo "<td>" . number_format($studentTotal) . "</td>";
    echo "<td>$scholarshipLabel</td>";
    echo "<td><strong>" . number_format($finalAmount) . "</strong></td>";
    echo "</tr>";

    $grandTotal += $studentTotal;
    $grandPayable += $finalAmount;
}

echo "</tbody>";

// Footer
echo "<tfoot><tr class='table-info'>";
echo "<th colspan='" . (3 + count($feeTypeIds)) . "' class='text-end'>Grand Total:</th>";
echo "<th>" . number_format($grandTotal) . "</th>";
echo "<th>-</th>";
echo "<th>" . number_format($grandPayable) . "</th>";
echo "</tr></tfoot>";

echo "</table>";