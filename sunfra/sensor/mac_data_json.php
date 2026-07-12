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
        "status" => "error",
        "message" => "DB_CONNECTION_FAILED"
    ]);
    exit;
}

$sql = "SELECT id, client_id, mac_address, type, mac_address_name, date 
        FROM sensor_macaddress_data
        ORDER BY id DESC";

$result = $mysqli->query($sql);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "total_records" => count($data),
    "data" => $data
], JSON_PRETTY_PRINT);

$mysqli->close();
?>