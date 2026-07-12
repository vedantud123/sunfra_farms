<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]));
}

$date = isset($_GET['date']) ? $_GET['date'] : null;

$sql = "SELECT 
            id,
            mac_address,
            temp,
            humidity,
            timestamp
        FROM temperature_sensor 
        WHERE 1=1";

// ✅ If date is provided → filter by that date
if ($date) {
    $sql .= " AND timestamp >= '$date 00:00:00' 
              AND timestamp <= '$date 23:59:59'";
}
// ✅ If no date → show last 24 hours
else {
    $sql .= " AND timestamp >= NOW() - INTERVAL 24 HOUR";
}

$sql .= " ORDER BY timestamp DESC";

$result = $mysqli->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "id"          => $row["id"],
            "mac_address" => $row["mac_address"],
            "temp"        => $row["temp"],
            "humidity"    => $row["humidity"],
            "timestamp"   => $row["timestamp"]
        ];
    }

    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);

} else {
    echo json_encode([
        "status" => "error",
        "message" => "No data found"
    ]);
}

$mysqli->close();
?>
