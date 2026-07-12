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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !is_array($input)) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

// Prepare statements
$stmtSelect = mysqli_prepare($conn, "SELECT id FROM task_master WHERE client_id = ? AND assigned_date = ? AND location = ?");
$stmtInsert = mysqli_prepare($conn, "INSERT INTO task_master (assigned_date, location, person_name, client_id) VALUES (?, ?, ?, ?)");
$stmtUpdate = mysqli_prepare($conn, "UPDATE task_master SET person_name = ? WHERE id = ?");

foreach ($input as $item) {
    $assigned_date = $item['date']; // your frontend still sends 'date'
    $location = $item['location'];
    $person_name = $item['person_name'];

    // Check if exists
    mysqli_stmt_bind_param($stmtSelect, "iss", $client_id, $assigned_date, $location);
    mysqli_stmt_execute($stmtSelect);
    $res = mysqli_stmt_get_result($stmtSelect);

    if (mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $id = $row['id'];
        mysqli_stmt_bind_param($stmtUpdate, "si", $person_name, $id);
        mysqli_stmt_execute($stmtUpdate);
    } else {
        mysqli_stmt_bind_param($stmtInsert, "sssi", $assigned_date, $location, $person_name, $client_id);
        mysqli_stmt_execute($stmtInsert);
    }
}

echo json_encode(["status" => "success"]);
?>
