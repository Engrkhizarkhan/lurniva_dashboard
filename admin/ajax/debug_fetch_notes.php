<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h3>Debug Information for fetch_notes.php</h3>";

// Test 1: Check session
echo "<h4>1. Session Check:</h4>";
if (isset($_SESSION['admin_id'])) {
    echo "✓ admin_id exists in session: " . $_SESSION['admin_id'] . "<br>";
} else {
    echo "✗ admin_id not found in session<br>";
    echo "Available session variables: <pre>" . print_r($_SESSION, true) . "</pre>";
}

// Test 2: Database connection
echo "<h4>2. Database Connection:</h4>";
try {
    require '../sass/db_config.php';
    echo "✓ Database connection successful<br>";
    echo "Connection info: " . $conn->host_info . "<br>";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Check if table exists
echo "<h4>3. Table Structure:</h4>";
try {
    $result = $conn->query("DESCRIBE notes_board");
    if ($result) {
        echo "✓ Table 'notes_board' exists<br>";
        echo "Columns:<br>";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
        }
    } else {
        echo "✗ Table 'notes_board' does not exist or query failed: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "✗ Error checking table: " . $e->getMessage() . "<br>";
}

// Test 4: Check data in table
if (isset($_SESSION['admin_id'])) {
    echo "<h4>4. Data Check:</h4>";
    try {
        $admin_id = $_SESSION['admin_id'];
        $sql = "SELECT COUNT(*) as count FROM notes_board WHERE school_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo "✓ Found " . $row['count'] . " notes for admin_id: " . $admin_id . "<br>";
    } catch (Exception $e) {
        echo "✗ Error counting records: " . $e->getMessage() . "<br>";
    }
}

// Test 5: Test the actual query
if (isset($_SESSION['admin_id'])) {
    echo "<h4>5. Query Test:</h4>";
    try {
        $admin_id = $_SESSION['admin_id'];
        $sql = "SELECT * FROM notes_board WHERE school_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            echo "✓ Query successful. Sample record:<br>";
            echo "<pre>" . print_r($row, true) . "</pre>";
        } else {
            echo "✓ Query successful but no records found<br>";
        }
    } catch (Exception $e) {
        echo "✗ Query failed: " . $e->getMessage() . "<br>";
    }
}

echo "<h4>6. PHP Configuration:</h4>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "MySQLi extension: " . (extension_loaded('mysqli') ? '✓ Loaded' : '✗ Not loaded') . "<br>";

?>
