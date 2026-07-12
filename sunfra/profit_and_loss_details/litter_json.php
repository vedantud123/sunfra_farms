<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

$host = "localhost";
$user = "sunfra_farms";
$pass = "sunfra_farms"; 
$db   = "sunfra_farms"; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid client_id"
    ]);
    exit;
}

$sql = "SELECT id, shead_name, number_of_vehicle, litter_cost, date 
        FROM litter_costing 
        WHERE client_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => $row['id'],
        "shead_name" => $row['shead_name'],
        "number_of_vehicle" => $row['number_of_vehicle'],
        "litter_cost" => $row['litter_cost'],
        "date" => $row['date']
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);

$stmt->close();
$conn->close();
?>
