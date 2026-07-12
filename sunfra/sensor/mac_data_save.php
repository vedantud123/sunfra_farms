<?php
date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli(
    "localhost",
    "sunfra_farms",
    "sunfra_farms",
    "sunfra_farms"
);

if ($mysqli->connect_error) {
    die("DB_CONNECTION_FAILED");
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die("INVALID_REQUEST_METHOD");
}

$client_id = $_GET['client_id'] ?? '';
$mac_address = $_GET['mac_address'] ?? '';
$type = $_GET['type'] ?? '';
$mac_address_name = $_GET['mac_address_name'] ?? '';

if ($client_id === '' || $mac_address === '' || $type === '' || $mac_address_name === '') {
    die("MISSING_PARAMETERS");
}

$current_date = date("Y-m-d");

$stmt = $mysqli->prepare(
    "INSERT INTO sensor_macaddress_data
     (client_id, mac_address, type, mac_address_name, date)
     VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "issss",
    $client_id,
    $mac_address,
    $type,
    $mac_address_name,
    $current_date
);

if ($stmt->execute()) {
    echo "SUCCESS";
} else {
    echo "FAILED";
}

$stmt->close();
$mysqli->close();
