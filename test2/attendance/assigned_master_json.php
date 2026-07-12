<?php
session_start();
header('Content-Type: application/json');

// Direct DB connection
$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if (!$conn) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . mysqli_connect_error()]));
}

if (!isset($_SESSION['client_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$client_id = (int)$_SESSION['client_id'];
$date = $_GET['date'] ?? date('Y-m-d');

// Use assigned_date column
$stmt = mysqli_prepare($conn, "SELECT id, location, person_name FROM task_master WHERE client_id = ? AND assigned_date = ?");
mysqli_stmt_bind_param($stmt, "is", $client_id, $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$assignments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $assignments[] = $row;
}

echo json_encode([
    "status" => "success",
    "assignments" => $assignments
]);
?>
