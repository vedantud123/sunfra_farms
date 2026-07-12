<?php

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Kolkata');


if (isset($_GET['mac_address'], $_GET['status'])) {
    $mac_address = $conn->real_escape_string($_GET['mac_address']);
    $status =$conn->real_escape_string($_GET['status']);
    $timestamp = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO `water_level_sensor` (mac_address,status, datetime) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $mac_address, $status, $timestamp);
    $stmt->execute();
    $stmt->close();
}

?>