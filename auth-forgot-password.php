<?php
session_start();
require_once 'admin/sass/db_config.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $message = "Email is required.";
        $message_type = "danger";
    } else {
        // check user in all tables
        $tables = ['schools' => 'school_email', 'students' => 'email', 'faculty' => 'email'];
        $found = false;

        foreach ($tables as $table => $column) {
            $stmt = $conn->prepare("SELECT id FROM $table WHERE $column = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $found = true;
                $user = $result->fetch_assoc();

                // generate reset code
                $reset_code = rand(100000, 999999);
                $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

                // save to DB
                $update = $conn->prepare("UPDATE $table SET verification_code=?, code_expires_at=?, verification_attempts=0 WHERE id=?");
                $update->bind_param("ssi", $reset_code, $expires, $user['id']);
                $update->execute();

                // send email
                $subject = "Password Reset Code";
                $body = "Hello,\n\nYour password reset code is: $reset_code\nThis code expires in 5 minutes.";
                $headers = "From: shayans1215225@gmail.com";

                mail($email, $subject, $body, $headers);

                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_table'] = $table;

                header("Location: reset_password.php");
                exit;
            }
        }

        if (!$found) {
            $message = "No account found with this email.";
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
    <title>Forgot Password - Lurniva</title>
    <!-- General CSS Files -->
    <link rel="stylesheet" href="admin/assets/css/app.min.css">
    <link rel="stylesheet" href="admin/assets/css/style.css">
    <link rel="stylesheet" href="admin/assets/css/components.css">
    <link rel="stylesheet" href="admin/assets/css/custom.css">
    <link rel='shortcut icon' type='admin/image/x-icon' href='assets/img/favicon.ico' />
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
                                <h4>Forgot Password</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">We will send a reset code to your email</p>

                                <?php if ($message): ?>
                                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input id="email" type="email" class="form-control" name="email" required
                                            autofocus>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                                            Send Reset Code
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
    <!-- General JS Scripts -->
    <script src="admin/assets/js/app.min.js"></script>
    <script src="admin/assets/js/scripts.js"></script>
    <script src="admin/assets/js/custom.js"></script>
</body>

</html>