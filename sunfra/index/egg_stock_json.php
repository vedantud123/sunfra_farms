<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date   = $_GET['to_date'] ?? date('Y-m-d');
$client_id = $_GET['client_id'] ?? 1;

function getTrayCount($eggs) {
    $wholeTrays = floor($eggs / 30);
    $remainder = $eggs % 30;
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}

$stmt = $mysqli->prepare("SELECT shead_name, type_of_eggs, SUM(no_of_eggs) AS total_eggs
                          FROM egg_godown_stock
                          WHERE DATE(`timestamp`) BETWEEN ? AND ?
                            AND client_id = ?
                            AND sale IS NULL
                          GROUP BY shead_name, type_of_eggs
                          ORDER BY shead_name");
$stmt->bind_param("ssi", $from_date, $to_date, $client_id);
$stmt->execute();
$result = $stmt->get_result();

$dataMap = [];

while ($row = $result->fetch_assoc()) {
    $shead = $row['shead_name'];

    if (!isset($dataMap[$shead])) {
        $dataMap[$shead] = [
            "shead_name" => $shead,
            "from_date" => $from_date,
            "to_date" => $to_date,
            "Good" => 0,
            "Small" => 0,
            "Big" => 0,
            "Damaged" => 0
        ];
    }

    $eggType = ucfirst(strtolower($row['type_of_eggs']));
    if (isset($dataMap[$shead][$eggType])) {
        $dataMap[$shead][$eggType] += (int)$row['total_eggs'];
    }
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