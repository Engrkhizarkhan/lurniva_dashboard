<?php
session_start();
require_once '../sass/db_config.php';

$student_id = $_SESSION['student_id'] ?? 0;
$school_id  = $_SESSION['school_id'] ?? 0;
$action     = $_POST['action'] ?? ($_GET['action'] ?? '');

if(!$student_id || !$school_id){
  echo "Session missing.";
  exit;
}

function dd($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

if($action === 'insert'){
  $teacher_id = (int)($_POST['teacher_id'] ?? 0);
  $leave_type = trim($_POST['leave_type'] ?? '');
  $start_date = $_POST['start_date'] ?? '';
  $end_date   = $_POST['end_date'] ?? '';
  $reason     = trim($_POST['reason'] ?? '');

  if(!$teacher_id || !$leave_type || !$start_date || !$end_date || !$reason){
    echo "All fields are required.";
    exit;
  }

  $stmt = $conn->prepare("INSERT INTO student_leaves 
    (student_id, school_id, teacher_id, leave_type, start_date, end_date, reason, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
  $stmt->bind_param('iiissss', $student_id, $school_id, $teacher_id, $leave_type, $start_date, $end_date, $reason);

  echo $stmt->execute() ? "Leave submitted." : ("Error: ".$conn->error);
  exit;
}

if($action === 'getAll'){
  // join teacher name
  $sql = "SELECT sl.*, f.full_name AS teacher_name
          FROM student_leaves sl
          LEFT JOIN faculty f ON sl.teacher_id = f.id
          WHERE sl.school_id = ? AND sl.student_id = ?
          ORDER BY sl.created_at DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ii', $school_id, $student_id);
  $stmt->execute();
  $res = $stmt->get_result();

  if(!$res->num_rows){
    echo '<tr><td colspan="6" class="text-center">No leave requests yet.</td></tr>';
    exit;
  }

  while($row = $res->fetch_assoc()){
    $status = strtolower($row['status'] ?? 'Pending');
    $badgeClass = $status === 'approved' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'pending');
    $trClass = $status === 'rejected' ? 'class="rejected-row"' : '';
    $tooltip = htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8');

    $actions = '';
    if($status === 'pending'){
      $actions = '<button class="btn btn-sm btn-info editLeave" data-id="'.$row['id'].'">Edit</button>
                  <button class="btn btn-sm btn-danger deleteLeave" data-id="'.$row['id'].'">Delete</button>';
    } else {
      $actions = '<button class="btn btn-sm btn-secondary" disabled>Locked</button>';
    }

    echo '<tr '.$trClass.' data-toggle="tooltip" data-placement="top" title="'.$tooltip.'">
            <td>'.dd($row['teacher_name']).'</td>
            <td>'.dd($row['leave_type']).'</td>
            <td>'.dd($row['start_date']).' to '.dd($row['end_date']).'</td>
            <td>'.dd($row['reason']).'</td>
            <td><span class="badge '.$badgeClass.'">'.ucfirst($status).'</span></td>
            <td>'.$actions.'</td>
          </tr>';
  }
  exit;
}

if($action === 'getOne'){
  $id = (int)($_POST['id'] ?? 0);
  if(!$id){ echo json_encode([]); exit; }

  $stmt = $conn->prepare("SELECT * FROM student_leaves WHERE id = ? AND school_id = ? AND student_id = ?");
  $stmt->bind_param('iii', $id, $school_id, $student_id);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();

  // Only allow editing if Pending
  if(!$row || strtolower($row['status']) !== 'pending'){
    echo json_encode([]);
    exit;
  }

  echo json_encode($row);
  exit;
}

if($action === 'update'){
  $id         = (int)($_POST['id'] ?? 0);
  $teacher_id = (int)($_POST['teacher_id'] ?? 0);
  $leave_type = trim($_POST['leave_type'] ?? '');
  $start_date = $_POST['start_date'] ?? '';
  $end_date   = $_POST['end_date'] ?? '';
  $reason     = trim($_POST['reason'] ?? '');

  if(!$id || !$teacher_id || !$leave_type || !$start_date || !$end_date || !$reason){
    echo "All fields are required.";
    exit;
  }

  // Update only if current status is Pending
  $check = $conn->prepare("SELECT status FROM student_leaves WHERE id = ? AND school_id = ? AND student_id = ?");
  $check->bind_param('iii', $id, $school_id, $student_id);
  $check->execute();
  $cr = $check->get_result()->fetch_assoc();
  if(!$cr || strtolower($cr['status']) !== 'pending'){
    echo "Cannot update. Only Pending requests can be updated.";
    exit;
  }

  $stmt = $conn->prepare("UPDATE student_leaves
                          SET teacher_id=?, leave_type=?, start_date=?, end_date=?, reason=?, updated_at=NOW()
                          WHERE id=? AND school_id=? AND student_id=?");
  $stmt->bind_param('isssssii', $teacher_id, $leave_type, $start_date, $end_date, $reason, $id, $school_id, $student_id);

  echo $stmt->execute() ? "Leave updated." : ("Error: ".$conn->error);
  exit;
}

if($action === 'delete'){
  $id = (int)($_POST['id'] ?? 0);
  if(!$id){ echo "Invalid id."; exit; }

  // Delete only if Pending
  $stmt = $conn->prepare("DELETE FROM student_leaves 
                          WHERE id = ? AND school_id = ? AND student_id = ? AND status = 'Pending'");
  $stmt->bind_param('iii', $id, $school_id, $student_id);

  echo $stmt->execute() && $stmt->affected_rows ? "Leave deleted." : "Cannot delete (only Pending).";
  exit;
}

echo "Invalid action.";