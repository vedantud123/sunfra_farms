<?php
header('Content-Type: application/json');

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing or invalid client_id'
    ]);
    exit;
}

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $mysqli->connect_error
    ]);
    exit;
}

$query = "SELECT * FROM weighbridge WHERE client_id = $client_id ORDER BY id DESC";

$data = [];

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $entry = [
            'id' => (int)$row['id'],
            'date' => $row['date'],
            'vehicleNumber' => $row['vehicleNumber'],
            'material' => $row['material'],
            'empty' => (float)$row['empty'],
            'gross' => (float)$row['gross'],
            'net' => (float)$row['net'],
            'ownerName' => $row['ownerName'],
            'type' => $row['type'],
            'driverNumber' => $row['driverNumber'],
            'ownerNumber' => $row['ownerNumber'],
            'details' => $row['details'],
        ];
        $data[] = $entry;
    }
    $result->free();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Query failed: ' . $mysqli->error
    ]);
    exit;
}

$mysqli->close();

echo json_encode([
    (string)$client_id => $data
], JSON_PRETTY_PRINT);
?>
