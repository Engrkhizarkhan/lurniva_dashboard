<?php
session_start();
require_once 'sass/db_config.php';

// ✅ Ensure parent is logged in
if (!isset($_SESSION['parent_id'])) {
    header("Location: ../login.php");
    exit;
}

$parent_id = $_SESSION['parent_id'];

// ✅ Fetch parent info
$stmt = $conn->prepare("SELECT full_name, parent_cnic, email, phone, profile_photo FROM parents WHERE id = ?");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$parent = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Parent Profile</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
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
            <img alt="image" src="uploads/profile/<?php echo htmlspecialchars($parent['profile_photo']); ?>" class="user-img-radious-style">
          </a>
          <div class="dropdown-menu dropdown-menu-right pullDown">
            <div class="dropdown-title">Hello <?php echo htmlspecialchars($parent['full_name']); ?></div>
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
          <a href="index.php"><img alt="image" src="assets/img/Final Logo (1).jpg" class="header-logo"/> <span class="logo-name">Parent Panel</span></a>
        </div>
        <ul class="sidebar-menu">
          <li class="menu-header">Main</li>
          <li class="active"><a href="index.php" class="nav-link"><i data-feather="monitor"></i><span>Dashboard</span></a></li>
        </ul>
      </aside>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <section class="section">
        <div class="section-body">
          <div class="row mt-sm-4">
            <!-- Left Column -->
            <div class="col-12 col-md-12 col-lg-4">
              <div class="card author-box">
                <div class="card-body">
                  <div class="author-box-center">
                    <img alt="image" src="uploads/profile/<?php echo $parent['profile_photo'] ?: 'default.png'; ?>" 
                         class="rounded-circle author-box-picture">
                    <div class="clearfix"></div>
                    <div class="author-box-name">
                      <a href="#"><?php echo htmlspecialchars($parent['full_name']); ?></a>
                    </div>
                    <div class="author-box-job">Parent</div>
                  </div>
                  <div class="text-center">
                    <div class="author-box-description mt-3">
                      <p><b>CNIC:</b> <?php echo $parent['parent_cnic']; ?></p>
                      <p><b>Email:</b> <?php echo $parent['email']; ?></p>
                      <p><b>Phone:</b> <?php echo $parent['phone']; ?></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="col-12 col-md-12 col-lg-8">
              <div class="card">
                <div class="padding-20">
                  <!-- Tabs -->
                  <ul class="nav nav-tabs" id="myTab2" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" id="about-tab2" data-toggle="tab" href="#about" role="tab"
                         aria-selected="true">About</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="settings-tab2" data-toggle="tab" href="#settings" role="tab"
                         aria-selected="false">Settings</a>
                    </li>
                  </ul>

                  <div class="tab-content tab-bordered" id="myTab3Content">
                    <!-- About Tab -->
                    <div class="tab-pane fade show active" id="about" role="tabpanel">
                      <div class="row">
                        <div class="col-md-4"><strong>Full Name:</strong><br><p class="text-muted"><?php echo $parent['full_name']; ?></p></div>
                        <div class="col-md-4"><strong>Email:</strong><br><p class="text-muted"><?php echo $parent['email']; ?></p></div>
                        <div class="col-md-4"><strong>Phone:</strong><br><p class="text-muted"><?php echo $parent['phone']; ?></p></div>
                      </div>
                    </div>

                    <!-- Settings Tab -->
                    <div class="tab-pane fade" id="settings" role="tabpanel">
                      <form method="POST" action="update_profile.php" enctype="multipart/form-data">
                        <div class="card-header"><h4>Edit Profile</h4></div>
                        <div class="card-body">
                          <div class="row">
                            <div class="form-group col-md-6">
                              <label>Full Name</label>
                              <input type="text" name="full_name" class="form-control" value="<?php echo $parent['full_name']; ?>">
                            </div>
                            <div class="form-group col-md-6">
                              <label>Phone</label>
                              <input type="text" name="phone" class="form-control" value="<?php echo $parent['phone']; ?>">
                            </div>
                          </div>
                          <div class="row">
                            <div class="form-group col-md-12">
                              <label>Email</label>
                              <input type="email" name="email" class="form-control" value="<?php echo $parent['email']; ?>">
                            </div>
                          </div>

                          <!-- Profile Image -->
                          <div class="row">
                            <div class="form-group col-md-12">
                              <label>Profile Image</label>
                              <input type="file" name="profile_photo" class="form-control">
                            </div>
                          </div>

                          <!-- Password Change -->
                          <div class="row">
                            <div class="form-group col-md-6">
                              <label>New Password</label>
                              <input type="password" name="new_password" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                              <label>Confirm Password</label>
                              <input type="password" name="confirm_password" class="form-control">
                            </div>
                          </div>
                        </div>
                        <div class="card-footer text-right">
                          <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                      </form>
                    </div> <!-- End Settings -->
                  </div>
                </div>
              </div>
            </div> <!-- End Right Column -->
          </div>
        </div>
      </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
      <div class="footer-left">Parent Dashboard</div>
    </footer>
  </div>
</div>

<script src="assets/js/app.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>
