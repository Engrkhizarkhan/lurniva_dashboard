<?php
session_start();
require_once '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) exit;

$type = $_POST['type'] ?? 'transport';

mysqli_query($conn, "UPDATE notifications 
                     SET is_read=1 
                     WHERE user_type='admin' AND type='$type'");
