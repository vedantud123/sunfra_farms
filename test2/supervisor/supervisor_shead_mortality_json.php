<?php
header('Content-Type: application/json');

if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {
    echo json_encode(["error" => "Missing or invalid client_id parameter"]);
    exit();
}

$client_id = (int)$_GET['client_id']; 

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $mysqli->connect_error]));
}

$query = "SELECT id, date, sheadNo, noOfBirds, timestamp, client_id 
          FROM supervisor_shead_mortality 
          WHERE client_id = ? 
          ORDER BY id DESC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $result->free();
}

echo json_encode([$client_id => $data], JSON_PRETTY_PRINT);
?>
