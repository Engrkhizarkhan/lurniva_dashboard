<?php 
require_once 'assets/php/header.php'; 

if (!isset($_POST['id'])) { // match AJAX param
    header("Location: students_list.php");
    exit;
}
$student_id = intval($_POST['id']);

?>

<style>
#apps {
    padding-left: 20px;
    position: relative;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#apps i {
    color: #6777ef !important;
}
</style>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h4>Student Profile</h4>
        </div>

        <!-- Student Profile Card -->
        <div class="card shadow p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img id="student_photo" src="assets/img/default.png" class="img-fluid rounded-circle mb-3"
                        style="max-width: 150px; border: 4px solid #ddd;">
                </div>
                <div class="col-md-9">
                    <div class="row mb-2">
                        <div class="col-sm-6"><strong>Full Name:</strong> <span class="text-muted"
                                id="student_name"></span></div>
                        <div class="col-sm-6"><strong>Father's Name:</strong> <span class="text-muted"
                                id="student_father"></span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-6"><strong>Roll No:</strong> <span class="text-muted"
                                id="student_roll"></span></div>
                        <div class="col-sm-6"><strong>Form-B / CNIC:</strong> <span class="text-muted"
                                id="student_cnic"></span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-6"><strong>Phone Number:</strong> <span class="text-muted"
                                id="student_phone"></span></div>
                        <div class="col-sm-6"><strong>City:</strong> <span class="text-muted" id="student_city"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12"><strong>Address:</strong> <span class="text-muted"
                                id="student_address"></span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Class Details Card -->
        <div class="section-header">
            <h4>Class Data</h4>
        </div>
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-sm-6"><strong>Class:</strong> <span class="text-muted" id="class_name"></span></div>
                <div class="col-sm-6"><strong>Section:</strong> <span class="text-muted" id="class_section"></span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-6"><strong>Roll No:</strong> <span class="text-muted" id="student_roll_class"></span>
                </div>
                <div class="col-sm-6"><strong>Class Teacher:</strong> <span class="text-muted"
                        id="class_teacher"></span></div>
            </div>
        </div>

        <!-- Subjects with Ratings -->
        <div class="section-header">
            <h4>Subjects</h4>
        </div>
        <div class="row" id="subject_cards"></div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(function() {
        loadStudentProfile(<?php echo $student_id; ?>);
    });

    function loadStudentProfile(studentId) {
        $.ajax({
            url: 'ajax/get_student.php',
            type: 'POST',
            dataType: 'json',
            data: {
                student_id: studentId
            },
            success: function(response) {
                if (response.status === 'success') {
                    let s = response.data.student;
                    let c = response.data.class;
                    let subjects = response.data.subjects;

                    // 👉 Student details
                    $('#student_name').text(s.full_name);
                    $('#student_father').text(s.parent_name);
                    $('#student_roll').text(s.roll_number);
                    $('#student_roll_class').text(s.roll_number);
                    $('#student_cnic').text(s.cnic_formb);
                    $('#student_phone').text(s.phone);
                    $('#student_city').text(s.city);
                    $('#student_address').text(s.address);
                    $('#student_photo').attr('src', s.profile_photo || 'assets/img/default.png');

                    // 👉 Class details
                    $('#class_name').text(c.class_name);
                    $('#class_section').text(c.section);
                    $('#class_teacher').text(c.teacher_name || '');

                    // 👉 Card background colors
                    let colors = [
                        'l-bg-green',
                        'l-bg-cyan',
                        'l-bg-orange',
                        'l-bg-purple',
                        'l-bg-red'
                    ];

                    // 👉 Build subject cards
                    let subjectHTML = '';
                    subjects.forEach((sub, index) => {
                        const colorClass = colors[index % colors.length];
                        subjectHTML += `
        <div class="col-xl-3 col-lg-6">
            <a href="javascript:void(0)" 
               class="subject-card-link" 
               data-id="${sub.id}" 
               data-name="${sub.period_name}" 
               data-teacher="${sub.teacher_name}">
                <div class="card ${colorClass}">
                    <div class="card-statistic-3">
                        <div class="card-icon card-icon-large"><i class="fa fa-book"></i></div>
                        <div class="card-content">
                            <h4 class="card-title">${sub.period_name}</h4>
                            <span>${sub.teacher_name}</span>
                            <div class="text-warning">
                                ${'★'.repeat(sub.rating)}${'☆'.repeat(5 - sub.rating)}
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>`;
                    });


                    // 👉 Inject cards into container
                    $('#subject_cards').html(subjectHTML);
                } else {
                    alert(response.message || "Error loading data");
                }
            },
            error: function() {
                alert("Failed to load student profile.");
            }
        });
    }

    $(document).on('click', '.subject-card-link', function(e) {
        e.preventDefault(); // prevent page reload
        let subjectId = $(this).data('id');
        let subjectName = $(this).data('name');
        let teacher = $(this).data('teacher');

        alert(
            "Subject ID: " + subjectId +
            "\nSubject: " + subjectName +
            "\nTeacher: " + teacher
        );
    });
    </script>




    <!-- Subject Test Results Graph -->
    <div class="card shadow p-4 mb-4">
        <h5 class="text-center">Subject Test Results</h5>
        <canvas id="subjectResultsChart" height="200"></canvas>
    </div>

    <!-- Monthly Leave Chart -->
    <div class="card shadow p-4">
        <h5 class="text-center">Monthly Leaves</h5>
        <canvas id="leaveChart" height="200"></canvas>
    </div>

    </section>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Subject Test Results Chart
new Chart(document.getElementById('subjectResultsChart'), {
    type: 'bar',
    data: {
        labels: ['English', 'Math', 'Science', 'Computer', 'Urdu'],
        datasets: [{
            label: 'Score (%)',
            data: [85, 92, 72, 90, 80],
            backgroundColor: [
                '#4CAF50', // English - Green
                '#2196F3', // Math - Blue
                '#FF9800', // Science - Orange
                '#9C27B0', // Computer - Purple
                '#F44336' // Urdu - Red
            ]
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

// Leave Chart
new Chart(document.getElementById('leaveChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Leaves',
            data: [2, 1, 0, 3, 1, 2],
            borderColor: '#F44336',
            backgroundColor: '#FFCDD2',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>


<?php require_once 'assets/php/footer.php'; ?>