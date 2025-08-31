<?php
require_once 'assets/php/header.php';
require 'sass/db_config.php';

$feeTypes = $conn->query("SELECT id, fee_name FROM fee_types WHERE school_id = " . $_SESSION['admin_id']);
?>
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

#show_fee_structures {
    color: #000;
}
</style>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>All Fee Structures</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Class Fee Structures</h4>
                </div>
                <div class="card-body">
                    <div id="fee_structures_result">
                        <p>Loading fee structures...</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateFeeStructureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Fee Structure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Update Form -->
                <form id="updateFeeStructureForm">
                    <input type="hidden" id="update_fee_structure_id" name="fee_structure_id">
                    <div class="mb-3">
                        <label class="">Frequency</label>
                        <select class="form-control select2" id="update_frequency" name="frequency" required>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="one Time">One Time</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="">Status</label>
                        <select class="form-control" id="update_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" readonly disabled hidden id="update_class_grade">
                    </div>
                    <!-- Same Add Fee Type Table -->
                    <div class="mb-3">
                        <label class="">Add Fee Type</label>
                        <div class="input-group mb-2">
                            <select class="form-control select2" id="update_fee_type_id">
                                <option value="">Select Fee Type</option>
                                <?php while ($row = $feeTypes->fetch_assoc()) : ?>
                                <option value="<?= htmlspecialchars($row['id']) ?>">
                                    <?= htmlspecialchars($row['fee_name']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                            <input type="number" class="form-control" id="update_rate" placeholder="Enter Rate">
                            <button type="button" class="btn btn-secondary" id="updateFeeStructureBtn">Add</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="">Total Amount</label>
                        <input type="number" class="form-control" id="update_total_amount" name="total_amount" readonly>
                    </div>
                    <table class="table table-bordered" id="update_feeTypeTable">
                        <thead>
                            <tr>
                                <th>Fee Type</th>
                                <th>Rate</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(document).ready(function() {

    // ✅ Load all fee structures on page load
    function loadFeeStructures() {
        $.ajax({
            url: "ajax/fetch_fee_structures.php",
            method: "GET",
            dataType: "html",
            success: function(data) {
                $("#fee_structures_result").html(data);
            },
            error: function(xhr) {
                $("#fee_structures_result").html(
                    "<p class='text-danger'>An error occurred while fetching data.</p>"
                );
                console.log("Status:", xhr.status);
                console.log("Response:", xhr.responseText);
            }
        });
    }
    loadFeeStructures();

    // ✅ Delete fee structure
    $(document).on('click', '.delete-fee-structure', function() {
        const id = $(this).data('id');
        if (confirm("Are you sure you want to delete this fee structure?")) {
            $.ajax({
                url: "ajax/delete_fee_structure.php",
                method: "POST",
                data: {
                    id: id
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === "success") {
                        alert("Fee structure deleted successfully.");
                        loadFeeStructures();
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr) {
                    alert("An error occurred while deleting.");
                    console.log("Status:", xhr.status);
                    console.log("Response:", xhr.responseText);
                }
            });
        }
    });

    // ✅ Open update modal & load data (basic)
    $(document).on('click', '.update-fee-structure', function() {
        var feeId = $(this).data('id');
        $('#update_fee_structure_id').val(feeId);

        // Clear previous
        $('#update_feeTypeTable tbody').empty();

        $.get("ajax/get_fee_structure.php", {
            id: feeId
        }, function(data) {
            $('#update_frequency').val(data.frequency);
            $('#update_status').val(data.status);
            $('#update_total_amount').val(data.amount);
            $('#update_class_grade').val(data.class_grade);

            // Fill Fee Type table if any
            if (data.fee_items && data.fee_items.length) {
                data.fee_items.forEach(function(item) {
                    var row = `<tr>
            <td>
              ${item.fee_name}
              <input type="hidden" class="fee_type_id" value="${item.id}">
            </td>
            <td>
              <input type="number" class="form-control rate" value="${item.rate}">
            </td>
            <td>
              <button type="button" class="btn btn-danger btn-sm remove-fee-type">Remove</button>
            </td>
          </tr>`;
                    $('#update_feeTypeTable tbody').append(row);
                });
                calculateUpdateTotal();
            }
        }, 'json');

        var modal = new bootstrap.Modal(document.getElementById('updateFeeStructureModal'));
        modal.show();
    });
    // ✅ Remove fee type row
    $(document).on('click', '.remove-fee-type', function() {
        $(this).closest('tr').remove();
        calculateUpdateTotal();
    });

    // ✅ Recalculate on rate input
    $(document).on('input', '.rate', function() {
        calculateUpdateTotal();
    });

    function calculateUpdateTotal() {
        var total = 0;
        $('#update_feeTypeTable tbody tr').each(function() {
            var rate = parseFloat($(this).find('.rate').val()) || 0;
            total += rate;
        });
        $('#update_total_amount').val(total);
    }



    //remove fee type
    $(document).on('click', '.remove-fee-type', function() {
        const row = $(this).closest('tr');
        const feeTypeId = row.find('.fee_type_id').val();
        const rate = parseFloat(row.find('.rate').val());
        const feeStructureId = $('#update_fee_structure_id').val();

        if (confirm("Are you sure you want to remove this fee type?")) {
            $.ajax({
                url: 'ajax/remove_fee_type.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    fee_type_id: feeTypeId,
                    fee_structure_id: feeStructureId,
                    rate: rate
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Fee type removed.');
                        loadFeeStructures()
                        // Refresh rows in modal
                        $('#update_feeTypeTable tbody').empty();
                        response.fee_items.forEach(function(item) {
                            const newRow = `<tr>
              <td>
                ${item.fee_name}
                <input type="hidden" class="fee_type_id" value="${item.id}">
              </td>
              <td>
                <input type="number" class="form-control rate" value="${item.rate}" readonly>
              </td>
              <td>
                <button type="button" class="btn btn-danger btn-sm remove-fee-type">Remove</button>
              </td>
            </tr>`;
                            $('#update_feeTypeTable tbody').append(newRow);
                        });
                        $('#update_total_amount').val(response.new_amount);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert('Error while removing fee type.');
                }
            });
        }
    });

    // On your Update button click
    $('#updateFeeStructureBtn').click(function(e) {
        e.preventDefault();

        // Collect form data
        var fee_structure_id = $('#update_fee_structure_id').val();
        var fee_type_id = $('#update_fee_type_id').val();
        var rate = $('#update_rate').val();
        var class_grade = $('#update_class_grade').val();
        var total_amount = $('#update_total_amount').val();

        // Send AJAX request
        $.ajax({
            url: 'ajax/update_fee_structures.php',
            type: 'POST',
            data: {
                fee_structure_id: fee_structure_id,
                fee_type_id: fee_type_id,
                rate: rate,
                class_grade: class_grade,
                total_amount: total_amount
            },
            dataType: 'json',
            success: function(response) {
                console.log(response); // ✅ See what you got back
                // Do something with the response, e.g., show message
                if (response.status === 'success') {

                    // 🔄 Reload the modal rows:
                    $('.update-fee-structure[data-id="' + response.fee_structure_id +
                        '"]').click();
                    loadFeeStructures();
                } else {
                    alert('Update failed: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
    // ⚡ Attach onchange for both
    $('#update_frequency, #update_status').on('change', function() {
        // Get selected values
        var frequency = $('#update_frequency').val();
        var status = $('#update_status').val();
        var fee_structure_id = $('#update_fee_structure_id')
            .val(); // Assuming you have this hidden input
        // ✅ AJAX call
        $.post('ajax/update_fee_structure.php', {
            fee_structure_id: fee_structure_id,
            frequency: frequency,
            status: status
        }, function(response) {
            console.log(response); // ✅ See what you got back

            if (response.status === 'success') {

                $('.update-fee-structure[data-id="' + response.fee_structure_id + '"]')
                    .click();
                loadFeeStructures();
            } else {
                alert('Update failed: ' + response.message);
            }
        }, 'json');
    });



});
</script>

<?php require_once 'assets/php/footer.php'; ?>