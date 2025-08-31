<?php
session_start();
require '../sass/db_config.php';

$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM notes_board WHERE school_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res = $stmt->get_result();

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