<?php
session_start();
require '../sass/db_config.php';

$school_id = $_SESSION['admin_id'];

$action = $_POST['action'] ?? '';

if ($action === 'insert' || $action === 'update') {
    $title = $_POST['title'];
    $notice_date = $_POST['notice_date'];
    $expiry_date = $_POST['expiry_date'];
    $issued_by = $_POST['issued_by'];
    $purpose = $_POST['purpose'];
    $notice_type = $_POST['notice_type'];
    $audience = $_POST['audience'];
    $file_path = '';

    if (!empty($_FILES['file']['name'])) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $file_path = 'uploads/notices/' . time() . '.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], '../' . $file_path);
    }

    if ($action === 'insert') {
        $stmt = $conn->prepare("INSERT INTO digital_notices (school_id, title, notice_date, expiry_date, issued_by, purpose, notice_type, audience, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss", $school_id, $title, $notice_date, $expiry_date, $issued_by, $purpose, $notice_type, $audience, $file_path);
    } else {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE digital_notices SET title=?, notice_date=?, expiry_date=?, issued_by=?, purpose=?, notice_type=?, audience=?, file_path=? WHERE id=? AND school_id=?");
        $stmt->bind_param("ssssssssii", $title, $notice_date, $expiry_date, $issued_by, $purpose, $notice_type, $audience, $file_path, $id, $school_id);
    }

    echo $stmt->execute() ? 'Saved successfully' : 'Failed';
    exit;
}

if ($action === 'delete') {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM digital_notices WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $school_id);
    echo $stmt->execute() ? 'Deleted successfully' : 'Failed';
    exit;
}

if ($action === 'getAll') {
    $result = $conn->query("SELECT * FROM digital_notices WHERE school_id = $school_id ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
    echo "
<div class='card notice-card mb-4'>

  <!-- Card Header -->
  <div class='card-header d-flex justify-content-between align-items-center p-3'>
    <h4 class='mb-0'>{$row['title']}</h4>

    <!-- Type & Audience badges -->
    <div>
      <span class='badge badge-primary'>{$row['notice_type']}</span>
      <span class='badge badge-info'>{$row['audience']}</span>
    </div>
  </div>

  <!-- Card Body -->
  <div class='card-body'>

    <!-- Dates -->
    <div class='mb-2 text-muted'>
      <i data-feather=\"calendar\"></i> {$row['notice_date']}
      <span class='mx-2'>|</span>
      <i data-feather=\"clock\"></i> {$row['expiry_date']}
    </div>

    <!-- Purpose / description -->
    <p class='mb-3'>{$row['purpose']}</p>";

    /* Attachment button (if any) */
    if (!empty($row['file_path'])) {
      echo "
      <a href='uploads/notices/{$row['file_path']}' target='_blank'
         class='btn btn-icon icon-left btn-info btn-sm me-2'>
        <i data-feather=\"download\"></i> Attachment
      </a>";
    }

echo "
  </div> <!-- /card-body -->

  <!-- Card Footer -->
  <div class='card-footer text-end pt-0 pb-3'>

    <!-- Issuer -->
    <span class='text-muted me-3'>
      <i data-feather=\"user\"></i> Issued by: <strong>{$row['issued_by']}</strong>
    </span>

    <!-- Delete button -->
    <button class='btn btn-danger btn-sm deleteNotice'
            data-id='{$row['id']}'>
      <i data-feather=\"trash-2\"></i> Delete
    </button>

  </div>
</div>";

    }
    exit;
}
?>