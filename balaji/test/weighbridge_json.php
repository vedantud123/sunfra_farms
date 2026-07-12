<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $mysqli->connect_error
    ]);
    exit;
}

$query = "SELECT * FROM weighbridge ORDER BY id DESC";

$groupedData = [];

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $clientId = $row['client_id'] ?? 0;

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

        if (!isset($groupedData[$clientId])) {
            $groupedData[$clientId] = [];
        }
        $groupedData[$clientId][] = $entry;
    }
    $result->free();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching weighBridge data: ' . $mysqli->error
    ]);
    exit;
}

$mysqli->close();

echo json_encode($groupedData, JSON_PRETTY_PRINT);
?>
