<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '216.172.184.173';
$user = 'sunfra_farms';
$password = 'sunfra_farms';
$database = 'sunfra_farms';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}

$mac_address = isset($_GET['mac_address']) ? $_GET['mac_address'] : null;
$temp = isset($_GET['temp']) ? $_GET['temp'] : null;
$humidity = isset($_GET['humidity']) ? $_GET['humidity'] : null;

// Validate inputs
if (empty($mac_address) || empty($temp) || empty($humidity)) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required parameters. Use ?mac_address=XX&temp=YY&humidity=ZZ"
    ]);
    exit;
}

// Set timezone to Asia/Kolkata
date_default_timezone_set('Asia/Kolkata');
$current_time = date('Y-m-d H:i:s');

// Prepare and execute SQL insert
$stmt = $conn->prepare("INSERT INTO temperature_sensor (mac_address, temp, humidity, timestamp) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sdds", $mac_address, $temp, $humidity, $current_time);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Data inserted successfully.",
        "inserted_time" => $current_time
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Insertion failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
