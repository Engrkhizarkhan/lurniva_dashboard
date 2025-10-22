<?php
require_once '../../admin/sass/db_config.php'; // adjust path if needed

try {
    // ✅ Check if 'meeting_person' column exists and its type
    $check = $conn->query("SHOW COLUMNS FROM `meeting_announcements` LIKE 'meeting_person'");
    if ($check && $check->num_rows > 0) {
        $col = $check->fetch_assoc();
        if (stripos($col['Type'], 'varchar') !== false) {
            echo "✅ Column 'meeting_person' is already VARCHAR.";
        } else {
            // ✅ Change to VARCHAR
            $sql = "ALTER TABLE `meeting_announcements` 
                    MODIFY `meeting_person` VARCHAR(50) NOT NULL";
            if ($conn->query($sql)) {
                echo "🎉 Successfully converted 'meeting_person' to VARCHAR(50)!";
            } else {
                echo "❌ Error converting 'meeting_person': " . $conn->error;
            }
        }
    } else {
        echo "⚠️ Column 'meeting_person' not found in meeting_announcements table.";
    }

    // ✅ Check and convert 'meeting_person2'
    $check2 = $conn->query("SHOW COLUMNS FROM `meeting_announcements` LIKE 'meeting_person2'");
    if ($check2 && $check2->num_rows > 0) {
        $col2 = $check2->fetch_assoc();
        if (stripos($col2['Type'], 'varchar') !== false) {
            echo "<br>✅ Column 'meeting_person2' is already VARCHAR.";
        } else {
            $sql2 = "ALTER TABLE `meeting_announcements` 
                     MODIFY `meeting_person2` VARCHAR(50) NOT NULL";
            if ($conn->query($sql2)) {
                echo "<br>🎉 Successfully converted 'meeting_person2' to VARCHAR(50)!";
            } else {
                echo "<br>❌ Error converting 'meeting_person2': " . $conn->error;
            }
        }
    } else {
        echo "<br>⚠️ Column 'meeting_person2' not found in meeting_announcements table.";
    }

} catch (Exception $e) {
    echo "⚠️ Exception: " . $e->getMessage();
}

$conn->close();
?>