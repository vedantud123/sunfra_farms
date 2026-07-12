<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit();
}

$query = "SELECT * FROM supervisor_feed_feeding_shead ORDER BY client_id, id DESC";

$result = $mysqli->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clientId = $row['client_id'];

        if (!isset($data[$clientId])) {
            $data[$clientId] = [];
        }

        $data[$clientId][] = [
            "id" => $row["id"],
            "sheadNo" => $row["sheadNo"],
            "Box_1" => $row["Box_1"],
            "Box_2" => $row["Box_2"],
            "Box_3" => $row["Box_3"],
            "Box_4" => $row["Box_4"],
            "Box_5" => $row["Box_5"],
            "Box_6" => $row["Box_6"],
            "Box_7" => $row["Box_7"],
            "Box_8" => $row["Box_8"],
            "Box_9" => $row["Box_9"],
            "Box_10" => $row["Box_10"],
            "timestamp" => $row["timestamp"]
        ];
    }
    $result->free();
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
