<?php
require_once '../sass/db_config.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    exit("Unauthorized access");
}

$student_id = $_SESSION['student_id'];
$year = date("Y");

// 1. Get all fee periods of selected year
$periods = $conn->query("
    SELECT * FROM fee_periods 
    WHERE YEAR(start_date) = '$year'
    ORDER BY start_date ASC
");

if ($periods->num_rows === 0) {
    exit("<p>No periods found for $year</p>");
}
?>

<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h4>Attendance Report (<?= $year ?>)</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <?php for ($d = 1; $d <= 31; $d++): ?>
                            <th><?= $d ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
          // Loop periods
          while ($period = $periods->fetch_assoc()): 
              $start_date = $period['start_date'];
              $end_date   = $period['end_date'];
              $month      = date("m", strtotime($start_date));
              $yearSel    = date("Y", strtotime($start_date));
              $daysInMonth = date("t", strtotime($start_date));

              // Attendance
              $sql = "SELECT date, status FROM student_attendance 
                      WHERE student_id = '$student_id' 
                      AND date BETWEEN '$start_date' AND '$end_date'";
              $result = $conn->query($sql);

              $attendance = [];
              while ($row = $result->fetch_assoc()) {
                  $day = (int)date("j", strtotime($row['date']));
                  $attendance[$day] = strtoupper(substr($row['status'], 0, 1));
              }
          ?>
                        <tr>
                            <td>
                                <?= $period['period_name'] ?> (<?= date("M-Y", strtotime($start_date)) ?>)
                            </td>
                            <?php for ($d = 1; $d <= 31; $d++): ?>
                            <?php if ($d > $daysInMonth): ?>
                            <td>-</td>
                            <?php else: 
                  $currentDate = "$yearSel-$month-" . str_pad($d, 2, "0", STR_PAD_LEFT);
                  $val = "N"; // default

                  if (date("w", strtotime($currentDate)) == 0) {
                      $val = "S"; 
                  } elseif (isset($attendance[$d])) {
                      $val = $attendance[$d]; 
                  }

                  // Map to badge classes
                  $badgeClass = [
                      "P" => "badge badge-success",
                      "A" => "badge badge-danger",
                      "L" => "badge badge-info",
                      "S" => "badge badge-warning", // orange (Bootstrap "warning")
                      "N" => "badge badge-primary"  // blue
                  ][$val];
                ?>
                            <td>
                                <div class="<?= $badgeClass ?>"><?= $val ?></div>
                            </td>
                            <?php endif; ?>
                            <?php endfor; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>