<?php
header('Content-Type: application/json');

// Connect to DB
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

// Validate inputs
$client_id = isset($_GET['client_id']) ? (int) $_GET['client_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';

if ($client_id === 0 || !$date) {
    echo json_encode(['error' => 'Missing client_id or date']);
    exit;
}

// Prepare query
$stmt = $mysqli->prepare("
    SELECT sale 
    FROM egg_godown_stock 
    WHERE sale IS NOT NULL 
      AND client_id = ? 
      AND DATE(TIMESTAMP) = ? 
    GROUP BY sale
");

$stmt->bind_param("is", $client_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$sales = [];
while ($row = $result->fetch_assoc()) {
    $sales[] = $row['sale'];
}

echo json_encode($sales);
?>
