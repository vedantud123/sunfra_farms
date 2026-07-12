<?php

$conn = new mysqli('216.172.184.173', 'sunfra_farms', 'sunfra_farms', 'sunfra_farms');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT ssid, password FROM wifi_config";
$result = $conn->query($query);

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        echo "ssid-".$row['ssid'] . ",password-" . $row['password'] . "\n";
    }
} else {
    echo "No data found.";
}

$conn->close();
?>