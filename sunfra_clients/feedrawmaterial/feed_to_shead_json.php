<?php
header('Content-Type: application/json');

if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {
    echo json_encode(["error" => "Missing client_id parameter"]);
    exit();
}

$client_id = (int)$_GET['client_id']; 

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$query = "SELECT id, sheadNo, tons, timestamp FROM feed_shead_feeding WHERE client_id = ? ORDER BY sheadNo ASC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$output = [$client_id => $data];

echo json_encode($output);

$stmt->close();
$mysqli->close();
?>
