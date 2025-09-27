<?php
require_once '../sass/db_config.php';

$class_id = intval($_GET['class_id'] ?? 0);
$student_id = intval($_GET['student_id'] ?? 0);

// if student is selected -> show detailed calendar table
if($student_id){
    $month = date('m');
    $year = date('Y');
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN,$month,$year);

    // Fetch attendance for student in this month
    $q = mysqli_query($conn, "
        SELECT date, status 
        FROM student_attendance 
        WHERE class_meta_id=$class_id AND student_id=$student_id 
          AND MONTH(date)=$month AND YEAR(date)=$year
    ");
    $attendance = [];
    while($row = mysqli_fetch_assoc($q)){
        $day = date('j', strtotime($row['date']));
        $attendance[$day] = $row['status'];
    }

    echo "<div class='table-responsive'><table class='table table-striped'><thead><tr>";
    echo "<th>Period</th>";
    for($d=1;$d<=$daysInMonth;$d++){
        echo "<th>$d</th>";
    }
    echo "</tr></thead><tbody>";

    echo "<tr><td>".date('M Y')." (".date('M-Y').")</td>";
    for($d=1;$d<=$daysInMonth;$d++){
        $status = $attendance[$d] ?? 'N';
        $class = "badge-primary";
        if($status=='P') $class="badge-success";
        if($status=='A') $class="badge-danger";
        if($status=='S') $class="badge-warning";
        echo "<td><div class='badge $class'>$status</div></td>";
    }
    echo "</tr></tbody></table></div>";
    exit;
}

// If no student -> show summary of class
$q = mysqli_query($conn, "
    SELECT s.id,s.full_name,
      SUM(CASE WHEN a.status='P' THEN 1 ELSE 0 END) as presents,
      SUM(CASE WHEN a.status='A' THEN 1 ELSE 0 END) as absents,
      SUM(CASE WHEN a.status='S' THEN 1 ELSE 0 END) as short_leaves
    FROM students s
    LEFT JOIN student_attendance a ON a.student_id=s.id AND a.class_meta_id=$class_id
    WHERE s.class_grade=$class_id
    GROUP BY s.id
");

echo "<table class='table table-bordered'><thead><tr>
        <th>Student</th><th>Presents</th><th>Absents</th><th>Short Leaves</th>
      </tr></thead><tbody>";
while($row=mysqli_fetch_assoc($q)){
    echo "<tr>
            <td>{$row['full_name']}</td>
            <td><span class='badge badge-success'>{$row['presents']}</span></td>
            <td><span class='badge badge-danger'>{$row['absents']}</span></td>
            <td><span class='badge badge-warning'>{$row['short_leaves']}</span></td>
          </tr>";
}
echo "</tbody></table>";
