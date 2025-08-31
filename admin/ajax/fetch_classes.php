<?php
// fetch_classes.php

// Include your DB connection
header('Content-Type: text/html');

require '../sass/db_config.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT id, name FROM classes WHERE status = 'Active' ORDER BY name ASC";
    $result = $conn->query($sql);

    $classes = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $classes[] = [
                'id' => $row['id'],
                'name' => $row['name']
            ];
        }
    }

    echo json_encode($classes);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error fetching classes: ' . $e->getMessage()]);
}

$conn->close();
?>