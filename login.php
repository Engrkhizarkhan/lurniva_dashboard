<?php
session_start();
require_once 'admin/sass/db_config.php';
require_once 'mail_library.php'; // PHPMailer

// --- ✅ Set timezone ---
date_default_timezone_set('Asia/Karachi');
$current_date = date("Y-m-d");

// --- ✅ Only handle POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo "<script>alert('Email and password are required.'); window.location.href='login.php';</script>";
    exit;
}

// --- Helper function: check subscription ---
function isSubscriptionValid($subscription_end) {
    if (empty($subscription_end)) return false;
    return strtotime($subscription_end) >= strtotime(date("Y-m-d"));
}

// --- Helper function: send verification code ---
function sendVerification($conn, $table, $id, $email, $name, $type='student') {
    $code = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
    $conn->query("UPDATE $table SET verification_code='$code', code_expires_at='$expiry' WHERE id=$id");

    $subject = ucfirst($type) . " Account Verification";
    $msg = "Hello $name,<br><br>Your verification code is: <b>$code</b><br>This code expires in 5 minutes.";

    if (sendMail($email, $subject, $msg, $name)) {
        $_SESSION['pending_email'] = $email;
        $_SESSION['user_type'] = $type;
        header("Location: verify.php");
        exit;
    } else {
        echo "<script>alert('Failed to send verification email.'); window.location.href='login.php';</script>";
        exit;
    }
}

// --- Login Check Flow ---
$users = [
    ['table'=>'app_admin', 'redirect'=>'otp.php', 'fields'=>['id','full_name','email','message_email','password'], 'type'=>'app_admin'],
    ['table'=>'schools', 'redirect'=>'admin/index.php', 'fields'=>['id','school_name','admin_contact_person','password','is_verified','status','subscription_end','school_email'], 'type'=>'school'],
    ['table'=>'faculty', 'redirect'=>'Faculty Dashboard/index.php', 'fields'=>['id','campus_id','full_name','email','password','photo','is_verified','status','subscription_end'], 'type'=>'faculty'],
    ['table'=>'students', 'redirect'=>'student/index.php', 'fields'=>['id','school_id','full_name','email','password','profile_photo','is_verified','status','subscription_end'], 'type'=>'student'],
    ['table'=>'parents', 'redirect'=>'parent/index.php', 'fields'=>['id','full_name','parent_cnic','email','phone','profile_photo','password','status','is_verified','subscription_end'], 'type'=>'parent'],
];

$found = false;
foreach($users as $userType){
    $fields = implode(',', $userType['fields']);
    $stmt = $conn->prepare("SELECT $fields FROM {$userType['table']} WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $account = $result->fetch_assoc();
        $found = true;

        // --- Check subscription ---
        if (!isSubscriptionValid($account['subscription_end'])) {
            $conn->query("UPDATE {$userType['table']} SET status='Pending' WHERE id={$account['id']}");
            echo "<script>alert('Your {$userType['type']} subscription has expired. Please renew to continue.'); window.location.href='login.php';</script>";
            exit;
        }

        // --- Check password ---
        if (!password_verify($password, $account['password'])) {
            echo "<script>alert('Invalid password.'); window.location.href='login.php';</script>";
            exit;
        }

        // --- Check verification ---
        if (!empty($account['is_verified']) && $account['is_verified'] == 1) {
            // set session
            switch($userType['type']){
                case 'app_admin':
                    $_SESSION['pending_admin_id'] = $account['id'];
                    $_SESSION['pending_email'] = $account['email'];
                    header("Location: otp.php"); exit;
                case 'school':
                    $_SESSION['admin_id'] = $account['id'];
                    $_SESSION['admin_name'] = $account['admin_contact_person'];
                    $_SESSION['school_name'] = $account['school_name'];
                    header("Location: admin/index.php"); exit;
                case 'faculty':
                    $_SESSION['admin_id'] = $account['id'];
                    $_SESSION['admin_name'] = $account['full_name'];
                    $_SESSION['campus_id'] = $account['campus_id'];
                    $_SESSION['faculty_photo'] = $account['photo'];
                    header("Location: Faculty Dashboard/index.php"); exit;
                case 'student':
                    $_SESSION['student_id'] = $account['id'];
                    $_SESSION['student_name'] = $account['full_name'];
                    $_SESSION['school_id'] = $account['school_id'];
                    $_SESSION['student_photo'] = $account['profile_photo'];
                    header("Location: student/index.php"); exit;
                case 'parent':
                    $_SESSION['parent_id'] = $account['id'];
                    $_SESSION['parent_name'] = $account['full_name'];
                    $_SESSION['parent_cnic'] = $account['parent_cnic'];
                    $_SESSION['parent_phone'] = $account['phone'];
                    $_SESSION['parent_photo'] = $account['profile_photo'];
                    header("Location: parent/index.php"); exit;
            }
        } else {
            sendVerification($conn, $userType['table'], $account['id'], $account['email'], $account['full_name'] ?? $account['admin_contact_person'], $userType['type']);
        }
    }
}

if (!$found) {
    echo "
    <script>
        if (confirm('No account found with this email. Do you want to sign up?')) {
            window.location.href = 'auth-register.php';
        } else {
            window.location.href = 'login.php';
        }
    </script>";
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