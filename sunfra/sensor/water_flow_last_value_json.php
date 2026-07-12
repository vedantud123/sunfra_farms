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

define('PULSES_PER_LITER', 450);

$todayStart = date('Y-m-d 00:00:00');
$todayEnd   = date('Y-m-d 23:59:59');

$macQuery = "
    SELECT mac_address, mac_address_name
    FROM sensor_macaddress_data
    WHERE client_id = ? AND type = 'Water Flow Monitoring'
";

$macStmt = $mysqli->prepare($macQuery);
$macStmt->bind_param("i", $client_id);
$macStmt->execute();
$macResult = $macStmt->get_result();

$macAddresses = [];
$macNames     = [];

while ($row = $macResult->fetch_assoc()) {
    $macAddresses[] = $row['mac_address'];
    $macNames[$row['mac_address']] = $row['mac_address_name'];
}

$macStmt->close();

if (empty($macAddresses)) {
    echo json_encode([
        "status" => "success",
        "date" => date('Y-m-d'),
        "count" => 0,
        "grand_total_liters" => 0,
        "data" => []
    ]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($macAddresses), '?'));
$types = str_repeat('s', count($macAddresses)) . 'ss';

$flowQuery = "
    SELECT mac_address, SUM(pulsecount) AS total_pulses
    FROM water_flow_meter_expo
    WHERE mac_address IN ($placeholders)
      AND timestamp BETWEEN ? AND ?
      AND status = 'flowing'
    GROUP BY mac_address
";

$flowStmt = $mysqli->prepare($flowQuery);

$params = array_merge($macAddresses, [$todayStart, $todayEnd]);

$flowStmt->bind_param($types, ...$params);
$flowStmt->execute();

$flowResult = $flowStmt->get_result();

$data = [];
$grandTotalLiters = 0;

while ($row = $flowResult->fetch_assoc()) {
    $mac = $row['mac_address'];
    $flowName = $macNames[$mac] ?? 'Unknown';

    $liters = $row['total_pulses'] / PULSES_PER_LITER;

    $data[$flowName] = [
        "mac_address"       => $mac,
        "today_pulses"      => (int)$row['total_pulses'],
        "water_used_liters" => round($liters, 2)
    ];

    $grandTotalLiters += $liters;
}

uksort($data, function ($a, $b) {
    preg_match('/\d+/', $a, $numA);
    preg_match('/\d+/', $b, $numB);
    return intval($numA[0] ?? 0) <=> intval($numB[0] ?? 0);
});

$flowStmt->close();
$mysqli->close();

echo json_encode([
    "date" => date('Y-m-d'),
    "grand_total_liters" => round($grandTotalLiters, 2),
    "data" => $data
]);

?>
