<?php
require_once '../../admin/sass/db_config.php'; // adjust path if needed

try {
    // ✅ Check if 'school_id' column exists
    $check = $conn->query("SHOW COLUMNS FROM `student_behavior` LIKE 'school_id'");
    if ($check && $check->num_rows > 0) {
        echo "✅ Column 'school_id' already exists in student_behavior table.";
    } else {
        // ✅ Add the missing column
        $sql = "ALTER TABLE `student_behavior` ADD COLUMN `school_id` INT(11) NOT NULL AFTER `id`";
        if ($conn->query($sql)) {
            echo "🎉 Successfully added 'school_id' column to student_behavior table.";
        } else {
            echo "❌ Error adding column 'school_id': " . $conn->error;
        }
    }
} catch (Exception $e) {
    echo "⚠️ Exception: " . $e->getMessage();
}

$conn->close();
?>