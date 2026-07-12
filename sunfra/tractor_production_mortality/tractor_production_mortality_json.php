<?php
header('Content-Type: application/json');

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing client_id']);
    exit;
}

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

$query = "SELECT * FROM tractor_production_mortality WHERE client_id = $client_id ORDER BY id DESC";

$productionData = [];

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $productionData[] = [
            'id' => (int)$row['id'],
            'date' => $row['date'],
            'sheadNo' => $row['sheadNo'],
            'production' => (int)$row['production'],
            'eggTrays' => (int)$row['eggTrays'],
            'looseEggs' => (int)$row['looseEggs'],
            'mortality' => (int)$row['mortality'],
            'batch_id' => (int)$row['batch_id']
        ];
    }
    $result->free();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching data: ' . $mysqli->error]);
    exit;
}

$mysqli->close();

$response = [
    (string)$client_id => $productionData
];
echo json_encode($response, JSON_PRETTY_PRINT);
?>
