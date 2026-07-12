<?php
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn->connect_error) {
    echo "ERROR: Database connection failed";
    exit;
}

date_default_timezone_set('Asia/Kolkata');

if (isset($_GET['mac_address']) && isset($_GET['status'])) {

    $mac_address = $conn->real_escape_string($_GET['mac_address']);
    $status      = $conn->real_escape_string($_GET['status']);
    $timestamp   = date('Y-m-d H:i:s');

    $stmt = $conn->prepare(
        "INSERT INTO water_level_sensor (mac_address, status, datetime) VALUES (?, ?, ?)"
    );

    if ($stmt) {
        $stmt->bind_param("sss", $mac_address, $status, $timestamp);

        if ($stmt->execute()) {
            echo "SUCCESS: Data inserted successfully";
        } else {
            echo "ERROR: Insert failed";
        }

        $stmt->close();
    } else {
        echo "ERROR: Statement preparation failed";
    }

} else {
    echo "ERROR: Missing parameters";
}

$conn->close();
?>