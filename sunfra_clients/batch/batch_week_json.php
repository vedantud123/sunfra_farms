<?php

header('Content-Type: application/json');

$response = [];

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    echo json_encode(['error' => "Connection failed: " . $mysqli->connect_error]);
    exit;
}

$client_id = (int)$_GET['client_id'];
if (!$client_id) {
    echo json_encode(["status" => "error", "message" => "❌ Client ID missing from request"]);
    exit;
}

$shead_url = "https://sunfra.com/farm/sunfra_clients/configuration/shead_chick_grower_json.php?client_id=$client_id";
$shead_response = file_get_contents($shead_url);

if ($shead_response === false) {
    echo json_encode(["error" => "Unable to fetch shead data"]);
    exit;
}

$shead_data = json_decode($shead_response, true);
if (!is_array($shead_data)) {
    echo json_encode(["error" => "Invalid JSON received"]);
    exit;
}

$shead_list = [];
foreach ($shead_data as $item) {
    if (isset($item['shead_name'])) {
        $normalized = strtolower(str_replace('', '', $item['shead_name']));
        $shead_list[] = $normalized;
    }
}

$egg_data = [];

foreach ($shead_list as $shead) {
    $hatchDate = '';
    $stmt = $mysqli->prepare("SELECT hatchDate FROM batch WHERE cullDate = '0000-00-00' AND sheadNo = ? AND client_id = ?");
    $stmt->bind_param("si", $shead, $client_id);
    $stmt->execute();
    $stmt->bind_result($hatchDate);
    $stmt->fetch();
    $stmt->close();

    if (!empty($hatchDate)) {
        $start = new DateTime($hatchDate);
        $today = new DateTime();
        $diff = $start->diff($today)->days + 1;
        $week = floor($diff / 7);
    } else {
        $week = "N/A";
    }

    $egg_data[] = ["Running_week" => $week];
}

echo json_encode([$client_id => $egg_data], JSON_PRETTY_PRINT);
?>