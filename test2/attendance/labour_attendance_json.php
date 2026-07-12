<?php
header('Content-Type: application/json');

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id <= 0) {
    echo json_encode(["error" => "Invalid client_id"]);
    exit;
}

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

$query = "SELECT id, name, date, timestamp, status, working_place 
          FROM attendance 
          WHERE client_id = $client_id 
          ORDER BY id DESC";

$result = $mysqli->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
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

// Wrap the array inside the client_id key
echo json_encode([$client_id => $data], JSON_PRETTY_PRINT);
?>
