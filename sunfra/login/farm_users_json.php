<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "❌ DB connection failed"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT u.id, u.username, u.password, u.client_id, c.company_name AS client_name, c.status
    FROM farm_users u
    JOIN sunfra_clients c ON u.client_id = c.client_id
");
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(["status" => "success", "users" => $users]);
?>