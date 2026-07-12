<?php

if (isset($_GET['client_id'])) {
    $client_id = (int) $_GET['client_id'];
}else {
    echo json_encode(["error" => "Client ID not found in session or URL"]);
    exit;
}

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

// DB connection check
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// SQL query
$sql = "SELECT id, vaccine_name, vaccine_cost, labour_cost, timestamp, shead_number 
        FROM vaccination_costing 
        WHERE client_id = ? 
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Output result
header('Content-Type: application/json');
echo json_encode([$client_id => $data], JSON_PRETTY_PRINT);

$stmt->close();
$conn->close();
?>
