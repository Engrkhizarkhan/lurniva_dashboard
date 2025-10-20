<?php
session_start();
require_once '../sass/db_config.php';

$school_id  = $_SESSION['school_id'] ?? 0;
$student_id = $_SESSION['student_id'] ?? 0;

if(!$school_id || !$student_id){
  echo '<option value="">Session missing</option>';
  exit;
}


$stmt = $conn->prepare("SELECT class_grade, section 
                        FROM students 
                        WHERE id = ? AND school_id = ?");
$stmt->bind_param('ii', $student_id, $school_id);
$stmt->execute();
$res = $stmt->get_result();
$stu = $res->fetch_assoc();

if(!$stu){
  echo '<option value="">Student not found</option>';
  exit;
}

$class_grade = $stu['class_grade'];
$section     = $stu['section'];

// Get matching class_timetable_meta ids
$metaStmt = $conn->prepare("SELECT id 
                            FROM class_timetable_meta 
                            WHERE school_id = ? AND class_name = ? AND section = ?");
$metaStmt->bind_param('iss', $school_id, $class_grade, $section);
$metaStmt->execute();
$metaRes = $metaStmt->get_result();

$metaIds = [];
while($m = $metaRes->fetch_assoc()){
  $metaIds[] = (int)$m['id'];
}
if(empty($metaIds)){
  echo '<option value="">No teachers found</option>';
  exit;
}

// Get distinct teacher ids from details
$in = implode(',', array_fill(0, count($metaIds), '?'));
$types = str_repeat('i', count($metaIds));
$sql = "SELECT DISTINCT teacher_id 
        FROM class_timetable_details 
        WHERE timing_meta_id IN ($in) AND teacher_id IS NOT NULL AND teacher_id <> 0";
$detStmt = $conn->prepare($sql);
$detStmt->bind_param($types, ...$metaIds);
$detStmt->execute();
$detRes = $detStmt->get_result();

$teacherIds = [];
while($d = $detRes->fetch_assoc()){
  $teacherIds[] = (int)$d['teacher_id'];
}
if(empty($teacherIds)){
  echo '<option value="">No teachers found</option>';
  exit;
}

// Fetch teachers from faculty
$in2 = implode(',', array_fill(0, count($teacherIds), '?'));
$types2 = str_repeat('i', count($teacherIds)+1); // +1 for school_id (campus_id)
$sql2 = "SELECT id, full_name 
         FROM faculty 
         WHERE campus_id = ? AND id IN ($in2)
         ORDER BY full_name ASC";
$stmt2 = $conn->prepare($sql2);
$params = array_merge([$school_id], $teacherIds);
$stmt2->bind_param($types2, ...$params);
$stmt2->execute();
$res2 = $stmt2->get_result();

$out = '';
while($t = $res2->fetch_assoc()){
  $out .= '<option value="'.$t['id'].'">'.htmlspecialchars($t['full_name']).'</option>';
}
echo $out ?: '<option value="">No teachers found</option>';