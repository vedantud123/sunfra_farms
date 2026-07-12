<?php
header('Content-Type: application/json');

if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {
    echo json_encode(["error" => "Missing client_id parameter"]);
    exit();
}

$client_id = (int)$_GET['client_id'];

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($mysqli->connect_errno) {
    echo json_encode(["error" => "Failed to connect to MySQL: " . $mysqli->connect_error]);
    exit();
}

$query = "SELECT id, location FROM config_location WHERE client_id = $client_id";
$result = $mysqli->query($query);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[$client_id][] = [
            "id" => (int)$row['id'],
            "location" => $row['location']  
        ];
    }
} else {
    $data[$client_id] = [];  
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
