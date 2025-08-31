<?php
session_start();
require_once 'admin/sass/db_config.php';

if (!isset($_SESSION['pending_email']) || !isset($_SESSION['user_type'])) {
    header("Location: registration.php");
    exit;
}

$email = mysqli_real_escape_string($conn, $_SESSION['pending_email']);
$type  = $_SESSION['user_type'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Handle verification code submission
    if (isset($_POST['verification_code'])) {
        $code = mysqli_real_escape_string($conn, $_POST['verification_code']);

        if ($type === 'school') {
            $sql = "SELECT id FROM schools 
                    WHERE school_email='$email' 
                      AND verification_code='$code' 
                      AND is_verified=0";
        } else if ($type === 'student') {
            $sql = "SELECT id FROM students 
                    WHERE email='$email' 
                      AND verification_code='$code' 
                      AND is_verified=0";
        } else if ($type === 'faculty') {
            $sql = "SELECT id FROM faculty 
                    WHERE email='$email' 
                      AND verification_code='$code' 
                      AND is_verified=0";
        }

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            if ($type === 'school') {
                $update = "UPDATE schools 
                           SET is_verified=1, verification_code=NULL 
                           WHERE school_email='$email'";
            } else if ($type === 'student') {
                $update = "UPDATE students 
                           SET is_verified=1, verification_code=NULL 
                           WHERE email='$email'";
            } else if ($type === 'faculty') {
                $update = "UPDATE faculty 
                           SET is_verified=1, verification_code=NULL 
                           WHERE email='$email'";
            }
            mysqli_query($conn, $update);

            unset($_SESSION['pending_email']);
            unset($_SESSION['user_type']);
            header("Location: login.php");
            exit;
        } else {
            $error = "Invalid or expired verification code.";
        }
    }

    // ✅ Handle resend code request
    if (isset($_POST['resend'])) {
        $newCode = rand(100000, 999999);

        if ($type === 'school') {
            $update = "UPDATE schools SET verification_code='$newCode' WHERE school_email='$email'";
        } else if ($type === 'student') {
            $update = "UPDATE students SET verification_code='$newCode' WHERE email='$email'";
        } else if ($type === 'faculty') {
            $update = "UPDATE faculty SET verification_code='$newCode' WHERE email='$email'";
        }
        mysqli_query($conn, $update);

        // 👉 TODO: send $newCode via email (use PHPMailer or mail())
        // mail($email, "Your Verification Code", "Your new code is: $newCode");

        $success = "A new verification code has been sent to $email";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Email Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Verify Your Email</h2>

        <?php if (isset($error))   echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Enter 6-digit Code</label>
                <input type="text" name="verification_code" class="form-control" maxlength="6" required>
            </div>
            <button type="submit" class="btn btn-success">Verify</button>
        </form>

        <form method="POST" class="mt-3">
            <button type="submit" name="resend" id="resendBtn" class="btn btn-link" disabled>Resend Code</button>
            <span id="timer" class="text-muted"></span>
        </form>
    </div>

    <script>
    // Countdown timer (in seconds)
    let countdown = 60; // 1 minute
    let resendBtn = document.getElementById("resendBtn");
    let timerEl = document.getElementById("timer");

    function updateTimer() {
        if (countdown > 0) {
            resendBtn.disabled = true;
            timerEl.innerText = "Resend available in " + countdown + "s";
            countdown--;
            setTimeout(updateTimer, 1000);
        } else {
            resendBtn.disabled = false;
            timerEl.innerText = "";
        }
    }
    updateTimer();
    </script>
</body>

</html>