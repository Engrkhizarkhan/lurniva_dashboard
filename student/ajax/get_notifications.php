<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

$school_id  = $_SESSION['school_id'];
$student_id = $_SESSION['student_id'];

$allData = [];

/* ---------------------------------------------------
   1️⃣ General student notifications (from notifications table)
--------------------------------------------------- */
$notifQ = mysqli_query($conn, "
    SELECT id, type, module, title, is_read, created_at
    FROM notifications
    WHERE user_id = $student_id
      AND user_type = 'student'
    ORDER BY created_at DESC
    LIMIT 10
");

while($row = mysqli_fetch_assoc($notifQ)){
    $allData[] = [
        'id'        => $row['id'],
        'title'     => $row['title'],
        'status'    => $row['is_read'] == 0 ? 'Open' : 'Read',
        'type'      => $row['type'],
        'created_at'=> $row['created_at'],
        'module'    => $row['module']
    ];
}

/* ---------------------------------------------------
   2️⃣ Bus problem notifications (last 3 hours)
--------------------------------------------------- */

// Get student route
$routeRow = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT route_id FROM transport_student_routes 
    WHERE student_id=$student_id AND school_id=$school_id
    LIMIT 1
"));

if($routeRow){
    $route_id = $routeRow['route_id'];

    // Get buses (🔔 if route_id is in buses table, filter by it)
    $bus_ids = [];
    $res = mysqli_query($conn, "SELECT id FROM buses WHERE school_id=$school_id");
    while($row = mysqli_fetch_assoc($res)){
        $bus_ids[] = $row['id'];
    }

    if(!empty($bus_ids)){
        $bus_ids_str = implode(',', $bus_ids);

        $busQ = mysqli_query($conn, "
            SELECT bp.id, b.bus_number, bp.problem, bp.status, bp.created_at
            FROM bus_problems bp
            JOIN buses b ON b.id = bp.bus_id
            WHERE bp.school_id=$school_id
              AND bp.bus_id IN ($bus_ids_str)
              AND bp.status='Open'
              AND bp.created_at >= NOW() - INTERVAL 3 HOUR
            ORDER BY bp.created_at DESC
            LIMIT 10
        ");

        while($row = mysqli_fetch_assoc($busQ)){
            $allData[] = [
                'id'        => "bus_" . $row['id'],
                'title'     => "🚨 Bus #{$row['bus_number']}: {$row['problem']}",
                'status'    => $row['status'],
                'type'      => 'bus',
                'created_at'=> $row['created_at']
            ];
        }
    }
}

/* ---------------------------------------------------
   3️⃣ Return merged notifications sorted by created_at
--------------------------------------------------- */
usort($allData, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

echo json_encode(['status'=>'success','data'=>$allData]);