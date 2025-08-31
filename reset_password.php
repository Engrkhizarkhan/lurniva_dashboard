<?php
session_start();
require_once 'admin/sass/db_config.php';

$message = '';
$message_type = '';

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_table'])) {
    header("Location: forgot_password.php");
    exit;
}

$email = $_SESSION['reset_email'];
$table = $_SESSION['reset_table'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($code) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $message_type = "danger";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "danger";
    } else {
        // get user info
        $column = ($table === "schools") ? "school_email" : "email";
        $stmt = $conn->prepare("SELECT id, verification_code, code_expires_at FROM $table WHERE $column=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['verification_code'] === $code && strtotime($user['code_expires_at']) > time()) {
                // update password
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $update = $conn->prepare("UPDATE $table SET password=?, verification_code=NULL, code_expires_at=NULL WHERE id=?");
                $update->bind_param("si", $hashed, $user['id']);
                $update->execute();

                $message = "Password reset successfully. You can now log in.";
                $message_type = "success";

                // cleanup session
                unset($_SESSION['reset_email'], $_SESSION['reset_table']);
            } else {
                $message = "Invalid or expired verification code.";
                $message_type = "danger";
            }
        } else {
            $message = "No account found.";
            $message_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Reset Password - Lurniva</title>
    <link rel="stylesheet" href="admin/assets/css/app.min.css">
    <link rel="stylesheet" href="admin/assets/css/style.css">
    <link rel="stylesheet" href="admin/assets/css/components.css">
    <link rel="stylesheet" href="admin/assets/css/custom.css">
    <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />
</head>

<body>
    <div class="loader"></div>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div
                        class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h4>Reset Password</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Enter the code you received and your new password</p>

                                <?php if ($message): ?>
                                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="form-group">
                                        <label for="code">Verification Code</label>
                                        <input id="code" type="text" class="form-control" name="code" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input id="new_password" type="password" class="form-control"
                                            name="new_password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm Password</label>
                                        <input id="confirm_password" type="password" class="form-control"
                                            name="confirm_password" required>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                                            Reset Password
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script src="admin/assets/js/app.min.js"></script>
    <!-- <script src="admin/assets/js/scripts.js"></script> -->
    <script src="admin/assets/js/custom.js"></script>
</body>

</html>