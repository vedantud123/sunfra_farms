<?php
header("Content-Type: application/json");

// TODO: change credentials
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "msg" => "DB error"]);
    exit;
}

$sql = "
SELECT 
    t.id,
    t.feeding_time,
    IFNULL(w.feed_weight_kg, 0) AS feed_weight_kg
FROM feed_trolly_timing t
LEFT JOIN feed_trolly_weight w ON t.id = w.timing_id
ORDER BY t.feeding_time ASC
";

$res = $conn->query($sql);
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
