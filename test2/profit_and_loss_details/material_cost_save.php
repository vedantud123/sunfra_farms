<?php
session_start();

header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "message" => "❌ DB connection failed: " . $mysqli->connect_error]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !is_array($input)) {
    echo json_encode(["status" => "error", "message" => "❌ Invalid JSON input"]);
    exit;
}

$id = isset($input['id']) ? intval($input['id']) : 0;
$name = isset($input['name']) ? trim($input['name']) : '';
$price = isset($input['price']) ? trim($input['price']) : '';
$client_id = isset($input['client_id']) ? trim($input['client_id']) : '';

if (empty($name) || empty($price)) {
    echo json_encode(["status" => "error", "message" => "❌ Name or Price is missing"]);
    exit;
}

if ($id > 0) {
    // ✅ UPDATE
    $stmt = $mysqli->prepare("UPDATE feed_rawmaterial_price SET name = ?, price = ? WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ssii", $name, $price, $id, $client_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "✅ Updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "❌ Update failed"]);
    }
    $stmt->close();
} else {
    // ✅ INSERT
    $stmt = $mysqli->prepare("INSERT INTO feed_rawmaterial_price (name, price, client_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $name, $price, $client_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "✅ Inserted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "❌ Insert failed"]);
    }
    $stmt->close();
}

$mysqli->close();
