<?php
date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $mysqli->connect_error]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "Missing or invalid JSON input"]);
    exit;
}

$client_id = intval($input['client_id'] ?? 0);
$shead_name = $mysqli->real_escape_string(trim($input['shead_name'] ?? ''));
$no_of_eggs = intval($input['no_of_eggs'] ?? 0);
$sale = $mysqli->real_escape_string(trim($input['sale'] ?? ''));
$type_of_eggs = "Damaged";
$remarks = "Return";
$sale_price = 0;
$timestamp = date('Y-m-d H:i:s');

// Validate input
if (!$client_id || !$shead_name || !$no_of_eggs || !$sale) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$query = "INSERT INTO egg_godown_stock 
    (shead_name, no_of_eggs, type_of_eggs, timestamp, sale_price, sale, remarks, client_id) 
    VALUES ('$shead_name', $no_of_eggs, '$type_of_eggs', '$timestamp', $sale_price, '$sale', '$remarks', $client_id)";

if ($mysqli->query($query)) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["error" => $mysqli->error]);
}
?>
