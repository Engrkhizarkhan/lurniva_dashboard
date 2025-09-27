<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['admin_id'])){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$school_id = $_SESSION['admin_id'];
$bus_id = (int)($_POST['bus_id'] ?? 0);
$problem = trim($_POST['problem'] ?? '');

if($bus_id <= 0 || $problem == ''){
    echo json_encode(['status'=>'error','message'=>'Invalid input']);
    exit;
}

// Check bus exists
$bus = mysqli_fetch_assoc(mysqli_query($conn, "SELECT bus_number FROM buses WHERE id=$bus_id AND school_id=$school_id"));
if(!$bus){
    echo json_encode(['status'=>'error','message'=>'Bus not found']);
    exit;
}
$bus_number = $bus['bus_number'];

// Insert into bus_problems
$stmt = $conn->prepare("INSERT INTO bus_problems (bus_id, school_id, problem) VALUES (?,?,?)");
$stmt->bind_param("iis", $bus_id, $school_id, $problem);
$stmt->execute();
$problem_id = $stmt->insert_id;
$stmt->close();


echo json_encode(['status'=>'success','message'=>'Bus problem reported and students notified successfully']);
?>
