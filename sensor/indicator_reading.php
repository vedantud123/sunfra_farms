<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]));
}

date_default_timezone_set('Asia/Kolkata');

$mac_address = $_REQUEST['mac_address'] ?? null;
$value       = $_REQUEST['value'] ?? null;

if (!$mac_address || !$value) {
    echo json_encode([
        "status" => "error",
        "message" => "Required fields (mac_address, value) missing"
    ]);
    exit;
}

$timestamp = date("Y-m-d H:i:s");

$sql = "INSERT INTO indicator_reading (mac_address, value, timestamp) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "SQL Prepare failed: " . $mysqli->error
    ]);
    exit;
}

$stmt->bind_param("sss", $mac_address, $value, $timestamp);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Data inserted successfully",
        "timestamp" => $timestamp
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Insert failed: " . $stmt->error
    ]);
}

$stmt->close();
$mysqli->close();
?>
