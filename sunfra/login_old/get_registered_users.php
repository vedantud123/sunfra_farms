<?php
session_start();
header("Content-Type: application/json");

$client_id = $_SESSION['client_id'] ?? 0;
if (!$client_id) {
    echo json_encode([]);
    exit;
}

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT username, status FROM farm_users WHERE client_id = ? AND status = 'user' ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$grouped = [];
while ($row = $result->fetch_assoc()) {
    $grouped[$client_id][] = $row;
}

$conn->close();
echo json_encode($grouped, JSON_PRETTY_PRINT);
?>
