<?php require_once 'assets/php/header.php'; ?>


<style>
#fee_type {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#fee_type svg {
    color: #6777ef !important;
}

#fee_type span {
    color: #6777ef !important;
}

#fee_type ul {
    display: block !important;
}

#fee_type_data {
    color: #000;
}
</style>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Fee Type Form</h2>
        </div>

        <div class="section-body">
            <form id="feeTypeForm">
                <input type="hidden" id="school_id" value="<?= $_SESSION['admin_id']; ?>">
                <input type="hidden" id="id"> <!-- Used for edit -->

                <div class="mb-3">
                    <label for="fee_name" class="form-label">Fee Name</label>
                    <input type="text" class="form-control" id="fee_name" required>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
            </form>

            <div id="response" class="mt-3"></div>

            <hr>
            <h4>Fee Types</h4>
            <table class="table table-bordered" id="feeTypeTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fee Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
function loadFeeTypes() {
    $.get("ajax/get_fee_types.php", function(data) {
        let rows = "";
        data.forEach(fee => {
            rows += `
                <tr>
                    <td>${fee.id}</td>
                    <td>${fee.fee_name}</td>
                    <td>${fee.status}</td>
                    <td>
                        <button class='btn btn-sm btn-info editBtn' 
                            data-id='${fee.id}' data-name='${fee.fee_name}' 
                            data-status='${fee.status}'>Edit</button>
                        <button class='btn btn-sm btn-danger deleteBtn' 
                            data-id='${fee.id}'>Delete</button>
                    </td>
                </tr>
            `;
        });
        $("#feeTypeTable tbody").html(rows);
    }, "json");
}

loadFeeTypes();

$("#feeTypeForm").on("submit", function(e) {
    e.preventDefault();
    const data = {
        school_id: $("#school_id").val(),
        fee_name: $("#fee_name").val(),
        status: $("#status").val(),
        id: $("#id").val() // ✅ this will be empty on insert
    };

    $.ajax({
        url: "ajax/save_fee_type.php",
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json",
        success: function(response) {
            $("#response").html(
                `<div class="alert alert-${response.status === 'success' ? 'success' : 'danger'}">${response.message}</div>`
            );
            if (response.status === 'success') {
                $("#feeTypeForm")[0].reset();
                $("#id").val('');
                loadFeeTypes();
            }
        }
    });
});

$(document).on("click", ".editBtn", function() {
    $("#id").val($(this).data("id"));
    $("#fee_name").val($(this).data("name"));
    $("#status").val($(this).data("status"));
});

$(document).on("click", ".deleteBtn", function() {
    if (confirm("Delete this fee type?")) {
        $.post("ajax/delete_fee_type.php", {
            id: $(this).data("id")
        }, function(response) {
            $("#response").html(
                `<div class="alert alert-${response.status === 'success' ? 'success' : 'danger'}">${response.message}</div>`
            );
            if (response.status === 'success') {
                loadFeeTypes();
            }
        }, "json");
    }
});
</script>
<?php require_once 'assets/php/footer.php'; ?>