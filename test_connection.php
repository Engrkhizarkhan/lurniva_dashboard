<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";

// Test database connection
try {
    require_once 'admin/sass/db_config.php';
    echo "Database connection successful!<br>";
    
    // Test a simple query
    $result = $conn->query("SELECT 1 as test");
    if ($result) {
        echo "Database query test successful!<br>";
        $row = $result->fetch_assoc();
        echo "Query result: " . $row['test'] . "<br>";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

echo "Test completed.";
?>
