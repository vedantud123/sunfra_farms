<?php
session_start();
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
date_default_timezone_set('Asia/Kolkata');

$timestamp = date('Y-m-d H:i:s');
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $taskId = $_POST['id'];
    $sql_update = "UPDATE farm_task_list_logs SET status = 'Done' , timestamp = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("si", $timestamp, $taskId);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    
    $stmt->close();
}

$conn->close();
?>
