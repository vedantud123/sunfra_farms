<?php
header('Content-Type: application/json');
date_default_timezone_set("Asia/Kolkata");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "message" => $mysqli->connect_error]);
    exit;
}

$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if (empty($from_date) || empty($to_date) || $client_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Missing required parameters"]);
    exit;
}

$api_url = "https://sunfra.com/farm/test2/profit_and_loss_details/profit_loss_process.php?client_id={$client_id}";
$response = file_get_contents($api_url);

function format_numbers(&$array) {
    foreach ($array as $key => $value) {
        if (is_numeric($value)) {
            $array[$key] = number_format((float)$value, 2, '.', '');
        }
    }
}

$query = "SELECT shead_name,
                 SUM(feed_used) AS feed_used,
                 SUM(feed_cost) AS feed_cost,
                 SUM(medicine) AS medicine,
                 SUM(other_cost) AS other_cost,
                 SUM(labour_cost) AS labour_cost,
                 SUM(total) AS total,
                 SUM(production) AS production,
                 egg_cost,
                 SUM(total_egg_revenue) AS total_egg_revenue,
                 SUM(profit) AS profit
          FROM profit_and_loss  
          WHERE DATE(datetime) BETWEEN ? AND ? 
            AND client_id = ? 
          GROUP BY shead_name";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ssi", $from_date, $to_date, $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    format_numbers($row); // Format each row
    $data[] = $row;
}
$stmt->close();

$total_query = "SELECT SUM(feed_used) AS feed_used,
                       SUM(feed_cost) AS feed_cost,
                       SUM(medicine) AS medicine,
                       SUM(other_cost) AS other_cost,
                       SUM(labour_cost) AS labour_cost,
                       SUM(total) AS total,
                       SUM(production) AS production,
                       egg_cost,
                       SUM(total_egg_revenue) AS total_egg_revenue,
                       SUM(profit) AS profit
                FROM profit_and_loss  
                WHERE DATE(datetime) BETWEEN ? AND ? 
                  AND client_id = ?";

$total_stmt = $mysqli->prepare($total_query);
$total_stmt->bind_param("ssi", $from_date, $to_date, $client_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_data = $total_result->fetch_assoc();
$total_stmt->close();

format_numbers($total_data); 

$mysqli->close();

echo json_encode([
    "status" => "success",
    "data" => $data,
    "total" => $total_data
], JSON_PRETTY_PRINT);
?>
