<?php
session_start();
require_once 'sass/db_config.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = "Email and password are required.";
        $message_type = 'danger';
    } else {
        $stmt = $conn->prepare("SELECT id, school_name, admin_contact_person, password FROM schools WHERE school_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['admin_contact_person'];
                $_SESSION['school_name'] = $user['school_name'];

                header("Location: dashboard.php");
                exit;
            } else {
                $message = "Invalid password.";
                $message_type = 'danger';
            }
        } else {
            $message = "No account found with that email.";
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Lurniva</title>
    <link rel="stylesheet" href="assets/css/app.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/T Logo.png" />
    <style>
    body,
    html {
        margin: 0;
        padding: 0;
        height: 100%;
        background-color: #f1f5f9;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: 'Segoe UI', sans-serif;
    }

    .login-wrapper {
        text-align: center;
        max-width: 900px;
        padding: 20px;
        width: 100%;
    }

    .logo-circle {
        width: 140px;
        height: 140px;
        margin: 0 auto 30px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #0ac5f7;
    }

    .logo-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .login-box {
        background-color: #ffffff;
        border-radius: 12px;
        padding: 50px 80px;
        margin: 0 auto;
    }

    .login-box h4 {
        margin-bottom: 25px;
        color: #0ac5f7;
    }

    .form-control {
        border-radius: 6px;
        height: 45px;
        font-size: 16px;
    }

    .btn-primary {
        background-color: #0ac5f7;
        border: none;
    }

    .btn-primary:hover {
        background-color: #089dc4;
    }

    .below-links {
        margin-top: 20px;
        font-size: 0.95rem;
    }

    .below-links a {
        color: #0ac5f7;
        text-decoration: none;
        font-weight: 500;
    }

    .below-links a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .login-box {
            padding: 30px 20px;
        }

        .login-wrapper {
            max-width: 95%;
        }
    }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <!-- Logo -->
        <div class="logo-circle">
            <img src="assets/img/Final Logo.jpg" alt="Logo">
        </div>

        <!-- Login Form -->
        <div class="login-box">
            <h4>Admin Login</h4>

            <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="needs-validation"
                novalidate>
                <div class="form-group mb-3">
                    <input id="email" type="email" class="form-control" name="email" placeholder="Email" required
                        autofocus>
                    <div class="invalid-feedback">Please enter your email.</div>
                </div>

                <div class="form-group mb-3">
                    <input id="password" type="password" class="form-control" name="password" placeholder="Password"
                        required>
                    <div class="invalid-feedback">Please enter your password.</div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary btn-block btn-lg w-100">Login</button>
                </div>
            </form>
        </div>

        <!-- Extra links -->
        <div class="below-links mt-3">
            <div><a href="auth-forgot-password.html">Forgot Password?</a></div>
            <div style="margin-top: 10px;">Don’t have an account? <a href="auth-register.html">Sign up!</a></div>
        </div>
    </div>

    <script src="assets/js/app.min.js"></script>
    <script src="assets/js/scripts.js"></script>
    <script src="assets/js/custom.js"></script>
</body>

</html>