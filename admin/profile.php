<?php require_once 'assets/php/header.php'; ?>

<style>
/* Your existing styles */
</style>

<div class="main-content" style="min-height: 577px;">
    <section class="section">
        <div class="section-body">
            <div class="row mt-sm-4">

                <!-- Left column: School info summary -->
                <div class="col-12 col-md-12 col-lg-4">
                    <div class="card author-box">
                        <div class="card-body text-center">
                            <img id="schoolLogo" src="assets/img/users/user-1.png" alt="Logo"
                                class="rounded-circle author-box-picture"
                                style="width:120px; height:120px; object-fit:cover;">
                            <h4 id="schoolName" class="mt-2"></h4>
                            <p id="schoolType" class="text-muted"></p>
                            <p id="schoolAddress" class="text-muted"></p>
                            <p><strong>Registration No:</strong> <span id="registrationNumber"></span></p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4>Contact Details</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Affiliation Board:</strong> <span id="affiliationBoard"></span></p>
                            <p><strong>Phone:</strong> <span id="schoolPhone"></span></p>
                            <p><strong>Email:</strong> <span id="schoolEmail"></span></p>
                            <p><strong>Website:</strong> <a href="#" id="schoolWebsite" target="_blank"></a></p>
                            <p><strong>Admin Contact Person:</strong> <span id="adminContactPerson"></span></p>
                            <p><strong>Admin Email:</strong> <span id="adminEmail"></span></p>
                            <p><strong>Admin Phone:</strong> <span id="adminPhone"></span></p>
                            <p><strong>Address:</strong> <span id="fullAddress"></span></p>
                        </div>
                    </div>
                </div>

                <!-- Right column: Tabs -->
                <div class="col-12 col-md-12 col-lg-8">
                    <div class="card">
                        <div class="padding-20">
                            <ul class="nav nav-tabs" id="myTab2" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="about-tab2" data-toggle="tab" href="#about"
                                        role="tab" aria-selected="true">About</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="settings-tab2" data-toggle="tab" href="#settings" role="tab"
                                        aria-selected="false">Setting</a>
                                </li>
                                <!-- Add this inside the same <ul class="nav nav-tabs" ...> -->
                                <li class="nav-item">
                                    <a class="nav-link" id="password-tab2" data-toggle="tab" href="#password" role="tab"
                                        aria-selected="false">Security & Profile</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="password-tab2" data-toggle="tab" href="#image" role="tab"
                                        aria-selected="false">Profile Image</a>
                                </li>

                            </ul>

                            <div class="tab-content tab-bordered" id="myTab3Content">
                                <div class="tab-pane fade active show" id="about" role="tabpanel"
                                    aria-labelledby="about-tab2">
                                    <!-- About content - replicate left side info here or summary -->
                                    <h5>School Overview</h5>
                                    <p id="schoolOverview"></p>
                                </div>

                                <div class="tab-pane fade" id="settings" role="tabpanel"
                                    aria-labelledby="settings-tab2">
                                    <form id="profileForm" class="needs-validation" novalidate>
                                        <div class="card-header">
                                            <h4>Edit Profile</h4>
                                        </div>
                                        <div class="card-body">
                                            <input type="hidden" id="schoolId" name="school_id">
                                            <div class="form-group">
                                                <label for="school_name">School Name</label>
                                                <input type="text" id="school_name" name="school_name"
                                                    class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="school_type">School Type</label>
                                                <input type="text" id="school_type" name="school_type"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="registration_number">Registration Number</label>
                                                <input type="text" id="registration_number" name="registration_number"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="affiliation_board">Affiliation Board</label>
                                                <input type="text" id="affiliation_board" name="affiliation_board"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="school_email">School Email</label>
                                                <input type="email" id="school_email" name="school_email"
                                                    class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="school_phone">School Phone</label>
                                                <input type="text" id="school_phone" name="school_phone"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="school_website">School Website</label>
                                                <input type="text" id="school_website" name="school_website"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="admin_contact_person">Admin Contact Person</label>
                                                <input type="text" id="admin_contact_person" name="admin_contact_person"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="admin_email">Admin Email</label>
                                                <input type="email" id="admin_email" name="admin_email"
                                                    class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="admin_phone">Admin Phone</label>
                                                <input type="text" id="admin_phone" name="admin_phone"
                                                    class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="country">Country</label>
                                                <input type="text" id="country" name="country" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="state">State</label>
                                                <input type="text" id="state" name="state" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="city">City</label>
                                                <input type="text" id="city" name="city" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="address">Address</label>
                                                <textarea id="address" name="address" class="form-control"></textarea>
                                            </div>
                                            <!-- Optional: upload logo input here -->

                                        </div>
                                        <div class="card-footer text-right">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="password" role="tabpanel"
                                    aria-labelledby="password-tab2">
                                    <form id="passwordForm" class="needs-validation" novalidate>
                                        <div class="card-header">
                                            <h4>Change Password</h4>
                                        </div>
                                        <div class="card-body">
                                            <input type="hidden" id="schoolIdPwd" name="school_id">
                                            <div class="form-group">
                                                <label for="current_password">Current Password</label>
                                                <input type="password" id="current_password" name="current_password"
                                                    class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="new_password">New Password</label>
                                                <input type="password" id="new_password" name="new_password"
                                                    class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="confirm_password">Confirm New Password</label>
                                                <input type="password" id="confirm_password" name="confirm_password"
                                                    class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="card-footer text-right">
                                            <button type="submit" class="btn btn-primary">Update Password</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="image" role="tabpanel" aria-labelledby="password-tab2">
                                    <form id="imageFrom" class="needs-validation" novalidate>
                                        <div class="form-group">
                                            <label for="school_logo">School Logo</label>
                                            <input type="file" id="school_logo" name="school_logo" accept="image/*"
                                                class="form-control-file">
                                            <img id="logoPreview" src="assets/img/users/user-1.png" alt="Logo Preview"
                                                style="margin-top:10px; max-height:100px;">
                                        </div>

                                        <button type="button" id="uploadLogoBtn" class="btn btn-secondary">Upload
                                            Logo</button>

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
// Show preview when file is selected
$('#school_logo').on('change', function() {
    const [file] = this.files;
    if (file) {
        $('#logoPreview').attr('src', URL.createObjectURL(file));
    }
});

// AJAX upload on button click
$('#uploadLogoBtn').on('click', function() {
    let fileInput = $('#school_logo')[0];
    if (fileInput.files.length === 0) {
        alert('Please select a logo image first.');
        return;
    }

    let formData = new FormData();
    formData.append('school_logo', fileInput.files[0]);

    $.ajax({
        url: 'ajax/upload_logo.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                alert('Logo uploaded successfully!');
                // Update logo on page if needed
                $('#schoolLogo').attr('src', res.logo_path + '?t=' + new Date().getTime());
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function(xhr) {
            alert('Upload error: ' + xhr.responseText);
        }
    });
});

$('#passwordForm').on('submit', function(e) {
    e.preventDefault();

    // Simple client-side validation
    const current = $('#current_password').val().trim();
    const newPass = $('#new_password').val().trim();
    const confirmPass = $('#confirm_password').val().trim();

    if (!current || !newPass || !confirmPass) {
        alert('Please fill all password fields.');
        return;
    }
    if (newPass !== confirmPass) {
        alert('New password and confirm password do not match.');
        return;
    }

    $.ajax({
        url: 'ajax/update_password.php',
        method: 'POST',
        dataType: 'json',
        data: {
            current_password: current,
            new_password: newPass
        },
        success: function(res) {
            if (res.status === 'success') {
                alert('Password updated successfully!');
                // Optional: clear form fields
                $('#passwordForm')[0].reset();
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function(xhr) {
            alert('An error occurred: ' + xhr.responseText);
        }
    });
});

$(document).ready(function() {
    // Load profile data via AJAX
    function loadProfile() {
        $.ajax({
            url: 'ajax/get_school_profile.php',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    let d = res.data;
                    // Fill left side
                    $('#schoolLogo').attr('src', d.logo ? 'uploads/logos/' + d.logo :
                        'assets/img/users/user-1.png');
                    $('#logoPreview').attr('src', d.logo ? 'uploads/logos/' + d.logo :
                        'assets/img/users/user-1.png');
                    $('#schoolName').text(d.school_name);
                    $('#schoolType').text(d.school_type);
                    $('#schoolAddress').text(`${d.city}, ${d.state}, ${d.country}`);
                    $('#registrationNumber').text(d.registration_number);
                    $('#affiliationBoard').text(d.affiliation_board);
                    $('#schoolPhone').text(d.school_phone);
                    $('#schoolEmail').text(d.school_email);
                    $('#schoolWebsite').text(d.school_website).attr('href', d.school_website);
                    $('#adminContactPerson').text(d.admin_contact_person);
                    $('#adminEmail').text(d.admin_email);
                    $('#adminPhone').text(d.admin_phone);
                    $('#fullAddress').text(`${d.address}`);

                    // About tab summary
                    $('#schoolOverview').text(
                        `School "${d.school_name}" is a ${d.school_type} located in ${d.city}, ${d.state}, ${d.country}.`
                    );

                    // Fill form inputs
                    $('#schoolId').val(d.id);
                    $('#school_name').val(d.school_name);
                    $('#school_type').val(d.school_type);
                    $('#registration_number').val(d.registration_number);
                    $('#affiliation_board').val(d.affiliation_board);
                    $('#school_email').val(d.school_email);
                    $('#school_phone').val(d.school_phone);
                    $('#school_website').val(d.school_website);
                    $('#admin_contact_person').val(d.admin_contact_person);
                    $('#admin_email').val(d.admin_email);
                    $('#admin_phone').val(d.admin_phone);
                    $('#country').val(d.country);
                    $('#state').val(d.state);
                    $('#city').val(d.city);
                    $('#address').val(d.address);

                } else {
                    alert(res.message || 'Failed to load profile data');
                }
            },
            error: function(xhr) {
                alert('Error loading profile: ' + xhr.responseText);
            }
        });
    }

    loadProfile();

    // Form submission
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();

        // Simple client validation
        if (!this.checkValidity()) {
            this.reportValidity();
            return;
        }

        let formData = $(this).serialize();

        $.ajax({
            url: 'ajax/update_school_profile.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    alert('Profile updated successfully');
                    loadProfile();
                    // Optionally switch to About tab after update:
                    $('#about-tab2').tab('show');
                } else {
                    alert(res.message || 'Failed to update profile');
                }
            },
            error: function(xhr) {
                alert('Error updating profile: ' + xhr.responseText);
            }
        });
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>