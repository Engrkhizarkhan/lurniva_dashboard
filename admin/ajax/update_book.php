<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); 
    exit;
}

$school_id = (int)($_SESSION['admin_id'] ?? 0); // Added school_id from session
$id        = (int)($_POST['id'] ?? 0);
$title     = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
$author    = mysqli_real_escape_string($conn, $_POST['author'] ?? '');
$publisher = mysqli_real_escape_string($conn, $_POST['publisher'] ?? '');
$isbn      = mysqli_real_escape_string($conn, $_POST['isbn'] ?? '');
$category  = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
$quantity  = (int)($_POST['quantity'] ?? 1);

// Update query with school_id condition
$sql = "UPDATE books 
        SET title='$title',
            author='$author',
            publisher='$publisher',
            isbn='$isbn',
            category='$category',
            quantity=$quantity,
            available=$quantity
        WHERE id=$id AND school_id=$school_id";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['status'=>'success','message'=>'Book updated successfully']);
} else {
    echo json_encode(['status'=>'error','message'=>mysqli_error($conn)]);
}
