<?php
require_once '../sass/db_config.php';

if (!isset($_POST['faculty_id'])) {
    echo "No faculty selected";
    exit;
}

$faculty_id = intval($_POST['faculty_id']);

// Group attendance by month
$query = "
    SELECT 
        MONTH(attendance_date) as month,
        YEAR(attendance_date) as year,
        DAY(attendance_date) as day,
        status
    FROM faculty_attendance
    WHERE faculty_id = ?
    ORDER BY attendance_date
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

// Build data structure
$attendance = [];
while ($row = $result->fetch_assoc()) {
    $monthKey = $row['year'] . "-" . str_pad($row['month'], 2, "0", STR_PAD_LEFT);
    if (!isset($attendance[$monthKey])) {
        $attendance[$monthKey] = [];
    }
    $attendance[$monthKey][$row['day']] = $row['status'];
}

// Generate table
echo '<div class="table-responsive"><table class="table table-striped">';
echo '<thead><tr><th>Period</th>';
for ($d = 1; $d <= 31; $d++) {
    echo "<th>$d</th>";
}
echo '</tr></thead><tbody>';

foreach ($attendance as $month => $days) {
    $monthName = date("M Y", strtotime($month . "-01"));
    echo "<tr><td>$monthName ($month)</td>";

    for ($d = 1; $d <= 31; $d++) {
        if (isset($days[$d])) {
            $status = $days[$d];
            $badgeClass = match ($status) {
                'P' => 'success',
                'A' => 'danger',
                'L' => 'warning',
                default => 'primary', // N = Not marked
            };
            echo "<td><div class='badge badge-$badgeClass'>$status</div></td>";
        } else {
            echo "<td><div class='badge badge-primary'>N</div></td>";
        }
    }

    echo "</tr>";
}

echo '</tbody></table></div>';
