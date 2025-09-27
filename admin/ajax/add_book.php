<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) ) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); 
    exit;
}

$school_id = (int)$_SESSION['admin_id'];

$title     = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
$author    = mysqli_real_escape_string($conn, $_POST['author'] ?? '');
$publisher = mysqli_real_escape_string($conn, $_POST['publisher'] ?? '');
$isbn      = mysqli_real_escape_string($conn, $_POST['isbn'] ?? '');
$category  = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
$quantity  = (int)($_POST['quantity'] ?? 1);

$sql = "INSERT INTO books (school_id, title, author, publisher, isbn, category, quantity, available) 
        VALUES ($school_id, '$title', '$author', '$publisher', '$isbn', '$category', $quantity, $quantity)";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['status'=>'success','message'=>'Book added successfully']);
} else {
    echo json_encode(['status'=>'error','message'=>mysqli_error($conn)]);
}
