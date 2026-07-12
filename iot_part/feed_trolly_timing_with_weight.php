<?php
header('Content-Type: application/json');
include("db.php");

$time = $_GET['time'] ?? null;  // example: 00:50:00

if(!$time){
    echo json_encode(["success" => false, "message" => "time missing"]);
    exit;
}

$sql = "
    SELECT feed_weight_kg 
    FROM feed_trolly_weight
    WHERE DATE_FORMAT(updated_at, '%H:%i') = DATE_FORMAT(?, '%H:%i')
    ORDER BY updated_at DESC
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $time);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    echo json_encode([
        "success" => true,
        "unloaded_kg" => intval($row['feed_weight_kg'])
    ]);
} else {
    echo json_encode([
        "success" => true,
        "unloaded_kg" => 0
    ]);
}
?>
