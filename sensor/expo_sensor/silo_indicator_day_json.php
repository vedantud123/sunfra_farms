<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]));
}

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : null;
$to_date   = isset($_GET['to_date'])   ? $_GET['to_date']   : null;

if (!$from_date || !$to_date) {
    echo json_encode([
        "status" => "error",
        "message" => "Please send both from_date and to_date in YYYY-MM-DD format"
    ]);
    exit;
}

$sql = "SELECT 
            id,
            mac_address,
            value,
            timestamp,
            DATE(timestamp) AS date
        FROM indicator_reading
        WHERE DATE(timestamp) BETWEEN ? AND ?
        ORDER BY DATE(timestamp) , id";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "id"          => $row["id"],
            "mac_address" => $row["mac_address"],
            "value"       => $row["value"],
            "timestamp"   => $row["timestamp"],
            "date"        => $row["date"]
        ];
    }

    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);

} else {
    echo json_encode([
        "status" => "error",
        "message" => "No data found for the selected date range"
    ]);
}

$mysqli->close();
?>
