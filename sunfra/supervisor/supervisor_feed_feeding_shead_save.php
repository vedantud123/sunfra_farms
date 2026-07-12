<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$sheadNo = $_POST['sheadNo'] ?? null;
$client_id = $_POST['client_id'] ?? null;

if (!$client_id || !$sheadNo) {
    echo json_encode(["status" => "error", "message" => "Client ID and Shead No are required."]);
    exit;
}

$box_api_url = "https://sunfra.com/farm/sunfra/configuration/config_shead_box_json.php?client_id=" . $client_id;
$box_json = file_get_contents($box_api_url);
$box_data = json_decode($box_json, true);

$shead_box_list = [];
if (isset($box_data[$client_id])) {
    foreach ($box_data[$client_id] as $box_entry) {
        $shead_box_list[] = $box_entry['box_numbers'];
    }
}

$date = date('Y-m-d');

$delete_sql = "DELETE FROM supervisor_feed_feeding_shead_test 
               WHERE sheadNo = ? AND client_id = ? AND `date` = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("sis", $sheadNo, $client_id, $date);
$delete_stmt->execute();
$delete_stmt->close();

$insert_sql = "INSERT INTO supervisor_feed_feeding_shead_test (sheadNo, box_number, quantity, client_id, `date`) 
               VALUES (?, ?, ?, ?, ?)";
$insert_stmt = $conn->prepare($insert_sql);

foreach ($shead_box_list as $box_number) {
    $quantity = $_POST[$box_number] ?? 0;
    $insert_stmt->bind_param("ssdis", $sheadNo, $box_number, $quantity, $client_id, $date);
    $insert_stmt->execute();
}

$insert_stmt->close();
$conn->close();

echo json_encode(["status" => "success", "message" => "Old data deleted and new data inserted successfully."]);
?>
