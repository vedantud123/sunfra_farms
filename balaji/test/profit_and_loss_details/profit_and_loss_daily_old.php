<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

function getTrayCount($trays) {
    $wholeTrays = floor($trays / 30);
    $remainder = $trays % 30;
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}

$shead_queries = ['Shead 1', 'Shead 2', 'Shead 3', 'Shead 4', 'Shead 5', 'Shead 6', 'Shead 7', 'Shead 8','Chick', 'Grower'];

$from_date =  $_POST['from_date'] ?? date('Y-m-d');
$to_date =  $_POST['to_date'] ?? date('Y-m-d');


$date = date('Y-m-d');
$timestamp = date('Y-m-d H:i:s');
foreach ($shead_queries as $shead) {
	$shead_name = '';
	$total_cal = $medicine_cal = $tons = 0;
	$sql = "SELECT shead_name, COUNT(*) AS count FROM feed_indicator_logs WHERE DATE(`TIMESTAMP`) = ? AND shead_name = ?";
	$stmt_logs = $mysqli->prepare($sql);
	$stmt_logs->bind_param("ss", $date, $shead);
	$stmt_logs->execute();
	$result_logs = $stmt_logs->get_result();
	if ($result_logs->num_rows >= 0) {
		while ($row_logs = $result_logs->fetch_assoc()) {
			
			#$shead_name2 = $row_logs['shead_name'];
			$tons = $row_logs['count'];
			$shead_name = strtolower(str_replace(" ", "_", $shead));

			if (stripos($shead_name, "chick") !== false) {
				$shead_name = "chick";
			} elseif (stripos($shead_name, "grower") !== false) {
				$shead_name = "grower";
			}
	}
			$feed_formula_sql = "SELECT feed_rawMaterial_name, quantity FROM feed_formula_detail WHERE feed_formulaType = ?";
			$stmt_formula = $mysqli->prepare($feed_formula_sql);
			$stmt_formula->bind_param("s", $shead_name);
			$stmt_formula->execute();
			$feed_formula_result = $stmt_formula->get_result();
			$total_quantity = 0; 
			if ($feed_formula_result->num_rows > 0) {
				while ($formula_row = $feed_formula_result->fetch_assoc()) {
					$material_name = $formula_row['feed_rawMaterial_name'];
					$quantity = $formula_row['quantity'];
					$total_quantity += $quantity;
					$price_per_kg_sql = "SELECT price FROM feed_rawMaterial_price WHERE NAME = ?";
					$stmt_price_per_kg = $mysqli->prepare($price_per_kg_sql);
					$stmt_price_per_kg->bind_param("s", $material_name);
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
			
			$water_formula_sql = "select * from sanitization where date(timestamp) = ?";
			$stmt_water_formula = $mysqli->prepare($water_formula_sql);
			$stmt_water_formula->bind_param("s", $date);
			$stmt_water_formula->execute();
			$water_formula_result = $stmt_water_formula->get_result();

			$total_water_quantity = 0;

			if ($water_formula_result->num_rows > 0) {
				while ($water_formula_row = $water_formula_result->fetch_assoc()) {
					$water_medicine_name = $water_formula_row['name'];
					$water_quantity = $water_formula_row['quantity'];
					$total_water_quantity += $water_quantity;
					$price_per_lit_sql = "SELECT price FROM water_medicine_and_sanitization_price WHERE medicine_name = ?";
					$stmt_price_per_lit = $mysqli->prepare($price_per_lit_sql);
					$stmt_price_per_lit->bind_param("s", $water_medicine_name);
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
			$egg_production_sql = "SELECT SUM(no_of_eggs) AS total_eggs FROM egg_godown_stock WHERE shead_name = ? AND DATE(`TIMESTAMP`) = ? AND sale IS NULL";
			$stmt_eggs = $mysqli->prepare($egg_production_sql);
			$stmt_eggs->bind_param("ss", $shead, $date);
			$stmt_eggs->execute();
			$result_eggs = $stmt_eggs->get_result();
			$total_eggs = 0;
			if ($row_eggs = $result_eggs->fetch_assoc()) {
				$total_eggs = $row_eggs['total_eggs'] ?? 0;
			}
			$stmt_eggs->close();
			
			$egg_cutting_price_sql = "SELECT * FROM `egg_cutting_price` WHERE shead_name = ?";
			$stmt_eggs_cutting_price = $mysqli->prepare($egg_cutting_price_sql);
			$stmt_eggs_cutting_price->bind_param("s", $shead);
			$stmt_eggs_cutting_price->execute();
			$result_eggs = $stmt_eggs_cutting_price->get_result();
			$cutting_price = $medicine_cost = $other_cost = 0;

			if ($row_eggs = $result_eggs->fetch_assoc()) {
				$cutting_price = $row_eggs['cutting_price'] ?? 0;
				#$medicine_cost = $row_eggs['medicine_cost'] ?? 0;
				$other_cost = $row_eggs['other_cost'] ?? 0;
			}

			$stmt_eggs_cutting_price->close();
			#$medicine_cal = 0;
			$feed_medicine_other_cal = $total_cal + $medicine_cal + $other_cost;
			$total_eggs_price = $total_eggs * $cutting_price;
			$profit = $total_eggs_price - $feed_medicine_other_cal ;
			
			
			$sql = "SELECT id FROM profit_and_loss WHERE shead_name = ? AND DATE(DATETIME) = ?";
			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param("ss", $shead, $date);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$id = $row['id'];
				$query = "UPDATE profit_and_loss SET shead_name = ?, feed_used = ?, feed_cost = ?, medicine = ?, other_cost = ?, total = ?, production = ?, egg_cost = ?, total_egg_revenue = ?, profit = ? WHERE id = ?";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("siiiidddiii", $shead, $total_quantity, $total_cal, $medicine_cal, $other_cost, $feed_medicine_other_cal, getTrayCount($total_eggs), $cutting_price, $total_eggs_price, $profit, $id);

			} else {
				$query = "INSERT INTO profit_and_loss (shead_name, feed_used, feed_cost, medicine, other_cost, total, production, egg_cost, total_egg_revenue, profit, datetime) 
						  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$stmt = $mysqli->prepare($query);
				$stmt->bind_param("siiiidddiis", $shead, $total_quantity, $total_cal, $medicine_cal, $other_cost, $feed_medicine_other_cal, getTrayCount($total_eggs), $cutting_price, $total_eggs_price, $profit, $timestamp);

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
    <style>
        body {
			font-family: Arial, sans-serif;
			background-color: #f4f4f9;
			margin: 0;
			padding: 0;
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
		}

		.container {
			max-width: 1200px;
			margin: 20px auto;
			padding: 20px;
			background: white;
			border-radius: 8px;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
			text-align: center;
		}

		h1 {
			color: #333;
		}

		.button-container {
			margin-bottom: 20px;
		}

		.button-container a,
		.button-container button {
			text-decoration: none;
			padding: 10px 15px;
			margin: 5px;
			color: white;
			background-color: #007BFF;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			font-size: 14px;
		}

		.button-container a:hover,
		.button-container button:hover {
			background-color: #0056b3;
		}

		form {
			background: white;
			padding: 20px;
			border-radius: 10px;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
			display: inline-block;
		}

		label {
			font-size: 18px;
			font-weight: bold;
		}

		input[type="date"] {
			padding: 8px;
			font-size: 16px;
			border-radius: 5px;
			border: 1px solid #ccc;
			margin-left: 10px;
		}

		button {
			background-color: #007bff;
			color: white;
			padding: 8px 16px;
			font-size: 16px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			margin-left: 10px;
		}

		button:hover {
			background-color: #0056b3;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			margin: 20px 0;
		}

		table th,
		table td {
			padding: 10px 15px;
			text-align: left;
			border: 1px solid #ddd;
		}

		table th {
			background-color: #007BFF;
			color: white;
		}

		table tr:nth-child(even) {
			background-color: #f9f9f9;
		}

		table tr:hover {
			background-color: #f1f1f1;
		}

		table a {
			color: #007BFF;
			text-decoration: none;
		}

		table a:hover {
			text-decoration: underline;
		}

    </style>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .sidebar { transition: all 0.3s ease; }
    .sidebar a:hover { background-color: #2b6cb0; color: white; }
    .collapsed { width: 64px !important; }
    .collapsed .sidebar-text { display: none; }
    .collapsed nav a { justify-content: center; }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .sidebar.show { display: block; }
    }
  </style>
</head>
<?php session_start(); ?>
<?php $clientName = $_SESSION['client_name'] ?? 'Yours'; ?>
<body class="bg-gray-100 text-gray-800">
    <div class="container">
        <h1>Profit & Loss Management</h1>
        <div class="button-container">
            <button onclick="window.location.href='https://sunfra.com/farm/profitandloss_details.php';">Go Back</button>
        </div>
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
			$query = "SELECT shead_name,SUM(feed_used) AS feed_used , SUM(feed_cost) AS feed_cost ,SUM(medicine) AS medicine , SUM(other_cost) AS other_cost ,SUM(total) AS total, SUM(production) AS production ,egg_cost, SUM(total_egg_revenue) AS total_egg_revenue , SUM(profit) AS profit 
					FROM profit_and_loss  WHERE DATE(DATETIME) BETWEEN ? AND ? GROUP BY shead_name
					ORDER BY FIELD (shead_name,'Shead 1', 'Shead 2', 'Shead 3', 'Shead 4', 'Shead 5', 'Shead 6','Shead 7','Shead 8','Chick' ,'Grower')";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $from_date, $to_date); 
			$stmt->execute();
			$result = $stmt->get_result(); 

			echo '<table>
				  <tr>
					  <th>Shead Name</th>
					  <th>Feed Used</th>
					  <th>Feed Cost</th>
					  <th>Medicine</th>
					  <th>Other Cost</th>
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
                      SUM(total) AS total, 
                      SUM(production) AS production, 
                      egg_cost, 
                      SUM(total_egg_revenue) AS total_egg_revenue, 
                      SUM(profit) AS profit
               FROM profit_and_loss  
               WHERE DATE(DATETIME) BETWEEN ? AND ?";

			$total_stmt = $mysqli->prepare($total_query);
			$total_stmt->bind_param("ss", $from_date, $to_date); 
			$total_stmt->execute();
			$total_result = $total_stmt->get_result(); 

			if ($row = $total_result->fetch_assoc()) { 
				echo '<tr>
						  <td><strong>Total</strong></td>
						  <td><strong>' . htmlspecialchars($row["feed_used"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["feed_cost"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["medicine"]) . '</strong></td>
						  <td><strong>' . htmlspecialchars($row["other_cost"]) . '</strong></td>
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

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>