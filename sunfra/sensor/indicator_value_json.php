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
    WHERE client_id = ? AND type = 'Silo Monitoring'
";

$macStmt = $mysqli->prepare($macQuery);
$macStmt->bind_param("i", $client_id);
$macStmt->execute();
$macResult = $macStmt->get_result();

$macAddresses = [];
$macDetails   = [];

while ($row = $macResult->fetch_assoc()) {
    $macAddresses[] = $row['mac_address'];
    $macDetails[$row['mac_address']] = $row;
}

$macStmt->close();

if (empty($macAddresses)) {
    echo json_encode([
        "status" => "success",
        "client_id" => $client_id,
        "count" => 0,
        "data" => []
    ]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($macAddresses), '?'));
$types = str_repeat('s', count($macAddresses));

$indicatorQuery = "
    SELECT *
    FROM indicator_reading
    WHERE mac_address IN ($placeholders)
";

$indicatorStmt = $mysqli->prepare($indicatorQuery);
$indicatorStmt->bind_param($types, ...$macAddresses);
$indicatorStmt->execute();

$indicatorResult = $indicatorStmt->get_result();

$data = [];

while ($row = $indicatorResult->fetch_assoc()) {
    $mac = $row['mac_address'];

    $data[] = [
        "mac_address_name"  => $macDetails[$mac]['mac_address_name'] ?? null,
        "indicator_data"    => $row
    ];
}

$indicatorStmt->close();
$mysqli->close();

echo json_encode([
    "count" => count($data),
    "data" => $data
]);

?>