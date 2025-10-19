<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

try {
    require '../sass/db_config.php';
    
    // Check if admin_id is set in session
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('Admin ID not found in session');
    }
    
    $admin_id = $_SESSION['admin_id'];
    
    // Validate admin_id
    if (empty($admin_id) || !is_numeric($admin_id)) {
        throw new Exception('Invalid admin ID: ' . $admin_id);
    }
    
    $sql = "SELECT * FROM notes_board WHERE school_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if (!$res) {
        throw new Exception('Query execution failed: ' . $stmt->error);
    }

    // Check if there are any results
    if ($res->num_rows === 0) {
        echo "<div class='alert alert-info'>No notes found.</div>";
    } else {
        while ($row = $res->fetch_assoc()) {
            echo "<div class='card mb-2'>
                <div class='card-body'>
                    <h5 class='card-title'>" . htmlspecialchars($row['title']) . "</h5>
                    <p class='card-text'>" . nl2br(htmlspecialchars($row['content'])) . "</p>
                    <small class='text-muted'>Posted on " . $row['created_at'] . "</small><br>
                    <button class='btn btn-sm btn-warning editNote' data-id='{$row['id']}' data-title=\"" . htmlspecialchars($row['title'], ENT_QUOTES) . "\" data-content=\"" . htmlspecialchars($row['content'], ENT_QUOTES) . "\">Edit</button>
                    <button class='btn btn-sm btn-danger deleteNote' data-id='{$row['id']}'>Delete</button>
                </div>
            </div>";
        }
    }
    
} catch (Exception $e) {
    error_log('Error in fetch_notes.php: ' . $e->getMessage());
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
} catch (Error $e) {
    error_log('Fatal error in fetch_notes.php: ' . $e->getMessage());
    echo "<div class='alert alert-danger'>System Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>