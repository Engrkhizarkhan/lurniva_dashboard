<?php require_once 'assets/php/header.php'; ?>
<style>
#facultyForm {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}
</style>

<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Faculty Registration</h2>
        </div>

        <form id="facultyForm" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" />
            </div>
            <div class="mb-3">
                <label>CNIC/ID Number</label>
                <input type="text" name="cnic" class="form-control" />
            </div>
            <div class="mb-3">
                <label>Qualification</label>
                <input type="text" name="qualification" class="form-control" />
            </div>
            <div class="mb-3">
                <label>Subjects</label>
                <input type="text" name="subjects" class="form-control" placeholder="e.g., Math, Physics" />
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" />
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" class="form-control" name="password" />
            </div>
            <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" />
            </div>
            <div class="mb-3">
                <label>Address (Optional)</label>
                <textarea name="address" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Date of Joining</label>
                <input type="date" name="joining_date" class="form-control" />
            </div>
            <div class="mb-3">
                <label>Employment Type</label>
                <select name="employment_type" class="form-control">
                    <option value="">-- Select Type --</option>
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Contractual">Contractual</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Schedule Preference</label>
                <select name="schedule_preference" class="form-control">
                    <option value="">-- Select Schedule --</option>
                    <option value="Morning">Morning</option>
                    <option value="Evening">Evening</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Photo (Optional)</label>
                <input type="file" name="photo" class="form-control" accept="image/*" />
            </div>
            <button type="submit" class="btn btn-primary w-100">
                Register Faculty
            </button>
            <div id="facultyResponse" class="mt-3 text-center"></div>
        </form>
    </section>
    <hr>
    <h3>All Faculty</h3>
    <div class="table-responsive">

        <div id="facultyTable">Loading...</div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {

    loadFaculty();

    $(document).on('submit', '#facultyForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const $btn = $('button[type=submit]');
        let action = $btn.data('action') || 'insert';
        let id = $btn.data('id') || '';

        // Append action and id
        formData.append('action', action);
        if (id) formData.append('id', id);

        // Basic validation
        if (!formData.get('full_name') || !formData.get('email')) {
            alert('Please fill in required fields: Name and Email.');
            return;
        }

        // Only require password on insert
        if (action === 'insert' && !formData.get('password')) {
            alert('Password is required for new faculty.');
            return;
        }

        $.ajax({
            url: 'ajax/faculty_crud.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.trim() === 'success' || response.includes(
                        'Updated successfully')) {
                    $('#facultyForm')[0].reset();
                    $btn.text('Register Faculty').removeData('action').removeData('id');

                    // Remove existing_photo hidden input
                    $('[name=existing_photo]').remove();

                    loadFaculty();
                } else {
                    alert('Server Response: ' + response);
                }
            },
            error: function() {
                alert('Failed to submit faculty data.');
            }
        });
    });

    function loadFaculty() {
        $.post("ajax/faculty_crud.php", {
            action: "getAll"
        }, function(res) {
            $("#facultyTable").html(res);
        });
    }

    $(document).on("click", ".deleteBtn", function() {
        if (confirm("Are you sure?")) {
            $.post("ajax/faculty_crud.php", {
                id: $(this).data("id"),
                action: "delete"
            }, function(res) {
                alert("Delete Response: " + res);
                loadFaculty();
            });
        }
    });

    $(document).on("click", ".editBtn", function() {
        const id = $(this).data("id");
        $.post("ajax/faculty_crud.php", {
            id: id,
            action: "getOne"
        }, function(res) {
            const data = JSON.parse(res);

            // Populate form
            for (const key in data) {
                if ($(`[name=${key}]`).length) {
                    $(`[name=${key}]`).val(data[key]);
                }
            }

            const $btn = $('button[type=submit]');
            $btn.text("Update Faculty").data("action", "update").data("id", id);

            // Add or update hidden existing_photo input
            if ($('[name=existing_photo]').length) {
                $('[name=existing_photo]').val(data.photo);
            } else {
                $("#facultyForm").append(
                    `<input type="hidden" name="existing_photo" value="${data.photo}">`
                );
            }
        });
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>