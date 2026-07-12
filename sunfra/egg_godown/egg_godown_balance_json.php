<?php
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

header('Content-Type: application/json');

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

$response = [
    "date" => $selected_date,
    "data" => [],
    "totals" => []
];

$balance_query = "SELECT * FROM egg_godown_status 
                  WHERE DATE = '$selected_date' 
                  AND client_id = '$client_id'";
$balance_result = $mysqli->query($balance_query);

$totalOpeningBalance = 0;
$totalProduction = 0;
$totalSale = 0;
$totalClosingBalance = 0;

if ($balance_result && $balance_result->num_rows > 0) {
    while ($row = $balance_result->fetch_assoc()) {
        $OB = gettotaleggs($row['opening_balance']);
        $Pro = gettotaleggs($row['production']);
        $Sale = gettotaleggs($row['sale']);
        $CB = gettotaleggs($row['closing_balance']);

        $response["data"][] = [
            "date" => $row['date'],
            "shead_name" => $row['shead_name'],
            "opening_balance" => $row['opening_balance'],
            "production" => $row['production'],
            "sale" => $row['sale'],
            "closing_balance" => $row['closing_balance']
        ];

        $totalOpeningBalance += $OB;
        $totalProduction += $Pro;
        $totalSale += $Sale;
        $totalClosingBalance += $CB;
    }
}

$response["totals"] = [
    "opening_balance" => getTrayCount($totalOpeningBalance),
    "production" => getTrayCount($totalProduction),
    "sale" => getTrayCount($totalSale),
    "closing_balance" => getTrayCount($totalClosingBalance)
];

echo json_encode($response, JSON_PRETTY_PRINT);

function getTrayCount($trays) {
    $wholeTrays = floor($trays / 30);
    $remainder = $trays % 30; 
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}

function gettotaleggs($trays) {
    $wholeTrays = floor($trays); 
    $decimalPart = $trays - $wholeTrays; 
    $partialEggs = round($decimalPart * 100);

    $total_no_of_eggs = ($wholeTrays * 30) + $partialEggs;
    return $total_no_of_eggs;
}
?>
