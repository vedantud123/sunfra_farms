<?php

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Kolkata');

if (isset($_GET['mac_address'])) {
    $mac_address = $conn->real_escape_string($_GET['mac_address']);

    $level_1 = isset($_GET['level_1']) ? $conn->real_escape_string($_GET['level_1']) : NULL;
    $level_2 = isset($_GET['level_2']) ? $conn->real_escape_string($_GET['level_2']) : NULL;
    $level_3 = isset($_GET['level_3']) ? $conn->real_escape_string($_GET['level_3']) : NULL;
    $level_4 = isset($_GET['level_4']) ? $conn->real_escape_string($_GET['level_4']) : NULL;
    $level_5 = isset($_GET['level_5']) ? $conn->real_escape_string($_GET['level_5']) : NULL;

    $timestamp = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO `water_level_sensor_for_spice_garden` (mac_address, level_1, level_2, level_3, level_4, level_5, datetime) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $mac_address, $level_1, $level_2, $level_3, $level_4, $level_5, $timestamp);
    
    if($stmt->execute()){
        echo "Data saved successfully";
    } else {
        echo "Error saving data: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Missing mac_address";
}

$conn->close();
?>
