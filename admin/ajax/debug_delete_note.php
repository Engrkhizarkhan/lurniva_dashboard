<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h3>Debug Information for delete_note.php</h3>";

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

// Test 3: Check if notes exist
if (isset($_SESSION['admin_id'])) {
    echo "<h4>3. Available Notes:</h4>";
    try {
        $admin_id = $_SESSION['admin_id'];
        $sql = "SELECT id, title, created_at FROM notes_board WHERE school_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "✓ Found " . $result->num_rows . " notes:<br>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Title</th><th>Created</th><th>Actions</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "<td><button onclick='testDelete(" . $row['id'] . ")'>Test Delete</button></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "✓ No notes found for admin_id: " . $admin_id . "<br>";
        }
    } catch (Exception $e) {
        echo "✗ Error fetching notes: " . $e->getMessage() . "<br>";
    }
}

// Test 4: Check request method
echo "<h4>4. Request Information:</h4>";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "POST Data: <pre>" . print_r($_POST, true) . "</pre>";

// Test 5: Simulate delete operation (if POST data is provided)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_id'])) {
    echo "<h4>5. Delete Test Results:</h4>";
    $test_id = $_POST['test_id'];
    echo "Testing deletion of note ID: " . $test_id . "<br>";
    
    try {
        $admin_id = $_SESSION['admin_id'];
        
        // Check if note exists
        $check_sql = "SELECT id, title FROM notes_board WHERE id = ? AND school_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $test_id, $admin_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $note = $result->fetch_assoc();
            echo "✓ Note found: " . htmlspecialchars($note['title']) . "<br>";
            echo "✓ Test would be successful (not actually deleting in debug mode)<br>";
        } else {
            echo "✗ Note not found or access denied<br>";
        }
    } catch (Exception $e) {
        echo "✗ Error during test: " . $e->getMessage() . "<br>";
    }
}

?>

<script>
function testDelete(id) {
    if (confirm('Test delete note ID ' + id + '? (This will not actually delete in debug mode)')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'test_id';
        input.value = id;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
button { padding: 5px 10px; cursor: pointer; }
</style>