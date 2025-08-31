<?php
session_start();
require_once '../sass/db_config.php';

header('Content-Type: text/html; charset=UTF-8');

$student_id = $_SESSION['student_id'] ?? 0;
$school_id  = $_SESSION['school_id'] ?? 0;

if (!$student_id || !$school_id) {
    echo "<tr><td colspan='6'>No session found. Please log in again.</td></tr>";
    exit;
}

// SQL: Get all diary entries visible to this student
$sql = "
SELECT 
    de.id AS diary_id,
    de.subject,
    de.topic,
    de.description,
    de.attachment,
    de.deadline,
    de.parent_approval_required,
    de.student_option,
    ds.approve_parent
FROM diary_entries AS de
LEFT JOIN diary_students AS ds
    ON ds.diary_id = de.id
    AND ds.student_id = ?
WHERE de.school_id = ?
  AND (
        de.student_option = 'all'
        OR (de.student_option = 'specific' AND ds.student_id IS NOT NULL)
      )
ORDER BY de.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $school_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<tr><td colspan='6'>No diary entries found.</td></tr>";
    exit;
}

while ($row = $res->fetch_assoc()) {
    $subject   = htmlspecialchars($row['subject'] ?? '', ENT_QUOTES, 'UTF-8');
    $topic     = htmlspecialchars($row['topic'] ?? '', ENT_QUOTES, 'UTF-8');
    $desc      = htmlspecialchars($row['description'] ?? '', ENT_QUOTES, 'UTF-8');
    $deadline  = htmlspecialchars($row['deadline'] ?? '', ENT_QUOTES, 'UTF-8');

    // Attachment link
    $attachLink = "No file";
    if (!empty($row['attachment'])) {
        $filePath = "../Faculty Dashboard/uploads/results/" . rawurlencode($row['attachment']);
        $attachLink = "<a href='{$filePath}' target='_blank' class='btn btn-sm btn-primary'>Download</a>";
    }

    // Approval button / status
    $approval = "Not Required";
    if ($row['parent_approval_required'] === 'yes') {
        if (empty($row['approve_parent'])) {
            $approval = "<button class='btn btn-warning btn-sm approve-btn' data-id='{$row['diary_id']}'>Approve</button>";
        } else {
            $approval = "<span class='badge bg-success'>Approved</span>";
        }
    }

    echo "<tr>
        <td>{$subject}</td>
        <td>{$topic}</td>
        <td>{$desc}</td>
        <td>{$deadline}</td>
        <td>{$attachLink}</td>
        <td>{$approval}</td>
    </tr>";
}

$stmt->close();
$conn->close();
?>