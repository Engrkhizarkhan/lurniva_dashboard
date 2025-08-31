<?php
session_start();
header('Content-Type: text/html');

require '../sass/db_config.php';

$sql = "SELECT fs.id AS fee_structure_id, fs.school_id, fs.class_grade, fs.amount, fs.frequency, fs.status, fs.created_at
        FROM fee_structures fs
        ORDER BY fs.created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    echo "<div class='card mb-4'>";
    echo "<div class='card-header d-flex justify-content-between align-items-center'>";
    echo "<div>
            <strong>Class: " . htmlspecialchars($row['class_grade']) . "</strong> |
            Frequency: " . htmlspecialchars($row['frequency']) . " |
            Total Amount: " . htmlspecialchars($row['amount']) . "
          </div>";
    echo "<div>";

    // ✅ Add Bootstrap modal attributes
    echo "
    
    <button type='button' data-id='" . $row['fee_structure_id'] . "'
     class='btn btn-primary me-2 update-fee-structure' data-toggle='modal'
     data-target='.bd-example-modal-lg'>Update</button>
    ";

    echo "<button 
      class='btn btn-danger btn-sm delete-fee-structure' 
      data-id='" . $row['fee_structure_id'] . "'
    >Delete</button>";

    echo "</div></div>";

    echo "<div class='card-body'>";
    echo "<p>Status: " . htmlspecialchars($row['status']) . " | Created At: " . htmlspecialchars($row['created_at']) . "</p>";

    $stmt = $conn->prepare("SELECT cft.id, cft.fee_type_id, cft.rate FROM class_fee_types cft WHERE cft.fee_structure_id = ?");
    $stmt->bind_param("i", $row['fee_structure_id']);
    $stmt->execute();
    $fee_types_result = $stmt->get_result();

    if ($fee_types_result->num_rows > 0) {
      echo "<table class='table table-bordered'>";
      echo "<thead><tr><th>ID</th><th>Fee Type ID</th><th>Rate</th></tr></thead><tbody>";
      while ($fee_type = $fee_types_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($fee_type['id']) . "</td>";
        echo "<td>" . htmlspecialchars($fee_type['fee_type_id']) . "</td>";
        echo "<td>" . htmlspecialchars($fee_type['rate']) . "</td>";
        echo "</tr>";
      }
      echo "</tbody></table>";
    } else {
      echo "<p>No fee types found.</p>";
    }

    $stmt->close();
    echo "</div></div>";
  }
} else {
  echo "<p>No fee structures found.</p>";
}

$conn->close();
?>