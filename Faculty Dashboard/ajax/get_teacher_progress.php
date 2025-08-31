<?php
session_start();
header('Content-Type: application/json');
require_once '../sass/db_config.php';

$teacher_id = $_SESSION['admin_id'] ?? 0;
$school_id  = $_SESSION['campus_id'] ?? 0;

if(!$teacher_id || !$school_id){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$year = date('Y'); // current year; change if you want a parameter

// Helpers
function months_labels(){
    return ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
}

// 1) Get all class_meta records (class_name + section + id) the teacher teaches
$sql = "
SELECT DISTINCT ctm.id AS class_meta_id, ctm.class_name, ctm.section
FROM class_timetable_meta ctm
JOIN class_timetable_details ctd ON ctd.timing_meta_id = ctm.id
WHERE ctm.school_id = ? AND ctd.teacher_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $school_id, $teacher_id);
$stmt->execute();
$res = $stmt->get_result();

$classMetaIds = [];
$classes = []; // for counting unique classes
while($row = $res->fetch_assoc()){
    $classMetaIds[] = (int)$row['class_meta_id'];
    $classes[] = $row['class_name'].'|'.$row['section'];
}
$classes = array_unique($classes);
$classes_count = count($classes);

// 2) Count unique students taught by the teacher (via those classes)
$students_count = 0;
if (!empty($classes)) {
    // Build OR conditions for classes
    $inClauses = [];
    $params = [];
    $types = '';

    foreach($classes as $cs){
        list($cn,$sec) = explode('|',$cs);
        $inClauses[] = "(s.class_grade = ? AND s.section = ? AND s.school_id = ?)";
        $params[] = $cn; $types.='s';
        $params[] = $sec; $types.='s';
        $params[] = $school_id; $types.='i';
    }
    $sqlStu = "SELECT COUNT(DISTINCT s.id) AS cnt FROM students s WHERE ".implode(' OR ', $inClauses);
    $stmtStu = $conn->prepare($sqlStu);
    $stmtStu->bind_param($types, ...$params);
    $stmtStu->execute();
    $r = $stmtStu->get_result()->fetch_assoc();
    $students_count = (int)($r['cnt'] ?? 0);
    $stmtStu->close();
}

// If no classes, just return zeros
$categories = months_labels();
$attendanceSeries = array_fill(0, 12, 0.0);
$examSeries       = array_fill(0, 12, 0.0);
$testSeries       = array_fill(0, 12, 0.0);

if (empty($classMetaIds)) {
    echo json_encode([
        'status'=>'success',
        'categories'=>$categories,
        'series'=>[
            'attendance'=>$attendanceSeries,
            'exams'=>$examSeries,
            'tests'=>$testSeries
        ],
        'meta'=>[
            'year'=>$year,
            'classes_count'=>$classes_count,
            'students_count'=>$students_count,
            'generated_at'=>date('Y-m-d H:i:s')
        ]
    ]);
    exit;
}

/* ============================================================
   ATTENDANCE % per month (Present / Total) * 100
   Uses student_attendance (teacher_id, class_meta_id, status, date)
   Assumes status = 'Present' means present
============================================================ */
$placeholders = implode(',', array_fill(0, count($classMetaIds), '?'));
$types = str_repeat('i', count($classMetaIds)) . 'ii'; // classMetaIds..., school_id, teacher_id

$sqlAtt = "
SELECT 
  MONTH(date) AS mth,
  SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) AS present_cnt,
  COUNT(*) AS total_cnt
FROM student_attendance
WHERE class_meta_id IN ($placeholders)
  AND school_id = ?
  AND teacher_id = ?
  AND YEAR(date) = ?
GROUP BY MONTH(date)
";
$bindValues = array_merge($classMetaIds, [$school_id, $teacher_id, $year]);
$stmtAtt = $conn->prepare($sqlAtt);

// bind dynamically
$stmtAtt->bind_param($types.'i', ...$bindValues);
$stmtAtt->execute();
$rAtt = $stmtAtt->get_result();
while($row = $rAtt->fetch_assoc()){
    $idx = (int)$row['mth'] - 1;
    $pct = 0.0;
    if ((int)$row['total_cnt'] > 0) {
        $pct = ((float)$row['present_cnt'] / (float)$row['total_cnt']) * 100.0;
    }
    $attendanceSeries[$idx] = round($pct, 2);
}
$stmtAtt->close();

/* ============================================================
   EXAM Avg % per month
   exam_results (marks_obtained / total_marks), grouped by month of exam_date
   Link via exam_schedule -> class_name matches classes taught by teacher
============================================================ */

// Build OR for class_name/section
$clsClauses = [];
$params = [];
$types = 'i'; // school_id first
$params[] = $school_id;

foreach($classes as $cs){
    list($cn,$sec) = explode('|',$cs);
    // If your exam_schedule stores section too, add AND class_section = ?
    $clsClauses[] = "(es.class_name = ?)";
    $params[] = $cn; $types.='s';
}

$sqlEx = "
SELECT 
  MONTH(es.exam_date) AS mth,
  AVG((er.marks_obtained / NULLIF(er.total_marks,0)) * 100) AS avg_pct
FROM exam_results er
JOIN exam_schedule es ON er.exam_schedule_id = es.id
WHERE es.school_id = ?
  AND YEAR(es.exam_date) = ?
  AND (" . implode(' OR ', $clsClauses) . ")
GROUP BY MONTH(es.exam_date)
";
$types .= 'i'; // year
$params[] = $year;

$stmtEx = $conn->prepare($sqlEx);
$stmtEx->bind_param($types, ...$params);
$stmtEx->execute();
$rEx = $stmtEx->get_result();
while($row = $rEx->fetch_assoc()){
    $idx = (int)$row['mth'] - 1;
    $val = (float)($row['avg_pct'] ?? 0);
    $examSeries[$idx] = round($val, 2);
}
$stmtEx->close();

/* ============================================================
   TEST/ASSIGNMENT Avg % per month
   student_results (marks_obtained / teacher_assignments.total_marks)
   grouped by MONTH(ta.due_date), filtered by ta.teacher_id
============================================================ */
$sqlTs = "
SELECT 
  MONTH(ta.due_date) AS mth,
  AVG((sr.marks_obtained / NULLIF(ta.total_marks,0)) * 100) AS avg_pct
FROM student_results sr
JOIN teacher_assignments ta ON sr.assignment_id = ta.id
WHERE ta.school_id = ?
  AND ta.teacher_id = ?
  AND YEAR(ta.due_date) = ?
GROUP BY MONTH(ta.due_date)
";
$stmtTs = $conn->prepare($sqlTs);
$stmtTs->bind_param("iii", $school_id, $teacher_id, $year);
$stmtTs->execute();
$rTs = $stmtTs->get_result();
while($row = $rTs->fetch_assoc()){
    $idx = (int)$row['mth'] - 1;
    $val = (float)($row['avg_pct'] ?? 0);
    $testSeries[$idx] = round($val, 2);
}
$stmtTs->close();

// Respond
echo json_encode([
    'status'=>'success',
    'categories'=>$categories,
    'series'=>[
        'attendance'=>$attendanceSeries,
        'exams'=>$examSeries,
        'tests'=>$testSeries
    ],
    'meta'=>[
        'year'=>$year,
        'classes_count'=>$classes_count,
        'students_count'=>$students_count,
        'generated_at'=>date('Y-m-d H:i:s')
    ]
]);