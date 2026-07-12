<?php
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$date = date('Y-m-d');
#$date = '2025-05-18';

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

$production = [
    'production1' => 0,
    'production2' => 0,
    'production3' => 0,
    'production4' => 0,
    'production5' => 0,
    'production6' => 0,
    'production7' => 0,
    'production8' => 0,
];

$stmt = $conn->prepare("SELECT shead_name, production FROM egg_godown_status WHERE DATE = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$prod_result = $stmt->get_result();

while ($row = $prod_result->fetch_assoc()) {
    $shead = strtolower($row['shead_name']);
    if (preg_match('/shead (\d+)/', $shead, $matches)) {
        $index = (int)$matches[1];
        if ($index >= 1 && $index <= 8) {
            $key = "production{$index}";
            $production[$key] = (int)$row['production'];
        }
    }
}
$stmt->close();

$query = $conn->prepare("SELECT SUM(no_of_eggs) AS total_eggs FROM egg_godown_stock WHERE type_of_eggs IN ('Big', 'Damaged', 'Good') AND DATE(`timestamp`) = ? AND sale IS NULL");
$query->bind_param("s", $date);
$query->execute();
$sum_result = $query->get_result();

$total_eggs = 0;
if ($row = $sum_result->fetch_assoc()) {
    $total_eggs = (int)($row['total_eggs'] ?? 0);
}
$query->close();

$total_production = getTrayCount($total_eggs);

$checkStmt = $conn->prepare("SELECT id FROM summary_report WHERE date = ?");
$checkStmt->bind_param("s", $date);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$exists = $checkResult->num_rows > 0;
$checkStmt->close();

if ($exists) {
    $update = $conn->prepare("UPDATE summary_report SET production1=?, production2=?, production3=?, production4=?,production5=?, production6=?, production7=?, production8=?, total_production=? WHERE date=?");

    $update->bind_param("iiiiiiiiis", $production['production1'], $production['production2'], $production['production3'], $production['production4'], $production['production5'], $production['production6'], $production['production7'], $production['production8'], $total_production, $date);

    if ($update->execute()) {
        echo "Data updated successfully.";
    } else {
        echo "Update error: " . $update->error;
    }
    $update->close();
} else {
    $insert = $conn->prepare("INSERT INTO summary_report (date, production1, production2, production3, production4, production5, production6, production7, production8, total_production) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $insert->bind_param("siiiiiiiii", $date, $production['production1'], $production['production2'], $production['production3'], $production['production4'], $production['production5'], $production['production6'], $production['production7'], $production['production8'], $total_production);

    if ($insert->execute()) {
        echo "Data inserted successfully.";
    } else {
        echo "Insert error: " . $insert->error;
    }
    $insert->close();
}

$scrap_stmt = $conn->prepare("SELECT SUM(no_of_eggs) FROM egg_godown_stock WHERE sale = 'Scrap' AND DATE(`timestamp`) = ?");
$scrap_stmt->bind_param("s", $date);
$scrap_stmt->execute();
$scrap_stmt->bind_result($scrap);
$scrap_stmt->fetch();
$scrap_stmt->close();

$scrap = getTrayCount($scrap);

$update_stmt = $conn->prepare("UPDATE summary_report SET total_scrap = ? WHERE date = ?");
$update_stmt->bind_param("ss", $scrap, $date);
$update_stmt->execute();
$update_stmt->close();

$damage = [
    'damage1' => 0,
    'damage2' => 0,
    'damage3' => 0,
    'damage4' => 0,
    'damage5' => 0,
    'damage6' => 0,
    'damage7' => 0,
    'damage8' => 0,
];

$query = $conn->prepare("SELECT shead_name, SUM(no_of_eggs) as total_damaged 
                         FROM egg_godown_stock 
                         WHERE type_of_eggs = 'Damaged' AND sale IS NULL AND DATE(`timestamp`) = ? 
                         GROUP BY shead_name");
$query->bind_param("s", $date);
$query->execute();
$result = $query->get_result();

while ($row = $result->fetch_assoc()) {
    if (preg_match('/Shead (\d+)/i', $row['shead_name'], $matches)) {
        $index = (int)$matches[1];
        if ($index >= 1 && $index <= 8) {
            $damage["damage$index"] = $row['total_damaged'];
        }
    }
}

$total_damage = array_sum($damage);

$check = $conn->prepare("SELECT 1 FROM summary_report WHERE date = ?");
$check->bind_param("s", $date);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    $update_damage = $conn->prepare("UPDATE summary_report SET damage1=?, damage2=?, damage3=?, damage4=?, damage5=?, damage6=?, damage7=?, damage8=?, total_damage=? WHERE date=?");

    $update_damage->bind_param("iiiiiiiiis", getTrayCount($damage['damage1']), getTrayCount($damage['damage2']), getTrayCount($damage['damage3']),getTrayCount($damage['damage4']), getTrayCount($damage['damage5']),  getTrayCount($damage['damage6']), getTrayCount($damage['damage7']), getTrayCount($damage['damage8']), getTrayCount($total_damage), $date);

    if ($update_damage->execute()) {
        echo "Damage data updated successfully.";
    } else {
        echo "Damage update error: " . $update_damage->error;
    }

    $update_damage->close();
} else {
    echo "No summary report row found for date: $date.";
}

$check->close();

$shead_queries = ['Shead 1', 'Shead 2', 'Shead 3', 'Shead 4', 'Shead 5', 'Shead 6', 'Shead 7', 'Shead 8'];
$egg_data = [];
$percentage = [];

foreach ($shead_queries as $index => $shead) {
    $total_eggs = $hatchDate = $live_birds = 0;
    $duration = $runningWeeks = 0;

    if ($date) {
        $query = "SELECT SUM(no_of_eggs) AS total_eggs FROM egg_godown_stock WHERE DATE(TIMESTAMP) = ? AND sale IS NULL AND shead_name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $date, $shead);
        $stmt->execute();
        $stmt->bind_result($total_eggs);
        $stmt->fetch();
        $stmt->close();

        $total_eggs = ($total_eggs ?? 0);
		$eggweight_average = 0;
		$eggweight_query = "SELECT average FROM `egg_weight` WHERE shead_name = ? AND DATE = ?";
		$eggweight_stmt = $conn->prepare($eggweight_query);
		$eggweight_stmt->bind_param("ss", $shead, $date);
		$eggweight_stmt->execute();
		$eggweight_stmt->bind_result($eggweight_average);
		$eggweight_stmt->fetch();
		$eggweight_stmt->close();
		
        $number = preg_replace("/[^0-9]/", "", $shead);

        $day_query = "SELECT hatchDate, live_birds FROM `batch` WHERE cullDate = '0000-00-00' AND sheadNo = ?";
        $day_stmt = $conn->prepare($day_query);
        if ($day_stmt) {
            $day_stmt->bind_param("s", $number);
            $day_stmt->execute();
            $day_stmt->bind_result($hatchDate, $live_birds);
            $day_stmt->fetch();
            $day_stmt->close();
        }

        if (!empty($hatchDate)) {
            $startDateObj = new DateTime($hatchDate);
            $diff = $startDateObj->diff(new DateTime());
            $runningDays = $diff->days + 1;
        } else {
            $runningDays = "N/A";
        }

        $average = ($live_birds > 0) ? ($total_eggs / $live_birds) * 100 : 0;
        $average = rtrim(rtrim(number_format($average, 2, '.', ''), '0'), '.');

        echo "Shead: $shead — Percentage: $average%<br>";
		
        $key = "percentage" . ($index + 1);
        $percentage[$key] = $average;
		
		$column = "percentage" . ($index + 1);
		$column_2 = "eggweight" . $number;

		$update_query = "UPDATE summary_report SET `$column` = ?, `$column_2` = ? WHERE date = ?";
		$update_percentage = $conn->prepare($update_query);
		if ($update_percentage) {
			$update_percentage->bind_param("dds", $average,$eggweight_average, $date);

			if ($update_percentage->execute()) {
				echo "✅ $column updated successfully.<br>";
			} else {
				echo "❌ Update error: " . $update_percentage->error . "<br>";
			}
			$update_percentage->close();
		}
		

    }
}

$total_avg = 0;
$count = 0;

foreach ($percentage as $value) {
    if (is_numeric($value)) {
        $total_avg += $value;
        $count++;
    }
}

$overall_average = ($count > 0) ? $total_avg / $count : 0;
$overall_average = rtrim(rtrim(number_format($overall_average, 2, '.', ''), '0'), '.');

echo "<br>🔢 Overall Average Percentage: $overall_average%";

$update_query = "UPDATE summary_report SET average_percentage = ? WHERE date = ?";
$update_percentage = $conn->prepare($update_query);
if ($update_percentage) {
    $update_percentage->bind_param("ds", $overall_average, $date);
    if ($update_percentage->execute()) {
        echo "✅ average_percentage updated successfully.<br>";
    } else {
        echo "❌ Update error: " . $update_percentage->error . "<br>";
    }
    $update_percentage->close();
}

$available_sheads = ['Shead_1', 'Shead_2', 'Shead_3', 'Shead_4', 'Shead_5', 'Shead_6', 'Shead_7', 'Shead_8', 'Chick', 'Grower'];

foreach ($available_sheads as $index => $shead) {
	$total_box_data = 0 ;
    $feeding_query = "SELECT Box_1, Box_2, Box_3, Box_4, Box_5, Box_6, Box_7, Box_8, Box_9, Box_10 
                      FROM `supervisor_feed_feeding_shead` 
                      WHERE sheadNo = ? AND DATE(timestamp) = ?";
    
    $feeding_stmt = $conn->prepare($feeding_query);
    $feeding_stmt->bind_param("ss", $shead, $date);  
    $feeding_stmt->execute();
    $feeding_stmt->bind_result($Box_1, $Box_2, $Box_3, $Box_4, $Box_5, $Box_6, $Box_7, $Box_8, $Box_9, $Box_10);

    if ($feeding_stmt->fetch()) {
        $total_box_data = $Box_1 + $Box_2 + $Box_3 + $Box_4 + $Box_5 + $Box_6 + $Box_7 + $Box_8 + $Box_9 + $Box_10;
        echo "$shead — Total Boxes: $total_box_data<br>";
    } else {
        echo "$shead — No data found<br>";
    }

    $feeding_stmt->close();
	$noOfBirds = 0;
	$mortality_query = "SELECT noOfBirds FROM `supervisor_shead_mortality` WHERE sheadNo = ? AND DATE(timestamp) = ?";
	$mortality_stmt = $conn->prepare($mortality_query);
	$mortality_stmt->bind_param("ss", $shead, $date);
	$mortality_stmt->execute();
	$mortality_stmt->bind_result($noOfBirds);
	$mortality_stmt->fetch();
	$mortality_stmt->close();

	echo $noOfBirds . "<br>";
	
	$birds_weight_query = "SELECT birds_average FROM supervisor_birds_weight WHERE sheadNo = ? AND DATE(timestamp) = ?";
	$birds_weight_stmt = $conn->prepare($birds_weight_query);
	$birds_weight_stmt->bind_param("ss", $shead, $date);
	$birds_weight_stmt->execute();
	$birds_weight_stmt->bind_result($birds_average);
	$birds_weight_stmt->fetch();
	$birds_weight_stmt->close();

	if (strpos($shead, 'Shead_') === 0) {
		$shead_number = str_replace('Shead_', '', $shead); 
	} else {
		$shead_number = $shead; 
	}
	
	$day_query = "SELECT hatchDate, live_birds FROM `batch` WHERE cullDate = '0000-00-00' AND sheadNo = ?";
    $day_stmt = $conn->prepare($day_query);
    if ($day_stmt) {
        $day_stmt->bind_param("s", $shead_number);
        $day_stmt->execute();
        $day_stmt->bind_result($hatchDate, $live_birds);
        $day_stmt->fetch();
        $day_stmt->close();
    }
	
	$feed_intake_formula = ($total_box_data/$live_birds)*1000;
	echo $feed_intake_formula;
	
	$column = "feedintake" . $shead_number;
	echo $column;
	$column_2 = "mortality" . $shead_number; 
	echo $column_2 . "<br>";
	$column_3 = "birdsweight". $shead_number;
	echo $column_3 . "<br>";

	$update_query = "UPDATE summary_report SET `$column` = ?, `$column_2` = ? WHERE date = ?";
	
	$update_feedintake = $conn->prepare($update_query);
	if ($update_feedintake) {
		$update_feedintake->bind_param("dds", $feed_intake_formula, $noOfBirds, $date);

		if ($update_feedintake->execute()) {
			echo "✅ $column and $column_2 updated successfully.<br>";
		} else {
			echo "❌ Update error: " . $update_feedintake->error . "<br>";
		}
		$update_feedintake->close();
	}
}

$for_profit_and_loss = ['Shead 1', 'Shead 2', 'Shead 3', 'Shead 4', 'Shead 5', 'Shead 6', 'Shead 7', 'Shead 8', 'Chick', 'Grower'];

foreach ($for_profit_and_loss as $index => $shead) {

    $profit_loss_query = "SELECT shead_name, profit FROM `profit_and_loss` WHERE DATE(datetime) = ? AND shead_name = ?";
    $profit_loss_stmt = $conn->prepare($profit_loss_query);
    
    if ($profit_loss_stmt) {
        $profit_loss_stmt->bind_param("ss", $date, $shead);
        $profit_loss_stmt->execute();
        $profit_loss_stmt->bind_result($shead_name, $profit);
        $profit_loss_stmt->fetch();
        $profit_loss_stmt->close();
    }

    if (strpos($shead, 'Shead ') === 0) {
        $shead = str_replace('Shead ', '', $shead); 
    }

    $column = "profitloss" . $shead;
    echo $column . "<br>"; 
	
	$update_query = "UPDATE summary_report SET `$column` = ? WHERE date = ?";
	
	$update_feedintake = $conn->prepare($update_query);
	if ($update_feedintake) {
		$update_feedintake->bind_param("ss", $profit, $date);

		if ($update_feedintake->execute()) {
			echo "✅ $column updated successfully.<br>";
		} else {
			echo "❌ Update error: " . $update_feedintake->error . "<br>";
		}
		$update_feedintake->close();
	}
}

$profit_loss_query = "SELECT SUM(profit) FROM `profit_and_loss` WHERE DATE(DATETIME) = ?";
$profit_loss_stmt = $conn->prepare($profit_loss_query);

if ($profit_loss_stmt) {
    $profit_loss_stmt->bind_param("s", $date);
    $profit_loss_stmt->execute();
    $profit_loss_stmt->bind_result($total_profit);
    $profit_loss_stmt->fetch();
    $profit_loss_stmt->close();
}

$update_total_profit_loss = "UPDATE summary_report SET total_profit_loss = ? WHERE date = ?";
$update_stmt = $conn->prepare($update_total_profit_loss);

if ($update_stmt) {
    $update_stmt->bind_param("ds", $total_profit, $date); 

    if ($update_stmt->execute()) {
        echo "✅ Total profit and loss updated successfully.<br>";
    } else {
        echo "❌ Update error: " . $update_stmt->error . "<br>";
    }

    $update_stmt->close();
} else {
    echo "❌ Prepare failed: " . $conn->error . "<br>";
}

$conn->close();
?>
