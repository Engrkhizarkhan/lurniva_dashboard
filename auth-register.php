<?php
session_start();
require_once 'admin/sass/db_config.php';

/**
 * Insert default settings for a school/student
 */
function createDefaultSettings($conn, $person, $person_id) {
    $stmt = $conn->prepare("INSERT INTO school_settings 
        (person, person_id, layout, sidebar_color, color_theme, mini_sidebar, sticky_header) 
        VALUES (?, ?, '1', '1', 'white', '0', '0')");
    $stmt->bind_param("si", $person, $person_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Validate username: must be exactly 8 alphabetic characters
 */
function validateUsername($username) {
    return preg_match("/^[A-Za-z]{8}$/", $username);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ===== SCHOOL FORM =====
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'school') {
        $username = trim($_POST['username']);
        if (!validateUsername($username)) {
            die("❌ Username must be exactly 8 alphabetic characters (A-Z only).");
        }

        $school_name = $_POST['school_name'];
        $school_type = $_POST['school_type'];
        $registration_number = $_POST['registration_number'];
        $affiliation_board = $_POST['affiliation_board'];
        $school_email = $_POST['school_email'];
        $school_phone = $_POST['school_phone'];
        $school_website = $_POST['school_website'];
        $country = $_POST['country'];
        $state = $_POST['state'];
        $city = $_POST['city'];
        $address = $_POST['address'];
        $admin_contact_person = $_POST['admin_contact_person'];
        $admin_email = $_POST['admin_email'];
        $admin_phone = $_POST['admin_phone'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Verification
        $verification_code = rand(100000, 999999);
        $is_verified = 0;
        $code_expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        $verification_attempts = 0;

        // Logo upload
        $logo_name = null;
        if (!empty($_FILES['logo']['name'])) {
            $logo_name = time() . "_" . basename($_FILES['logo']['name']);
            $logo_path = "admin/uploads/logos/";
            if (!is_dir($logo_path)) mkdir($logo_path, 0777, true);
            move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path . $logo_name);
        }

        $stmt = $conn->prepare("INSERT INTO schools 
            (school_name, school_type, registration_number, affiliation_board,
             school_email, school_phone, school_website, country, state, city, address,
             logo, admin_contact_person, username, admin_email, admin_phone, password,
             verification_code, is_verified, code_expires_at, verification_attempts)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) die("Prepare failed: " . $conn->error);

        $stmt->bind_param(
            "sssssssssssssssssiisi",
            $school_name,
            $school_type,
            $registration_number,
            $affiliation_board,
            $school_email,
            $school_phone,
            $school_website,
            $country,
            $state,
            $city,
            $address,
            $logo_name,
            $admin_contact_person,
            $username,
            $admin_email,
            $admin_phone,
            $password,
            $verification_code,
            $is_verified,
            $code_expires_at,
            $verification_attempts
        );

        if ($stmt->execute()) {
            $school_id = $conn->insert_id;

            // ✅ Insert default settings
            createDefaultSettings($conn, "admin", $school_id);

            // Send verification email
            $to = $admin_email;
            $subject = "School Account Verification";
            $message = "Hello $admin_contact_person,\n\nYour verification code is: $verification_code\n\nThis code expires in 5 minutes.";
            $headers = "From: noreply@yourdomain.com";

            if (mail($to, $subject, $message, $headers)) {
                $_SESSION['pending_email'] = $admin_email;
                $_SESSION['user_type'] = 'school';
                header("Location: verify.php");
                exit;
            } else {
                echo "❌ Email sending failed!";
            }
        } else {
            die("Execute failed: " . $stmt->error);
        }
    }

    // ===== STUDENT FORM =====
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'student') {
        $username = trim($_POST['username']);
        if (!validateUsername($username)) {
            die("❌ Username must be exactly 8 alphabetic characters (A-Z only).");
        }

        $school_id = intval($_POST['school_id']);
        $parent_name = $_POST['parent_name'];
        $parent_cnic = $_POST['parent_cnic'];
        $full_name = $_POST['full_name'];
        $gender = $_POST['gender'];
        $dob = $_POST['dob'];
        $cnic_formb = $_POST['cnic_formb'];
        $class_grade = $_POST['class_grade'];
        $section = $_POST['section'];
        $roll_number = $_POST['roll_number'];
        $address = $_POST['address'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Verification
        $verification_code = rand(100000, 999999);
        $is_verified = 0;
        $code_expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        $verification_attempts = 0;
        $status = "pending";

        // Profile upload
        $profile_name = null;
        if (!empty($_FILES['profile_photo']['name'])) {
            $profile_name = time() . "_" . basename($_FILES['profile_photo']['name']);
            $adminPath = "admin/uploads/profile/";
            if (!is_dir($adminPath)) mkdir($adminPath, 0777, true);
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], $adminPath . $profile_name);
            $schoolPath = "student/uploads/profile/";
            if (!is_dir($schoolPath)) mkdir($schoolPath, 0777, true);
            copy($adminPath . $profile_name, $schoolPath . $profile_name);
        }

        $stmt = $conn->prepare("INSERT INTO students 
            (school_id, parent_name, parent_cnic, full_name, username, gender, dob, cnic_formb,
             class_grade, section, roll_number, address, email, phone, profile_photo,
             password, status, verification_code, is_verified, code_expires_at, verification_attempts)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) die("Prepare failed: " . $conn->error);

        $stmt->bind_param(
            "issssssssssssssssisi",
            $school_id,
            $parent_name,
            $parent_cnic,
            $full_name,
            $username,
            $gender,
            $dob,
            $cnic_formb,
            $class_grade,
            $section,
            $roll_number,
            $address,
            $email,
            $phone,
            $profile_name,
            $password,
            $status,
            $verification_code,
            $is_verified,
            $code_expires_at,
            $verification_attempts
        );

        if ($stmt->execute()) {
            $student_id = $conn->insert_id;

            // ✅ Insert default settings
            createDefaultSettings($conn, "student", $student_id);

            // Send verification email
            $to = $email;
            $subject = "Student Account Verification";
            $message = "Hello $full_name,\n\nYour verification code is: $verification_code\n\nThis code expires in 5 minutes.";
            $headers = "From: noreply@yourdomain.com";

            if (mail($to, $subject, $message, $headers)) {
                $_SESSION['pending_email'] = $email;
                $_SESSION['user_type'] = 'student';
                header("Location: verify.php");
                exit;
            } else {
                echo "❌ Email sending failed!";
            }
        } else {
            die("Execute failed: " . $stmt->error);
        }
    }

    // ===== PARENT FORM =====
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'parent') {
        $full_name = $_POST['full_name'];
        $parent_cnic = $_POST['parent_cnic'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Verification
        $verification_code = rand(100000, 999999);
        $is_verified = 0;
        $code_expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        $verification_attempts = 0;
        $status = "pending";

        // Profile upload
        $profile_name = null;
        if (!empty($_FILES['profile_photo']['name'])) {
            $profile_name = time() . "_" . basename($_FILES['profile_photo']['name']);
            $parentPath = "uploads/profile/";
            $adminPath = "admin/uploads/profile/";
            if (!is_dir($parentPath)) mkdir($parentPath, 0777, true);
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], $parentPath . $profile_name);
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], $adminPath . $profile_name);
        }

        $stmt = $conn->prepare("INSERT INTO parents 
            (full_name, parent_cnic, email, phone, profile_photo,
             password, status, verification_code, is_verified, code_expires_at, verification_attempts)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) die("Prepare failed: " . $conn->error);

        $stmt->bind_param(
            "sssssssissi",
            $full_name,
            $parent_cnic,
            $email,
            $phone,
            $profile_name,
            $password,
            $status,
            $verification_code,
            $is_verified,
            $code_expires_at,
            $verification_attempts
        );

        if ($stmt->execute()) {
            // 🚫 Do NOT insert settings for parents

            // Send verification email
            $to = $email;
            $subject = "Parent Account Verification";
            $message = "Hello $full_name,\n\nYour verification code is: $verification_code\n\nThis code expires in 5 minutes.";
            $headers = "From: noreply@yourdomain.com";

            if (mail($to, $subject, $message, $headers)) {
                $_SESSION['pending_email'] = $email;
                $_SESSION['user_type'] = 'parent';
                header("Location: verify.php");
                exit;
            } else {
                echo "❌ Email sending failed!";
            }
        } else {
            die("Execute failed: " . $stmt->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-light">

    <div class="container py-5">
        <h1 class="text-center mb-4">Registration</h1>

        <!-- Tabs Navigation -->
<ul class="nav nav-pills justify-content-center mb-4" id="registrationTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="school-tab" data-bs-toggle="pill" data-bs-target="#school"
            type="button" role="tab" aria-controls="school" aria-selected="true">
            School Registration
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="student-tab" data-bs-toggle="pill" data-bs-target="#student"
            type="button" role="tab" aria-controls="student" aria-selected="false">
            Student Registration
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="parent-tab" data-bs-toggle="pill" data-bs-target="#parent"
            type="button" role="tab" aria-controls="parent" aria-selected="false">
            Parent Registration
        </button>
    </li>
</ul>

<div class="tab-content" id="registrationTabsContent">
    <!-- ================== School Form ================== -->
    <div class="tab-pane fade show active" id="school" role="tabpanel" aria-labelledby="school-tab">
        <div class="card shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <form id="schoolForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="school">

                    <div class="mb-3">
                        <label class="form-label">School Name</label>
                        <input type="text" class="form-control" name="school_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username (8 alphabets only)</label>
                        <input type="text" class="form-control" name="username"
                            pattern="[A-Za-z]{8}" maxlength="8" minlength="8" required>
                        <small class="text-muted">Exactly 8 letters, no numbers/symbols.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">School Type</label>
                        <select class="form-select" name="school_type" required>
                            <option value="">Select Type</option>
                            <option value="Public">Public</option>
                            <option value="Private">Private</option>
                            <option value="Charter">Charter</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Registration Number</label>
                        <input type="text" class="form-control" name="registration_number" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Affiliation Board</label>
                        <input type="text" class="form-control" name="affiliation_board" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">School Email</label>
                        <input type="email" class="form-control" name="school_email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">School Phone</label>
                        <input type="tel" class="form-control" name="school_phone" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">School Website</label>
                        <input type="url" class="form-control" name="school_website">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" class="form-control" name="country" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <input type="text" class="form-control" name="state" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Name</label>
                        <input type="text" class="form-control" name="admin_contact_person" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Email</label>
                        <input type="email" class="form-control" name="admin_email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Phone</label>
                        <input type="tel" class="form-control" name="admin_phone" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">School Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary w-50">Register School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================== Student Form ================== -->
    <div class="tab-pane fade" id="student" role="tabpanel" aria-labelledby="student-tab">
        <div class="card shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <form id="studentForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="student">

                    <div class="mb-3">
                        <label class="form-label">Select School</label>
                        <select class="form-select" id="school_id" name="school_id" required>
                            <option value="">Loading schools...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parent Name</label>
                        <input type="text" class="form-control" name="parent_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parent CNIC</label>
                        <input type="text" class="form-control" name="parent_cnic"
                            pattern="[0-9]{13}" maxlength="13" minlength="13" required>
                        <small class="text-muted">Enter 13 digit CNIC without dashes.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Student Full Name</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username (8 alphabets only)</label>
                        <input type="text" class="form-control" name="username"
                            pattern="[A-Za-z]{8}" maxlength="8" minlength="8" required>
                        <small class="text-muted">Exactly 8 letters, no numbers/symbols.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="">-- Select --</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="dob" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">CNIC/Form-B</label>
                        <input type="text" class="form-control" name="cnic_formb" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Class/Grade</label>
                        <select class="form-select" id="class_grade" name="class_grade" required>
                            <option value="">Select Class</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Section</label>
                        <select class="form-select" id="section" name="section" required>
                            <option value="">-- Select Section --</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Roll Number (Optional)</label>
                        <input type="text" class="form-control" name="roll_number">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address (Optional)</label>
                        <textarea class="form-control" name="address"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Profile Photo (Optional)</label>
                        <input type="file" class="form-control" name="profile_photo" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary w-50">Register Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================== Parent Form ================== -->
    <div class="tab-pane fade" id="parent" role="tabpanel" aria-labelledby="parent-tab">
        <div class="card shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <form id="parentForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="parent">

                    <div class="mb-3">
                        <label class="form-label">Parent Full Name</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>

                    <div class="mb-3">
    <label class="form-label">Parent CNIC</label>
    <input type="text" class="form-control" name="parent_cnic"
        pattern="[0-9]{12,15}" minlength="12" maxlength="15" required>
    <small class="text-muted">Enter CNIC between 12 and 15 digits without dashes.</small>
</div>


                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Profile Photo (Optional)</label>
                        <input type="file" class="form-control" name="profile_photo" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary w-50">Register Parent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


    </div>

    <script>
    $(document).ready(function() {
        // Load schools via AJAX
        $.getJSON('get_schools.php', function(response) {
            if (response.status === 'success') {
                let options = '<option value="">-- Select School --</option>';
                $.each(response.data, function(i, school) {
                    options += `<option value="${school.id}">${school.school_name}</option>`;
                });
                $('#school_id').html(options);
            }
        });

        // Load classes on school change
        $('#school_id').change(function() {
            let schoolId = $(this).val();
            if (!schoolId) return $('#class_grade').html('<option value="">Select Class</option>');
            $.getJSON('get_classes.php', {
                school_id: schoolId
            }, function(response) {
                let options = '<option value="">Select Class</option>';
                if (response.status === 'success') {
                    $.each(response.data, function(i, cls) {
                        options +=
                            `<option value="${cls.class_name}">${cls.class_name}</option>`;
                    });
                }
                $('#class_grade').html(options);
            });
        });

        // Load sections on school or class change
        $('#school_id, #class_grade').change(function() {
            let schoolId = $('#school_id').val();
            let className = $('#class_grade').val();
            if (!schoolId || !className) return $('#section').html(
                '<option value="">-- Select Section --</option>');
            $.getJSON('get_sections.php', {
                school_id: schoolId,
                class_name: className
            }, function(response) {
                let options = '<option value="">-- Select Section --</option>';
                if (response.status === 'success') {
                    $.each(response.data, function(i, sec) {
                        options += `<option value="${sec}">${sec}</option>`;
                    });
                }
                $('#section').html(options);
            });
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>