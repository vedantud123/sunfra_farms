<?php
header("Content-Type: application/json");

// --------------------------------------
// DB Connection
// --------------------------------------
$host = '216.172.184.173';
$user = 'sunfra_farms';
$password = 'sunfra_farms';
$database = 'sunfra_farms';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "DB connection failed: " . $conn->connect_error
    ]);
    exit;
}

// --------------------------------------
// Read quantity from URL
// Example: feed_trolly_quantity_save.php?quantity=50
// --------------------------------------
$quantity = isset($_GET['quantity']) ? $_GET['quantity'] : null;

if ($quantity === null || $quantity === "") {
    echo json_encode([
        "success" => false,
        "message" => "quantity parameter is required"
    ]);
    exit;
}

// --------------------------------------
// Insert quantity
// --------------------------------------
$sql = "INSERT INTO feed_trolly_quantity (quantity) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $quantity);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Quantity saved",
        "insert_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Insert failed: " . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>
