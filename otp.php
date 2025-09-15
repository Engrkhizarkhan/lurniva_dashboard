<?php
session_start();
require_once 'admin/sass/db_config.php';

if (!isset($_SESSION['pending_email'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);
    $email = $_SESSION['pending_email'];

    $stmt = $conn->prepare("SELECT id, verification_code, code_expires_at 
                            FROM app_admin 
                            WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if ($row['verification_code'] === $otp && strtotime($row['code_expires_at']) > time()) {
            // ✅ OTP valid → login app admin
            $_SESSION['app_admin_id'] = $row['id'];
            unset($_SESSION['pending_email']); // cleanup
            header("Location: Lurniva Dashboard/index.php");
            exit;
        } else {
            echo "<script>alert('Invalid or expired OTP.'); window.location.href='otp.php';</script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    h2 {
        margin-bottom: 20px;
        font-size: 26px;
        font-weight: 600;
        color: #333;
    }

    .otp-card {
        background: #fff;
        padding: 30px 40px;
        border-radius: 12px;
        position: relative;
        text-align: center;
        width: 380px;
    }

    .otp-card::before {
        content: "";
        position: absolute;
        top: -3px;
        left: -3px;
        right: -3px;
        bottom: -3px;
        border-radius: 14px;
        background: linear-gradient(270deg, #ff0080, #7928ca, #2af598, #ff0080);
        background-size: 600% 600%;
        animation: gradientMove 6s ease infinite;
        z-index: -1;
    }

    @keyframes gradientMove {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .logo-section {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
    }

    .logo-section img {
        width: 40px;
        height: 40px;
        margin-right: 10px;
    }

    .logo-section h1 {
        font-size: 22px;
        font-weight: bold;
        color: #333;
        margin: 0;
    }

    .otp-inputs {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .otp-inputs input {
        width: 45px;
        height: 55px;
        text-align: center;
        font-size: 22px;
        font-weight: bold;
        border: 2px solid #ccc;
        border-radius: 8px;
        outline: none;
        transition: border-color 0.3s;
    }

    .otp-inputs input:focus {
        border-color: #7928ca;
    }

    button {
        padding: 12px 40px;
        border: none;
        border-radius: 8px;
        background: linear-gradient(90deg, #ff0080, #7928ca, #2af598);
        color: white;
        font-weight: bold;
        font-size: 16px;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    button:hover {
        transform: scale(1.05);
    }
    </style>
</head>

<body>

    <h2>Enter OTP</h2>

    <form method="POST">
        <div class="otp-card">
            <div class="logo-section">
                <img src="assets/img/T Logo.png" alt="Logo">
                <h1>Lurniva</h1>
            </div>
            <div class="otp-inputs">
                <input type="text" maxlength="1" name="otp[]" required>
                <input type="text" maxlength="1" name="otp[]" required>
                <input type="text" maxlength="1" name="otp[]" required>
                <input type="text" maxlength="1" name="otp[]" required>
                <input type="text" maxlength="1" name="otp[]" required>
                <input type="text" maxlength="1" name="otp[]" required>
            </div>
            <input type="hidden" name="otp" id="otpFull">
            <button type="submit">Submit</button>
        </div>
    </form>

    <script>
    const inputs = document.querySelectorAll(".otp-inputs input");
    const otpFull = document.getElementById("otpFull");

    inputs.forEach((input, index) => {
        input.addEventListener("input", () => {
            if (input.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            otpFull.value = Array.from(inputs).map(i => i.value).join('');
        });
        input.addEventListener("keydown", (e) => {
            if (e.key === "Backspace" && input.value === "" && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });
    </script>
</body>

</html>