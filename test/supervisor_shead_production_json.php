<?php
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

$query = "SELECT * FROM supervisor_production_shead ORDER BY id DESC";

$result = $mysqli->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $client_id = $row['client_id'] ?? 'Unknown';

        if (!isset($data[$client_id])) {
            $data[$client_id] = [];
        }

        $data[$client_id][] = [
            "id" => $row["id"],
            "sheadNo" => $row["sheadNo"],
            "no_of_trays" => $row["no_of_trays"],
            "no_of_loose_eggs" => $row["no_of_loose_eggs"],
            "production" => $row["production"],
            "no_of_damaged_eggs" => $row["no_of_damaged_eggs"],
            "timestamp" => $row["timestamp"]
        ];
    }

    $result->free();

    header("Content-Type: application/json");
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    echo json_encode(["status" => "error", "message" => "Query failed"]);
}
?>
