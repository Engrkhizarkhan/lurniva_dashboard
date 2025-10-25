<?php
require_once '../../admin/sass/db_config.php';


// Define your ALTER TABLE query
$sql = "
ALTER TABLE `parents`
CHANGE `status` `status` ENUM('Pending','Approved','Rejected') 
CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'Pending';
";

// Execute query
if ($conn->query($sql) === TRUE) {
    echo "✅ Table updated successfully!";
} else {
    echo "❌ Error updating table: " . $conn->error;
}

$conn->close();
?>