<?php
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "This API only accepts POST requests"]);
    exit;
}

// Get POST data
$id = $_POST['id'] ?? 0;
$vaccine_name = trim($_POST['vaccine_name'] ?? '');
$vaccine_cost = floatval($_POST['vaccine_cost'] ?? 0);
$labour_cost = floatval($_POST['labour_cost'] ?? 0);
$timestamp = $_POST['timestamp'] ?? date("Y-m-d H:i:s");
$shead_number = trim($_POST['shead_number'] ?? '');
$client_id = $_POST['client_id'] ?? 0;

// ✅ Validation
if (empty($vaccine_name) || empty($shead_number)) {
    echo json_encode(["success" => false, "error" => "Missing required fields: vaccine_name or shead_number"]);
    exit;
}

// 🔁 UPDATE if ID > 0
if ($id > 0) {
    $sql = "UPDATE vaccination_costing 
            SET vaccine_name = ?, vaccine_cost = ?, labour_cost = ?, timestamp = ?, shead_number = ?, client_id = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddssii", $vaccine_name, $vaccine_cost, $labour_cost, $timestamp, $shead_number, $client_id, $id);
} else {
    // ➕ INSERT new
    $sql = "INSERT INTO vaccination_costing (vaccine_name, vaccine_cost, labour_cost, timestamp, shead_number, client_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddssi", $vaccine_name, $vaccine_cost, $labour_cost, $timestamp, $shead_number, $client_id);
}

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $id > 0 ? "Updated successfully" : "Inserted successfully",
        "insert_id" => $id > 0 ? $id : $stmt->insert_id
    ]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
