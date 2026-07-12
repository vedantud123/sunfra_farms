<?php
session_start(); 
 
header('Content-Type: application/json');
 
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "message" => "❌ DB Connection failed"]);
    exit;
}
 
$client_id = (int)$_GET['client_id'];

$query = "SELECT id, shead_name, cutting_price FROM egg_cutting_price WHERE client_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
 
$data = [];
 
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => (int)$row['id'],
        "shead" => $row['shead_name'],
        "cutting_price" => (float)$row['cutting_price'],
    ];
}
 
echo json_encode([
    (string)$client_id => $data
]);
 
$stmt->close();
$mysqli->close();
?>