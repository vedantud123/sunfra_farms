<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]));
}

function getTrayCount($eggs) {
    $wholeTrays = floor($eggs / 30);
    $remainder = $eggs % 30;
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}

$client_id = isset($_REQUEST['client_id']) ? intval($_REQUEST['client_id']) : 0;
$from_date = isset($_REQUEST['from_date']) ? $_REQUEST['from_date'] : date('Y-m-d');
$to_date   = isset($_REQUEST['to_date']) ? $_REQUEST['to_date'] : date('Y-m-d');

if ($client_id <= 0) {
    echo json_encode(["error" => "Invalid client_id"]);
    exit;
}

$sql = "
    SELECT shead_name, SUM(no_of_eggs) as total_eggs
    FROM egg_godown_stock
    WHERE type_of_eggs = 'Damaged'
      AND sale IS NULL
      AND client_id = ?
      AND DATE(TIMESTAMP) BETWEEN ? AND ?
    GROUP BY shead_name
    ORDER BY shead_name
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("iss", $client_id, $from_date, $to_date);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $eggs = intval($row['total_eggs']);
    $data[] = [
        "shead_name" => $row['shead_name'],
        "trays"      => getTrayCount($eggs)
    ];
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>