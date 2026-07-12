<?php
date_default_timezone_set('Asia/Kolkata');

$pulseCount = isset($_GET['pulses']) ? intval($_GET['pulses']) : 0;
$macAddress = isset($_GET['mac']) ? trim($_GET['mac']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$uid = isset($_GET['uid']) ? trim($_GET['uid']) : uniqid(); // optional, you can still send it or ignore

if (empty($macAddress) || empty($status)) {
    http_response_code(400);
    echo "Missing mac address or status";
    exit;
}

$macAddress = str_replace(":", "-", $macAddress);

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    http_response_code(500);
    die("DB Connection failed: " . $conn->connect_error);
}

$timestamp = date('Y-m-d H:i:s');

$sql = "INSERT INTO water_flow_meter (pulsecount, mac_address, status, uid, timestamp) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("issss", $pulseCount, $macAddress, $status, $uid, $timestamp);
    if ($stmt->execute()) {
        echo "Data saved successfully";
    } else {
        http_response_code(500);
        echo "Execution failed: " . $stmt->error;
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo "Preparation failed: " . $conn->error;
}

$conn->close();
?>
