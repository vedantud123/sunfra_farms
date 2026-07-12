<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]));
}

$sql = "SELECT id, mac_address, value, timestamp 
        FROM indicator_reading 
        ORDER BY id DESC 
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "data" => $row
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No data found"
    ]);
}

$mysqli->close();
?>
