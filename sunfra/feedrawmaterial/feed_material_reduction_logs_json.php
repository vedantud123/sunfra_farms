<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]));
}

header('Content-Type: application/json');

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid client_id"
    ]);
    exit;
}

$sql = "
    SELECT 
        material_name,
        SUM(reduced_quantity) AS total_reduced_quantity,
        MAX(timestamp) AS latest_timestamp
    FROM 
        feed_material_reduction_logs
    WHERE 
        client_id = ?
        AND timestamp >= DATE_SUB(NOW(), INTERVAL 60 DAY)
    GROUP BY 
        material_name
    ORDER BY 
        latest_timestamp DESC
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "ok",
    "count" => count($data),
    "data" => $data
]);

$stmt->close();
$mysqli->close();
?>
