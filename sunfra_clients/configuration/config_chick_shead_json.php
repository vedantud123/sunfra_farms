<?php
header('Content-Type: application/json');

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$query = "
    SELECT shead_name 
    FROM shead_status 
    WHERE client_id = $client_id 
      AND description = 'Chick Number'
    ORDER BY id ASC
";

$result = $mysqli->query($query);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode(['message' => "No records found for client_id = $client_id with description = 'Shead Number'"]);
}

$mysqli->close();
?>
