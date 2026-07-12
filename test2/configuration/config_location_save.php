<?php
$mysqli =  mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo 'DB connection failed';
    exit;
}

$client_id = $_POST['client_id'] ?? 0;
$location = trim($_POST['location'] ?? '');
$operation = $_POST['operation'] ?? '';

if (!$client_id || !$location || !$operation) {
    http_response_code(400);
    echo 'Invalid input';
    exit;
}

if ($operation === 'add') {
    $stmt = $mysqli->prepare("INSERT INTO config_location (location, client_id) VALUES (?, ?)");
    $stmt->bind_param("si", $location, $client_id);
    $stmt->execute();
    echo 'Location added';
    $stmt->close();
}

elseif ($operation === 'delete') {
    $stmt = $mysqli->prepare("DELETE FROM config_location WHERE location = ? AND client_id = ?");
    $stmt->bind_param("si", $location, $client_id);
    $stmt->execute();
    echo 'Location deleted';
    $stmt->close();
}

$mysqli->close();
?>
