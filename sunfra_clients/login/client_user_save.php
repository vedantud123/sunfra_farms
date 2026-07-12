<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set("Asia/Kolkata");

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "❌ DB Connection Failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$client_id = $data['client_id'] ?? '';
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (!$client_id || !$username || !$password) {
    echo json_encode(["status" => "error", "message" => "⚠️ All fields are required."]);
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO farm_users (client_id, username, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $client_id, $username, $hashed_password);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "✅ User saved successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "❌ Failed to save user."]);
}

$stmt->close();
$conn->close();
?>
