<?php
header("Content-Type: application/json");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $mysqli->connect_error]));
}

$from_date = $_REQUEST['from_date'] ?? date('Y-m-d');
$to_date   = $_REQUEST['to_date'] ?? date('Y-m-d');
$client_id = $_REQUEST['client_id'] ?? 0;

$sale_query = "
    SELECT SUM(no_of_eggs) AS total_stock, sale 
    FROM egg_godown_stock 
    WHERE sale IS NOT NULL 
      AND client_id = ? 
      AND DATE(TIMESTAMP) BETWEEN ? AND ?
    GROUP BY sale 
    ORDER BY total_stock DESC
";

$sale_stmt = $mysqli->prepare($sale_query);
$sale_stmt->bind_param("iss", $client_id, $from_date, $to_date);
$sale_stmt->execute();
$sale_result = $sale_stmt->get_result();

$response = [];

if ($sale_result->num_rows > 0) {
    while ($row = $sale_result->fetch_assoc()) {
        $saler_name = $row['sale'];

        $return_query = "
            SELECT SUM(no_of_eggs) 
            FROM egg_godown_stock 
            WHERE remarks = 'Return' 
              AND sale = ? 
              AND client_id = ? 
              AND DATE(TIMESTAMP) BETWEEN ? AND ?
        ";
        $return_stmt = $mysqli->prepare($return_query);
        $return_stmt->bind_param("siss", $saler_name, $client_id, $from_date, $to_date);
        $return_stmt->execute();
        $return_stmt->bind_result($return_eggs);
        $return_stmt->fetch();
        $return_stmt->close();

        $total_stock = $row['total_stock'] ?? 0;
        $return_eggs = $return_eggs ?? 0;
        $current_stock = $total_stock - $return_eggs;

        $detail_query = "
            SELECT timestamp, shead_name, no_of_eggs, type_of_eggs, remarks 
            FROM egg_godown_stock 
            WHERE sale = ? 
              AND client_id = ? 
              AND DATE(TIMESTAMP) BETWEEN ? AND ?
            ORDER BY TIMESTAMP DESC, remarks DESC
        ";
        $detail_stmt = $mysqli->prepare($detail_query);
        $detail_stmt->bind_param("siss", $saler_name, $client_id, $from_date, $to_date);
        $detail_stmt->execute();
        $detail_result = $detail_stmt->get_result();

        $details = [];
        while ($detail_row = $detail_result->fetch_assoc()) {
            $details[] = [
                "date"        => date("Y-m-d", strtotime($detail_row['timestamp'])),
                "shed_no"     => $detail_row['shead_name'],
                "sale_qty"    => getTrayCount($detail_row['no_of_eggs']),
                "type_of_eggs"=> $detail_row['type_of_eggs'],
                "remarks"     => $detail_row['remarks']
            ];
        }
        $detail_stmt->close();

        $response[] = [
            "sale_name"      => $saler_name,
            "total_stock"    => getTrayCount($total_stock),
            "return_eggs"    => getTrayCount($return_eggs),
            "current_stock"  => getTrayCount($current_stock),
            "details"        => $details
        ];
    }
}

echo json_encode([
    "status" => "success",
    "from_date" => $from_date,
    "to_date"   => $to_date,
    "client_id" => $client_id,
    "data"      => $response
], JSON_PRETTY_PRINT);

function getTrayCount($trays) {
	$wholeTrays = floor($trays / 30);
    $remainder = $trays % 30; 
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}
?>