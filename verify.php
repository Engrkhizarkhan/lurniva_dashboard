<?php
session_start();
require_once 'admin/sass/db_config.php';
require_once 'mail_library.php'; // ✅ use your new library

// ✅ If session missing, redirect
if (!isset($_SESSION['pending_email']) || !isset($_SESSION['user_type'])) {
    header("Location: registration.php");
    exit;
}

$email = $_SESSION['pending_email'];
$type  = $_SESSION['user_type'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Handle verification code submission
    if (isset($_POST['verification_code'])) {
        $code = trim($_POST['verification_code']);

        if ($type === 'school') {
            $stmt = $conn->prepare("SELECT id FROM schools 
                                    WHERE school_email=? AND verification_code=? AND is_verified=0");
        } elseif ($type === 'student') {
            $stmt = $conn->prepare("SELECT id FROM students 
                                    WHERE email=? AND verification_code=? AND is_verified=0");
        } elseif ($type === 'faculty') {
            $stmt = $conn->prepare("SELECT id FROM faculty 
                                    WHERE email=? AND verification_code=? AND is_verified=0");
        } elseif ($type === 'parent') {
            $stmt = $conn->prepare("SELECT id FROM parents 
                                    WHERE email=? AND verification_code=? AND is_verified=0");
        }

        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            if ($type === 'school') {
                $update = $conn->prepare("UPDATE schools 
                                          SET is_verified=1, verification_code=NULL 
                                          WHERE school_email=?");
            } elseif ($type === 'student') {
                $update = $conn->prepare("UPDATE students 
                                          SET is_verified=1, verification_code=NULL 
                                          WHERE email=?");
            } elseif ($type === 'faculty') {
                $update = $conn->prepare("UPDATE faculty 
                                          SET is_verified=1, verification_code=NULL 
                                          WHERE email=?");
            } elseif ($type === 'parent') {
                $update = $conn->prepare("UPDATE parents 
                                          SET is_verified=1, verification_code=NULL 
                                          WHERE email=?");
            }

            $update->bind_param("s", $email);
            $update->execute();

            // ✅ Clear session
            unset($_SESSION['pending_email']);
            unset($_SESSION['user_type']);

            header("Location: login.php");
            exit;
        } else {
            $error = "❌ Invalid or expired verification code.";
        }
    }

    // ✅ Handle resend code request
    if (isset($_POST['resend'])) {
        $newCode = rand(100000, 999999);

        if ($type === 'school') {
            $update = $conn->prepare("UPDATE schools SET verification_code=? WHERE school_email=?");
        } elseif ($type === 'student') {
            $update = $conn->prepare("UPDATE students SET verification_code=? WHERE email=?");
        } elseif ($type === 'faculty') {
            $update = $conn->prepare("UPDATE faculty SET verification_code=? WHERE email=?");
        } elseif ($type === 'parent') {
            $update = $conn->prepare("UPDATE parents SET verification_code=? WHERE email=?");
        }

        $update->bind_param("ss", $newCode, $email);
        $update->execute();

        // // ✅ Send email
        // $subject = "Your Verification Code";
        // $message = "Your new verification code is: $newCode\n\nThis code expires in 5 minutes.";
        // $headers = "From: noreply@yourdomain.com";

        // mail($email, $subject, $message, $headers);

        // $success = "✅ A new verification code has been sent to $email";
        
        // ✅ Send new code via PHPMailer
        $subject = "Your Verification Code - Lurniva";
        $body = "
            <p>Hello,</p>
            <p>Your new verification code is:</p>
            <h2 style='color:#007bff;'>$newCode</h2>
            <p>This code expires in <b>5 minutes</b>.</p>
            <p>If you didn’t request this, please ignore this email.</p>
            <br>
            <p>Best regards,<br><b>Lurniva Support Team</b></p>
        ";

        if (sendMail($email, $subject, $body)) {
            $success = "✅ A new verification code has been sent to <b>$email</b>.";
        } else {
            $error = "❌ Failed to send email. Please try again.";
        }
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
                <input type="text" name="verification_code" class="form-control" pattern="[0-9]{6}" maxlength="6"
                    required>
            </div>
            <button type="submit" class="btn btn-success">Verify</button>
        </form>

        <form method="POST" class="mt-3">
            <button type="submit" name="resend" id="resendBtn" class="btn btn-link" disabled>Resend Code</button>
            <span id="timer" class="text-muted"></span>
        </form>
    </div>

    <script>
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