<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');
$confirm_password = trim($data['confirm_password'] ?? '');

// Validation
if (!$username || !$password || !$confirm_password) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}
if ($password !== $confirm_password) {
    echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
    exit;
}

// DB Connection
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Get client_id from session
$client_id = $_SESSION['client_id'] ?? 0;

// If first time, fetch it from farm_users table (admin login)
if (!$client_id) {
    $stmt = $conn->prepare("SELECT client_id FROM farm_users WHERE username = ? AND password = ? AND status = 'admin'");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->bind_result($fetched_client_id);
    
    if ($stmt->fetch()) {
        $_SESSION['client_id'] = $fetched_client_id;
        $stmt->close();
        echo json_encode(["status" => "success", "message" => "Welcome Admin. Client ID set."]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid admin credentials or session missing"]);
        exit;
    }
}

// Check if username already exists for this client
$check = $conn->prepare("SELECT id FROM farm_users WHERE username = ? AND client_id = ?");
$check->bind_param("si", $username, $client_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Username already exists"]);
    exit;
}
$check->close();

// Insert new user
$stmt = $conn->prepare("INSERT INTO farm_users (username, password, client_id, status) VALUES (?, ?, ?, 'user')");
$stmt->bind_param("ssi", $username, $password, $client_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "User registered successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to register user. Try again."]);
}

$stmt->close();
$conn->close();
