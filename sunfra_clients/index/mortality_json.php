<?php
header('Content-Type: application/json');

if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {
    echo json_encode(["error" => "Missing or invalid client_id parameter"]);
    exit();
}

$client_id = (int)$_GET['client_id'];

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : null;
$to_date   = isset($_GET['to_date']) ? $_GET['to_date'] : null;

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $mysqli->connect_error]));
}

$query = "SELECT sheadNo, SUM(noOfBirds) AS totalBirds
          FROM supervisor_shead_mortality
          WHERE client_id = ?";

$params = [$client_id];
$types  = "i";

if ($from_date && $to_date) {
    $query .= " AND date BETWEEN ? AND ?";
    $params[] = $from_date;
    $params[] = $to_date;
    $types   .= "ss";
}

$query .= " GROUP BY sheadNo ORDER BY sheadNo ASC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();

$result = $stmt->get_result();

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "sheadNo"    => $row["sheadNo"],
            "totalBirds" => (int)$row["totalBirds"]
        ];
    }
    $result->free();
}

$stmt->close();
$mysqli->close();

echo json_encode([
    "client_id" => $client_id,
    "from_date" => $from_date,
    "to_date"   => $to_date,
    "records"   => $data
], JSON_PRETTY_PRINT);
?>
