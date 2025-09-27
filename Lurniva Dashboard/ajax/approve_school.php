<?php
require_once "../sass/db_config.php";

header('Content-Type: application/json');

$schoolId    = isset($_POST['school_id']) ? intval($_POST['school_id']) : 0;
$planId      = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;
$numStudents = isset($_POST['numstu']) ? intval($_POST['numstu']) : 0;

if ($schoolId <= 0 || $planId <= 0 || $numStudents <= 0) {
    echo json_encode(["success" => false, "message" => "Missing or invalid data"]);
    exit;
}

// ✅ Get plan info
$planQuery = $conn->prepare("SELECT duration_days, price FROM student_payment_plans WHERE id=? AND status='Active'");
$planQuery->bind_param("i", $planId);
$planQuery->execute();
$planData = $planQuery->get_result()->fetch_assoc();

if (!$planData) {
    echo json_encode(["success" => false, "message" => "Invalid plan"]);
    exit;
}

$durationDays    = intval($planData['duration_days']);
$pricePerStudent = intval($planData['price']);
$totalPrice      = $numStudents * $pricePerStudent;

// ✅ Insert order
$stmt = $conn->prepare("
    INSERT INTO student_plan_orders (school_id, plan_id, num_students, total_price, status, created_at) 
    VALUES (?, ?, ?, ?, 'Paid', NOW())
");
$stmt->bind_param("iiii", $schoolId, $planId, $numStudents, $totalPrice);
$stmt->execute();

// ✅ Update School Subscription + num_students
$conn->query("
    UPDATE schools 
    SET 
        status = 'Approved',
        num_students = $numStudents,
        subscription_start = CASE 
            WHEN subscription_end >= CURDATE() THEN subscription_start 
            ELSE CURDATE() 
        END,
        subscription_end = CASE 
            WHEN subscription_end >= CURDATE() THEN DATE_ADD(subscription_end, INTERVAL $durationDays DAY)
            ELSE DATE_ADD(CURDATE(), INTERVAL $durationDays DAY)
        END
    WHERE id = $schoolId
");

// ✅ Update Faculty Subscription (same logic as schools)
$conn->query("
    UPDATE faculty 
    SET 
        status = 'Approved',
        subscription_start = CASE 
            WHEN subscription_end >= CURDATE() THEN subscription_start 
            ELSE CURDATE() 
        END,
        subscription_end = CASE 
            WHEN subscription_end >= CURDATE() THEN DATE_ADD(subscription_end, INTERVAL $durationDays DAY)
            ELSE DATE_ADD(CURDATE(), INTERVAL $durationDays DAY)
        END
    WHERE campus_id = $schoolId
");

// ✅ Update Students only if already approved AND subscription still active
$conn->query("
    UPDATE students 
    SET 
        subscription_end = DATE_ADD(subscription_end, INTERVAL $durationDays DAY)
    WHERE 
        school_id = $schoolId
        AND status = 'Approved'
        AND subscription_end >= CURDATE()
");

echo json_encode([
    "success" => true,
    "message" => "School approved ✅ | Subscription updated | Faculty extended | Active students extended"
]);