<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn->connect_error) {
    echo json_encode(["error" => "❌ Connection failed: " . $conn->connect_error]);
    exit;
}

$client_id = $_GET['client_id'];

$query = "SELECT id, name, price FROM feed_rawmaterial_price WHERE client_id = ? ORDER BY name ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "$client_id" => $data
]);

$stmt->close();
$conn->close();
?>
