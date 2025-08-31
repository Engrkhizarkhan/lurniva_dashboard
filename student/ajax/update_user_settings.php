<?php
session_start();
require '../sass/db_config.php';

if (!isset($_SESSION['student_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

$admin_id = $_SESSION['student_id'];

// POST params
$layout = isset($_POST['layout']) ? $_POST['layout'] : null;
$sidebar_color = isset($_POST['sidebar_color']) ? $_POST['sidebar_color'] : null;
$color_theme = isset($_POST['color_theme']) ? $_POST['color_theme'] : null;
$mini_sidebar = isset($_POST['mini_sidebar']) ? $_POST['mini_sidebar'] : null;
$sticky_header = isset($_POST['sticky_header']) ? $_POST['sticky_header'] : null;

// Build dynamic update fields
$fields = [];
$params = [];
$types = "";

if ($layout !== null) {
    $fields[] = "layout = ?";
    $params[] = $layout;
    $types .= "i";
}
if ($sidebar_color !== null) {
    $fields[] = "sidebar_color = ?";
    $params[] = $sidebar_color;
    $types .= "i";
}
if ($color_theme !== null) {
    $fields[] = "color_theme = ?";
    $params[] = $color_theme;
    $types .= "s";
}
if ($mini_sidebar !== null) {
    $fields[] = "mini_sidebar = ?";
    $params[] = $mini_sidebar;
    $types .= "i";
}
if ($sticky_header !== null) {
    $fields[] = "sticky_header = ?";
    $params[] = $sticky_header;
    $types .= "i";
}

if (count($fields) === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No valid fields to update."
    ]);
    exit;
}

// First check if row exists
$check_sql = "SELECT id FROM school_settings WHERE person = 'student' AND person_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $admin_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    // Row exists — perform UPDATE
    $fields[] = "updated_at = NOW()";
    $sql = "UPDATE school_settings SET " . implode(", ", $fields) . " WHERE person = 'student' AND person_id = ?";
    $params[] = $admin_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            "status" => "error",
            "message" => "Prepare failed: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Settings updated successfully."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update settings."
        ]);
    }
    $stmt->close();
} else {
    // Row does not exist — perform INSERT
    $columns = ["person", "person_id"];
    $insert_params = ["student", $admin_id];
    $insert_types = "si";

    if ($layout !== null) {
        $columns[] = "layout";
        $insert_params[] = $layout;
        $insert_types .= "i";
    }
    if ($sidebar_color !== null) {
        $columns[] = "sidebar_color";
        $insert_params[] = $sidebar_color;
        $insert_types .= "i";
    }
    if ($color_theme !== null) {
        $columns[] = "color_theme";
        $insert_params[] = $color_theme?$color_theme:'white';
        $insert_types .= "s";
    }
    if ($mini_sidebar !== null) {
        $columns[] = "mini_sidebar";
        $insert_params[] = $mini_sidebar;
        $insert_types .= "i";
    }
    if ($sticky_header !== null) {
        $columns[] = "sticky_header";
        $insert_params[] = $sticky_header;
        $insert_types .= "i";
    }

    $columns[] = "created_at";
    $columns[] = "updated_at";
    $insert_params[] = date('Y-m-d H:i:s');
    $insert_params[] = date('Y-m-d H:i:s');
    $insert_types .= "ss";

    $sql = "INSERT INTO school_settings (" . implode(", ", $columns) . ") VALUES (" . str_repeat("?,", count($columns)-1) . "?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            "status" => "error",
            "message" => "Prepare failed: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param($insert_types, ...$insert_params);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Settings created successfully."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to create settings."
        ]);
    }
    $stmt->close();
}

$check_stmt->close();
$conn->close();