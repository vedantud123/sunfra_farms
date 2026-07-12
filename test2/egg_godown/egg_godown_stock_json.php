<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d');
$client_id = $_GET['client_id'] ?? 1;

function getTrayCount($eggs) {
    $wholeTrays = floor($eggs / 30);
    $remainder = $eggs % 30;
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}

$stmt = $mysqli->prepare("SELECT DATE(`timestamp`) AS entry_date, shead_name, type_of_eggs, no_of_eggs
                          FROM egg_godown_stock
                          WHERE DATE(`timestamp`) = ? AND client_id = ? AND sale IS NULL
                          ORDER BY shead_name");
$stmt->bind_param("si", $date, $client_id);
$stmt->execute();
$result = $stmt->get_result();

$dataMap = [];

while ($row = $result->fetch_assoc()) {
    $key = $row['entry_date'] . '|' . $row['shead_name'];

    if (!isset($dataMap[$key])) {
        $dataMap[$key] = [
            "date" => $row['entry_date'],
            "shead_name" => $row['shead_name'],
            "Good" => 0,
            "Small" => 0,
            "Big" => 0,
            "Damaged" => 0
        ];
    }

    $eggType = ucfirst(strtolower($row['type_of_eggs'])); 
    if (!isset($dataMap[$key][$eggType])) {
        $dataMap[$key][$eggType] = 0;
    }

    $dataMap[$key][$eggType] += (int)$row['no_of_eggs'];
}

foreach ($dataMap as &$entry) {
    $entry["Good"] = getTrayCount($entry["Good"]);
    $entry["Small"] = getTrayCount($entry["Small"]);
    $entry["Big"] = getTrayCount($entry["Big"]);
    $entry["Damaged"] = getTrayCount($entry["Damaged"]);
}

$response = array_values($dataMap);

echo json_encode($response, JSON_PRETTY_PRINT);
?>
