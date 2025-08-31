<?php
session_start();
require '../sass/db_config.php';

$data = $_POST;

$note_id = $data['note_id'];
$title = $data['title'];
$content = $data['content'];
$school_id = $data['school_id'];
$author_id = $data['author_id'];
$author_role = $data['author_role'];

if ($note_id) {
    // Update
    $stmt = $conn->prepare("UPDATE notes_board SET title=?, content=?, updated_at=NOW() WHERE id=? AND author_id=?");
    $stmt->bind_param("ssii", $title, $content, $note_id, $author_id);
    $stmt->execute();
    echo json_encode(["status" => "success", "message" => "Note updated."]);
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO notes_board (school_id, title, content, author_id, author_role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $school_id, $title, $content, $author_id, $author_role);
    $stmt->execute();
    echo json_encode(["status" => "success", "message" => "Note added."]);
}
