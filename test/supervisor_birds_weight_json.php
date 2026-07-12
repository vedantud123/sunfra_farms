<?php
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "SELECT * FROM supervisor_birds_weight ORDER BY id DESC";
$result = $mysqli->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $client_id = $row['client_id'];

        if (!isset($data[$client_id])) {
            $data[$client_id] = [];
        }

        $data[$client_id][] = [
            "id" => $row["id"],
            "sheadNo" => $row["sheadNo"],
            "bird1" => $row["bird1"],
            "bird2" => $row["bird2"],
            "bird3" => $row["bird3"],
            "bird4" => $row["bird4"],
            "bird5" => $row["bird5"],
            "bird6" => $row["bird6"],
            "bird7" => $row["bird7"],
            "bird8" => $row["bird8"],
            "birds_average" => $row["birds_average"],
            "timestamp" => $row["timestamp"]
        ];
    }
    $result->free();
}

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
?>
