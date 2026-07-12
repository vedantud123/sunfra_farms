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

if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "client_id is required"
    ]);
    exit;
}

$client_id = intval($_GET['client_id']);

$macQuery = "
    SELECT mac_address, mac_address_name, type
    FROM sensor_macaddress_data
    WHERE client_id = ? AND type = 'Water Level Monitoring'
";

$macStmt = $mysqli->prepare($macQuery);
$macStmt->bind_param("i", $client_id);
$macStmt->execute();
$macResult = $macStmt->get_result();

$response = [];

while ($macRow = $macResult->fetch_assoc()) {

    $macAddress = $macRow['mac_address'];

    $lastDataQuery = "
        SELECT id, mac_address, status, datetime
        FROM water_level_sensor
        WHERE mac_address = ?
        ORDER BY datetime DESC
        LIMIT 1
    ";

    $lastStmt = $mysqli->prepare($lastDataQuery);
    $lastStmt->bind_param("s", $macAddress);
    $lastStmt->execute();
    $lastResult = $lastStmt->get_result();

    $lastData = $lastResult->fetch_assoc();

    $response[] = [
        "mac_address"      => $macRow['mac_address'],
        "mac_address_name" => $macRow['mac_address_name'],
        "type"             => $macRow['type'],
        "last_data"        => $lastData ? $lastData : null
    ];

    $lastStmt->close();
}

$macStmt->close();
$mysqli->close();

echo json_encode([
    "status" => "success",
    "client_id" => $client_id,
    "data" => $response
], JSON_PRETTY_PRINT);

?>
