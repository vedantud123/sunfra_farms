<?php
date_default_timezone_set("Asia/Kolkata");
header("Content-Type: application/json; charset=utf-8");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "message" => $mysqli->connect_error]);
    exit;
}

$result = $mysqli->query("SELECT id, mac_address, client_id FROM auto_batch_mac_address ORDER BY id ASC");
if (!$result) {
    echo json_encode(["status" => "error", "message" => $mysqli->error]);
    exit;
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode(["status" => "success", "data" => $rows], JSON_UNESCAPED_UNICODE);

?>