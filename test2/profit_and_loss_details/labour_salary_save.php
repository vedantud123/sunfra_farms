<?php
header("Content-Type: application/json");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$id        = $_POST['id'] ?? '';
$client_id = $_POST['client_id'] ?? '';
$name      = $_POST['name'] ?? '';
$salary    = $_POST['salary'] ?? '';
$position  = $_POST['position'] ?? '';

if (!$client_id || !$name || !$salary || !$position) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

if ($id === '') {
    $stmt = $mysqli->prepare("INSERT INTO labour_salaries (client_id, name, salary, position) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $client_id, $name, $salary, $position);
    $action = "inserted";
} else {
    $stmt = $mysqli->prepare("UPDATE labour_salaries SET name = ?, salary = ?, position = ? WHERE id = ? AND client_id = ?");
    $stmt->bind_param("sssii", $name, $salary, $position, $id, $client_id);
    $action = "updated";
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Record $action successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Database operation failed"]);
}

$stmt->close();
$mysqli->close();
?>
