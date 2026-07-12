<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "unauthorized";
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Kolkata');
$timestamp = date('Y-m-d H:i:s');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $taskId = intval($_POST['id']);

    $sql_update = "UPDATE farm_task_list_logs 
                   SET status = 'Done', timestamp = ? 
                   WHERE id = ? AND client_id = ?";

    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sii", $timestamp, $taskId, $client_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
}

$conn->close();
?>
