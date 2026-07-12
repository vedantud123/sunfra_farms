<?php
$client_id = $_GET['client_id'] ?? 1;
$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date = $_GET['to_date'] ?? date('Y-m-d');

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
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

$shead_list = isset($shead_data['data']) ? $shead_data['data'] : $shead_data;

$days = (strtotime($to_date) - strtotime($from_date)) / (60 * 60 * 24) + 1;

$result = [];

foreach ($shead_list as $shead) {
    if (!isset($shead['shead_name'])) continue; 

    $shead_name = $conn->real_escape_string($shead['shead_name']);

    $sql = "
        SELECT shead_name, SUM(average) AS total_average 
        FROM `egg_weight`
        WHERE client_id = $client_id 
          AND DATE(`date`) BETWEEN '$from_date' AND '$to_date'
          AND shead_name = '$shead_name'
        GROUP BY shead_name
        ORDER BY shead_name
    ";

    $query_result = $conn->query($sql);

    if ($query_result && $query_result->num_rows > 0) {
        $row = $query_result->fetch_assoc();
        $per_day_avg = $days > 0 ? floatval($row['total_average']) / $days : 0;

        $result[] = [
            "shead_name" => $row['shead_name'],
            "days" => $days,
            "average_egg_weight" => number_format($per_day_avg, 2, '.', '')
        ];
    } else {
        $result[] = [
            "shead_name" => $shead_name,
            "days" => $days,
            "average_egg_weight" => number_format(0, 2, '.', '')
        ];
    }
}

echo json_encode(["data" => $result], JSON_PRETTY_PRINT);
$conn->close();
?>
