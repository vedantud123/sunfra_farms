<?php
if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {
    echo json_encode(["error" => "Missing client_id parameter"]);
    exit();
}

$client_id = (int)$_GET['client_id'];

$box_api_url = "https://sunfra.com/farm/test2/configuration/config_shead_box_json.php?client_id=" . $client_id;
$box_json = file_get_contents($box_api_url);
$box_data = json_decode($box_json, true);

$shead_box_list = [];
if (isset($box_data[$client_id])) {
    foreach ($box_data[$client_id] as $box_entry) {
        $shead_box_list[] = $box_entry['box_numbers'];
    }
}

$select_parts = [
    "`date`",
    "sheadNo"
];

foreach ($shead_box_list as $box) {
    $select_parts[] = "MAX(CASE WHEN box_number = '{$box}' THEN quantity ELSE 0 END) AS `{$box}`";
}

$select_clause = implode(",\n    ", $select_parts);

$query = "
    SELECT 
        $select_clause
    FROM 
        supervisor_feed_feeding_shead_test
    WHERE 
        client_id = {$client_id}
        AND `date` = CURDATE()
    GROUP BY 
        `date`, sheadNo
    ORDER BY 
        sheadNo;
";

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

$result = $mysqli->query($query);
if (!$result) {
    die("Query Error: " . $mysqli->error);
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $total = 0;
    foreach ($shead_box_list as $box) {
        $total += (int)$row[$box];
    }
    $row["Total"] = $total;
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    $client_id => $data
], JSON_PRETTY_PRINT);

$mysqli->close();
?>
