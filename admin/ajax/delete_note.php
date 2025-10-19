<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type for JSON response
header('Content-Type: application/json');

session_start();

try {
    require '../sass/db_config.php';
    
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed');
    }
    
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('Admin not logged in');
    }
    
    // Validate POST data
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('Note ID is required');
    }
    
    $id = $_POST['id'];
    
    // Validate ID is numeric
    if (!is_numeric($id)) {
        throw new Exception('Invalid note ID');
    }
    
    $admin_id = $_SESSION['admin_id'];
    
    // First check if the note exists and belongs to this admin's school
    $check_sql = "SELECT id FROM digital_notices WHERE id = ? AND school_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    
    if (!$check_stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $check_stmt->bind_param("ii", $id, $admin_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Note not found or access denied');
    }
    
    // Now delete the note
    $delete_sql = "DELETE FROM notes_board WHERE id = ? AND school_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    
    if (!$delete_stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $delete_stmt->bind_param("ii", $id, $admin_id);
    
    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode([
                "status" => "success", 
                "message" => "Note deleted successfully."
            ]);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "No note was deleted. It may have already been removed."
            ]);
        }
    } else {
        throw new Exception('Failed to delete note: ' . $delete_stmt->error);
    }
    
} catch (Exception $e) {
    error_log('Error in delete_note.php: ' . $e->getMessage());
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
} catch (Error $e) {
    error_log('Fatal error in delete_note.php: ' . $e->getMessage());
    echo json_encode([
        "status" => "error", 
        "message" => "System error occurred"
    ]);
}
?>