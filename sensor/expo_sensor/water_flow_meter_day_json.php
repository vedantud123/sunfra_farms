<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]));
}

$from_date = $_GET['from'] ?? null;
$to_date   = $_GET['to']   ?? null;

if (!$from_date || !$to_date) {
    echo json_encode([
        "status" => "error",
        "message" => "Please send from & to date"
    ]);
    exit;
}

$pulse_per_liter = 450;

$start = new DateTime($from_date . " 00:00:00");
$end   = new DateTime($to_date . " 23:00:00");

$data = [];

while ($start <= $end) {

    $hour_start = $start->format("Y-m-d H:00:00");
    $hour_end   = $start->modify("+1 hour")->format("Y-m-d H:00:00");

    $stmt = $mysqli->prepare("
        SELECT IFNULL(SUM(pulsecount), 0) AS total_pulsecount
        FROM water_flow_meter_expo
        WHERE timestamp >= ? 
          AND timestamp < ?
    ");

    $stmt->bind_param("ss", $hour_start, $hour_end);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    $pulse = (int)$result['total_pulsecount'];
    $liters = $pulse / $pulse_per_liter;

    $data[] = [
        "timestamp"   => $hour_start,
        "pulsecount"  => $pulse,
        "liters_used" => number_format($liters, 2, '.', '')
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);

$mysqli->close();
?>
