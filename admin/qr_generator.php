<?php require_once 'assets/php/header.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("qrGenerator");
    if (el) {
        el.classList.add("active");
    }
});
</script>
<div class="main-content">
    <section class="section">
        <div class="container">

            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col text-center">
                    <h2 class="fw-bold">QR Code Generator</h2>
                    <p class="text-muted mb-0">Easily generate QR codes for classes or students</p>
                </div>
            </div>

            <!-- Card -->
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">

                            <!-- Select Type -->
                            <div class="mb-3">
                                <label for="typeSelect" class="form-label">Select Type</label>
                                <select id="typeSelect" class="form-control">
                                    <option value="">-- Choose Type --</option>
                                    <option value="class">Class</option>
                                    <option value="student">Student</option>
                                </select>
                            </div>

                            <!-- Select Item -->
                            <div class="mb-3">
                                <label for="itemSelect" class="form-label">Select Item</label>
                                <select id="itemSelect" class="form-control">
                                    <option value="">-- Choose --</option>
                                </select>
                            </div>

                            <!-- QR Code Display -->
                            <!-- QR Code Display -->
                            <div class="text-center mt-4">
                                <div id="qrBox" class="p-4 border rounded">
                                    <p class="text-muted mb-0">Select an item to generate QR Code</p>
                                </div>
                                <button id="downloadQR" class="btn btn-primary mt-3 d-none">
                                    <i class="fas fa-download"></i> Download QR
                                </button>
                            </div>



                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2();

    // Debug log
    console.log("Select2 initialized!");

    // When type changes, fetch items
    $('#typeSelect').on('change', function() {
        let type = $(this).val();
        $('#itemSelect').html('<option value="">-- Loading --</option>');

        if (type === '') {
            $('#itemSelect').html('<option value="">-- Choose --</option>');
            return;
        }

        $.ajax({
            url: 'ajax/fetch_options.php',
            type: 'POST',
            data: {
                type: type
            },
            success: function(response) {
                $('#itemSelect').html(response);
            },
            error: function() {
                $('#itemSelect').html('<option value="">Error loading</option>');
            }
        });
    });

    // When item changes, generate QR
    // When item changes, generate QR
    $('#itemSelect').on('change', function() {
        let type = $('#typeSelect').val();
        let id = $(this).val();

        if (!id) {
            $('#qrBox').html('<p class="text-muted">Select an item to generate QR Code</p>');
            $('#downloadQR').addClass('d-none');
            return;
        }

        $.ajax({
            url: 'ajax/generate_qr.php',
            type: 'POST',
            data: {
                type: type,
                id: id
            },
            success: function(response) {
                $('#qrBox').html(response);
                $('#downloadQR').removeClass('d-none'); // Show button
            },
            error: function() {
                $('#qrBox').html(
                    '<div class="alert alert-danger">Error generating QR</div>');
                $('#downloadQR').addClass('d-none');
            }
        });
    });

    // Download button
    $('#downloadQR').on('click', function() {
        let img = $('#qrBox img').attr('src');
        if (!img) return;

        let link = document.createElement('a');
        link.href = img;
        link.download = 'qrcode.png';
        link.click();
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>