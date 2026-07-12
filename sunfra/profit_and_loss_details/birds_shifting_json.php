<?php
header('Content-Type: application/json');

$host = '216.172.184.173';
$user = 'sunfra_farms';
$password = 'sunfra_farms';
$database = 'sunfra_farms';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id <= 0) {
    echo json_encode(["error" => "Invalid or missing client_id"]);
    exit;
}

// Added food_cost to SELECT query
$stmt = $conn->prepare("SELECT id, shead_name, labour_cost, food_cost, client_id, date FROM birds_shifting WHERE client_id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if (count($data) > 0) {
    echo json_encode(["status" => "success", "data" => $data], JSON_PRETTY_PRINT);
} else {
    echo json_encode(["status" => "no_data", "message" => "No records found for this client_id"]);
}

$stmt->close();
$conn->close();
?>
