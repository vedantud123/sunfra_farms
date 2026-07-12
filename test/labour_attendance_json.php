<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

$query = "SELECT id, name, date, timestamp, status, working_place, client_id FROM attendance ORDER BY id DESC";

$result = $mysqli->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clientId = $row["client_id"] ?? '0'; 

        if (!isset($data[$clientId])) {
            $data[$clientId] = [];
        }

        $data[$clientId][] = [
            "id" => $row["id"],
            "name" => $row["name"],
            "date" => $row["date"],
            "timestamp" => $row["timestamp"],
            "status" => $row["status"],
            "working_place" => $row["working_place"]
        ];
    }
    $result->free();
} else {
    echo json_encode(["error" => "Query failed: " . $mysqli->error]);
    exit;
}

$mysqli->close();

echo json_encode($data, JSON_PRETTY_PRINT);
?>
