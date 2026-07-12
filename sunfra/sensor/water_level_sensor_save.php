<?php

header("Content-Type: application/json");

date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli(
    "localhost",
    "sunfra_farms",
    "sunfra_farms",
    "sunfra_farms"
);

if ($mysqli->connect_error) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);

    exit;
}

$mac_address = isset($_GET['mac_address']) ? $_GET['mac_address'] : '';
$condition   = isset($_GET['condition']) ? $_GET['condition'] : '';
$status      = isset($_GET['status']) ? $_GET['status'] : '';

if (empty($mac_address) || empty($condition) || empty($status)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Missing required parameters"
    ]);

    exit;
}

$current_time = date("Y-m-d H:i:s");

$stmt = $mysqli->prepare("
    INSERT INTO water_level
    (mac_address, `condition`, status, timestamp)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "ssss",
    $mac_address,
    $condition,
    $status,
    $current_time
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Data inserted successfully",
        "insert_id" => $stmt->insert_id,
        "timestamp" => $current_time
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to insert data"
    ]);
}

$stmt->close();
$mysqli->close();

?>