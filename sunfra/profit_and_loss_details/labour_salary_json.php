<?php
header("Content-Type: application/json");

$client_id = $_GET['client_id'] ?? null;

if (!$client_id || !is_numeric($client_id)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid or missing client_id"
    ]);
    exit;
}

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]);
    exit;
}

$data_stmt = $mysqli->prepare("SELECT * FROM labour_salaries WHERE client_id = ?");
$data_stmt->bind_param("i", $client_id);
$data_stmt->execute();
$data_result = $data_stmt->get_result();

$labour_data = [];
while ($row = $data_result->fetch_assoc()) {
    $labour_data[] = $row;
}

echo json_encode([
    $client_id => $labour_data
]);
?>
