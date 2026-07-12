<?php

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check both values
if (isset($_GET['voltage_value']) && isset($_GET['mac_address'])) {

    $voltage = $mysqli->real_escape_string($_GET['voltage_value']);
    $mac = $mysqli->real_escape_string($_GET['mac_address']);

    $sql = "INSERT INTO voltage_interns (voltage_value, mac_address) 
            VALUES ('$voltage', '$mac')";

    if ($mysqli->query($sql) === TRUE) {
        echo "Data inserted successfully";
    } else {
        echo "Error: " . $mysqli->error;
    }

} else {
    echo "Missing parameters";
}

$mysqli->close();

?>