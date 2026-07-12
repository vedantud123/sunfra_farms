<?php
session_start();

date_default_timezone_set('Asia/Kolkata');
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$client_id = $_GET['client_id'] ?? 0;

$date = date('Y-m-d');

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

$productions = [];
$damages = [];
$percentages = [];
$feed_intakes = [];
$mortalitys = [];
$egg_weights = [];
$profit_and_losses = [];

foreach ($shead_data as $index => $item) {
    if (!isset($item['shead_name'])) continue;

    $shead_name = $item['shead_name'];
    $normalized = strtolower($shead_name);

    if (preg_match('/\d+/', $shead_name, $matches)) {
        $num = $matches[0];
    } else {
        $num = $index + 1;
    }

    $productions["production{$num}"] = 0;
    $damages["damage{$num}"] = 0;
    $percentages["percentage{$num}"] = 0;
    $egg_weights["egg_weight{$num}"] = 0;

    if (strpos($normalized, "chick") !== false) {
        $suffix = "ch" . $num;
    } elseif (strpos($normalized, "grower") !== false) {
        $suffix = "gw" . $num;
    } else {
        $suffix = $num;
    }

    $feed_intakes["feed_intake{$suffix}"] = 0;
    $mortalitys["mortality{$suffix}"] = 0;
    $profit_and_losses["profit_and_loss{$suffix}"] = 0;
}

print_r($productions);
print_r($damages);
print_r($percentages);
print_r($feed_intakes);
print_r($mortalitys);
print_r($egg_weights);
print_r($profit_and_losses);


function getSheadNameFromVariable($variable, $client_id) {
    if (preg_match('/(\d+)/', $variable, $matches)) {
        $number = $matches[1]; 
        $expected_shead = null;

        // Decide type based on variable name
        if (stripos($variable, "productionch") !== false ||
            stripos($variable, "damagech") !== false ||
            stripos($variable, "percentagech") !== false ||
            stripos($variable, "feed_intakech") !== false ||
            stripos($variable, "mortalitych") !== false ||
            stripos($variable, "egg_weightch") !== false ||
            stripos($variable, "profit_and_lossch") !== false) {
            $expected_shead = "Chick " . $number;
        } 
        elseif (stripos($variable, "productiongw") !== false ||
                stripos($variable, "damagegw") !== false ||
                stripos($variable, "percentagegw") !== false ||
                stripos($variable, "feed_intakegw") !== false ||
                stripos($variable, "mortalitygw") !== false ||
                stripos($variable, "egg_weightgw") !== false ||
                stripos($variable, "profit_and_lossgw") !== false) {
            $expected_shead = "Grower " . $number;
        } 
        else {
            $expected_shead = "Shead " . $number;
        }

        $shead_url = "https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=$client_id";
        $shead_response = file_get_contents($shead_url);

        if ($shead_response === false) {
            return null; 
        }

        $shead_data = json_decode($shead_response, true);

        if (is_array($shead_data)) {
            foreach ($shead_data as $item) {
                if (isset($item['shead_name']) && $item['shead_name'] === $expected_shead) {
                    return $expected_shead; // Found match
                }
            }
        }
    }
    return null;
}


$total_production = 0;
foreach ($productions as $production => $value) {
    $production_safe = $conn->real_escape_string($production);
    $date_safe = $conn->real_escape_string($date);
    $client_id_safe = (int)$client_id;

    $check_query = "SELECT COUNT(*) AS count 
                    FROM summary_report_test 
                    WHERE summary = '$production_safe' 
                      AND date = '$date_safe' 
                      AND client_id = '$client_id_safe'";
    $check_result = $conn->query($check_query);

    if ($check_result) {
        $row = $check_result->fetch_assoc();

        $result = getSheadNameFromVariable($production, $client_id);
        //$result = strtolower(str_replace(' ', '_', $result));

        $result_safe = $conn->real_escape_string($result);
        $egg_query = "SELECT SUM(no_of_eggs) AS total_eggs 
                      FROM egg_godown_stock 
                      WHERE shead_name = '{$result_safe}' 
                        AND sale IS NULL 
                        AND DATE(`timestamp`) = '{$date_safe}' 
                        AND client_id = {$client_id_safe}";
        $res = $conn->query($egg_query);

        $total_eggs = $total_trays = 0;
        if ($res && $egg_row = $res->fetch_assoc()) {
            $total_eggs = $egg_row['total_eggs'] ?? 0;
            $total_trays = getTrayCount($total_eggs);
        }
		$total_production = $total_production + $total_eggs; 
        if ($row['count'] >= 1) {
            $update_query = "UPDATE summary_report_test 
                             SET value = '{$total_trays}' 
                             WHERE summary = '{$production_safe}' 
                               AND date = '{$date_safe}' 
                               AND client_id = '{$client_id_safe}'";
            $conn->query($update_query);
        } else {
            $insert_query = "INSERT INTO summary_report_test (client_id, summary, value, date) 
                             VALUES ('{$client_id_safe}', '{$production_safe}', '{$total_trays}', '{$date_safe}')";
            $conn->query($insert_query);
        }
    }
}

$check_total_production_query = "
    SELECT COUNT(*) AS cnt 
    FROM summary_report_test 
    WHERE summary = 'total_production' 
      AND date = '{$date}' 
      AND client_id = '{$client_id}'
";
$check_result = $conn->query($check_total_production_query);
$total_trays_production = getTrayCount($total_production);

if ($check_result && $row = $check_result->fetch_assoc()) {
    if ($row['cnt'] >= 1) {
        $update_query = "
            UPDATE summary_report_test 
            SET value = '{$total_trays_production}' 
            WHERE summary = 'total_production' 
              AND date = '{$date}' 
              AND client_id = '{$client_id}'
        ";
        $conn->query($update_query);
    } else {
        $insert_query = "
            INSERT INTO summary_report_test (client_id, summary, value, date) 
            VALUES ('{$client_id}', 'total_production', '{$total_trays_production}', '{$date}')
        ";
        $conn->query($insert_query);
    }
}
$scrap_query = "
    SELECT SUM(no_of_eggs) AS scrap_value 
    FROM egg_godown_stock 
    WHERE sale = 'Scrap' 
      AND DATE(`timestamp`) = '{$date}' 
      AND client_id = '{$client_id}'
";
$scrap_result = $conn->query($scrap_query);
$scrap_value = 0;

if ($scrap_result && ($row = $scrap_result->fetch_assoc())) {
    $scrap_value = $row['scrap_value'] ?? 0;
	$scrap_trays = getTrayCount($scrap_value);
}

$check_query = "
    SELECT COUNT(*) AS cnt 
    FROM summary_report_test 
    WHERE summary = 'scrap_production' 
      AND client_id = '{$client_id}' 
      AND date = '{$date}'
";
$check_result = $conn->query($check_query);

if ($check_result && ($row = $check_result->fetch_assoc())) {
    if ($row['cnt'] >= 1) {
        $update_query = "
            UPDATE summary_report_test 
            SET value = '{$scrap_trays}' 
            WHERE summary = 'scrap_production' 
              AND client_id = '{$client_id}' 
              AND date = '{$date}'
        ";
        $conn->query($update_query);
    } else {
        $insert_query = "
            INSERT INTO summary_report_test (client_id, summary, value, date) 
            VALUES ('{$client_id}', 'scrap_production', '{$scrap_trays}', '{$date}')
        ";
        $conn->query($insert_query);
    }
}

$total_damage = 0;
foreach ($damages as $damage => $value) {
    $damage_safe = $conn->real_escape_string($damage);
    $date_safe = $conn->real_escape_string($date);
    $client_id_safe = (int)$client_id;

    $check_query = "SELECT COUNT(*) AS count 
                    FROM summary_report_test 
                    WHERE summary = '$damage_safe' 
                      AND date = '$date_safe' 
                      AND client_id = '$client_id_safe'";
    $check_result = $conn->query($check_query);
    
    if ($check_result) {
        $row = $check_result->fetch_assoc();

        $result = getSheadNameFromVariable($damage, $client_id);
        $result_safe = $conn->real_escape_string($result);

        $egg_query = "SELECT SUM(no_of_eggs) AS total_eggs 
                      FROM egg_godown_stock 
                      WHERE shead_name = '{$result_safe}' 
                        AND type_of_eggs = 'Damaged'
                        AND sale IS NULL 
                        AND DATE(`timestamp`) = '{$date_safe}' 
                        AND client_id = {$client_id_safe}";
        $res = $conn->query($egg_query);

        $total_eggs = $total_trays = 0;
        if ($res && $egg_row = $res->fetch_assoc()) {
            $total_eggs = $egg_row['total_eggs'] ?? 0;
            $total_trays = getTrayCount($total_eggs);
        }

        $total_damage += $total_eggs; 

        if ($row['count'] >= 1) {
            $update_query = "UPDATE summary_report_test 
                             SET value = '{$total_trays}' 
                             WHERE summary = '{$damage_safe}' 
                               AND date = '{$date_safe}' 
                               AND client_id = '{$client_id_safe}'";
            $conn->query($update_query);
        } else {
            $insert_query = "INSERT INTO summary_report_test (client_id, summary, value, date) 
                             VALUES ('{$client_id_safe}', '{$damage_safe}', '{$total_trays}', '{$date_safe}')";
            $conn->query($insert_query);
        }
    }
}
$total_trays_damage = getTrayCount($total_damage);

$check_total_damaged_query = "
    SELECT COUNT(*) AS cnt 
    FROM summary_report_test 
    WHERE summary = 'total_damaged' 
      AND date = '{$date}' 
      AND client_id = '{$client_id}'
";
$check_result = $conn->query($check_total_damaged_query);

if ($check_result && ($row = $check_result->fetch_assoc())) {
    if ($row['cnt'] >= 1) {
        $update_query = "
            UPDATE summary_report_test 
            SET value = '{$total_trays_damage}' 
            WHERE summary = 'total_damaged' 
              AND date = '{$date}' 
              AND client_id = '{$client_id}'
        ";
        $conn->query($update_query);
    } else {
        $insert_query = "
            INSERT INTO summary_report_test (client_id, summary, value, date) 
            VALUES ('{$client_id}', 'total_damaged', '{$total_trays_damage}', '{$date}')
        ";
        $conn->query($insert_query);
    }
}

$percentage_count = 0;
$total_percentage = 0; 
foreach ($percentages as $percentage => $value) {
    $percentage_safe = $conn->real_escape_string($percentage);
    $date_safe = $conn->real_escape_string($date);
    $client_id_safe = (int)$client_id;

    $total_eggs = $hatchDate = $live_birds = 0;
    $duration = $runningWeeks = 0;
    $eggweight_average = 0;

    $result = getSheadNameFromVariable($percentage, $client_id);
    $result_safe = $conn->real_escape_string($result);

    $eggweight_query = "
        SELECT average 
        FROM egg_weight 
        WHERE shead_name = '{$result_safe}' 
          AND date = '{$date_safe}' 
          AND client_id = '{$client_id_safe}'
    ";
    $eggweight_result = $conn->query($eggweight_query);
    if ($eggweight_result && $row = $eggweight_result->fetch_assoc()) {
        $eggweight_average = $row['average'] ?? 0;
    }

    $total_eggs_query = "
        SELECT SUM(no_of_eggs) AS total_eggs 
        FROM egg_godown_stock 
        WHERE DATE(`timestamp`) = '{$date_safe}' 
          AND sale IS NULL 
          AND shead_name = '{$result_safe}' 
          AND client_id = '{$client_id_safe}'
    ";
    $total_eggs_result = $conn->query($total_eggs_query);
    if ($total_eggs_result && $eggs_row = $total_eggs_result->fetch_assoc()) {
        $total_eggs = $eggs_row['total_eggs'] ?? 0;
    }

    $number = preg_replace("/[^0-9]/", "", $result_safe);

    $day_query = "
        SELECT hatchDate, live_birds 
        FROM batch 
        WHERE cullDate = '0000-00-00' 
          AND sheadNo = '{$result_safe}' 
          AND client_id = '{$client_id_safe}'
    ";
    $day_result = $conn->query($day_query);
    if ($day_result && $day_row = $day_result->fetch_assoc()) {
        $hatchDate = $day_row['hatchDate'] ?? null;
        $live_birds = $day_row['live_birds'] ?? 0;
    }
	
	$average = ($live_birds > 0) ? ($total_eggs / $live_birds) * 100 : 0;
    $average = rtrim(rtrim(number_format($average, 2, '.', ''), '0'), '.');
	
	$check_query = "
		SELECT COUNT(*) AS cnt 
		FROM summary_report_test 
		WHERE summary = '{$percentage_safe}' 
		AND date = '{$date_safe}' 
		AND client_id = '{$client_id_safe}'
	";
	$check_result = $conn->query($check_query);
	$total_percentage = $total_percentage + $average;
	$percentage_count++;
	if ($check_result && ($row = $check_result->fetch_assoc())) {
		if ($row['cnt'] >= 1) {
			$update_query = "
				UPDATE summary_report_test 
				SET value = '{$average}' 
				WHERE summary = '{$percentage_safe}' 
				  AND date = '{$date_safe}' 
				  AND client_id = '{$client_id_safe}'
			";
			$conn->query($update_query);
		} else {
			$insert_query = "
				INSERT INTO summary_report_test (client_id, summary, value, date) 
				VALUES ('{$client_id_safe}', '{$percentage_safe}', '{$average}', '{$date_safe}')
			";
			$conn->query($insert_query);
		}
	}
}
$average_percentage = $total_percentage/$percentage_count;

$check_total_percentage_query = "
    SELECT COUNT(*) AS cnt 
    FROM summary_report_test 
    WHERE summary = 'average_percentage' 
      AND date = '{$date}' 
      AND client_id = '{$client_id}'
";

$check_average_result = $conn->query($check_total_percentage_query);
if ($check_average_result && ($row = $check_average_result->fetch_assoc())) {
	if ($row['cnt'] >= 1) {
        $update_query = "
            UPDATE summary_report_test 
            SET value = '{$average_percentage}' 
            WHERE summary = 'average_percentage' 
              AND date = '{$date}' 
              AND client_id = '{$client_id}'
        ";
        $conn->query($update_query);
    } else {
        $insert_query = "
            INSERT INTO summary_report_test (client_id, summary, value, date) 
            VALUES ('{$client_id}', 'average_percentage', '{$average_percentage}', '{$date}')
        ";
        $conn->query($insert_query);
    }
}

foreach ($feed_intakes as $feed_intake => $value) {
    $feed_intake_safe = $conn->real_escape_string($feed_intake);
    $date_safe = $conn->real_escape_string($date);
    $client_id_safe = (int)$client_id;
    
    $result = getSheadNameFromVariable($feed_intake, $client_id);
    $result_safe = $conn->real_escape_string($result);
    
	$particular_shead = str_replace(' ', '_', $conn->real_escape_string($result));
	
    $feed_intake_query = "
        SELECT SUM(quantity) AS total_quantity
        FROM `supervisor_feed_feeding_shead_test`
        WHERE sheadNo = '{$particular_shead}'
          AND date = '{$date_safe}'
          AND client_id = '{$client_id_safe}'
    ";
    $feed_intake_result = $conn->query($feed_intake_query);
    $total_quantity = 0;
    if ($feed_intake_result && $row = $feed_intake_result->fetch_assoc()) {
        $total_quantity = $row['total_quantity'] ?? 0;
    }
    $number = preg_replace("/[^0-9]/", "", $result_safe);
    $day_query = "
        SELECT live_birds
        FROM `batch`
        WHERE cullDate = '0000-00-00'
          AND sheadNo = '{$result_safe}'
          AND client_id = '{$client_id_safe}'
    ";
    $day_result = $conn->query($day_query);
    $live_birds = 0;
    if ($day_result && $day_row = $day_result->fetch_assoc()) {
        $live_birds = $day_row['live_birds'] ?? 0;
    }
    $feed_intake_formula = ($live_birds > 0) ? ($total_quantity / $live_birds) * 1000 : 0;

    $check_feed_intake_query = "
        SELECT COUNT(*) AS cnt 
        FROM summary_report_test 
        WHERE summary = '{$feed_intake_safe}' 
          AND date = '{$date_safe}' 
          AND client_id = '{$client_id_safe}'
    ";
    $check_result = $conn->query($check_feed_intake_query);
    $cnt = 0;
    if ($check_result && $row = $check_result->fetch_assoc()) {
        $cnt = (int)$row['cnt'];
    }

    if ($cnt >= 1) {
        $update_query = "
            UPDATE summary_report_test
            SET value = '{$feed_intake_formula}'
            WHERE summary = '{$feed_intake_safe}'
              AND date = '{$date_safe}'
              AND client_id = '{$client_id_safe}'
        ";
        $conn->query($update_query);
    } else {
        $insert_query = "
            INSERT INTO summary_report_test (summary, value, date, client_id)
            VALUES ('{$feed_intake_safe}', '{$feed_intake_formula}', '{$date_safe}', '{$client_id_safe}')
        ";
        $conn->query($insert_query);
    }
}

foreach ($mortalitys as $mortality => $value) {
    $mortality_safe = $conn->real_escape_string($mortality);
    $date_safe = $conn->real_escape_string($date);
    $client_id_safe = (int)$client_id;
    
    $result = getSheadNameFromVariable($mortality, $client_id);
    $result_safe = $conn->real_escape_string($result);
    
    $particular_shead = str_replace(' ', '_', $result_safe);

    $noOfBirds = 0;
    $take_mortality = "
        SELECT SUM(noOfBirds) AS total_birds
        FROM `supervisor_shead_mortality` 
        WHERE sheadNo = '{$particular_shead}' 
          AND DATE(timestamp) = '{$date_safe}' 
          AND client_id = '{$client_id_safe}'
    ";
    $mortality_result = $conn->query($take_mortality);
    if ($mortality_result && $row = $mortality_result->fetch_assoc()) {
        $noOfBirds = $row['total_birds'] ?? 0;
    }

    $cnt = 0;
    $check_mortality_query = "
        SELECT COUNT(*) AS cnt 
        FROM summary_report_test 
        WHERE summary = '{$mortality_safe}' 
          AND date = '{$date_safe}' 
          AND client_id = '{$client_id_safe}'
    ";
    $check_mortality_result = $conn->query($check_mortality_query);
    if ($check_mortality_result && $row = $check_mortality_result->fetch_assoc()) {
        $cnt = (int)$row['cnt'];
    }

    if ($cnt >= 1) {
        $update_query = "
            UPDATE summary_report_test
            SET value = '{$noOfBirds}'
            WHERE summary = '{$mortality_safe}'
              AND date = '{$date_safe}'
              AND client_id = '{$client_id_safe}'
        ";
        $conn->query($update_query);
    } else {
        $insert_query = "
            INSERT INTO summary_report_test (summary, value, date, client_id)
            VALUES ('{$mortality_safe}', '{$noOfBirds}', '{$date_safe}', '{$client_id_safe}')
        ";
        $conn->query($insert_query);
    }
}

foreach ($egg_weights as $egg_weight => $value) {
    $egg_weight_safe = $conn->real_escape_string($egg_weight);
    $date_safe = $conn->real_escape_string($date);
    $client_id_safe = (int)$client_id;
    
    $result = getSheadNameFromVariable($egg_weight, $client_id);
    $result_safe = $conn->real_escape_string($result);

    $egg_weight_average = 0;
    $take_egg_weight = "
        SELECT average
        FROM `egg_weight`
        WHERE shead_name = '{$result_safe}'
          AND DATE = '{$date_safe}'
          AND client_id = '{$client_id_safe}'
    ";

    $egg_weight_result = $conn->query($take_egg_weight);
    if ($egg_weight_result && $row = $egg_weight_result->fetch_assoc()) {
        $egg_weight_average = $row['average'] ?? 0;
    }
	
	$cnt = 0;
    $check_egg_weight_query = "
        SELECT COUNT(*) AS cnt 
        FROM summary_report_test 
        WHERE summary = '{$egg_weight_safe}' 
          AND date = '{$date_safe}' 
          AND client_id = '{$client_id_safe}'
    ";
    $check_egg_weight_result = $conn->query($check_egg_weight_query);
    if ($check_egg_weight_result && $row = $check_egg_weight_result->fetch_assoc()) {
        $cnt = (int)$row['cnt'];
    }

    if ($cnt >= 1) {
        $update_query = "
            UPDATE summary_report_test
            SET value = '{$egg_weight_average}'
            WHERE summary = '{$egg_weight_safe}'
              AND date = '{$date_safe}'
              AND client_id = '{$client_id_safe}'
        ";
        $conn->query($update_query);
    } else {
        $insert_query = "
            INSERT INTO summary_report_test (summary, value, date, client_id)
            VALUES ('{$egg_weight_safe}', '{$egg_weight_average}', '{$date_safe}', '{$client_id_safe}')
        ";
        $conn->query($insert_query);
    }
}

foreach ($profit_and_losses as $profit_and_loss => $value) {
    $profit_and_loss_safe = $conn->real_escape_string($profit_and_loss);
    $date_safe = $conn->real_escape_string($date);
    $client_id_safe = (int)$client_id;
    
    $result = getSheadNameFromVariable($profit_and_loss, $client_id);
    $result_safe = $conn->real_escape_string($result);
    
    $take_profit_loss = "
        SELECT profit 
        FROM profit_and_loss 
        WHERE client_id = '$client_id_safe' 
          AND shead_name = '$result_safe' 
          AND DATE(datetime) = '$date_safe'
    ";
    
    $res = $conn->query($take_profit_loss);
    if ($res && $row = $res->fetch_assoc()) {
        $profit_loss = (float)$row['profit'];
    } else {
        $profit_loss = 0;
    }
	
	$cnt = 0;
	$check_profit_and_loss = "SELECT COUNT(*) AS cnt 
        FROM summary_report_test 
        WHERE summary = '{$profit_and_loss_safe}' 
          AND date = '{$date_safe}' 
          AND client_id = '{$client_id_safe}'
    ";
	$check_profit_loss_result = $conn->query($check_profit_and_loss);
    if ($check_profit_loss_result && $row = $check_profit_loss_result->fetch_assoc()) {
        $cnt = (int)$row['cnt'];
    }

    if ($cnt >= 1) {
        $update_query = "
            UPDATE summary_report_test
            SET value = '{$profit_loss}'
            WHERE summary = '{$profit_and_loss_safe}'
              AND date = '{$date_safe}'
              AND client_id = '{$client_id_safe}'
        ";
        $conn->query($update_query);
    } else {
        $insert_query = "
            INSERT INTO summary_report_test (summary, value, date, client_id)
            VALUES ('{$profit_and_loss_safe}', '{$profit_loss}', '{$date_safe}', '{$client_id_safe}')
        ";
        $conn->query($insert_query);
    }
}

$check_profit_loss_query = "
    SELECT COUNT(*) AS count 
    FROM summary_report_test 
    WHERE summary = 'total_profit_loss' 
      AND date = '{$date}' 
      AND client_id = '{$client_id}'
";

$check_profit_loss_result = $conn->query($check_profit_loss_query);

if ($check_profit_loss_result && ($countRow = $check_profit_loss_result->fetch_assoc())) {
    
    $profit_loss_query = "
        SELECT SUM(profit) AS total_profit 
        FROM profit_and_loss 
        WHERE DATE(datetime) = '{$date}' 
          AND client_id = '{$client_id}'
    ";
    
    $profit_loss_result = $conn->query($profit_loss_query);
    $total_profit = 0;

    if ($profit_loss_result && ($profitRow = $profit_loss_result->fetch_assoc())) {
        $total_profit = $profitRow['total_profit'] ?? 0;
    }

    if ($countRow['count'] >= 1) {
        $update_query = "
            UPDATE summary_report_test 
            SET value = '{$total_profit}' 
            WHERE summary = 'total_profit_loss' 
              AND date = '{$date}' 
              AND client_id = '{$client_id}'
        ";
        $conn->query($update_query);
    } else {
        $insert_query = "
            INSERT INTO summary_report_test (client_id, summary, value, date) 
            VALUES ('{$client_id}', 'total_profit_loss', '{$total_profit}', '{$date}')
        ";
        $conn->query($insert_query);
    }
}
?>