<?php require_once 'assets/php/header.php'; ?>

<div class="main-content" style="min-height: 577px;">
    <section class="section">
        <div class="section-body">
            <div class="row mt-sm-4">

                <!-- Left column: Student info summary -->
                <div class="col-12 col-md-12 col-lg-4">
                    <div class="card author-box">
                        <div class="card-body text-center">
                            <img id="studentPhoto" src="assets/img/users/user-1.png" alt="Profile Photo"
                                class="rounded-circle author-box-picture"
                                style="width:120px; height:120px; object-fit:cover;">
                            <h4 id="studentName" class="mt-2"></h4>
                            <p id="studentClass" class="text-muted"></p>
                            <p><strong>Email:</strong> <span id="studentEmail"></span></p>
                            <p><strong>Phone:</strong> <span id="studentPhone"></span></p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4>Details</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Parent Name:</strong> <span id="parentName"></span></p>
                            <p><strong>Parent Email:</strong> <span id="parentEmail"></span></p>
                            <p><strong>Gender:</strong> <span id="studentGender"></span></p>
                            <p><strong>Date of Birth:</strong> <span id="studentDob"></span></p>
                            <p><strong>CNIC/Form-B:</strong> <span id="studentCnic"></span></p>
                            <p><strong>Class:</strong> <span id="studentClassGrade"></span></p>
                            <p><strong>Section:</strong> <span id="studentSection"></span></p>
                            <p><strong>Roll Number:</strong> <span id="studentRoll"></span></p>
                            <p><strong>Address:</strong> <span id="studentAddress"></span></p>
                        </div>
                    </div>
                </div>

                <!-- Right column: Tabs -->
                <div class="col-12 col-md-12 col-lg-8">
                    <div class="card">
                        <div class="padding-20">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#about">About</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#settings">Profile Settings</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#password">Security</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#image">Profile Image</a>
                                </li>
                            </ul>

                            <div class="tab-content tab-bordered">
                                <!-- About -->
                                <div class="tab-pane fade show active" id="about">
                                    <h5>Student Overview</h5>
                                    <p id="studentOverview"></p>
                                </div>

                                <!-- Settings -->
                                <div class="tab-pane fade" id="settings">
                                    <form id="studentForm" class="needs-validation" novalidate>
                                        <div class="card-header">
                                            <h4>Edit Profile</h4>
                                        </div>
                                        <div class="card-body">
                                            <input type="hidden" id="studentId" name="student_id">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" id="full_name" name="full_name" class="form-control"
                                                    required>
                                            </div>
                                            <div class="form-group">
                                                <label>Parent Name</label>
                                                <input type="text" id="parent_name" name="parent_name"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Parent Email</label>
                                                <input type="number" id="parent_email" name="parent_email"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <input type="text" id="gender" name="gender" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Date of Birth</label>
                                                <input type="date" id="dob" name="dob" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>CNIC/Form-B</label>
                                                <input type="text" id="cnic_formb" name="cnic_formb"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Class</label>
                                                <input type="text" id="class_grade" name="class_grade"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Section</label>
                                                <input type="text" id="section" name="section" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Roll Number</label>
                                                <input type="text" id="roll_number" name="roll_number"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" id="email" name="email" class="form-control"
                                                    required>
                                            </div>
                                            <div class="form-group">
                                                <label>Phone</label>
                                                <input type="text" id="phone" name="phone" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Address</label>
                                                <textarea id="address" name="address" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="card-footer text-right">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Password -->
                                <div class="tab-pane fade" id="password">
                                    <form id="passwordForm" novalidate>
                                        <div class="card-header">
                                            <h4>Change Password</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Current Password</label>
                                                <input type="password" id="current_password" name="current_password"
                                                    class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>New Password</label>
                                                <input type="password" id="new_password" name="new_password"
                                                    class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Confirm Password</label>
                                                <input type="password" id="confirm_password" name="confirm_password"
                                                    class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="card-footer text-right">
                                            <button type="submit" class="btn btn-primary">Update Password</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Image -->
                                <div class="tab-pane fade" id="image">
                                    <form id="imageForm" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label>Profile Photo</label>
                                            <input type="file" id="student_photo" name="student_photo" accept="image/*"
                                                class="form-control-file">
                                            <img id="photoPreview" src="assets/img/users/user-1.png"
                                                style="margin-top:10px; max-height:100px;">
                                        </div>
                                        <button type="button" id="uploadPhotoBtn"
                                            class="btn btn-primary">Upload</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    // Load profile
    function loadProfile() {
        $.get('ajax/get_school_profile.php', function(res) {
            if (res.status === 'success') {
                let d = res.data;
                $('#studentPhoto').attr('src', d.profile_photo ? 'uploads/profile/' + d.profile_photo :
                    'assets/img/users/user-1.png');
                $('#photoPreview').attr('src', d.profile_photo ? 'uploads/profile/' + d.profile_photo :
                    'assets/img/users/user-1.png');
                $('#studentName').text(d.full_name);
                $('#studentClass').text('Class: ' + d.class_grade + ' - Section: ' + d.section);
                $('#studentEmail').text(d.email);
                $('#studentPhone').text(d.phone);
                $('#parentName').text(d.parent_name);
                $('#parentEmail').text(d.parent_cnic);
                $('#studentGender').text(d.gender);
                $('#studentDob').text(d.dob);
                $('#studentCnic').text(d.cnic_formb);
                $('#studentClassGrade').text(d.class_grade);
                $('#studentSection').text(d.section);
                $('#studentRoll').text(d.roll_number);
                $('#studentAddress').text(d.address);

                $('#studentOverview').text(
                    `Student ${d.full_name} (${d.gender}), studying in class ${d.class_grade}, section ${d.section}.`
                    );

                // Fill form
                $('#studentId').val(d.id);
                $('#full_name').val(d.full_name);
                $('#parent_name').val(d.parent_name);
                $('#parent_email').val(d.parent_cnic);
                $('#gender').val(d.gender);
                $('#dob').val(d.dob);
                $('#cnic_formb').val(d.cnic_formb);
                $('#class_grade').val(d.class_grade);
                $('#section').val(d.section);
                $('#roll_number').val(d.roll_number);
                $('#email').val(d.email);
                $('#phone').val(d.phone);
                $('#address').val(d.address);
            } else {
                alert(res.message);
            }
        }, 'json');
    }
    loadProfile();

    // Update profile
    $('#studentForm').submit(function(e) {
        e.preventDefault();
        $.post('ajax/update_school_profile.php', $(this).serialize(), function(res) {
            if (res.status === 'success') {
                alert('Profile updated');
                loadProfile();
            } else {
                alert(res.message);
            }
        }, 'json');
    });

    // Update password
    $('#passwordForm').submit(function(e) {
        e.preventDefault();
        let current = $('#current_password').val();
        let newPass = $('#new_password').val();
        let confirmPass = $('#confirm_password').val();

        if (newPass !== confirmPass) {
            alert('Passwords do not match');
            return;
        }

        $.post('ajax/update_password.php', {
                current_password: current,
                new_password: newPass
            },
            function(res) {
                if (res.status === 'success') {
                    alert('Password updated');
                    $('#passwordForm')[0].reset();
                } else {
                    alert(res.message);
                }
            }, 'json');
    });

    // Upload photo
    $('#uploadPhotoBtn').click(function() {
        let file = $('#student_photo')[0].files[0];
        if (!file) {
            alert('Select an image first');
            return;
        }
        let formData = new FormData();
        formData.append('student_photo', file);

        $.ajax({
            url: 'ajax/upload_logo.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    alert('Photo uploaded');
                    $('#studentPhoto').attr('src', res.photo_path + '?t=' + new Date()
                        .getTime());
                    $('#photoPreview').attr('src', res.photo_path + '?t=' + new Date()
                        .getTime());
                } else {
                    alert(res.message);
                }
            }
        });
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>