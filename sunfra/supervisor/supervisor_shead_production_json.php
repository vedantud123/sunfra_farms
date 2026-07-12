<?php
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing or empty client_id parameter"]);
    exit;
}

$client_id = (int)$_GET['client_id'];

$query = "SELECT * FROM supervisor_production_shead WHERE client_id = $client_id ORDER BY id DESC";

$result = $mysqli->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
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
    echo json_encode([
       $client_id => $data
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode(["status" => "error", "message" => "Query failed"]);
}
?>

