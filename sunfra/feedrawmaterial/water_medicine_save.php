<?php
session_start(); 
 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "❌ DB connection failed"]);
    exit;
}

$data = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? $_POST
    : ($_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : json_decode(file_get_contents("php://input"), true));
 
if (empty($data['place']) || empty($data['name']) || !isset($data['quantity'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "❌ Missing required fields"]);
    exit;
}
 
$id = isset($data['id']) ? intval($data['id']) : 0;
$place = $mysqli->real_escape_string($data['place']);
$name = $mysqli->real_escape_string($data['name']);
$quantity = floatval($data['quantity']);
$description = $mysqli->real_escape_string($data['description'] ?? '');
$client_id = isset($data['client_id']) ? intval($data['client_id']) : 0;

if ($client_id === 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "❌ client_id is missing or invalid"]);
    exit;
}
$timestamp = date('Y-m-d H:i:s');

if ($id > 0) {
    $fetch_old = $mysqli->prepare("SELECT quantity FROM water_medicine WHERE id = ?");
    $fetch_old->bind_param("i", $id);
    $fetch_old->execute();
    $fetch_old->bind_result($old_quantity);
 
    if ($fetch_old->fetch()) {
        $fetch_old->close();
 
        $difference = $quantity - $old_quantity;
        $update_stock = true;
 
        if ($difference != 0) {
            $stock_query = "UPDATE feed_rawmaterial SET stock = stock - ? WHERE name = ? AND client_id = ?";
            $stock_stmt = $mysqli->prepare($stock_query);
            $stock_stmt->bind_param("dsi", $difference, $name, $client_id);
            $update_stock = $stock_stmt->execute();
            $stock_stmt->close();

            $log_query = "INSERT INTO feed_material_reduction_logs (material_name, reduced_quantity, timestamp, client_id) VALUES (?, ?, ?, ?)";
            $log_stmt = $mysqli->prepare($log_query);
            $log_stmt->bind_param("sdsi", $name, $difference, $timestamp, $client_id);
            $log_stmt->execute();
            $log_stmt->close();
        }
 
        $update_query = "UPDATE water_medicine SET place=?, name=?, quantity=?, description=?, timestamp=?, client_id=? WHERE id=?";
        $update_stmt = $mysqli->prepare($update_query);
        $update_stmt->bind_param("ssdssii", $place, $name, $quantity, $description, $timestamp, $client_id, $id);
 
        if ($update_stmt->execute() && $update_stock) {
            echo json_encode(["status" => "success", "message" => "✅ Entry updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "❌ Failed to update entry or stock"]);
        }
 
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "❌ Entry not found for update"]);
    }
 
} else {
    $insert_query = "INSERT INTO water_medicine (place, name, quantity, description, timestamp, client_id) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $mysqli->prepare($insert_query);
    $insert_stmt->bind_param("ssdssi", $place, $name, $quantity, $description, $timestamp, $client_id);
 
    $stock_query = "UPDATE feed_rawmaterial SET stock = stock - ? WHERE name = ? AND client_id = ?";
    $stock_stmt = $mysqli->prepare($stock_query);
    $stock_stmt->bind_param("dsi", $quantity, $name, $client_id);
 
    if ($insert_stmt->execute() && $stock_stmt->execute()) {

        $log_query = "INSERT INTO feed_material_reduction_logs (material_name, reduced_quantity, timestamp, client_id) VALUES (?, ?, ?, ?)";
        $log_stmt = $mysqli->prepare($log_query);
        $log_stmt->bind_param("sdsi", $name, $quantity, $timestamp, $client_id);
        $log_stmt->execute();
        $log_stmt->close();

        echo json_encode(["status" => "success", "message" => "✅ Entry saved"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "❌ Failed to save entry or update stock"]);
    }
}
?>
