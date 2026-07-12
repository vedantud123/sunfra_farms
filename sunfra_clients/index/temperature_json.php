<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
$date      = isset($_GET['date']) ? $_GET['date'] : "";

if (!$date) {
    $toDateTime   = date('Y-m-d H:i:s');
    $fromDateTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
} else {
    $today = date('Y-m-d');
    if ($date === $today) {
        $toDateTime   = date('Y-m-d H:i:s');
        $fromDateTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
    } else {
        $fromDateTime = date('Y-m-d 00:00:00', strtotime($date));
        $toDateTime   = date('Y-m-d 23:59:59', strtotime($date));
    }
}

$query = "
    SELECT DATE_FORMAT(datetime, '%H:%i') AS time, temperature, feelslike
    FROM temperature
    WHERE datetime BETWEEN '{$fromDateTime}' AND '{$toDateTime}'
    ORDER BY datetime ASC
";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(["error" => "SQL error", "query" => $query, "mysqli_error" => $conn->error]);
    exit;
}

$labels = [];
$temperatures = [];
$feelslike = [];

while ($row = $result->fetch_assoc()) {
    $labels[]       = $row['time'];
    $temperatures[] = round($row['temperature'], 2);
    $feelslike[]    = round($row['feelslike'], 2);
}

echo json_encode([
    "client_id"   => $client_id,
    "from"        => $fromDateTime,
    "to"          => $toDateTime,
    "labels"      => $labels,
    "temperature" => $temperatures,
    "feelslike"   => $feelslike
], JSON_PRETTY_PRINT);
?>
