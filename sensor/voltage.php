<?php

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
date_default_timezone_set('Asia/Kolkata');

if (isset($_GET['mac_address'], $_GET['voltage_value'])) {

    $mac_address = $conn->real_escape_string($_GET['mac_address']);
    $voltage_value = floatval($_GET['voltage_value']);
    $timestamp = date('Y-m-d H:i:s');
    $status = ($voltage_value < 510) ? 'off' : 'on';

    $sql = "INSERT INTO voltage (mac_address, voltage_value, timestamp, status) VALUES ('$mac_address', '$voltage_value', '$timestamp', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "message" => "Data stored successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing required parameters"]);
}

$conn->close();
?>

