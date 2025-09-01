<?php
session_start();

// --- Show all errors temporarily ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Database ---
require_once 'admin/sass/db_config.php';

// Test DB connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// --- Helper: send email ---
function sendMail($to, $subject, $message) {
    $from = "shayans1215225@gmail.com"; 
    $headers  = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    return mail($to, $subject, $message, $headers);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo "<script>alert('Email and password are required.'); window.location.href='login.php';</script>";
        exit;
    }

    echo "<pre>Debug: Trying to find user with email: $email</pre>";

    // --- Schools ---
    $stmt = $conn->prepare("SELECT id, school_name, admin_contact_person, password, is_verified, verification_code FROM schools WHERE school_email = ?");
    if (!$stmt) { die("Prepare failed (schools): " . $conn->error); }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        echo "<pre>Debug: Found school user: "; print_r($user); echo "</pre>";

        if (password_verify($password, $user['password'])) {
            echo "<pre>Debug: Password correct</pre>";
            if ($user['is_verified'] == 1) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['admin_contact_person'];
                $_SESSION['school_name'] = $user['school_name'];
                echo "<pre>Debug: Redirecting to admin/index.php</pre>";
                exit;
            }
        } else {
            echo "<pre>Debug: Invalid password for school user</pre>";
        }
        exit;
    }

    // --- Faculty ---
    $stmt = $conn->prepare("SELECT id, campus_id, full_name, email, password, photo, is_verified, verification_code FROM faculty WHERE email = ?");
    if (!$stmt) { die("Prepare failed (faculty): " . $conn->error); }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $faculty = $result->fetch_assoc();
        echo "<pre>Debug: Found faculty user: "; print_r($faculty); echo "</pre>";
        if (password_verify($password, $faculty['password'])) {
            echo "<pre>Debug: Faculty password correct</pre>";
        } else {
            echo "<pre>Debug: Invalid password for faculty user</pre>";
        }
        exit;
    }

    // --- Students ---
    $stmt = $conn->prepare("SELECT id, school_id, full_name, email, password, profile_photo, is_verified, verification_code FROM students WHERE email = ?");
    if (!$stmt) { die("Prepare failed (students): " . $conn->error); }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $student = $result->fetch_assoc();
        echo "<pre>Debug: Found student user: "; print_r($student); echo "</pre>";
        if (password_verify($password, $student['password'])) {
            echo "<pre>Debug: Student password correct</pre>";
        } else {
            echo "<pre>Debug: Invalid password for student user</pre>";
        }
        exit;
    }

    echo "<pre>Debug: No user found with this email</pre>";
}
?>

<form method="POST" action="">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>
