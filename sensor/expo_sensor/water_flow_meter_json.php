<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]));
}

$pulse_per_liter = 450;

$sql = "SELECT 
            DATE(timestamp) AS date,
            SUM(pulsecount) AS total_pulsecount
        FROM water_flow_meter_expo
        GROUP BY DATE(timestamp)
        ORDER BY DATE(timestamp) DESC";

$result = $mysqli->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $pulse = (int)$row['total_pulsecount'];
        $liters = $pulse / $pulse_per_liter;

        $data[] = [
            "date" => $row["date"],
            "pulsecount" => $pulse,
            "liters_used" => number_format($liters, 2, '.', '')
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
