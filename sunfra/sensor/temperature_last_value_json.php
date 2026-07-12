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

$client_id = $_GET['client_id'] ?? 0;
$client_id = intval($client_id);

if (!$client_id) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "client_id_required"
    ]);
    exit;
}

$sqlMac = "
    SELECT mac_address, mac_address_name
    FROM sensor_macaddress_data
    WHERE client_id = ?
      AND type = 'Temperature Monitoring'
";

$stmtMac = $mysqli->prepare($sqlMac);
$stmtMac->bind_param("i", $client_id);
$stmtMac->execute();
$resMac = $stmtMac->get_result();

$macs = [];
$macNames = [];

while ($row = $resMac->fetch_assoc()) {
    $macs[] = $row['mac_address'];
    $macNames[$row['mac_address']] = $row['mac_address_name'];
}

$stmtMac->close();

if (empty($macs)) {
    echo json_encode([
        "count" => 0,
        "data" => []
    ]);
    exit;
}

$data = [];

$sqlTemp = "
    SELECT temp, humidity, timestamp
    FROM temperature_sensor
    WHERE mac_address = ?
    ORDER BY timestamp DESC
    LIMIT 1
";

$stmtTemp = $mysqli->prepare($sqlTemp);

foreach ($macs as $mac) {
    $stmtTemp->bind_param("s", $mac);
    $stmtTemp->execute();
    $resTemp = $stmtTemp->get_result();

    if ($row = $resTemp->fetch_assoc()) {
        $data[$macNames[$mac]] = [
            "mac_address" => $mac,
            "temperature" => $row['temp'],
            "humidity" => $row['humidity'],
            "timestamp" => $row['timestamp']
        ];
    }
}

$stmtTemp->close();
$mysqli->close();

echo json_encode([
    "count" => count($data),
    "data" => $data
]);

?>