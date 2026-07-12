<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);  
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

$mode = $_POST['mode'] ?? 'add';
$old_batch_id = $_POST['old_batch_id'] ?? '';

$batch_id = $_POST['batch_id'] ?? '';
$breed = $_POST['breed'] ?? '';
$hatchDate = $_POST['hatchDate'] ?? '';
$noOfChicks = $_POST['noOfChicks'] ?? 0;
$sheadNo = $_POST['sheadNo'] ?? '';
$cullDate = $_POST['cullDate'] ?? '0000-00-00';
$live_birds = $_POST['live_birds'] ?? 0;
$client_id = $_POST['client_id'] ?? 0;

$noOfChicks = (int)$noOfChicks;
$live_birds = (int)$live_birds;
$client_id = (int)$client_id;

if ($mode === 'add') {
    $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM batch WHERE batch_id = ? AND client_id = ? AND cullDate = '0000-00-00'");
    $checkStmt->bind_param("si", $batch_id, $client_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode(['status' => 'error', 'message' => 'A batch with this ID already exists for this client and is not culled yet.']);
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO batch (batch_id, breed, hatchDate, noOfChicks, sheadNo, cullDate, live_birds, client_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssissii", $batch_id, $breed, $hatchDate, $noOfChicks, $sheadNo, $cullDate, $live_birds, $client_id);

} elseif ($mode === 'update' && !empty($old_batch_id)) {
    $stmt = $mysqli->prepare("UPDATE batch SET batch_id=?, breed=?, hatchDate=?, noOfChicks=?, sheadNo=?, cullDate=?, live_birds=? WHERE batch_id=? AND client_id=?");
    $stmt->bind_param("sssissisi", $batch_id, $breed, $hatchDate, $noOfChicks, $sheadNo, $cullDate, $live_birds, $old_batch_id, $client_id);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid operation']);
    exit;
}

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'SQL prepare failed: ' . $mysqli->error]);
    exit;
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => $mode === 'add' ? 'Batch added successfully.' : 'Batch updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
