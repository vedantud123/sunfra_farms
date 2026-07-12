<?php
header('Content-Type: application/json');
session_start();
date_default_timezone_set('Asia/Kolkata');

// Database connection
$host = "localhost";
$user = "sunfra_farms";  // update your DB username
$pass = "sunfra_farms";  // update your DB password
$db   = "sunfra_farms";  // update your DB name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// Get POST data
$shead_name   = isset($_POST['shead_name']) ? $conn->real_escape_string($_POST['shead_name']) : '';
$labour_cost  = isset($_POST['labour_cost']) ? floatval($_POST['labour_cost']) : 0;
$food_cost    = isset($_POST['food_cost']) ? floatval($_POST['food_cost']) : 0;
$client_id    = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;

// Basic validation
if (empty($shead_name) || $labour_cost <= 0 || $food_cost < 0 || $client_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
    exit;
}

// Insert into database with current date
$date = date('Y-m-d H:i:s'); // current datetime
$sql = "INSERT INTO birds_shifting (shead_name, labour_cost, food_cost, client_id, date) 
        VALUES ('$shead_name', '$labour_cost', '$food_cost', '$client_id', '$date')";

if ($conn->query($sql)) {
    echo json_encode(['status' => 'success', 'message' => 'Birds shifting data saved successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database insert failed: ' . $conn->error]);
}

$conn->close();
?>
