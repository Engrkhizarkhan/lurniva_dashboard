<?php
header('Content-Type: text/html');

require '../sass/db_config.php';

if (isset($_POST['fee_structure_id'])) {
    $fee_structure_id = intval($_POST['fee_structure_id']);

    $query = "
        SELECT 
            cf.id,
            cf.fee_structure_id,
            cf.school_id,
            cf.class_grade,
            cf.fee_type_id,
            cf.rate,
            ft.fee_name
        FROM 
            class_fee_types cf
        JOIN 
            fee_types ft ON cf.fee_type_id = ft.id
        WHERE 
            cf.fee_structure_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $fee_structure_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>