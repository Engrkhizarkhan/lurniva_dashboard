<?php
require_once '../../admin/sass/db_config.php'; // adjust path if needed

try {
    // ✅ Check if 'approve_parent' column exists
    $check = $conn->query("SHOW COLUMNS FROM `diary_students` LIKE 'approve_parent'");

    if ($check && $check->num_rows > 0) {
        // ✅ Column exists — delete it
        $sql = "ALTER TABLE `diary_students` DROP COLUMN `approve_parent`";
        if ($conn->query($sql)) {
            echo "🎉 Successfully deleted 'approve_parent' column from diary_students table.";
        } else {
            echo "❌ Error deleting column 'approve_parent': " . $conn->error;
        }
    } else {
        echo "✅ Column 'approve_parent' does not exist in diary_students table. Nothing to delete.";
    }

} catch (Exception $e) {
    echo "⚠️ Exception: " . $e->getMessage();
}

$conn->close();
?>