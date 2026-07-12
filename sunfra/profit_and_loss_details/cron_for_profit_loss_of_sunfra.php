<?php
date_default_timezone_set('Asia/Kolkata');

$client_id = 1;

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$location_url = "https://sunfra.com/farm/sunfra/configuration/config_location_json.php?client_id=$client_id";
$location_response = file_get_contents($location_url);

$shead_url = "https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=$client_id";
$shead_response = file_get_contents($shead_url);

$location_data = json_decode($location_response, true);
$shead_data = json_decode($shead_response, true);

$location_list = [];
if (is_array($location_data)) {
    foreach ($location_data as $key => $items) {
        foreach ($items as $item) {
            if (!empty($item['location'])) {
                $location_list[] = $item['location'];
            }
        }
    }
}

if (is_array($shead_data)) {
    foreach ($shead_data as $item) {
        if (!empty($item['shead_name'])) {
            $location_list[] = $item['shead_name'];
        }
    }
}

function getTrayCount($trays) {
    $wholeTrays = floor($trays / 30);
    $remainder = $trays % 30;
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}

$final_list = array_values(array_unique($location_list));
$date = date('Y-m-d');
$timestamp = date('Y-m-d H:i:s');

foreach ($final_list as $shead) {
	$shead_name = '';
	$total_cal = $medicine_cal = $tons = $total_amount = $batch_id_result = $birds_shifting_total_cost = $litter_cost = 0;
    $converted_shead = strtolower(str_replace(' ', '_', $shead));
	$sql = "SELECT sheadNo , tons FROM `feed_shead_feeding` WHERE DATE(`TIMESTAMP`) = ? AND sheadNo = ? AND client_id = ?";
	$stmt_logs = $mysqli->prepare($sql);
	$stmt_logs->bind_param("ssi", $date, $converted_shead, $client_id);
	$stmt_logs->execute();
	$result_logs = $stmt_logs->get_result();
	if ($result_logs->num_rows >= 0) {
		while ($row_logs = $result_logs->fetch_assoc()) {
			$medicine_cal = 0;
			#$shead_name2 = $row_logs['shead_name'];
			$tons = $row_logs['tons'];
			$shead_name = strtolower(str_replace(" ", "_", $shead));
	}
			$feed_formula_sql = "SELECT feed_rawMaterial_name, quantity FROM feed_formula_detail WHERE feed_formulaType = ? AND client_id = ?";
			$stmt_formula = $mysqli->prepare($feed_formula_sql);
			$stmt_formula->bind_param("si", $shead_name, $client_id);
			$stmt_formula->execute();
			$feed_formula_result = $stmt_formula->get_result();
			$total_quantity = 0; 
			if ($feed_formula_result->num_rows > 0) {
				while ($formula_row = $feed_formula_result->fetch_assoc()) {
					$material_name = $formula_row['feed_rawMaterial_name'];
					$quantity = $formula_row['quantity'];
					$total_quantity += $quantity;
					$price_per_kg_sql = "SELECT price FROM feed_rawmaterial_price WHERE NAME = ? AND client_id = ?";
					$stmt_price_per_kg = $mysqli->prepare($price_per_kg_sql);
					$stmt_price_per_kg->bind_param("si", $material_name, $client_id);
					$stmt_price_per_kg->execute();
					$price_per_kg_result = $stmt_price_per_kg->get_result();

					if ($price_per_kg_result->num_rows > 0) {
						while ($price_row = $price_per_kg_result->fetch_assoc()) {
							$price = $price_row['price'];
							$total_cal += $quantity * $price;
						}
					}
					$stmt_price_per_kg->close();
				}
			}
			
			$water_formula_sql = "SELECT * FROM `water_medicine` WHERE DATE(TIMESTAMP) = ? AND place = ? AND client_id = ?";
			$stmt_water_formula = $mysqli->prepare($water_formula_sql);
			$stmt_water_formula->bind_param("ssi", $date, $shead, $client_id);
			$stmt_water_formula->execute();
			$water_formula_result = $stmt_water_formula->get_result();

			$total_water_quantity = 0;

			if ($water_formula_result->num_rows > 0) {
				while ($water_formula_row = $water_formula_result->fetch_assoc()) {
					$water_medicine_name = $water_formula_row['name'];
					$water_quantity = $water_formula_row['quantity'];
					$total_water_quantity += $water_quantity;
					$price_per_lit_sql = "SELECT price FROM feed_rawmaterial_price WHERE NAME = ? AND client_id = ?";
					$stmt_price_per_lit = $mysqli->prepare($price_per_lit_sql);
					$stmt_price_per_lit->bind_param("si", $water_medicine_name, $client_id);
					$stmt_price_per_lit->execute();
					$price_per_kg_result = $stmt_price_per_lit->get_result();
					if ($price_per_kg_result->num_rows > 0) {
						while ($price_row = $price_per_kg_result->fetch_assoc()) {
							$water_price = $price_row['price'];
							$medicine_cal += $water_quantity * $water_price;
						}
					}
					$stmt_price_per_lit->close();
				}
			} 
			$total_quantity = $total_quantity * $tons;
			$total_cal = $total_cal * $tons;
			$stmt_formula->close();
			$stmt_water_formula->close();
			$egg_production_sql = "SELECT SUM(no_of_eggs) AS total_eggs FROM egg_godown_stock WHERE shead_name = ? AND DATE(`TIMESTAMP`) = ? AND sale IS NULL AND client_id = ?";
			$stmt_eggs = $mysqli->prepare($egg_production_sql);
			$stmt_eggs->bind_param("ssi", $shead, $date, $client_id);
			$stmt_eggs->execute();
			$result_eggs = $stmt_eggs->get_result();
			$total_eggs = 0;
			if ($row_eggs = $result_eggs->fetch_assoc()) {
				$total_eggs = $row_eggs['total_eggs'] ?? 0;
			}
			$stmt_eggs->close();
			
			$egg_cutting_price_sql = "SELECT * FROM `egg_cutting_price` WHERE shead_name = ? AND client_id = ?";
			$stmt_eggs_cutting_price = $mysqli->prepare($egg_cutting_price_sql);
			$stmt_eggs_cutting_price->bind_param("si", $shead, $client_id);
			$stmt_eggs_cutting_price->execute();
			$result_eggs = $stmt_eggs_cutting_price->get_result();
			$cutting_price = $medicine_cost = $other_cost = 0;

			if ($row_eggs = $result_eggs->fetch_assoc()) {
				$cutting_price = $row_eggs['cutting_price'] ?? 0;
				#$medicine_cost = $row_eggs['medicine_cost'] ?? 0;
				$other_cost = $row_eggs['other_cost'] ?? 0;
			}

			$stmt_eggs_cutting_price->close();
			
			$attendance_query = "SELECT * FROM `attendance` WHERE `date` = ? AND `working_place` = ? AND client_id = ?";
			$attendance_stmt = $mysqli->prepare($attendance_query);
			if (!$attendance_stmt) {
				die("Prepare failed: " . $mysqli->error);
			}
			$attendance_stmt->bind_param("ssi", $date, $shead, $client_id);
			$attendance_stmt->execute();
			$attendance_result = $attendance_stmt->get_result();

			while ($row = $attendance_result->fetch_assoc()) {
				$name = $row['name'];

				$supervisor_query = "SELECT * FROM `farm_supervisor` WHERE `name` = ? AND client_id = ?";
				$supervisor_stmt = $mysqli->prepare($supervisor_query);
				$supervisor_stmt->bind_param("si", $name, $client_id);
				$supervisor_stmt->execute();
				$supervisor_result = $supervisor_stmt->get_result();
				$salary = 0;
				if ($supervisor_result->num_rows == 0) {
					$salary_query = "SELECT salary FROM `labour_salaries` WHERE `name` = ? AND client_id = ?";
					$salary_stmt = $mysqli->prepare($salary_query);
					$salary_stmt->bind_param("si", $name, $client_id);
					$salary_stmt->execute();
					$salary_result = $salary_stmt->get_result();

					if ($salary_row = $salary_result->fetch_assoc()) {
						$salary = $salary_row['salary']; 
						$total_amount += $salary;
					}
					$salary_stmt->close();
				} else {
					$count = 0;
					$check_number_of_locations = "SELECT COUNT(*) as total FROM `task_master` WHERE `assigned_date` = ? AND `person_name` = ? AND client_id = ?";
					$check_stmt = $mysqli->prepare($check_number_of_locations);
					$check_stmt->bind_param("ssi", $date, $name, $client_id);
					$check_stmt->execute();
					$check_result = $check_stmt->get_result();

					if ($check_row = $check_result->fetch_assoc()) {
						$count = $check_row['total'];
					}
					$check_stmt->close();

					$salary_query = "SELECT salary FROM `labour_salaries` WHERE `name` = ? AND client_id = ?";
					$salary_stmt = $mysqli->prepare($salary_query);
					$salary_stmt->bind_param("si", $name, $client_id);
					$salary_stmt->execute();
					$salary_result = $salary_stmt->get_result();

					if ($salary_row = $salary_result->fetch_assoc()) {
						$salary = ($count > 0) ? ($salary_row['salary'] / $count) : 0;
						$total_amount += $salary;
					}
					$salary_stmt->close();
				}

				$supervisor_stmt->close();
			}

			$attendance_stmt->close();
			
			$sql = "SELECT SUM(litter_cost) AS total_litter_cost 
					FROM litter_costing 
					WHERE shead_name = ? AND client_id = ? AND DATE = ?";

			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param("sis", $shead, $client_id, $date); 

			$stmt->execute();

			$stmt->bind_result($total_litter_cost);
			$stmt->fetch();

			if($total_litter_cost !== null){
				$litter_cost = $total_litter_cost;
			}

			$stmt->close();
			
			$birds_shifting_query = "SELECT SUM(food_cost + labour_cost) AS total_cost 
									 FROM `sunfra_farms`.`birds_shifting` 
									 WHERE `date` = '$date' 
									 AND `shead_name` = '$shead'";

			$birds_shifting_result = $mysqli->query($birds_shifting_query);

			if ($birds_shifting_result) {
				$birds_shifting_row = $birds_shifting_result->fetch_assoc();
				$birds_shifting_total_cost = $birds_shifting_row['total_cost'] ? $birds_shifting_row['total_cost'] : 0;
				echo "Total Cost for $shead on $birds_shifting_date: " . $birds_shifting_total_cost;
			}
			
			$sql = "SELECT batch_id 
					FROM batch 
					WHERE sheadNo = ? 
					  AND cullDate = '0000-00-00'";
			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param("s", $shead);
			$stmt->execute();
			$stmt->bind_result($batch_id);

			$batch_id_result = 0;
			if ($stmt->fetch()) {
				$batch_id_result = $batch_id;
			}
			$stmt->close();

			$vaccine_query = "SELECT SUM(vaccine_cost) AS vaccine_cost, 
									 SUM(labour_cost) AS labour_cost 
							  FROM vaccination_costing 
							  WHERE shead_number = ? 
								AND DATE(timestamp) = ? 
								AND client_id = ?";
			$vaccine_stmt = $mysqli->prepare($vaccine_query);
			$vaccine_stmt->bind_param("ssi", $shead, $date, $client_id);
			$vaccine_stmt->execute();
			$vaccine_result = $vaccine_stmt->get_result();

			$vaccine_cost = 0;
			$labour_cost = 0;
			if ($vaccine_row = $vaccine_result->fetch_assoc()) {
				$vaccine_cost = $vaccine_row['vaccine_cost'] ?? 0;
				$labour_cost = $vaccine_row['labour_cost'] ?? 0;
			}
			$other_cost = $vaccine_cost + $labour_cost + $birds_shifting_total_cost;

			$feed_medicine_other_cal = $total_cal + $medicine_cal + $other_cost + $total_amount;
			$total_eggs_price = $total_eggs * $cutting_price;
			$profit = $total_eggs_price - $feed_medicine_other_cal;
			$profit = $profit + $litter_cost;
			$sql = "SELECT id 
					FROM profit_and_loss 
					WHERE shead_name = ? 
					  AND DATE(DATETIME) = ? 
					  AND client_id = ?";
			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param("ssi", $shead, $date, $client_id);
			$stmt->execute();
			$result = $stmt->get_result();

			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$id = $row['id'];

				$query = "UPDATE profit_and_loss 
						  SET shead_name = ?, 
							  feed_used = ?, 
							  feed_cost = ?, 
							  medicine = ?, 
							  other_cost = ?, 
							  labour_cost = ?, 
							  total = ?, 
							  production = ?, 
							  egg_cost = ?, 
							  total_egg_revenue = ?, 
							  profit = ?, 
							  batch_id = ? 
						  WHERE id = ? 
							AND client_id = ?";
				$stmt = $mysqli->prepare($query);

				$production = getTrayCount($total_eggs);

				$stmt->bind_param(
					"siiiiddddisiii",
					$shead,
					$total_quantity,
					$total_cal,
					$medicine_cal,
					$other_cost,
					$total_amount,
					$feed_medicine_other_cal,
					$production,         
					$cutting_price,
					$total_eggs_price,
					$profit,
					$batch_id_result,
					$id,
					$client_id
				);

			} else {
				$query = "INSERT INTO profit_and_loss 
						  (shead_name, feed_used, feed_cost, medicine, other_cost, 
						   labour_cost, total, production, egg_cost, total_egg_revenue, 
						   profit, datetime, client_id, batch_id) 
						  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$stmt = $mysqli->prepare($query);

				$production = getTrayCount($total_eggs);

				$stmt->bind_param(
					"siiiiddddiisii",
					$shead,
					$total_quantity,
					$total_cal,
					$medicine_cal,
					$other_cost,
					$total_amount,
					$feed_medicine_other_cal,
					$production,          // string
					$cutting_price,
					$total_eggs_price,
					$profit,
					$timestamp,           // datetime
					$client_id,
					$batch_id_result
				);
			}


			if ($stmt->execute()) {
				$stmt->close();
			} else {
				echo "Error: " . $stmt->error;
			}
		}
}
?>