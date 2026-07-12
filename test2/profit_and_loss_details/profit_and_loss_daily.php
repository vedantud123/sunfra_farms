<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$client_id = $_SESSION['client_id'] ?? 0;

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

$shead_queries = ['Shead 1', 'Shead 2', 'Shead 3', 'Shead 4', 'Shead 5', 'Shead 6', 'Shead 7', 'Shead 8','Chick', 'Grower','Egg_Godown', 'Feed_Godown','Gate_Manager', 'Others' ];

$from_date =  $_POST['from_date'] ?? date('Y-m-d');
$to_date =  $_POST['to_date'] ?? date('Y-m-d');


$date = date('Y-m-d');
$timestamp = date('Y-m-d H:i:s');
foreach ($shead_queries as $shead) {
	$shead_name = '';
	$total_cal = $medicine_cal = $tons = 0;
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

			if (stripos($shead_name, "chick") !== false) {
				$shead_name = "chick";
			} elseif (stripos($shead_name, "grower") !== false) {
				$shead_name = "grower";
			}
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
					$price_per_lit_sql = "SELECT price FROM water_medicine_and_sanitization_price WHERE medicine_name = ? AND client_id = ?";
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
			
			$total_amount = 0;

			$attendance_query = "SELECT * FROM `attendance` WHERE `date` = ? AND `working_place` = ? AND client_id = ?";
			$attendance_stmt = $mysqli->prepare($attendance_query);
			if (!$attendance_stmt) {
				die("Prepare failed: " . $mysqli->error);
			}
			$attendance_stmt->bind_param("ssi", $date, $converted_shead, $client_id);
			$attendance_stmt->execute();
			$attendance_result = $attendance_stmt->get_result();

			while ($row = $attendance_result->fetch_assoc()) {
				$name = $row['name'];

				$supervisor_query = "SELECT * FROM `farm_supervisor` WHERE `name` = ? AND client_id = ?";
				$supervisor_stmt = $mysqli->prepare($supervisor_query);
				$supervisor_stmt->bind_param("si", $name, $client_id);
				$supervisor_stmt->execute();
				$supervisor_result = $supervisor_stmt->get_result();

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
			
			$vaccine_query = "SELECT SUM(vaccine_cost) AS vaccine_cost, SUM(labour_cost) AS labour_cost FROM `vaccination_costing` WHERE shead_number = ? AND DATE(timestamp) = ? AND client_id = ?";
			$vaccine_stmt = $mysqli->prepare($vaccine_query);
			$vaccine_stmt->bind_param("ssi", $shead, $date, $client_id);
			$vaccine_stmt->execute();
			$vaccine_result = $vaccine_stmt->get_result();

			if ($vaccine_row = $vaccine_result->fetch_assoc()) {
				$vaccine_cost = $vaccine_row['vaccine_cost'] ?? 0;
				$labour_cost = $vaccine_row['labour_cost'] ?? 0;
				$other_cost = $vaccine_cost + $labour_cost ;
			}

			$feed_medicine_other_cal = $total_cal + $medicine_cal + $other_cost + $total_amount;
			$total_eggs_price = $total_eggs * $cutting_price;
			$profit = $total_eggs_price - $feed_medicine_other_cal ;
			
			$sql = "SELECT id FROM profit_and_loss WHERE shead_name = ? AND DATE(DATETIME) = ? AND client_id = ?";
			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param("ssi", $shead, $date, $client_id);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$id = $row['id'];
				$query = "UPDATE profit_and_loss SET shead_name = ?, feed_used = ?, feed_cost = ?, medicine = ?, other_cost = ?, labour_cost = ?, total = ?, production = ?, egg_cost = ?, total_egg_revenue = ?, profit = ? WHERE id = ? AND client_id = ?";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("siiiiddddiiii", $shead, $total_quantity, $total_cal, $medicine_cal, $other_cost, $total_amount, $feed_medicine_other_cal, getTrayCount($total_eggs), $cutting_price, $total_eggs_price, $profit, $id, $client_id);
			} else {
				$query = "INSERT INTO profit_and_loss (shead_name, feed_used, feed_cost, medicine, other_cost, labour_cost, total, production, egg_cost, total_egg_revenue, profit, datetime, client_id) 
						  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("siiiiddddiisi", $shead, $total_quantity, $total_cal, $medicine_cal, $other_cost, $total_amount, $feed_medicine_other_cal, getTrayCount($total_eggs), $cutting_price, $total_eggs_price, $profit, $timestamp, $client_id);
			}

			if ($stmt->execute()) {
				$stmt->close();
			} else {
				echo "Error: " . $stmt->error;
			}
		}
}
$stmt_logs->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit & Loss Management</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Reset box-sizing */
*, *::before, *::after {
  box-sizing: border-box !important;
}

/* Reset html and body */
html, body {
  margin: 0 !important;
  padding: 20px !important;
  font-family: Arial, sans-serif !important;
  background-color: #f4f4f9 !important;
  color: #333 !important;
  text-align: center !important;
  line-height: 1.5 !important;
}

/* Headings */
h1 {
  font-weight: 700 !important;
  font-size: 1.75rem !important;
  color: #333 !important;
  margin-bottom: 20px !important;
  text-align: center !important;
  line-height: 1.2 !important;
}

/* Buttons and links */
button, .add-data a {
  background-color: #1E293B !important;
  color: white !important;
  padding: 10px 15px !important;
  border-radius: 5px !important;
  border: none !important;
  cursor: pointer !important;
  font-weight: 600 !important;
  font-size: 14px !important;
  display: inline-block !important;
  user-select: none !important;
  white-space: nowrap !important;
  text-decoration: none !important;
  transition: background-color 0.3s ease !important;
}

button:hover, .add-data a:hover {
  background-color: #0056b3 !important;
}

.button-container {
  margin-bottom: 20px !important;
  text-align: center !important;
}

form {
  max-width: 500px !important;
  margin: 0 auto 40px !important;
  padding: 20px !important;
  background-color: white !important;
  border-radius: 10px !important;
  box-shadow: 0 0 10px rgba(0,0,0,0.1) !important;
  font-family: Arial, sans-serif !important;
  text-align: left !important;
}

form p {
  display: flex !important;
  justify-content: space-between !important;
  align-items: center !important;
  margin: 10px 0 !important;
}

label {
  flex: 1 1 40% !important;
  font-weight: 700 !important;
  text-align: left !important;
  padding-right: 10px !important;
  white-space: nowrap !important;
}

input, select {
  flex: 2 1 55% !important;
  padding: 8px !important;
  font-size: 14px !important;
  border: 1px solid #ccc !important;
  border-radius: 5px !important;
  background-color: white !important;
  box-sizing: border-box !important;
  color: #000 !important;
}

/* Submit button */
button[type="submit"] {
  width: 100% !important;
  font-weight: 700 !important;
  border: none !important;
  border-radius: 5px !important;
  cursor: pointer !important;
  padding: 12px !important;
  background-color: #007bff !important;
  transition: background-color 0.3s ease !important;
}

button[type="submit"]:hover {
  background-color: #0056b3 !important;
}

/* Table styling */
table {
  width: 80% !important;
  max-width: 800px !important;
  margin: 30px auto !important;
  border-collapse: collapse !important;
  background-color: white !important;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
  border-radius: 8px !important;
  font-size: 14px !important;
  text-align: center !important;
  overflow: hidden !important;
}

th, td {
  padding: 10px !important;
  border: 1px solid #ddd !important;
  vertical-align: middle !important;
}

th {
  background-color: #007bff !important;
  color: white !important;
  font-weight: 700 !important;
  white-space: nowrap !important;
}

td:first-child, th:first-child {
  background-color: #28a745 !important;
  color: white !important;
  font-weight: 700 !important;
  white-space: nowrap !important;
}

tr:nth-child(even) {
  background-color: #f8f9fa !important;
}

tr:hover {
  background-color: #f1f1f1 !important;
}

td a {
  color: #007bff !important;
  font-weight: 700 !important;
  text-decoration: none !important;
}

td a:hover {
  text-decoration: underline !important;
}.sidebar {
			position: fixed;
			top: 0;
			left: 0;
			width: 70px;
			height: 100vh;
			background-color: #016795;
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			padding-top: 10px;
			overflow-y: auto;
			transition: width 0.3s ease;
			z-index: 1000;
			box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
		  }
		  .sidebar.expanded {
			width: 250px;
		  }
		  .sidebar a {
			color: white;
			text-decoration: none;
			width: 100%;
			padding: 14px 20px;
			display: flex;
			align-items: center;
			font-size: 15px;
			transition: background-color 0.2s ease-in-out;
			white-space: nowrap;
		  }
		  .sidebar a:hover {
			background-color: #0194c7;
		  }
		  .sidebar i {
			font-size: 16px;
			min-width: 30px;
			text-align: center;
		  }
		  .label {
			margin-left: 10px;
			white-space: nowrap;
			display: none;
		  }
		  .sidebar.expanded .label {
			display: inline;
		  }
		  .toggle-btn {
			width: 100%;
			cursor: pointer;
			padding: 10px 20px;
			background: none;
			border: none;
			color: white;
			font-size: 18px;
			text-align: left;
			outline: none;
			user-select: none;
			display: flex;
			align-items: center;
		  }
		  .toggle-btn i {
			margin-right: 10px;
		  }
		  .attendance-submenu {
			display: none;
			flex-direction: column;
			background: #1e293b;
			width: 100%;
			padding-left: 40px;
			transition: all 0.3s ease;
		  }
		  .attendance-submenu button {
			background: none;
			border: none;
			color: white;
			text-align: left;
			padding: 10px 20px;
			font-size: 14px;
			cursor: pointer;
			transition: background-color 0.2s ease;
		  }
		  .attendance-submenu button:hover {
			background-color: #2563EB;
		  }.main-content {
			  margin-left: 250px;
			  transition: margin-left 0.3s;
			}

			.main-content.collapsed {
			  margin-left: 50px;
			}.content {
			  margin-left: 70px;
			  transition: margin-left 0.3s ease;
			}

			.sidebar.expanded ~ .content {
			  margin-left: 250px;
			}.content.expanded {
			  margin-left: 250px;
			}

    </style>
</head>
<body>
<div>
<div class="sidebar" id="sidebar">
  <button class="toggle-btn" id="sidebarToggleBtn" aria-label="Toggle menu" title="Toggle menu">
    <i class="fas fa-bars"></i>
    <span class="label">Menu</span>
  </button>
  <a href="https://sunfra.com/farm/test2/index.php"><i class="fas fa-home"></i><span class="label">My Dashboard</span></a>
  <a href="https://sunfra.com/farm/test2/batch/batch_json_to_web.php"><i class="fas fa-globe"></i><span class="label">Batch</span></a>
  <a href="https://sunfra.com/farm/test2/weighbridge/weighbridge_json_to_web.php"><i class="fas fa-truck"></i><span class="label">WeighBridge</span></a>
  <a href="https://sunfra.com/farm/test2/tractor_production_mortality/tractor_production_mortality_json_to_web.php"><i class="fas fa-tractor"></i><span class="label">Tractor Production</span></a>

  <div class="attendance-dropdown">
    <a onclick="toggleAttendance()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-user-check"></i>
      <span class="label">Attendance <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="attendanceSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/labour_master_json_to_web.php'">👷‍♂️ Labour Details</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/labour_attendance_json_to_web.php'">📅 Labour Attendance</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/assigned_master_json_to_web.php'">📝 Assigned Master</button>
    </div>
  </div>
	<div class="attendance-dropdown">
	  <a onclick="toggleShed()" class="attendance-toggle">
		<i class="fas fa-users-cog"></i>
		<span class="label">Shead Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
	  </a>
	  <div class="attendance-submenu" id="shedSubmenu">
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_feed_feeding_shead_json_to_web.php'">🥣 Feed Feeding Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_shead_mortality_json_to_web.php'">💀 Shead Mortality</button>
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_shead_production_json_to_web.php'">📦 Production Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_birds_weight_json_to_web.php'">🐥 Birds Weight</button>
	  </div>
	</div>
  <div class="attendance-dropdown">
    <a onclick="toggleFeedPlant()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-industry"></i>
      <span class="label">Feed Plant Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="feedPlantSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/feed_formula/feed_formula_json_to_web.php'">⚙️ Feed Formula</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/feedrawmaterial/feed_raw_material_json_to_web.php'">📥 Feed Raw Material</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/feedrawmaterial/feed_to_shead_json_to_web.php'">🚚 Feed Material To Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/feedrawmaterial/water_medicine_json_to_web.php'">🧪 Water Medicine</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleEggGodown()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-warehouse"></i>
      <span class="label">Egg Godown <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="eggGodownSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_godown_stock_json_to_web.php'">🥚 Egg Production From Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_godown_sale_json_to_web.php'">💰 Eggs for Sale</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_weight.php'">⚖️ Egg Weight</button>
    	<button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_damaged_json_to_web.php'">⚖️ Weekly Egg Damages</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleProfitLoss()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-chart-line"></i>
      <span class="label">Profit & Loss <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="profitLossSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/material_cost_json_to_web.php'">Feed Material Price</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/egg_cutting_json_to_web.php'">Egg Price Per Piece</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/profit_and_loss_daily.php'">Profit & Loss Summary</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/summary_report_json_to_web.php'">Summary Report</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/vaccination_json_to_web.php'">Vaccination</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/labour_salary_json_to_web.php'">Labour Salary</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/test2/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <a href="https://sunfra.com/farm/test2/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/test2/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/test2/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
    <div class="container">
        <h1>Profit & Loss Management</h1>
		<form method="POST">
			<p>
				<label for="from_date">From :</label>
				<input type="date" name="from_date" id="from_date" value="<?= htmlspecialchars($from_date) ?>">
			</p>
			<p>
				<label for="to_date">To :</label>
				<input type="date" name="to_date" id="to_date" value="<?= htmlspecialchars($to_date) ?>">
			</p>
				<button type="submit">Submit</button>
		</form>

        <?php
			$query = "SELECT shead_name,SUM(feed_used) AS feed_used , SUM(feed_cost) AS feed_cost ,SUM(medicine) AS medicine , SUM(other_cost) AS other_cost , SUM(labour_cost) AS labour_cost, SUM(total) AS total, SUM(production) AS production ,egg_cost, SUM(total_egg_revenue) AS total_egg_revenue , SUM(profit) AS profit 
					FROM profit_and_loss  WHERE DATE(DATETIME) BETWEEN ? AND ? AND client_id = ? GROUP BY shead_name
					ORDER BY FIELD (shead_name,'Shead 1', 'Shead 2', 'Shead 3', 'Shead 4', 'Shead 5', 'Shead 6','Shead 7','Shead 8','Chick' ,'Grower','Egg_Godown', 'Feed_Godown','Gate_Manager','Others')";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ssi", $from_date, $to_date, $client_id); 
			$stmt->execute();
			$result = $stmt->get_result(); 

			echo '<table>
				  <tr>
					  <th>Shead Name</th>
					  <th>Feed Used</th>
					  <th>Feed Cost</th>
					  <th>Medicine</th>
					  <th>Other Cost</th>
					  <th>Labour Cost</th>
					  <th>Total</th>
					  <th>Production</th>
					  <th>Egg Cost</th>
					  <th>Total Egg Revenue</th>
					  <th>Profit</th>
				  </tr>';

			if ($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					echo '<tr>
							  <td>' . htmlspecialchars($row["shead_name"]) . '</td>
							  <td>' . htmlspecialchars($row["feed_used"]) . '</td>
							  <td>' . htmlspecialchars($row["feed_cost"]) . '</td>
							  <td>' . htmlspecialchars($row["medicine"]) . '</td>
							  <td>' . htmlspecialchars($row["other_cost"]) . '</td>
							  <td>' . htmlspecialchars($row["labour_cost"]) . '</td>
							  <td>' . htmlspecialchars($row["total"]) . '</td>
							  <td>' . htmlspecialchars($row["production"]) . '</td>
							  <td>' . htmlspecialchars($row["egg_cost"]) . '</td>
							  <td>' . htmlspecialchars($row["total_egg_revenue"]) . '</td>
							  <td>' . htmlspecialchars($row["profit"]) . '</td>
						  </tr>';
				}
			} else {
				echo '<tr><td colspan="12">No records found.</td></tr>'; 
			}
			
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
               WHERE DATE(DATETIME) BETWEEN ? AND ? AND client_id = ?";

			$total_stmt = $mysqli->prepare($total_query);
			$total_stmt->bind_param("ssi", $from_date, $to_date, $client_id); 
			$total_stmt->execute();
			$total_result = $total_stmt->get_result(); 

			if ($row = $total_result->fetch_assoc()) { 
				echo '<tr>
						  <td><strong>Total</strong></td>
						  <td><strong>' . htmlspecialchars($row["feed_used"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["feed_cost"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["medicine"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["other_cost"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["labour_cost"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["total"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["production"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["egg_cost"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["total_egg_revenue"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["profit"]) . '</strong></td>
					  </tr>';
			} else {
				echo '<tr><td colspan="10">No records found.</td></tr>';
			}

			$total_stmt->close();

			$stmt->close();
			$mysqli->close();

        ?>
        </table>
    </div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
	<script>
const sidebar = document.getElementById('sidebar');
const mainContent = document.querySelector('.content'); // or '.main-content'
const toggleBtn = document.getElementById('sidebarToggleBtn');

toggleBtn.addEventListener('click', () => {
  sidebar.classList.toggle('expanded');
  mainContent.classList.toggle('expanded');  // toggle expanded class for margin shift

  const icon = toggleBtn.querySelector('i');
  if (sidebar.classList.contains('expanded')) {
    icon.classList.remove('fa-bars');
    icon.classList.add('fa-times');
  } else {
    icon.classList.add('fa-bars');
    icon.classList.remove('fa-times');
  }
});

  function toggleAttendance() {
    toggleSubmenu('attendanceSubmenu');
  }
  function toggleFeedPlant() {
    toggleSubmenu('feedPlantSubmenu');
  }
  function toggleEggGodown() {
    toggleSubmenu('eggGodownSubmenu');
  }
  function toggleProfitLoss() {
    toggleSubmenu('profitLossSubmenu');
  }
  function toggleShed() {
    toggleSubmenu('shedSubmenu');
  }
  function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    if (!submenu) return;
    if (submenu.style.display === 'flex') {
      submenu.style.display = 'none';
    } else {
      submenu.style.display = 'flex';
    }
  }
  </script>
</body>
</html>