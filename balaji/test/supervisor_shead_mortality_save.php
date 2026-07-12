<?php
session_start();

header("Content-Type: application/json");
date_default_timezone_set("Asia/Kolkata");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

$client_id = isset($data['client_id']) ? intval($data['client_id']) : 0;
$sheadNo = mysqli_real_escape_string($conn, $data['sheadNo']);
$noOfBirds = intval($data['noOfBirds']);
$id = isset($data['id']) ? intval($data['id']) : 0;

$date = date('Y-m-d');
$timestamp = date('Y-m-d H:i:s');

if ($id >= 1) {
    // Update existing record
    $sql = "UPDATE supervisor_shead_mortality SET sheadNo = ?, noOfBirds = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $sheadNo, $noOfBirds, $id);
} else {
    // Check for duplicate entry
    $check_query = "SELECT id FROM supervisor_shead_mortality WHERE sheadNo = ? AND date = ? AND client_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ssi", $sheadNo, $date, $client_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo json_encode([
            "status" => "exists",
            "message" => "Mortality already exists for $sheadNo on $date"
        ]);
        $check_stmt->close();
        $conn->close();
        exit;
    }
    $check_stmt->close();

    // Insert new record
    $sql = "INSERT INTO supervisor_shead_mortality (sheadNo, noOfBirds, date, client_id, timestamp) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisis", $sheadNo, $noOfBirds, $date, $client_id, $timestamp);
}

if ($stmt->execute()) {
    $stmt->close();

    // Update batch table
    if (preg_match("/^(chick|grower)$/i", $sheadNo)) {
        $update_query = "UPDATE batch SET live_birds = live_birds - ? WHERE sheadNo = ? AND client_id = ? AND cullDate = '0000-00-00'";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("isi", $noOfBirds, $sheadNo, $client_id);
    } else {
        $number = preg_replace("/[^0-9]/", "", $sheadNo);
        $update_query = "UPDATE batch SET live_birds = live_birds - ? WHERE sheadNo = ? AND client_id = ? AND cullDate = '0000-00-00'";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("isi", $noOfBirds, $number, $client_id);
    }

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Data stored and batch updated"]);
    } else {
        http_response_code(200);
        echo json_encode(["status" => "partial", "message" => "Stored, but failed to update batch"]);
    }

    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "DB insert/update failed"]);
}

$conn->close();
?>
