<?php
header('Content-Type: application/json');

$response = [];

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}


$query = "SELECT * FROM tractor_production_mortality ORDER BY id DESC";

$productionData = [];

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $client_id = $row['client_id'] ?? 'Unknown';
       // $company = $row['company_name'] ?? 'Unknown';
        
        $productionData[$client_id][] = [
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

echo json_encode($productionData, JSON_PRETTY_PRINT);
?>
