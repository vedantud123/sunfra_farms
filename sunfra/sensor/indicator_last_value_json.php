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
    SELECT ir.*
    FROM indicator_reading ir
    INNER JOIN (
        SELECT mac_address, MAX(id) AS max_id
        FROM indicator_reading
        WHERE mac_address IN ($placeholders)
        GROUP BY mac_address
    ) latest
    ON ir.id = latest.max_id
";

$indicatorStmt = $mysqli->prepare($indicatorQuery);
$indicatorStmt->bind_param($types, ...$macAddresses);
$indicatorStmt->execute();

$indicatorResult = $indicatorStmt->get_result();

$data = [];

while ($row = $indicatorResult->fetch_assoc()) {
    $mac = $row['mac_address'];
    $siloName = $macDetails[$mac]['mac_address_name'] ?? 'Unknown';

    $data[$siloName] = [
        "mac_address"   => $mac,
        "indicator_data"=> $row
    ];
}

$indicatorStmt->close();
$mysqli->close();

uksort($data, function ($a, $b) {
    preg_match('/\d+/', $a, $numA);
    preg_match('/\d+/', $b, $numB);
    return intval($numA[0]) <=> intval($numB[0]);
});

echo json_encode([
    "count" => count($data),
    "data" => $data
]);


?>