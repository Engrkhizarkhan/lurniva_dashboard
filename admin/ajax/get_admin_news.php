<?php
session_start();
require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "msg" => "Not logged in"]);
    exit;
}

$adminId = $_SESSION['admin_id'];
$today   = date("Y-m-d");

$news = [];

/* 1. Meeting Requests (pending) */
$q1 = $conn->query("SELECT id, title, created_at 
                    FROM meeting_requests 
                    WHERE status='pending' 
                    ORDER BY created_at DESC LIMIT 10");
while ($r = $q1->fetch_assoc()) {
    $news[] = [
        "title" => "📅 New meeting request: " . $r['title'],
        "link"  => "admin_meeting_requests.php",
        "date"  => $r['created_at'],
        "style" => ""
    ];
}

/* 2. New Students (last 3 days) */
$q2 = $conn->query("SELECT full_name, created_at 
                    FROM students 
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)");
while ($r = $q2->fetch_assoc()) {
    $news[] = [
        "title" => "👩‍🎓 New student joined: " . $r['full_name'],
        "link"  => "admin_students.php",
        "date"  => $r['created_at'],
        "style" => ""
    ];
}

/* 3. School Tasks (due soon = next 3 days) */
$q3 = $conn->query("SELECT task_title, due_date 
                    FROM school_tasks 
                    WHERE due_date >= CURDATE() 
                      AND due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
while ($r = $q3->fetch_assoc()) {
    $news[] = [
        "title" => "📝 Task due soon: " . $r['task_title'],
        "link"  => "admin_tasks.php",
        "date"  => $r['due_date'],
        "style" => ""
    ];
}

/* 4. Faculty Attendance (missing today) */
$q4 = $conn->query("SELECT faculty_id 
                    FROM faculty_attendance 
                    WHERE attendance_date = '$today'");
if ($q4->num_rows == 0) {
    $news[] = [
        "title" => "⚠ Faculty attendance missing today",
        "link"  => "admin_attendance.php",
        "date"  => $today,
        "style" => ""
    ];
}

/* 5. Faculty Leaves (pending) */
$q5 = $conn->query("SELECT reason, created_at 
                    FROM faculty_leaves 
                    WHERE status='pending'");
while ($r = $q5->fetch_assoc()) {
    $news[] = [
        "title" => "🏖 Faculty leave request: " . $r['reason'],
        "link"  => "admin_leaves.php",
        "date"  => $r['created_at'],
        "style" => ""
    ];
}

/* 6. Admin Meetings (today) */
$q6 = $conn->query("SELECT title, meeting_date, meeting_time 
                    FROM meeting_announcements 
                    WHERE meeting_date = '$today' 
                      AND ((meeting_person='admin' AND person_id_one='$adminId') 
                        OR (meeting_person2='admin' AND person_id_two='$adminId'))");
while ($r = $q6->fetch_assoc()) {
    $news[] = [
        "title" => "📌 Admin meeting today: " . $r['title'] . " at " . $r['meeting_time'],
        "link"  => "admin_meetings.php",
        "date"  => $r['meeting_date'],
        "style" => ""
    ];
}
/* 7. Payment Plan Expiring (within 3-4 days) */
$q7 = $conn->prepare("SELECT school_name, subscription_end 
                      FROM schools 
                      WHERE id = ?");
$q7->bind_param("i", $adminId);
$q7->execute();
$res7 = $q7->get_result();

if ($row = $res7->fetch_assoc()) {
    $endDate = $row['subscription_end'];
    if ($endDate) {
        $today   = new DateTime();
        $expiry  = new DateTime($endDate);
        
        // Get signed difference in days
        $interval = $today->diff($expiry);
        $daysLeft = (int)$interval->format("%r%a"); // e.g. +3, +10, -2
        // echo $daysLeft;
        
        // Debugging line (optional - remove later)
        // echo "DEBUG: Expiry=$endDate | DaysLeft=$daysLeft<br>";

        if ($daysLeft >= 0 && $daysLeft <= 4) {
            $news[] = [
                "title" => "⚠ Your payment plan for <b>" . htmlspecialchars($row['school_name']) . 
                           "</b> expires on <b style='color:darkred'>" . $endDate . "</b>",
                "link"  => null, // no link
                "date"  => $endDate
            ];
        }
    }
}




$conn->close();

echo json_encode(["status" => "success", "data" => $news]);