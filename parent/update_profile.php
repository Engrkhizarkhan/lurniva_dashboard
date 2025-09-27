<?php
session_start();
require_once '../admin/sass/db_config.php';

// ✅ Ensure parent logged in
if (!isset($_SESSION['parent_id'])) {
    header("Location: ../login.php");
    exit;
}

$parent_id = $_SESSION['parent_id'];

// ✅ Fetch existing parent record
$stmt = $conn->prepare("SELECT profile_photo, password FROM parents WHERE id = ?");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$parent = $stmt->get_result()->fetch_assoc();
$currentPhoto = $parent['profile_photo'];
$currentPassword = $parent['password'];

// ✅ Collect form inputs
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// ✅ File upload
$profile_photo = $currentPhoto;
if (!empty($_FILES['profile_photo']['name'])) {
    $uploadDir = "uploads/profile/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $newFileName = time() . "_" . basename($_FILES['profile_photo']['name']);
    $targetPath = $uploadDir . $newFileName;
    $targetadmin = "../admin/uploads/profile/". $newFileName;

    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath)) {
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetadmin);
        $profile_photo = $newFileName;
        // ✅ Remove old photo if exists
        if ($currentPhoto && file_exists($uploadDir . $currentPhoto)) {
            unlink($uploadDir . $currentPhoto);
        }
    }
}

// ✅ Password update (only if new + confirm provided)
if (!empty($new_password)) {
    if ($new_password !== $confirm_password) {
        echo "<script>alert('❌ Passwords do not match.'); window.location.href='profile.php';</script>";
        exit;
    }
    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
} else {
    $hashedPassword = $currentPassword; // keep old password
}

// ✅ Update DB
$stmt = $conn->prepare("UPDATE parents 
                        SET full_name=?, email=?, phone=?, profile_photo=?, password=? 
                        WHERE id=?");
$stmt->bind_param("sssssi", $full_name, $email, $phone, $profile_photo, $hashedPassword, $parent_id);

if ($stmt->execute()) {
    // ✅ Update session with new values
    $_SESSION['parent_name']  = $full_name;
    $_SESSION['parent_phone'] = $phone;
    $_SESSION['parent_photo'] = $profile_photo;

    echo "<script>alert('✅ Profile updated successfully.'); window.location.href='profile.php';</script>";
} else {
    echo "<script>alert('❌ Update failed.'); window.location.href='profile.php';</script>";
}
?>
