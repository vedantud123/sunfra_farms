<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$client_id = 1;

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

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

$shead_queries = ['shead 1', 'shead 2', 'shead 3', 'shead 4', 'shead 5', 'shead 6', 'shead 7', 'shead 8'];

$date_condition = date("Y-m-d");
foreach ($shead_queries as $shead) {
    $production_value = $sale_value = $closing_balance_for_day = '';
	
    if ($date_condition) {
		
        $query = "SELECT 
            SUM(CASE WHEN sale IS NULL AND type_of_eggs IN ('Good', 'Big', 'Bullet', 'Small') THEN no_of_eggs ELSE 0 END) AS production_good_eggs,
            SUM(CASE WHEN sale IS NOT NULL AND type_of_eggs IN ('Good', 'Big', 'Bullet', 'Small') THEN no_of_eggs ELSE 0 END) AS sale_good_eggs,
            SUM(CASE WHEN sale IS NULL AND type_of_eggs IN ('Damaged', 'Scrap') THEN no_of_eggs ELSE 0 END) AS production_damage_eggs,
            SUM(CASE WHEN sale IS NOT NULL AND type_of_eggs IN ('Damaged', 'Scrap') THEN no_of_eggs ELSE 0 END) AS sale_damage_eggs
            FROM egg_godown_stock
            WHERE shead_name = ? AND DATE(TIMESTAMP) = ? AND client_id = ?";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssi", $shead, $date_condition, $client_id);
        $stmt->execute();
        $stmt->bind_result($production_good, $sale_good, $production_damage, $sale_damage);
        $stmt->fetch();
        $stmt->close();
		
        $production_value = $production_good + $production_damage ;
        #$production_value = $production_good + $production_damage;
		$production = getTrayCount($production_value);
        $sale_value = $sale_good + $sale_damage;
		$return_query = "SELECT shead_name, SUM(no_of_eggs) AS return_eggs FROM egg_godown_stock WHERE remarks = 'Return' AND shead_name = ? AND DATE(TIMESTAMP) = ? AND client_id = ?";

        $return_query_stmt = $mysqli->prepare($return_query);
        $return_query_stmt->bind_param("ssi", $shead, $date_condition, $client_id);
        $return_query_stmt->execute();
        $return_query_stmt->bind_result($name, $return_eggs);
        $return_query_stmt->fetch();
        $return_query_stmt->close();
		
		$sale_value = $sale_value - $return_eggs;
		$sale = getTrayCount($sale_value);

        $yesterday_date = date("Y-m-d", strtotime("-1 day"));
        $balance_query = "SELECT closing_balance FROM egg_godown_status WHERE DATE = ? AND shead_name = ? AND client_id = ?";
        $balance_stmt = $mysqli->prepare($balance_query);
        $balance_stmt->bind_param("ssi", $yesterday_date, $shead, $client_id);
        $balance_stmt->execute();
        $balance_stmt->bind_result($opening_balance);
        $balance_stmt->fetch();
        $balance_stmt->close();
		
        $opening_balance = $opening_balance ?? 0.00; 
		$current_balance = $production + $opening_balance;
		#$current_balance = $current_balance * 30;
		$current_balance = gettotaleggs($current_balance);
		$closing_balance_for_day = $current_balance - $sale_value;

		$closing_balance_for_day = getTrayCount($closing_balance_for_day);
		
		$check_data_query = "SELECT opening_balance FROM `egg_godown_status` WHERE DATE = ? AND shead_name = ? AND client_id = ?";
		$check_data_stmt = $mysqli->prepare($check_data_query);
		$check_data_stmt->bind_param("ssi", $date_condition, $shead, $client_id);
		$check_data_stmt->execute();
		$check_data_stmt->store_result();
		$row_count = $check_data_stmt->num_rows;

		if ($row_count > 0) {
			$update_query = "UPDATE egg_godown_status 
							 SET opening_balance = ?, production = ?, sale = ?, closing_balance = ? 
							 WHERE DATE = ? AND shead_name = ? AND client_id = ?";
			$update_stmt = $mysqli->prepare($update_query);
			$update_stmt->bind_param("ddddssi", $opening_balance, $production, $sale, $closing_balance_for_day, $date_condition, $shead, $client_id);

			if ($update_stmt->execute()) {
				echo "Record updated successfully for Shead: $shead<br>";
			} else {
				echo "Error updating record: " . $update_stmt->error . "<br>";
			}
			$update_stmt->close();
		} else {
			$insert_query = "INSERT INTO egg_godown_status (date, shead_name, opening_balance, production, sale, closing_balance, client_id)
							 VALUES (?, ?, ?, ?, ?, ?, ?)";
			$insert_stmt = $mysqli->prepare($insert_query);
			$insert_stmt->bind_param("ssddddi", $date_condition, $shead, $opening_balance, $production, $sale, $closing_balance_for_day, $client_id);

			if ($insert_stmt->execute()) {
				echo "Record inserted successfully for Shead: $shead<br>";
			} else {
				echo "Error inserting record: " . $insert_stmt->error . "<br>";
			}
			$insert_stmt->close();
		}

		$check_data_stmt->close();
    }
}

$mysqli->close();
?>