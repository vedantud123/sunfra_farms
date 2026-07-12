<?php
header("Content-Type: application/json");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $mysqli->connect_error]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = isset($data['id']) ? intval($data['id']) : 0;
$client_id = isset($data['client_id']) ? intval($data['client_id']) : 0;
$date = $mysqli->real_escape_string($data['date']);
$sheadNo = intval($data['sheadNo']);
$eggTrays = intval($data['eggTrays']);
$looseEggs = intval($data['looseEggs']);
$mortality = intval($data['mortality']);
$production = ($eggTrays * 30) + $looseEggs;
$timestamp = date('Y-m-d H:i:s');

$batch_id = 0;
$batchQuery = $mysqli->query("SELECT batch_id FROM batch WHERE sheadNo='$sheadNo' AND (cullDate > hatchDate OR cullDate='0000-00-00')");
if ($batchQuery && $batchQuery->num_rows > 0) {
    $row = $batchQuery->fetch_assoc();
    $batch_id = $row['batch_id'];
}

if ($id > 0) {
    $sql = "UPDATE tractor_production_mortality SET 
        sheadNo='$sheadNo', 
        batch_id='$batch_id', 
        date='$date', 
        eggTrays='$eggTrays', 
        looseEggs='$looseEggs', 
        mortality='$mortality', 
        production='$production',
        client_id='$client_id'
        WHERE id='$id'";
} else {
    $sql = "INSERT INTO tractor_production_mortality 
        (sheadNo, batch_id, date, eggTrays, looseEggs, mortality, production, timestamp, client_id) 
        VALUES 
        ('$sheadNo', '$batch_id', '$date', '$eggTrays', '$looseEggs', '$mortality', '$production', '$timestamp', '$client_id')";
}

if ($mysqli->query($sql)) {
    echo json_encode(["status" => "success", "message" => "✅ Data saved successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => $mysqli->error]);
}

$mysqli->close();
?>
