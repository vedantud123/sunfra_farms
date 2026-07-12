<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

$batch_id = $_POST['batch_id'] ?? '';
$breed = $_POST['breed'] ?? '';
$hatchDate = $_POST['hatchDate'] ?? '';
$noOfChicks = $_POST['noOfChicks'] ?? '';
$sheadNo = $_POST['sheadNo'] ?? '';
$cullDate = $_POST['cullDate'] ?? '0000-00-00';
$live_birds = $_POST['live_birds'] ?? '';

if (empty($batch_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Batch ID is required for update.']);
    exit;
}

$stmt = $mysqli->prepare("UPDATE bat SET breed = ?, hatchDate = ?, noOfChicks = ?, sheadNo = ?, cullDate = ?, live_birds = ? WHERE batch_id = ?");

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param("ssissis", $breed, $hatchDate, $noOfChicks, $sheadNo, $cullDate, $live_birds, $batch_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Batch updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
