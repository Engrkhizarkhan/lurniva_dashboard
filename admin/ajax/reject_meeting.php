<?php
require_once '../sass/db_config.php';

$id = $_POST['id'];
if(mysqli_query($conn, "UPDATE meeting_requests SET status='rejected' WHERE id='$id'")) {
    echo "Meeting Request Rejected!";
} else {
    echo "Error rejecting request!";
}