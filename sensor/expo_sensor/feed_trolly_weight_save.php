<?php
header("Content-Type: application/json");

// TODO: change credentials
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "msg" => "DB error"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$feeding_time = isset($input['feeding_time']) ? $input['feeding_time'] : null; // "05:00:00"
$weight_kg    = isset($input['feed_weight_kg']) ? (int)$input['feed_weight_kg'] : 0;

if (!$feeding_time || $weight_kg <= 0) {
    echo json_encode(["success" => false, "msg" => "Invalid input"]);
    exit;
}

// find timing_id for that time
$stmt = $conn->prepare("SELECT id FROM feed_trolly_timing WHERE feeding_time = ? LIMIT 1");
$stmt->bind_param("s", $feeding_time);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    echo json_encode(["success" => false, "msg" => "Timing not found"]);
    exit;
}

$timing_id = (int)$row['id'];

// upsert weight
$check = $conn->prepare("SELECT id FROM feed_trolly_weight WHERE timing_id = ? LIMIT 1");
$check->bind_param("i", $timing_id);
$check->execute();
$checkRes = $check->get_result();

if ($checkRes->num_rows > 0) {
    $upd = $conn->prepare("UPDATE feed_trolly_weight SET feed_weight_kg = ? WHERE timing_id = ?");
    $upd->bind_param("ii", $weight_kg, $timing_id);
    $upd->execute();
} else {
    $ins = $conn->prepare("INSERT INTO feed_trolly_weight (timing_id, feed_weight_kg) VALUES (?,?)");
    $ins->bind_param("ii", $timing_id, $weight_kg);
    $ins->execute();
}

echo json_encode(["success" => true]);
