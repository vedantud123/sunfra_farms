<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "❌ DB connection failed"]);
    exit;
}

$client_id = $_GET['client_id'] ?? 0;

$query = "SELECT * FROM `water_medicine` WHERE client_id = ? ORDER BY id DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => (int)$row["id"],
        "place" => $row["place"],
        "name" => $row["name"],
        "quantity" => (float)$row["quantity"],
        "timestamp" => $row["timestamp"],
        "description" => $row["description"],
        "client_id" => (int)$row["client_id"]
    ];
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
