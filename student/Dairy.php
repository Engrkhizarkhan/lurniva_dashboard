<?php require_once 'assets/php/header.php'; ?>


<div class="main-content">
    <section class="section">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Topic</th>
                    <th>Description</th>
                    <th>Deadline</th>
                    <th>Attachment</th>
                    <th>Parent Approval</th>
                </tr>
            </thead>
            <tbody id="diaryTableBody"></tbody>
        </table>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            let el = document.getElementById("dairy");
            if (el) {
                el.classList.add("active");
            }
        });
        </script>


        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script>
        $(document).ready(function() {
            // Load diary entries
            $.post("ajax/get_student_diary.php", function(data) {
                $("#diaryTableBody").html(data);
            });

            // Handle approval click
            $(document).on("click", ".approve-btn", function() {
                var diaryId = $(this).data("id");
                $.post("ajax/update_parent_approval.php", {
                    diary_id: diaryId
                }, function(resp) {
                    // Reload list after approval
                    $.post("ajax/get_student_diary.php", function(data) {
                        $("#diaryTableBody").html(data);
                    });
                });
            });
        });
        </script>

    </section>
</div>

<?php require_once 'assets/php/footer.php'; ?>