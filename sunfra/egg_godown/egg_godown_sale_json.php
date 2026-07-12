<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

if (!isset($_GET['client_id']) || !is_numeric($_GET['client_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid client_id']);
    exit;
}

$client_id = (int) $_GET['client_id'];

$query = "SELECT id, timestamp, shead_name, no_of_eggs, type_of_eggs, sale, sale_price, remarks 
          FROM egg_godown_stock 
          WHERE sale IS NOT NULL AND client_id = ? 
          ORDER BY id DESC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['no_of_eggs'] = getTrayCount($row['no_of_eggs']); // Optional conversion
    $data[] = $row;
}

echo json_encode([$client_id => $data]);

function getTrayCount($eggs) {
    $wholeTrays = floor($eggs / 30);
    $remainder = $eggs % 30;
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}
?>



