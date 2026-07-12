<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
$errors = [];

if (!$input) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

$client_id     = $mysqli->real_escape_string($input['client_id'] ?? '');
$username      = $mysqli->real_escape_string($input['username'] ?? '');
$password      = $mysqli->real_escape_string($input['password'] ?? '');
$company_name  = $mysqli->real_escape_string($input['company_name'] ?? '');

if (empty($client_id) || empty($username) || empty($password) || empty($company_name)) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

// Check duplicate client_id
$check = $mysqli->query("SELECT client_id FROM sunfra_registration WHERE client_id = '$client_id'");
if ($check && $check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Client ID already exists. Please choose another one."]);
    exit;
}

// Start transaction
$mysqli->begin_transaction();

try {
    // Insert into sunfra_registration
    $sql1 = "INSERT INTO sunfra_registration (client_id, username, password, company_name, status)
             VALUES ('$client_id', '$username', '$password', '$company_name', 'admin')";
    if (!$mysqli->query($sql1)) {
        throw new Exception("Failed to insert into sunfra_registration");
    }

    // Insert into farm_users
    $sql2 = "INSERT INTO farm_users (username, password, client_id, status)
             VALUES ('$username', '$password', '$client_id', 'admin')";
    if (!$mysqli->query($sql2)) {
        throw new Exception("Failed to insert into farm_users");
    }

    // Commit if both succeed
    $mysqli->commit();
    echo json_encode(["status" => "success", "message" => "Client registered successfully."]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$mysqli->close();
?>
