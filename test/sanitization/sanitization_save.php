<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "❌ DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['place']) ||
    empty($data['name']) ||
    !isset($data['quantity']) ||
    !isset($data['client_id'])
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "❌ Missing required fields"]);
    exit;
}

$place = $mysqli->real_escape_string($data['place']);
$name = $mysqli->real_escape_string($data['name']);
$quantity = floatval($data['quantity']);
$description = isset($data['description']) ? $mysqli->real_escape_string($data['description']) : '';
$timestamp = date('Y-m-d H:i:s');
$client_id = intval($data['client_id']);

$insert_query = "INSERT INTO `water_medicine` (place, name, quantity, description, timestamp, client_id) VALUES (?, ?, ?, ?, ?, ?)";
$insert_stmt = $mysqli->prepare($insert_query);
$insert_stmt->bind_param("ssdssi", $place, $name, $quantity, $description, $timestamp, $client_id);

$stock_query = "UPDATE feed_rawmaterial SET stock = stock - ? WHERE name = ? AND client_id = ?";
$stock_stmt = $mysqli->prepare($stock_query);
$stock_stmt->bind_param("dsi", $quantity, $name, $client_id);

$is_json = strpos($_SERVER["CONTENT_TYPE"] ?? '', "application/json") !== false;

if ($insert_stmt->execute() && $stock_stmt->execute()) {
    $insert_stmt->close();
    $stock_stmt->close();
    $mysqli->close();

    if ($is_json) {
        echo json_encode([
            "status" => "success",
            "message" => "✅ Sanitization record added",
            "redirect" => "https://sunfra.com/farm/test/sanitization/sanitization_json_to_web.php"
        ]);
    } else {
        header("Location: https://sunfra.com/farm/test/sanitization/sanitization_json_to_web.php");
    }
    exit;
} else {
    exit;
}
?>
