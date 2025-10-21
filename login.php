<?php
session_start();
require_once 'admin/sass/db_config.php';
require_once 'mail_library.php'; // include PHPMailer library

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $current_date = date("Y-m-d");

    if (empty($email) || empty($password)) {
        echo "<script>alert('Email and password are required.'); window.location.href='login.php';</script>";
        exit;
    }

    // --- ✅ Check App Admin ---
    $stmt = $conn->prepare("SELECT id, full_name, email,message_email, password FROM app_admin WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $otp = rand(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            $conn->query("UPDATE app_admin SET verification_code='$otp', code_expires_at='$expiry' WHERE id=" . $admin['id']);

            $subject = "App Admin OTP Verification";
            $msg = "Hello {$admin['full_name']},<br><br>Your OTP is: <b>$otp</b><br><br>This code expires in 5 minutes.";

            if (sendMail($admin['message_email'], $subject, $msg, $admin['full_name'])) {
                $_SESSION['pending_email'] = $email;
                $_SESSION['pending_admin_id'] = $admin['id'];
                $_SESSION['user_type'] = 'app_admin';
                header("Location: otp.php");
                exit;
            } else {
                echo "<script>alert('Failed to send OTP email.'); window.location.href='login.php';</script>";
                exit;
            }
        } else {
            echo "<script>alert('Invalid password.'); window.location.href='login.php';</script>";
            exit;
        }
    }

    // --- ✅ Check Schools ---
    $stmt = $conn->prepare("
        SELECT id, school_name, admin_contact_person, password, is_verified, status, subscription_end, school_email 
        FROM schools 
        WHERE school_email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (empty($user['subscription_end']) || $user['subscription_end'] < $current_date) {
            $conn->query("UPDATE schools SET status='Pending' WHERE id=" . $user['id']);
            echo "<script>alert('Your school subscription has expired. Please renew to continue.'); window.location.href='login.php';</script>";
            exit;
        }

        if (password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 1) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['admin_contact_person'];
                $_SESSION['school_name'] = $user['school_name'];
                header("Location: admin/index.php");
                exit;
            } else {
                $verification_code = rand(100000, 999999);
                $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
                $conn->query("UPDATE schools SET verification_code='$verification_code', code_expires_at='$expiry' WHERE id=".$user['id']);

                $subject = "School Account Verification";
                $msg = "Hello {$user['admin_contact_person']},<br><br>Your verification code is: <b>$verification_code</b><br><br>This code expires in 5 minutes.";

                if (sendMail($email, $subject, $msg, $user['admin_contact_person'])) {
                    $_SESSION['pending_email'] = $email;
                    $_SESSION['user_type'] = 'school';
                    header("Location: verify.php");
                    exit;
                } else {
                    echo "<script>alert('Failed to send verification email.'); window.location.href='login.php';</script>";
                    exit;
                }
            }
        } else {
            echo "<script>alert('Invalid password.'); window.location.href='login.php';</script>";
            exit;
        }
    }

    // --- ✅ Check Faculty ---
    $stmt = $conn->prepare("
        SELECT id, campus_id, full_name, email, password, photo, is_verified, status, subscription_end 
        FROM faculty 
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $faculty = $result->fetch_assoc();

        if (empty($faculty['subscription_end']) || $faculty['subscription_end'] < $current_date) {
            $conn->query("UPDATE faculty SET status='Pending' WHERE id=" . $faculty['id']);
            echo "<script>alert('Your faculty subscription has expired. Please renew to continue.'); window.location.href='login.php';</script>";
            exit;
        }

        if (password_verify($password, $faculty['password'])) {
            if ($faculty['is_verified'] == 1) {
                $_SESSION['admin_id'] = $faculty['id'];
                $_SESSION['admin_name'] = $faculty['full_name'];
                $_SESSION['campus_id'] = $faculty['campus_id'];
                $_SESSION['faculty_photo'] = $faculty['photo'];
                header("Location: Faculty Dashboard/index.php");
                exit;
            } else {
                $verification_code = rand(100000, 999999);
                $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
                $conn->query("UPDATE faculty SET verification_code='$verification_code', code_expires_at='$expiry' WHERE id=".$faculty['id']);

                $subject = "Faculty Account Verification";
                $msg = "Hello {$faculty['full_name']},<br><br>Your verification code is: <b>$verification_code</b><br><br>This code expires in 5 minutes.";

                if (sendMail($email, $subject, $msg, $faculty['full_name'])) {
                    $_SESSION['pending_email'] = $email;
                    $_SESSION['user_type'] = 'faculty';
                    header("Location: verify.php");
                    exit;
                } else {
                    echo "<script>alert('Failed to send verification email.'); window.location.href='login.php';</script>";
                    exit;
                }
            }
        } else {
            echo "<script>alert('Invalid password.'); window.location.href='login.php';</script>";
            exit;
        }
    }

    // --- ✅ Check Students ---
    $stmt = $conn->prepare("
        SELECT id, school_id, full_name, email, password, profile_photo, is_verified, status, subscription_end 
        FROM students
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $student = $result->fetch_assoc();

        if (empty($student['subscription_end']) || $student['subscription_end'] < $current_date) {
    $conn->query("UPDATE students SET status='Pending' WHERE id=" . $student['id']);
    echo "<script>
        alert('Your student subscription has expired on {$student['subscription_end']}. Please renew to continue.');
        window.location.href='login.php';
    </script>";
    exit;
}



        if (password_verify($password, $student['password'])) {
            if ($student['is_verified'] == 1) {
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['full_name'];
                $_SESSION['school_id'] = $student['school_id'];
                $_SESSION['student_photo'] = $student['profile_photo'];
                header("Location: student/index.php");
                exit;
            } else {
                $verification_code = rand(100000, 999999);
                $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
                $conn->query("UPDATE students SET verification_code='$verification_code', code_expires_at='$expiry' WHERE id=".$student['id']);

                $subject = "Student Account Verification";
                $msg = "Hello {$student['full_name']},<br><br>Your verification code is: <b>$verification_code</b><br><br>This code expires in 5 minutes.";

                if (sendMail($email, $subject, $msg, $student['full_name'])) {
                    $_SESSION['pending_email'] = $email;
                    $_SESSION['user_type'] = 'student';
                    header("Location: verify.php");
                    exit;
                } else {
                    echo "<script>alert('Failed to send verification email.'); window.location.href='login.php';</script>";
                    exit;
                }
            }
        } else {
            echo "<script>alert('Invalid password.'); window.location.href='login.php';</script>";
            exit;
        }
    }

    // --- ✅ Check Parents ---
    $stmt = $conn->prepare("
        SELECT id, full_name, parent_cnic, email, phone, profile_photo, password, status, is_verified, subscription_end 
        FROM parents 
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $parent = $result->fetch_assoc();

        if (empty($parent['subscription_end']) || $parent['subscription_end'] < $current_date) {
            $conn->query("UPDATE parents SET status='Pending' WHERE id=" . $parent['id']);
            echo "<script>alert('Your parent subscription has expired. Please renew to continue.'); window.location.href='login.php';</script>";
            exit;
        }

        if (password_verify($password, $parent['password'])) {
            if ($parent['is_verified'] == 1) {
                $_SESSION['parent_id']    = $parent['id'];
                $_SESSION['parent_name']  = $parent['full_name'];
                $_SESSION['parent_cnic']  = $parent['parent_cnic'];
                $_SESSION['parent_phone'] = $parent['phone'];
                $_SESSION['parent_photo'] = $parent['profile_photo'];

                header("Location: parent/index.php");
                exit;
            } else {
                $verification_code = rand(100000, 999999);
                $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
                $conn->query("UPDATE parents SET verification_code='$verification_code', code_expires_at='$expiry' WHERE id=".$parent['id']);

                $subject = "Parent Account Verification";
                $msg = "Hello {$parent['full_name']},<br><br>Your verification code is: <b>$verification_code</b><br><br>This code expires in 5 minutes.";

                if (sendMail($email, $subject, $msg, $parent['full_name'])) {
                    $_SESSION['pending_email'] = $email;
                    $_SESSION['user_type']     = 'parent';
                    header("Location: verify.php");
                    exit;
                } else {
                    echo "<script>alert('Failed to send verification email.'); window.location.href='login.php';</script>";
                    exit;
                }
            }
        } else {
            echo "<script>alert('Invalid password.'); window.location.href='login.php';</script>";
            exit;
        }
    }

    // --- 🚨 No account found ---
    echo "
    <script>
        if (confirm('No account found with this email. Do you want to sign up?')) {
            window.location.href = 'auth-register.php';
        } else {
            window.location.href = 'login.php';
        }
    </script>
    ";
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Login - Lurniva</title>
    <link rel="stylesheet" href="admin/assets/css/app.min.css" />
    <link rel="stylesheet" href="admin/assets/css/style.css" />
    <link rel="stylesheet" href="admin/assets/css/components.css" />
    <link rel="stylesheet" href="admin/assets/css/custom.css" />
    <link rel="shortcut icon" type="image/x-icon" href="admin/assets/img/T Logo.png" />

    <style>
    body,
    html {
        margin: 0;
        padding: 0;
        height: 100%;
        font-family: "Segoe UI", sans-serif;
    }

    .login-container {
        display: flex;
        height: 100vh;
    }

    .left-section {
        background: linear-gradient(#1da1f2, #794bc4, #17c3b2);
        width: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 30px;
    }

    .right-section {
        background-color: #ffffff;
        width: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    .logo {
        width: 300px;
        max-width: 90%;
        height: auto;
        border-radius: 0;
        transition: all 0.3s ease;
    }

    .login-box {
        width: 100%;
        max-width: 400px;
    }

    .card {
        border: none;
        border-radius: 10px;
    }

    .forgot-password {
        margin-top: 10px;
        font-size: 0.875rem;
    }

    .create-account {
        margin-top: 15px;
        text-align: center;
        font-size: 0.9rem;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .login-container {
            flex-direction: column;
            height: auto;
        }

        .left-section,
        .right-section {
            width: 100%;
            height: auto;
            padding: 20px;
        }

        .right-section {
            order: -1;
            /* show logo first */
            padding-top: 40px;
        }

        .logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            /* make it circle */
            object-fit: cover;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
        }

        .login-box {
            margin-top: 20px;
        }
    }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Right Section (Logo) -->
        <div class="right-section">
            <img src="admin/assets/img/Final Logo.jpg" alt="Logo" class="logo" />
        </div>

        <!-- Left Section (Form) -->
        <div class="left-section">
            <div class="login-box">
                <div class="card card-primary">
                    <div class="card-header text-white">
                        <h4>Login</h4>
                    </div>
                    <div class="card-body bg-white">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $message_type ?>">
                            <?= $message ?>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>"
                            class="needs-validation" novalidate>
                            <div class="form-group">
                                <label for="email" class="text-dark">Email</label>
                                <input id="email" type="email" class="form-control" name="email" required autofocus />
                                <div class="invalid-feedback">Please fill in your email</div>
                            </div>

                            <div class="form-group">
                                <label for="password" class="text-dark">Password</label>
                                <input id="password" type="password" class="form-control" name="password" required />
                                <div class="invalid-feedback">Please fill in your password</div>
                                <div class="forgot-password">
                                    <a href="auth-forgot-password.php" class="text-small">Forgot Password?</a>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-lg btn-block"
                                    style="background: linear-gradient(#1da1f2, #794bc4, #17c3b2); border: none; color: white;">
                                    Login
                                </button>
                            </div>

                            <div class="create-account text-dark">
                                Don't have an account?
                                <a href="auth-register.php">Sign Up!</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="admin/assets/js/app.min.js"></script>
    <!-- <script src="admin/assets/js/scripts.js"></script> -->
    <script src="admin/assets/js/custom.js"></script>
</body>

</html>