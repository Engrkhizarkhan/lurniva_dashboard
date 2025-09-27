<?php
require_once "../sass/db_config.php";

header("Content-Type: application/json");

$action = $_REQUEST['action'] ?? "";

if ($action === "save") {
    $id = intval($_POST['id'] ?? 0);
    $plan_name = $_POST['plan_name'] ?? "";
    $description = $_POST['description'] ?? "";
    $price = intval($_POST['price'] ?? 0);
    $duration_days = intval($_POST['duration_days'] ?? 0);
    $status = $_POST['status'] ?? "Inactive";

    if ($id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE student_payment_plans 
                                SET plan_name=?, description=?, price=?, duration_days=?, status=?, updated_at=NOW()
                                WHERE id=?");
        $stmt->bind_param("ssdisi", $plan_name, $description, $price, $duration_days, $status, $id);
        $stmt->execute();
        echo json_encode(["success" => true, "message" => "Plan updated successfully"]);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO student_payment_plans (plan_name, description, price, duration_days, status, created_at, updated_at) 
                                VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssdis", $plan_name, $description, $price, $duration_days, $status);
        $stmt->execute();
        echo json_encode(["success" => true, "message" => "Plan added successfully"]);
    }
    exit;
}

if ($action === "read") {
    $result = $conn->query("SELECT * FROM student_payment_plans ORDER BY created_at DESC");
    $rows = "";
    while ($row = $result->fetch_assoc()) {
        $rows .= "<tr>
            <td>{$row['id']}</td>
            <td>{$row['plan_name']}</td>
            <td>{$row['description']}</td>
            <td>PKR {$row['price']}</td>
            <td>{$row['duration_days']} days</td>
            <td>{$row['status']}</td>
            <td>
                <button class='btn btn-sm btn-warning' onclick='editPlan({$row['id']})'>Edit</button>
                <button class='btn btn-sm btn-danger' onclick='deletePlan({$row['id']})'>Delete</button>
            </td>
        </tr>";
    }
    echo $rows;
    exit;
}

if ($action === "get") {
    $id = intval($_GET['id'] ?? 0);
    $res = $conn->query("SELECT * FROM student_payment_plans WHERE id=$id");
    echo json_encode($res->fetch_assoc());
    exit;
}

if ($action === "delete") {
    $id = intval($_POST['id'] ?? 0);
    $conn->query("DELETE FROM student_payment_plans WHERE id=$id");
    echo json_encode(["success" => true, "message" => "Plan deleted successfully"]);
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid action"]);