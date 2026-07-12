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

$shead_url = "https://sunfra.com/farm/sunfra/configuration/shead_number_json.php?client_id=$client_id";
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

$days_counted = max(1, (strtotime($to_date) - strtotime($from_date)) / (60 * 60 * 24) + 1);

$results = [];
$total_percentage = 0;
$percentage_count = 0;

foreach ($shead_data as $shead) {
    $shead_name = $conn->real_escape_string($shead['shead_name'] ?? '');
    $client_id_safe = (int)$client_id;
	
    $total_eggs = $hatchDate = $live_birds = 0;
    $eggweight_average = 0;

    $eggweight_query = "
        SELECT SUM(average) AS total_average 
        FROM egg_weight 
        WHERE shead_name = '{$shead_name}' 
          AND date BETWEEN '{$from_date}' AND '{$to_date}'
          AND client_id = '{$client_id_safe}'
    ";
    $eggweight_result = $conn->query($eggweight_query);
    if ($eggweight_result && $row = $eggweight_result->fetch_assoc()) {
        $eggweight_average = $row['total_average'] ?? 0;
		$eggweight_average = $eggweight_average/$days_counted;
    }

    $total_eggs_query = "
        SELECT SUM(no_of_eggs) AS total_eggs 
        FROM egg_godown_stock 
        WHERE DATE(`timestamp`) BETWEEN '{$from_date}' AND '{$to_date}' 
          AND sale IS NULL 
          AND shead_name = '{$shead_name}' 
          AND client_id = '{$client_id_safe}'
    ";
    $total_eggs_result = $conn->query($total_eggs_query);
    if ($total_eggs_result && $eggs_row = $total_eggs_result->fetch_assoc()) {
        $total_eggs = $eggs_row['total_eggs'] ?? 0;
    }

    $day_query = "
        SELECT hatchDate, live_birds 
        FROM batch 
        WHERE cullDate = '0000-00-00' 
          AND sheadNo = '{$shead_name}' 
          AND client_id = '{$client_id_safe}'
    ";
    $day_result = $conn->query($day_query);
    if ($day_result && $day_row = $day_result->fetch_assoc()) {
        $hatchDate = $day_row['hatchDate'] ?? null;
        $live_birds = $day_row['live_birds'] ?? 0;
    }

    $average = ($live_birds > 0) ? ($total_eggs / $live_birds) * 100 : 0;
	$average = $average/$days_counted;
	$average = number_format((float)$average, 2, '.', ''); 
	$eggweight_average = number_format((float)$eggweight_average, 2, '.', ''); 

	$results[] = [
		"shead_name"        => $shead_name,
		"average_percentage"=> $average
	];

    $total_percentage += $average;
    $percentage_count++;
}

$overall_average = ($percentage_count > 0) ? round($total_percentage / $percentage_count, 2) : 0;
$overall_average = ($percentage_count > 0) 
    ? number_format($total_percentage / $percentage_count, 2, '.', '') 
    : "0.00";

echo json_encode([
    "days_counted"     => (int)$days_counted,
    "sheads"           => $results,
    "overall_average"  => $overall_average
]);
?>
