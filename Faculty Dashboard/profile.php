<?php require_once 'assets/php/header.php'; ?>

<div class="main-content" style="min-height: 577px;">
    <section class="section">
        <div class="section-body">
            <div class="row mt-sm-4">

                <!-- Left column: Faculty info summary -->
                <div class="col-12 col-md-12 col-lg-4">
                    <div class="card author-box">
                        <div class="card-body text-center">
                            <img id="facultyPhoto" src="assets/img/users/user-1.png" alt="Profile Photo"
                                class="rounded-circle author-box-picture"
                                style="width:120px; height:120px; object-fit:cover;">
                            <h4 id="facultyName" class="mt-2"></h4>
                            <p id="facultyQualification" class="text-muted"></p>
                            <p><strong>Email:</strong> <span id="facultyEmail"></span></p>
                            <p><strong>Phone:</strong> <span id="facultyPhone"></span></p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4>Details</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>CNIC:</strong> <span id="facultyCnic"></span></p>
                            <p><strong>Subjects:</strong> <span id="facultySubjects"></span></p>
                            <p><strong>Joining Date:</strong> <span id="facultyJoiningDate"></span></p>
                            <p><strong>Employment Type:</strong> <span id="facultyEmployment"></span></p>
                            <p><strong>Schedule Preference:</strong> <span id="facultySchedule"></span></p>
                            <p><strong>Address:</strong> <span id="facultyAddress"></span></p>
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
                                    <h5>Faculty Overview</h5>
                                    <p id="facultyOverview"></p>
                                </div>

                                <!-- Settings -->
                                <div class="tab-pane fade" id="settings">
                                    <form id="facultyForm" class="needs-validation" novalidate>
                                        <div class="card-header">
                                            <h4>Edit Profile</h4>
                                        </div>
                                        <div class="card-body">
                                            <input type="hidden" id="facultyId" name="faculty_id">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" id="full_name" name="full_name" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>CNIC</label>
                                                <input type="text" id="cnic" name="cnic" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Qualification</label>
                                                <input type="text" id="qualification" name="qualification" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Subjects</label>
                                                <input type="text" id="subjects" name="subjects" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" id="email" name="email" class="form-control" required>
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
                                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>New Password</label>
                                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Confirm Password</label>
                                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
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
                                            <input type="file" id="faculty_photo" name="faculty_photo" accept="image/*" class="form-control-file">
                                            <img id="photoPreview" src="assets/img/users/user-1.png"
                                                style="margin-top:10px; max-height:100px;">
                                        </div>
                                        <button type="button" id="uploadPhotoBtn" class="btn btn-primary">Upload</button>
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
                $('#facultyPhoto').attr('src', d.photo ? 'uploads/profile/' + d.photo : 'assets/img/users/user-1.png');
                $('#photoPreview').attr('src', d.photo ? 'uploads/profile/' + d.photo : 'assets/img/users/user-1.png');
                $('#facultyName').text(d.full_name);
                $('#facultyQualification').text(d.qualification);
                $('#facultyEmail').text(d.email);
                $('#facultyPhone').text(d.phone);
                $('#facultyCnic').text(d.cnic);
                $('#facultySubjects').text(d.subjects);
                $('#facultyJoiningDate').text(d.joining_date);
                $('#facultyEmployment').text(d.employment_type);
                $('#facultySchedule').text(d.schedule_preference);
                $('#facultyAddress').text(d.address);

                $('#facultyOverview').text(`Faculty ${d.full_name} (${d.qualification}), teaching ${d.subjects}.`);

                // Fill form
                $('#facultyId').val(d.id);
                $('#full_name').val(d.full_name);
                $('#cnic').val(d.cnic);
                $('#qualification').val(d.qualification);
                $('#subjects').val(d.subjects);
                $('#email').val(d.email);
                $('#phone').val(d.phone);
                $('#address').val(d.address);
                $('#joining_date').val(d.joining_date);
                $('#employment_type').val(d.employment_type);
                $('#schedule_preference').val(d.schedule_preference);
            } else {
                alert(res.message);
            }
        }, 'json');
    }
    loadProfile();

    // Update profile
    $('#facultyForm').submit(function(e) {
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

        $.post('ajax/update_password.php', {current_password: current, new_password: newPass}, function(res) {
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
        let file = $('#faculty_photo')[0].files[0];
        if (!file) {
            alert('Select an image first');
            return;
        }
        let formData = new FormData();
        formData.append('faculty_photo', file);

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
                    $('#facultyPhoto').attr('src', res.photo_path + '?t=' + new Date().getTime());
                    $('#photoPreview').attr('src', res.photo_path + '?t=' + new Date().getTime());
                } else {
                    alert(res.message);
                }
            }
        });
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>
