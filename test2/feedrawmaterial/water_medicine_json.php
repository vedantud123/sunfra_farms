<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "❌ DB connection failed"]);
    exit;
}

$client_id = (int)$_GET['client_id'];
if (!$client_id) {
    echo json_encode(["status" => "error", "message" => "❌ Client ID missing from request"]);
    exit;
}

$query = "SELECT * FROM water_medicine WHERE client_id = ? ORDER BY id DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $cid = (string)$row["client_id"];  
    if (!isset($data[$cid])) {
        $data[$cid] = [];
    }

    $data[$cid][] = [
        "id" => (int)$row["id"],
        "place" => $row["place"],
        "name" => $row["name"],
        "quantity" => (float)$row["quantity"],
        "timestamp" => $row["timestamp"],
        "description" => $row["description"],
        "client_id" => (int)$row["client_id"]
    ];
}

if (empty($data)) {
    echo json_encode(["status" => "ok", "data" => [], "message" => "No records found."]);
} else {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}
?>
