<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h3>Debug Information for timetable.php</h3>";

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
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Check required tables exist
echo "<h4>3. Table Structure Check:</h4>";
$required_tables = [
    'school_timings',
    'class_timetable_meta',
    'class_timetable_weekdays',
    'class_timetable_details'
];

foreach ($required_tables as $table) {
    try {
        $result = $conn->query("DESCRIBE $table");
        if ($result) {
            echo "✓ Table '$table' exists<br>";
        } else {
            echo "✗ Table '$table' does not exist: " . $conn->error . "<br>";
        }
    } catch (Exception $e) {
        echo "✗ Error checking table '$table': " . $e->getMessage() . "<br>";
    }
}

// Test 4: Check POST data
echo "<h4>4. Request Information:</h4>";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "Content Type: " . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'Not set') . "<br>";
echo "POST Data: <pre>" . print_r($_POST, true) . "</pre>";

// Test 5: Check required POST fields
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h4>5. POST Data Validation:</h4>";
    $required_fields = ['assembly_time', 'leave_time'];
    $optional_fields = ['is_finalized', 'half_day_config', 'classes'];
    
    foreach ($required_fields as $field) {
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            echo "✓ Required field '$field': " . $_POST[$field] . "<br>";
        } else {
            echo "✗ Missing required field '$field'<br>";
        }
    }
    
    foreach ($optional_fields as $field) {
        if (isset($_POST[$field])) {
            if ($field === 'half_day_config' || $field === 'classes') {
                $decoded = json_decode($_POST[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "✓ Optional field '$field': Valid JSON<br>";
                } else {
                    echo "✗ Optional field '$field': Invalid JSON - " . json_last_error_msg() . "<br>";
                }
            } else {
                echo "✓ Optional field '$field': " . $_POST[$field] . "<br>";
            }
        } else {
            echo "ℹ️ Optional field '$field': Not provided<br>";
        }
    }
}

// Test 6: Test database operations
if (isset($_SESSION['admin_id'])) {
    echo "<h4>6. Database Operation Test:</h4>";
    try {
        $admin_id = $_SESSION['admin_id'];
        
        // Test if school_timings table accepts data
        $test_query = "SELECT COUNT(*) as count FROM school_timings WHERE school_id = ?";
        $stmt = $conn->prepare($test_query);
        if ($stmt) {
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            echo "✓ Existing timetables for school {$admin_id}: " . $row['count'] . "<br>";
            $stmt->close();
        } else {
            echo "✗ Failed to prepare test query: " . $conn->error . "<br>";
        }
        
    } catch (Exception $e) {
        echo "✗ Database test failed: " . $e->getMessage() . "<br>";
    }
}

// Test 7: Sample timetable form for testing
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "<h4>7. Test Form:</h4>";
    echo '<form method="POST" action="">
        <p><label>Assembly Time: <input type="time" name="assembly_time" value="08:00" required></label></p>
        <p><label>Leave Time: <input type="time" name="leave_time" value="14:00" required></label></p>
        <p><label>Is Finalized: <input type="checkbox" name="is_finalized" value="1"></label></p>
        <p><label>Half Day Config (JSON): <br><textarea name="half_day_config" rows="3" cols="50">{"friday":{"assembly_time":"08:00","leave_time":"12:00","total_periods":5}}</textarea></label></p>
        <p><label>Classes (JSON): <br><textarea name="classes" rows="5" cols="50">[{"class_name":"Grade 1","section":"A","total_periods":6,"periods":[{"period_name":"Math","start_time":"08:00","end_time":"08:45","teacher_id":1,"is_break":false,"period_type":"regular"}]}]</textarea></label></p>
        <p><input type="submit" value="Test Timetable Processing"></p>
    </form>';
}

echo "<h4>8. PHP Configuration:</h4>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "MySQLi extension: " . (extension_loaded('mysqli') ? '✓ Loaded' : '✗ Not loaded') . "<br>";
echo "JSON extension: " . (extension_loaded('json') ? '✓ Loaded' : '✗ Not loaded') . "<br>";

?>

<style>
table { margin: 10px 0; border-collapse: collapse; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
form { background: #f5f5f5; padding: 15px; margin: 10px 0; }
textarea, input { margin: 5px 0; }
</style>