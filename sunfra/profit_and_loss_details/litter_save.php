<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

// Database connection
$host = '216.172.184.173';
$user = 'sunfra_farms';
$password = 'sunfra_farms';
$database = 'sunfra_farms';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Validate POST parameters
$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$shead_name = isset($_POST['shead_name']) ? trim($_POST['shead_name']) : '';
$number_of_vehicle = isset($_POST['number_of_vehicle']) ? intval($_POST['number_of_vehicle']) : 0;
$litter_cost = isset($_POST['litter_cost']) ? trim($_POST['litter_cost']) : '';
$date = date('Y-m-d'); // Auto-set current date

if ($client_id <= 0 || empty($shead_name) || empty($litter_cost)) {
    echo json_encode(["status" => "error", "message" => "Missing or invalid parameters."]);
    exit;
}

// Prepare insert statement
$stmt = $conn->prepare("INSERT INTO litter_costing (shead_name, number_of_vehicle, litter_cost, client_id, date) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sisis", $shead_name, $number_of_vehicle, $litter_cost, $client_id, $date);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Data inserted successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error inserting data: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
