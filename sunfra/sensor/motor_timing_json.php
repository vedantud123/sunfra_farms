<?php
header("Content-Type: application/json");
date_default_timezone_set('Asia/Kolkata');

$host = '216.172.184.173';
$user = 'sunfra_farms';
$password = 'sunfra_farms';
$database = 'sunfra_farms';

$mysqli = new mysqli($host, $user, $password, $database);

if ($mysqli->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "DB connection failed"
    ]);
    exit;
}

$client_id = $_REQUEST['client_id'] ?? '';

if (empty($client_id)) {
    echo json_encode([
        "status" => "error",
        "message" => "client_id required"
    ]);
    exit;
}

$stmt = $mysqli->prepare("
    SELECT id, motor_number, start_time, end_time, client_id
    FROM motor_time_config
    WHERE client_id = ?
    ORDER BY motor_number ASC
");

$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);

$stmt->close();
$mysqli->close();

?>