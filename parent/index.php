<?php
session_start();
require_once '../admin/sass/db_config.php';

// ✅ Restrict Access
if (!isset($_SESSION['parent_id'])) {
    header("Location: ../login.php");
    exit;
}

$parentId    = $_SESSION['parent_id'];
$parentName  = $_SESSION['parent_name'] ?? "Parent User";
$parentPhoto = $_SESSION['parent_photo'] ?? "default.png";
$parent_cnic = $_SESSION['parent_cnic'] ?? "default.png";

// ---------------------------
// ✅ Fetch Children Count
// ---------------------------
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM students WHERE parent_cnic = ?");
$stmt->bind_param("i", $parent_cnic);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$childrenCount = $result['total'] ?? 0;
// ---------------------------
// ✅ Fetch Attendance % (avg of all children for current month)
// ---------------------------
$parent_cnic = $_SESSION['parent_cnic']; // assuming stored in session after login

// Get all student IDs for this parent
$stmt = $conn->prepare("SELECT id FROM students WHERE parent_cnic = ?");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();
$result = $stmt->get_result();

$studentIds = [];
while ($row = $result->fetch_assoc()) {
    $studentIds[] = $row['id'];
}

$attendancePercent = 0;

if (!empty($studentIds)) {
    $studentIdsStr = implode(",", $studentIds);

    // Get current active fee period (monthly period)
    $period = $conn->query("
        SELECT id, start_date, end_date 
        FROM fee_periods 
        WHERE status = 'active' AND period_type = 'monthly'
        ORDER BY start_date DESC LIMIT 1
    ")->fetch_assoc();

    if ($period) {
        $startDate = $period['start_date'];
        $endDate   = $period['end_date'];

        // Total days for all students
        $sqlTotal = "
            SELECT COUNT(*) as total_days 
            FROM student_attendance 
            WHERE student_id IN ($studentIdsStr) 
              AND date BETWEEN '$startDate' AND '$endDate'
        ";
        $totalDays = $conn->query($sqlTotal)->fetch_assoc()['total_days'] ?? 0;

        // Present days for all students
        $sqlPresent = "
            SELECT COUNT(*) as present_days 
            FROM student_attendance 
            WHERE student_id IN ($studentIdsStr) 
              AND status = 'Present' 
              AND date BETWEEN '$startDate' AND '$endDate'
        ";
        $presentDays = $conn->query($sqlPresent)->fetch_assoc()['present_days'] ?? 0;

        if ($totalDays > 0) {
            $attendancePercent = round(($presentDays / $totalDays) * 100, 2);
        }
    }
}


// ---------------------------
// ✅ Fetch Pending Fee
// ---------------------------
$stmt = $conn->prepare("
    SELECT SUM(balance_due) AS pending_fee
    FROM fee_slip_details
    WHERE student_id IN (SELECT id FROM students WHERE parent_cnic = ?)
");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$pendingFee = $result['pending_fee'] ?? 0;
// ---------------------------
// ✅ Fetch Behavior Alerts
// ---------------------------
$stmt = $conn->prepare("
    SELECT COUNT(*) AS alerts 
    FROM student_behavior
    WHERE student_id IN (SELECT id FROM students WHERE parent_cnic = ?) 
      AND parent_approved=0
");
$stmt->bind_param("i", $parent_cnic);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$behaviorAlerts = $result['alerts'] ?? 0;

// ---------------------------
// ✅ Fetch Attendance Trend (last 7 days for all kids)
// ---------------------------

$stmt = $conn->prepare("
    SELECT DATE(date) AS date, 
           AVG(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) * 100 AS percentage
    FROM student_attendance
    WHERE student_id IN (SELECT id FROM students WHERE parent_cnic = ?)
    GROUP BY DATE(date)
    ORDER BY date DESC
    LIMIT 7
");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();

$result = $stmt->get_result();
$attendanceTrend = [];
while ($row = $result->fetch_assoc()) {
    $attendanceTrend[] = [
        'date' => $row['date'],
        'percentage' => round($row['percentage'], 2)
    ];
}
$attendanceTrend = array_reverse($attendanceTrend); // oldest → newest

// ---------------------------
// ✅ Fee Status for Donut Chart
// ---------------------------
// ---------------------------
// ✅ Fetch Fee Paid & Pending (all kids of a parent)
// ---------------------------
$parent_cnic = $_SESSION['parent_cnic']; // make sure you have this in session

$stmt = $conn->prepare("
    SELECT 
        SUM(amount_paid) AS paid, 
        SUM(balance_due) AS pending
    FROM fee_slip_details
    WHERE student_id IN (SELECT id FROM students WHERE parent_cnic = ?)
");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();

$result = $stmt->get_result()->fetch_assoc();
$feePaid    = $result['paid'] ?? 0;
$feePending = $result['pending'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Parent Dashboard</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body>
<div id="app">
  <div class="main-wrapper main-wrapper-1">
    <div class="navbar-bg"></div>
    <nav class="navbar navbar-expand-lg main-navbar sticky">
      <div class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
          <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn"><i data-feather="align-justify"></i></a></li>
        </ul>
      </div>
      <ul class="navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
            <img alt="image" src="uploads/profile/<?php echo $parentPhoto; ?>" class="user-img-radious-style">
          </a>
          <div class="dropdown-menu dropdown-menu-right pullDown">
            <div class="dropdown-title">Hello <?php echo $parentName; ?></div>
            <a href="profile.php" class="dropdown-item has-icon"><i class="far fa-user"></i> Profile</a>
            <a href="settings.php" class="dropdown-item has-icon"><i class="fas fa-cog"></i> Settings</a>
            <div class="dropdown-divider"></div>
            <a href="logout.php" class="dropdown-item has-icon text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </li>
      </ul>
    </nav>

    <!-- Sidebar -->
    <div class="main-sidebar sidebar-style-2">
      <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
          <a href="parent.php"><img alt="image" src="assets/img/Final Logo (1).jpg" class="header-logo"/> <span class="logo-name">Parent Panel</span></a>
        </div>
        <ul class="sidebar-menu">
          <li class="menu-header">Main</li>
          <li class="active"><a href="parent.php" class="nav-link"><i data-feather="monitor"></i><span>Dashboard</span></a></li>
        </ul>
      </aside>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <section class="section">
        <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-statistic-2">
              <div class="card-icon shadow-primary bg-primary"><i class="fas fa-user-graduate"></i></div>
              <div class="card-wrap">
                <div class="card-header"><h4>Children</h4></div>
                <div class="card-body"><?php echo $childrenCount; ?></div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-statistic-2">
              <div class="card-icon shadow-success bg-success"><i class="fas fa-calendar-check"></i></div>
              <div class="card-wrap">
                <div class="card-header"><h4>Attendance</h4></div>
                <div class="card-body"><?php echo $attendancePercent; ?>%</div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-statistic-2">
              <div class="card-icon shadow-warning bg-warning"><i class="fas fa-file-invoice-dollar"></i></div>
              <div class="card-wrap">
                <div class="card-header"><h4>Pending Fee</h4></div>
                <div class="card-body">Rs<?php echo $pendingFee; ?></div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-statistic-2">
              <div class="card-icon shadow-danger bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
              <div class="card-wrap">
                <div class="card-header"><h4>Behavior Alerts</h4></div>
                <div class="card-body"><?php echo $behaviorAlerts; ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts -->
        <div class="row">
          <div class="col-12 col-lg-8">
            <div class="card">
              <div class="card-header"><h4>Attendance Trend</h4></div>
              <div class="card-body"><div id="attendanceChart"></div></div>
            </div>
          </div>
          <div class="col-12 col-lg-4">
            <div class="card">
              <div class="card-header"><h4>Fee Status</h4></div>
              <div class="card-body"><div id="feeChart"></div></div>
            </div>
          </div>
        </div>
      <?php


$parent_cnic = $_SESSION['parent_cnic'];

// ✅ Fetch all children of parent
$stmt = $conn->prepare("
    SELECT id, full_name, class_grade, section, profile_photo 
    FROM students 
    WHERE parent_cnic = ?
");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();
$children = $stmt->get_result();
?>

<div class="card">
  <div class="card-header">
    <h4>Children</h4>
  </div>
  <div class="card-body">
    <ul class="list-unstyled user-progress list-unstyled-border list-unstyled-noborder">
      <?php while ($student = $children->fetch_assoc()): ?>
        <?php
          // ✅ Get attendance percentage for this student
          $attStmt = $conn->prepare("
              SELECT 
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS presents,
                COUNT(*) AS total_days
              FROM student_attendance
              WHERE student_id = ?
          ");
          $attStmt->bind_param("i", $student['id']);
          $attStmt->execute();
          $attData = $attStmt->get_result()->fetch_assoc();
          
          $attendancePercent = 0;
          if ($attData['total_days'] > 0) {
              $attendancePercent = round(($attData['presents'] / $attData['total_days']) * 100, 2);
          }
        ?>
        
        <li class="media">
          <img alt="image" 
               class="mr-3 rounded-circle" 
               width="50" 
               src="../student/uploads/profile/<?php echo $student['profile_photo'] ?: 'default.png'; ?>">
          
          <div class="media-body">
            <div class="media-title"><?php echo htmlspecialchars($student['full_name']); ?></div>
            <div class="text-job text-muted">
              Class: <?php echo $student['class_grade']; ?> | Section: <?php echo $student['section']; ?>
            </div>
          </div>

          <div class="media-progressbar">
            <div class="progress-text"><?php echo $attendancePercent; ?>%</div>
            <div class="progress" data-height="6" style="height: 6px;">
              <div class="progress-bar bg-primary" 
                   data-width="<?php echo $attendancePercent; ?>%" 
                   style="width: <?php echo $attendancePercent; ?>%;">
              </div>
            </div>
          </div>

          <div class="media-cta">
            <form method="POST" action="set_student_session.php">
              <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
              <button type="submit" class="btn btn-outline-primary">Detail</button>
            </form>
          </div>
        </li>
      <?php endwhile; ?>
    </ul>
  </div>
</div>

      </section>
    </div>

    <footer class="main-footer">
      <div class="footer-left"><a href="#">Parent Dashboard</a></div>
    </footer>
  </div>
</div>

<!-- JS -->
<script src="assets/js/app.min.js"></script>
<script src="assets/bundles/apexcharts/apexcharts.min.js"></script>
<script src="assets/js/scripts.js"></script>

<script>
  // Attendance Chart (Dynamic)
  var attendanceData = <?php echo json_encode(array_column($attendanceTrend, 'percentage')); ?>;
  var attendanceLabels = <?php echo json_encode(array_column($attendanceTrend, 'date')); ?>;

  var options = {
    chart: { type: 'line', height: 300 },
    series: [{ name: 'Attendance %', data: attendanceData }],
    xaxis: { categories: attendanceLabels }
  };
  new ApexCharts(document.querySelector("#attendanceChart"), options).render();

  // Fee Chart (Dynamic)
  var feeOptions = {
    chart: { type: 'donut', height: 250 },
    labels: ['Paid', 'Pending'],
    series: [<?php echo $feePaid; ?>, <?php echo $feePending; ?>],
    colors: ['#28a745', '#dc3545']
  };
  new ApexCharts(document.querySelector("#feeChart"), feeOptions).render();
</script>
</body>
</html>
