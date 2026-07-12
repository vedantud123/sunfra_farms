<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "❌ DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (
    !$data ||
    !isset($data['name'], $data['stock'], $data['metric'], $data['type'], $data['client_id'])
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "❌ Missing required fields"]);
    exit;
}

$id = isset($data['id']) ? intval($data['id']) : 0;
$client_id = intval($data['client_id']);
$name = $mysqli->real_escape_string($data['name']);
$stock = floatval($data['stock']);
$metric = $mysqli->real_escape_string($data['metric']);
$type = $mysqli->real_escape_string($data['type']);

if ($id == 0) {
    $checkQuery = "SELECT id FROM feed_rawmaterial WHERE client_id = $client_id AND name = '$name'";
    $checkResult = $mysqli->query($checkQuery);

    if ($checkResult && $checkResult->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "❌ Material name already exists for this client"]);
        exit;
    }
}

if ($id > 0) {
    $query = "UPDATE feed_rawmaterial 
              SET name='$name', stock=$stock, metric='$metric', type='$type', client_id=$client_id 
              WHERE id=$id";
} else {
    $query = "INSERT INTO feed_rawmaterial (client_id, name, stock, metric, type) 
              VALUES ($client_id, '$name', $stock, '$metric', '$type')";
}

if ($mysqli->query($query)) {
    echo json_encode(["status" => "success", "message" => "✅ Data saved successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "❌ Failed to save data", "error" => $mysqli->error]);
}
?>
