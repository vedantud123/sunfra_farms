<?php
header('Content-Type: application/json');
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
 
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}
 
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
 
if ($client_id === 0) {
    echo json_encode(["error" => "Missing or invalid client_id"]);
    exit;
}
 
$sql = "SELECT * FROM egg_weight WHERE client_id = ? order by shead_name";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
 
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
 
$response = [$client_id => $data];
echo json_encode($response);
?>