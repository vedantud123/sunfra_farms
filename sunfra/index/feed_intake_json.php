<?php
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d');
$to_date   = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

if (!$client_id) {
    echo json_encode(["error" => "Missing client_id"]);
    exit;
}

$shead_url = "https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=$client_id";
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

$output = [];

foreach ($shead_data as $feed_intake) {
    $shead_name_api = $feed_intake['shead_name'];            
    $shead_name_db  = str_replace(" ", "_", $shead_name_api);

    $query = "
        SELECT SUM(quantity) AS total_quantity
        FROM `supervisor_feed_feeding_shead_test`
        WHERE client_id = {$client_id}
          AND date BETWEEN '{$from_date}' AND '{$to_date}'
          AND sheadNo = '{$shead_name_db}'
    ";
    $result = $conn->query($query);
    $total_quantity = ($result && $row = $result->fetch_assoc()) ? (float)$row['total_quantity'] : 0;

    $day_query = "
        SELECT live_birds
        FROM batch
        WHERE cullDate = '0000-00-00'
          AND sheadNo = '{$shead_name_api}'
          AND client_id = {$client_id}
        LIMIT 1
    ";
    $day_result = $conn->query($day_query);
    $live_birds = ($day_result && $day_row = $day_result->fetch_assoc()) ? (int)$day_row['live_birds'] : 0;

    $days_counted = max(1, (strtotime($to_date) - strtotime($from_date)) / (60 * 60 * 24) + 1); // Avoid division by zero
    $average_feed_intake = ($live_birds > 0) ? ($total_quantity / $live_birds) * 1000 / $days_counted : 0;
	$average_feed_intake = number_format((float)$average_feed_intake, 2, '.', '');

    $output[] = [
        "shead_name" => $shead_name_api,  
        "from_date" => $from_date,
        "to_date" => $to_date,
        "days" => (int)$days_counted,
        "average_feed_intake" => $average_feed_intake
    ];
}

echo json_encode(["data" => $output], JSON_PRETTY_PRINT);
?>
