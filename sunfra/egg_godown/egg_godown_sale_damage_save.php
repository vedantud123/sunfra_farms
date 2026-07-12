<?php
date_default_timezone_set('Asia/Kolkata');
header("Content-Type: application/json");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

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

/* ---------- INPUT VALUES ---------- */

$client_id = intval($input['client_id'] ?? 0);
$shead_name = $mysqli->real_escape_string(trim($input['shead_name'] ?? ''));
$no_of_eggs = intval($input['no_of_eggs'] ?? 0);
$sale = $mysqli->real_escape_string(trim($input['sale'] ?? ''));

/* DATE FROM API */
$entry_date = $input['date'] ?? date("Y-m-d");

/* CONVERT DATE TO DATETIME */
$timestamp = $entry_date . " " . date("H:i:s");

/* STATIC VALUES */

$type_of_eggs = "Damaged";
$remarks = "Return";
$sale_price = 0;

/* ---------- VALIDATION ---------- */

if (!$client_id || !$shead_name || !$no_of_eggs || !$sale) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

/* ---------- INSERT QUERY ---------- */

$query = "
INSERT INTO egg_godown_stock 
(shead_name, no_of_eggs, type_of_eggs, timestamp, sale_price, sale, remarks, client_id) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $mysqli->prepare($query);

$stmt->bind_param(
    "sississi",
    $shead_name,
    $no_of_eggs,
    $type_of_eggs,
    $timestamp,
    $sale_price,
    $sale,
    $remarks,
    $client_id
);

/* ---------- EXECUTE ---------- */

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "timestamp_saved" => $timestamp
    ]);

} else {

    http_response_code(500);
    echo json_encode(["error" => $stmt->error]);

}

$stmt->close();
$mysqli->close();
?>