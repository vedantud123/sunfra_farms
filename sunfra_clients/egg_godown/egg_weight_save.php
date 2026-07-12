<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['client_id'])) {
    $_SESSION['client_id'] = 1;
}

$client_id = $_SESSION['client_id'];

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "❌ Database connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$required = ['date', 'shead_name', 'tray_1', 'tray_2', 'tray_3', 'tray_4', 'tray_5', 'tray_6', 'tray_7', 'tray_8'];
foreach ($required as $key) {
    if (!isset($data[$key])) {
        echo json_encode(["status" => "error", "message" => "❌ Missing field: $key"]);
        exit;
    }
}

$trays = [];

foreach ($data as $key => $value) {
    if (strpos($key, 'tray_') === 0) {
        $trays[] = intval($value);
    }
}

$trayCount = count($trays);

if ($trayCount === 0) {
    echo json_encode(["status" => "error", "message" => "No tray data provided"]);
    exit;
}

$total_eggs = array_sum($trays);
$avg_per_tray = $total_eggs / $trayCount;
$average = $avg_per_tray / 30;

if (isset($data['id']) && !empty($data['id'])) {
    // UPDATE existing
    $stmt = $mysqli->prepare("
        UPDATE egg_weight 
        SET date = ?, shead_name = ?, tray_1 = ?, tray_2 = ?, tray_3 = ?, tray_4 = ?, tray_5 = ?, tray_6 = ?, tray_7 = ?, tray_8 = ?, average = ?
        WHERE id = ? AND client_id = ?
    ");
    $stmt->bind_param("ssiiiiiiiidii", 
        $data['date'], $data['shead_name'],
        $trays[0], $trays[1], $trays[2], $trays[3], $trays[4], $trays[5], $trays[6], $trays[7],
        $average, $data['id'], $client_id
    );

    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "success", "message" => "✅ Record updated"]);
} else {
    // INSERT new
    $stmt = $mysqli->prepare("
        INSERT INTO egg_weight (date, shead_name, tray_1, tray_2, tray_3, tray_4, tray_5, tray_6, tray_7, tray_8, average, client_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssiiiiiiiddi", 
        $data['date'], $data['shead_name'],
        $trays[0], $trays[1], $trays[2], $trays[3], $trays[4], $trays[5], $trays[6], $trays[7],
        $average, $client_id
    );
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "success", "message" => "✅ New record inserted"]);
}
$mysqli->close();
?>