<?php
header("Content-Type: application/json");

$host = "216.172.184.173";
$user = "sunfra_farms";
$password = "sunfra_farms";
$database = "sunfra_farms";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => $conn->connect_error]));
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'today';

date_default_timezone_set("Asia/Kolkata");

$today       = date("Y-m-d");
$yesterday   = date("Y-m-d", strtotime("-1 day"));
$weekDate    = date("Y-m-d", strtotime("-7 days"));
$month       = date("m");
$year        = date("Y");

switch ($filter) {

    case "today":
        $query = "SELECT * FROM dosing_pump_water
                  WHERE DATE(`timestamp`) = '$today'
                  ORDER BY `timestamp` DESC";
        break;

    case "yesterday":
        $query = "SELECT * FROM dosing_pump_water
                  WHERE DATE(`timestamp`) = '$yesterday'
                  ORDER BY `timestamp` DESC";
        break;

    case "weekly":
        $query = "SELECT * FROM dosing_pump_water
                  WHERE DATE(`timestamp`) >= '$weekDate'
                  ORDER BY `timestamp` DESC";
        break;

    case "monthly":
        $query = "SELECT * FROM dosing_pump_water
                  WHERE MONTH(`timestamp`) = '$month'
                  AND YEAR(`timestamp`) = '$year'
                  ORDER BY `timestamp` DESC";
        break;

    case "yearly":
        $query = "SELECT * FROM dosing_pump_water
                  WHERE YEAR(`timestamp`) = '$year'
                  ORDER BY `timestamp` DESC";
        break;

    case "custom":
        $from = isset($_GET['from']) ? $_GET['from'] : null;
        $to   = isset($_GET['to']) ? $_GET['to'] : null;

        if (!$from || !$to) {
            echo json_encode([
                "status" => "error",
                "message" => "Please provide 'from' and 'to' date"
            ]);
            exit;
        }

        $query = "SELECT * FROM dosing_pump_water
                  WHERE DATE(`timestamp`) BETWEEN '$from' AND '$to'
                  ORDER BY `timestamp` DESC";
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid filter"]);
        exit;
}

$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id"         => $row["id"],
        "water_value"=> $row["water_value"],
        "timestamp"  => $row["timestamp"],
        "date"       => date("d M Y", strtotime($row["timestamp"])),
        "time"       => date("h:i A", strtotime($row["timestamp"]))
    ];
}

echo json_encode([
    "status"    => "success",
    "filter"    => $filter,
    "date_used" => $today,
    "count"     => count($data),
    "data"      => $data
]);
?>
