<?php
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data safely
$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$material_name = isset($_POST['material_name']) ? $conn->real_escape_string(trim($_POST['material_name'])) : '';
$price = isset($_POST['price']) ? $conn->real_escape_string(trim($_POST['price'])) : ''; // varchar, so using string

if ($client_id <= 0 || empty($material_name) || $price === '') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid input."]);
    exit;
}

// Check if record exists
$sqlCheck = "SELECT COUNT(*) as count FROM feed_rawmaterial_price WHERE client_id = ? AND name = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("is", $client_id, $material_name);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
$row = $resultCheck->fetch_assoc();

if ($row['count'] > 0) {
    // Update existing record
    $sqlUpdate = "UPDATE feed_rawmaterial_price SET price = ? WHERE client_id = ? AND name = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("sis", $price, $client_id, $material_name);

    if ($stmtUpdate->execute()) {
        echo json_encode(["status" => "success", "message" => "Price updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to update price."]);
    }
    $stmtUpdate->close();

} else {
    // Insert new record
    $sqlInsert = "INSERT INTO feed_rawmaterial_price (name, price, client_id) VALUES (?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("ssi", $material_name, $price, $client_id);

    if ($stmtInsert->execute()) {
        echo json_encode(["status" => "success", "message" => "Material price added successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to add material price."]);
    }
    $stmtInsert->close();
}

$stmtCheck->close();
$conn->close();
?>
