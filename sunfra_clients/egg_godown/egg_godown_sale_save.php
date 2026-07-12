<?php
header("Content-Type: application/json");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}
date_default_timezone_set('Asia/Kolkata');

$id = $data['id'] ?? '';
$client_id = $data['client_id'] ?? '';
$shead_name = $data['shead_name'] ?? '';
$no_of_eggs = $data['no_of_eggs'] ?? '';
$type_of_eggs = $data['type_of_eggs'] ?? '';
$sale = $data['sale'] ?? '';
$sale_price = $data['sale_price'] ?? '';
$remarks = $data['remarks'] ?? '';
$created_at = date("Y-m-d H:i:s");

if (!empty($id)) {
    $stmt = $mysqli->prepare("UPDATE egg_godown_stock SET shead_name=?, no_of_eggs=?, type_of_eggs=?, sale=?, sale_price=?, remarks=? WHERE id=? and client_id=?");
    $stmt->bind_param("ssssssii", $shead_name, $no_of_eggs, $type_of_eggs, $sale, $sale_price, $remarks, $id, $client_id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO egg_godown_stock (client_id, shead_name, no_of_eggs, type_of_eggs, sale, sale_price, remarks, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $client_id, $shead_name, $no_of_eggs, $type_of_eggs, $sale, $sale_price, $remarks, $created_at);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
