<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'];
date_default_timezone_set('Asia/Kolkata');

$links = [
    ["name" => "Egg Production From Shead", "url" => "https://sunfra.com/farm/test/egg_godown/egg_godown_stock.php"],
    ["name" => "Eggs for Sale", "url" => "https://sunfra.com/farm/test/egg_godown/egg_godown_sale.php"],
    ["name" => "Egg Weight", "url" => "https://sunfra.com/farm/test/egg_godown/egg_weight.php"]
];

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
    return ($wholeTrays * 30) + $partialEggs;
}

$selected_date = $_POST['selected_date'] ?? date('Y-m-d');

$shead_queries = ['shead 1', 'shead 2', 'shead 3', 'shead 4', 'shead 5', 'shead 6', 'shead 7', 'shead 8'];
$egg_data = [];

foreach ($shead_queries as $shead) {
    $available_eggs = $available_damage_eggs = 0;
    $good_eggs = $damage_eggs = $small_eggs = $big_eggs = $bullet_eggs = $scrap_eggs = $duration = $runningWeeks = $hatchDate = $live_birds = 0;

    $query = "
        SELECT 
            SUM(CASE WHEN type_of_eggs = 'Good' THEN no_of_eggs ELSE 0 END),
            SUM(CASE WHEN type_of_eggs IN ('Damaged', 'Scrap') THEN no_of_eggs ELSE 0 END),
            SUM(CASE WHEN type_of_eggs IN ('Bullet', 'Small') THEN no_of_eggs ELSE 0 END),
            SUM(CASE WHEN type_of_eggs = 'Big' THEN no_of_eggs ELSE 0 END)
        FROM egg_godown_stock 
        WHERE DATE(timestamp) = ? AND shead_name = ? AND sale IS NULL AND client_id = ?
        GROUP BY shead_name
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssi", $selected_date, $shead, $client_id);
    $stmt->execute();
    $stmt->bind_result($good_eggs, $damage_eggs, $small_eggs, $big_eggs);
    $stmt->fetch();
    $stmt->close();

    $return_query = "SELECT SUM(no_of_eggs) FROM egg_godown_stock 
                     WHERE remarks = 'Return' AND sale IS NOT NULL AND shead_name = ? 
                     AND DATE(timestamp) = ? AND client_id = ?";
    $return_stmt = $mysqli->prepare($return_query);
    $return_stmt->bind_param("ssi", $shead, $selected_date, $client_id);
    $return_stmt->execute();
    $return_stmt->bind_result($new_damage_eggs);
    $return_stmt->fetch();
    $return_stmt->close();

    $total_damage_eggs = ($damage_eggs ?? 0) + ($scrap_eggs ?? 0) + ($new_damage_eggs ?? 0);

    $good_eggs_intrays = getTrayCount($good_eggs);
    $damage_eggs_intrays = getTrayCount($total_damage_eggs);
    $small_eggs_intrays = getTrayCount($small_eggs);
    $big_eggs_intrays = getTrayCount($big_eggs);

    $shead_num = preg_replace("/[^0-9]/", "", $shead);

    $batch_query = "SELECT hatchDate, live_birds FROM batch 
                WHERE cullDate IS NULL AND sheadNo = ? AND client_id = ?";


    $batch_stmt = $mysqli->prepare($batch_query);
    $batch_stmt->bind_param("ii", $shead_num, $client_id);
    $batch_stmt->execute();
    $batch_stmt->bind_result($hatchDate, $live_birds);
    $batch_stmt->fetch();
    $batch_stmt->close();

    if (!empty($hatchDate)) {
        $startDateObj = new DateTime($hatchDate);
        $diff = $startDateObj->diff(new DateTime());
        $runningDays = $diff->days + 1;
    } else {
        $runningDays = "N/A";
    }

    $runningWeeks = (is_numeric($runningDays)) ? floor($runningDays / 7) : "N/A";
    $duration = (is_numeric($runningDays)) ? "$runningWeeks" : "Done";

    $total_production = ($good_eggs + $damage_eggs + $small_eggs + $big_eggs + $bullet_eggs + $scrap_eggs);
    $average = ($live_birds > 0) ? ($total_production / $live_birds) * 100 : 0;
    $average = rtrim(rtrim(number_format($average, 2, '.', ''), '0'), '.');

    $egg_data[] = [
        "Running_week" => $duration,
        "shead_name" => ucfirst($shead),
        "good_eggs" => $good_eggs_intrays,
        "damaged_eggs" => $damage_eggs_intrays,
        "small_eggs" => $small_eggs_intrays,
        "big_eggs" => $big_eggs_intrays,
        "Production_Percentage" => $average
    ];
}

// Get balances
$balance_query = "SELECT * FROM egg_godown_status WHERE DATE = ? AND client_id = ?";
$balance_stmt = $mysqli->prepare($balance_query);
$balance_stmt->bind_param("si", $selected_date, $client_id);
$balance_stmt->execute();
$balance_result = $balance_stmt->get_result();

$totalOpeningBalanace = $totalProduction = $totalSale = $totalClosingBalance = 0;

$total_query = "SELECT opening_balance, production, sale, closing_balance 
                FROM egg_godown_status WHERE DATE = ? AND client_id = ?";
$total_stmt = $mysqli->prepare($total_query);
$total_stmt->bind_param("si", $selected_date, $client_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();

while ($row = $total_result->fetch_assoc()) {
    $OB = gettotaleggs($row['opening_balance']);
    $Pro = gettotaleggs($row['production']);
    $Sale = gettotaleggs($row['sale']);
    $CB = gettotaleggs($row['closing_balance']);

    $totalOpeningBalanace += $OB;
    $totalProduction += $Pro;
    $totalSale += $Sale;
    $totalClosingBalance += $CB;
}

// Sales table
$sale_query = "SELECT SUM(no_of_eggs) AS total_stock, sale 
               FROM egg_godown_stock 
               WHERE sale IS NOT NULL AND DATE(timestamp) = ? AND client_id = ?
               GROUP BY sale ORDER BY total_stock DESC";
$sale_stmt = $mysqli->prepare($sale_query);
$sale_stmt->bind_param("si", $selected_date, $client_id);
$sale_stmt->execute();
$sale_result = $sale_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egg Godown Dashboard</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}

		body {
			font-family: 'Arial', sans-serif;
			color: #333;
			padding: 10px;
			display: flex;
			flex-direction: column;
			align-items: center;
			font-size: 12px;
		}

		h1 {
			color: #444;
			margin-bottom: 10px;
			font-size: 18px;
		}

		.link-item {
			margin: 12px 0;
			padding: 10px 15px;
			border: 3px solid #ddd;
			border-radius: 5px;
			transition: background-color 0.3s, color 0.3s;
		}

		.link-item a {
			text-decoration: none;
			color: #007bff;
			font-weight: bold;
			font-size: 16px; /* Increased font size */
		}

		.link-item:hover {
			background-color: #f0f8ff;
		}

		.link-item:hover a {
			color: #0056b3;
		}

		.table-container {
					background-color: #ffffff;
					padding: 12px; 
					border-radius: 8px;
					box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
					width: 100%;
					max-width: 600px; 
					border: 1px solid #ddd; 
		}

		.logout-button {
			display: block;
			width: 120px;
			margin: 20px auto;
			padding: 12px;
			text-align: center;
			background-color: #ff4d4d;
			color: white;
			text-decoration: none;
			border-radius: 5px;
			font-weight: bold;
			font-size: 14px;
		}

		.logout-button:hover {
			background-color: #e63939;
		}

		form {
			display: flex;
			justify-content: flex-start;
			align-items: center;
			margin-bottom: 10px;
		}

		label {
			font-weight: bold;
			margin-right: 6px;
			font-size: 14px;
		}

		select {
			padding: 6px 10px;
			border: 1px solid #ddd;
			border-radius: 4px;
			font-size: 14px;
		}

		button {
			background-color: #27ae60;
			color: white;
			padding: 8px 12px;
			border: none;
			border-radius: 4px;
			cursor: pointer;
			font-size: 14px;
			transition: background-color 0.3s;
		}

		button:hover {
			background-color: #219150;
		}

		table {
					width: 100%;
					border-collapse: collapse;
					margin-top: 12px; 
					border: 1px solid #ddd; 
				}

				th, td {
					padding: 6px; 
					text-align: left;
					border-bottom: 1px solid #ddd;
					font-size: 12px; 
				}

				th {
					background-color: #f4f4f4;
					font-weight: bold;
				}

				tbody tr:nth-child(even) {
					background-color: #f9f9f9;
				}

				tbody tr:hover {
					background-color: #f1f1f1;
				}
		/* Back Button */
		.go-back-btn {
			background-color: #e74c3c;
			color: white;
			padding: 8px 12px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			margin-bottom: 14px;
			text-transform: uppercase;
			font-weight: bold;
			font-size: 12px;
			text-align: center;
		}

		.go-back-btn:hover {
			background-color: #c0392b;
		}

		/* Container */
		.container {
			max-width: 800px;
			margin: 0 auto;
			background-color: #ffffff;
			padding: 20px;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		.details-table {
			display: none;
			width: 100%;
			margin-top: 10px;
			border: 1px solid #ddd;
		}

		.details-table tbody td {
			padding: 10px;
			border: 1px solid #ddd;
			white-space: nowrap;
		}

		table thead th {
			background-color: #007bff;
			color: white;
			padding: 12px;
			text-align: left;
		}

		/* Date Input */
		input[type="date"] {
			padding: 8px 12px;
			font-size: 14px;
			border: 1px solid #ddd;
			border-radius: 4px;
			color: #333;
			background-color: #ffffff;
			cursor: pointer;
		}

		input[type="date"]:hover {
			border-color: #2980b9;
			background-color: #f9f9f9;
		}

		input[type="date"]:focus {
			outline: none;
			border-color: #007bff;
			box-shadow: 0 0 4px #007bff;
		}.sidebar {
		  position: fixed;
		  top: 0;
		  left: 0;
		  width: 250px;
		  height: 100vh;
		  background: #0d6efd;
		  color: #fff;
		  padding: 20px 10px;
		  transition: width 0.3s;
		  overflow-y: auto;
		  z-index: 1050;
		}

		.sidebar.collapsed {
		  width: 50px !important;
		  padding: 20px 0 !important;
		}

		.sidebar.collapsed .sidebar-text {
		  display: none !important;
		}

		.main-content {
		  margin-left: 250px;
		  transition: margin-left 0.3s;
		}

		.main-content.collapsed {
		  margin-left: 50px;
		}

		/* MOBILE view - sidebar off-canvas */
		@media (max-width: 768px) {
		  .sidebar {
			position: fixed;
			left: 0; top: 0;
			height: 100vh;
			width: 250px;
			transform: translateX(-100%);
			transition: transform 0.3s;
			z-index: 1100;
			background: #0d6efd;
		  }
		  .sidebar.show {
			transform: translateX(0);
		  }
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
		  }
		}@media (max-width: 768px) {
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
		  }
		}.btn-submit {
		  background-color: #007BFF;
		  color: white;
		  border: none;
		  padding: 8px 14px;
		  border-radius: 5px;
		  cursor: pointer;
		  font-size: 14px;
		  transition: background-color 0.3s ease;
		}

		.btn-submit:hover {
		  background-color: #0056b3;
		}.back-button {
		  display: inline-block;
		  margin-bottom: 1rem;
		  background-color: #3498db; /* Blue background */
		  color: white;
		  padding: 10px 16px;
		  border-radius: 6px;
		  text-decoration: none;
		  font-weight: 600;
		  transition: background-color 0.3s ease;
		}

		.back-button:hover {
		  background-color: #217dbb; /* Darker blue on hover */
		}.centered {
		  text-align: center;
		  margin-bottom: 1rem; /* optional spacing */
		}


    </style>
	<script>
		window.onload = function () {
			fetch('https://sunfra.com/farm/test/egg_godown/egg_godown_status.php')
				.then(response => {
					if (!response.ok) {
						console.error('Script request failed: egg_godown_status.php');
					}
				})
				.catch(error => console.error('Error:', error));

			fetch('https://sunfra.com/farm/test/profit_and_loss_details/profit_and_loss_daily.php')
				.then(response => {
					if (!response.ok) {
						console.error('Script request failed: profit_and_loss_daily.php');
					}
				})
				.catch(error => console.error('Error:', error));
		};
	</script>

</head>
<body>
<div>
	<aside id="sidebar" class="sidebar bg-blue-800 text-white p-4">
	<div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="text-xl font-semibold sidebar-text"><?= htmlspecialchars($clientName) ?></h2>
		<button id="collapse-btn" class="text-white">
		  <i class="fas fa-angle-double-left"></i>
		</button>
      </div>

      <nav class="space-y-2">
         <a href="https://sunfra.com/farm/test/test_dashboard.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-home"></i> <span class="sidebar-text">Home</span>
        </a>
        <a href="https://sunfra.com/farm/test/batch_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-globe"></i> <span class="sidebar-text">Batch</span>
        </a>
        <a href="https://sunfra.com/farm/test/weighbridge_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-truck"></i> <span class="sidebar-text">WeighBridge</span>
        </a>
        <a href="https://sunfra.com/farm/test/tractor_production_mortality_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-tractor"></i> <span class="sidebar-text">Tractor Production Mortality</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_attendance.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-check"></i> <span class="sidebar-text">Attendance</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-tie"></i> <span class="sidebar-text">Shead Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-warehouse"></i> <span class="sidebar-text">Feed Plant Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/egg_godown/egg_godown.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-egg"></i> <span class="sidebar-text">Egg Godown Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_profit_loss_details.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-chart-line"></i> <span class="sidebar-text">Profit And Loss</span>
        </a>
        <a href="https://sunfra.com/farm/test/settings.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-sliders-h"></i> <span class="sidebar-text">Feature Settings</span>
        </a>
        <a href="https://sunfra.com" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-life-ring"></i> <span class="sidebar-text">Support</span>
        </a>
        <a href="https://sunfra.com/farm/test/logout.php" class="flex items-center gap-3 p-2 rounded hover:bg-red-600">
          <i class="fas fa-sign-out-alt"></i> <span class="sidebar-text">Logout</span>
        </a>
      </nav>
    </aside>
<main class="main-content">
    	<div class="centered">
		  <a href="https://sunfra.com/farm/test/test_dashboard.php" class="back-button">
			← Go Back
		  </a>
		  <h1>Egg Godown</h1>
		</div>

    <div class="links-container">
        <?php foreach ($links as $link): ?>
            <div class="link-item">
                <a href="<?= htmlspecialchars($link['url']) ?>">
                    <?= htmlspecialchars($link['name']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
	
	<form method="POST">
		<p>
			<label for="selected_date">Date:</label>
			<input type="date" name="selected_date" id="selected_date" value="<?= htmlspecialchars($selected_date) ?>">
			<button type="submit" class="btn-submit">Submit</button>
		</p>
	</form>
	
	<div class="table-container2">
        <h2>Balance</h2>
        <table>
            <tr>
                <th>Date</th>
                <th>Shead Name</th>
                <th>Opening Balance</th>
                <th>Production</th>
                <th>Sale</th>
                <th>Closing Balance</th>
            </tr>
            <?php while ($row = $balance_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['shead_name']) ?></td>
                <td><?= htmlspecialchars($row['opening_balance']) ?></td>
                <td><?= htmlspecialchars($row['production']) ?></td>
                <td><?= htmlspecialchars($row['sale']) ?></td>
                <td><?= htmlspecialchars($row['closing_balance']) ?></td>
            </tr>
            <?php endwhile; ?>
			<tr>
				<td></td>
                <td><strong>Total</strong></td>
                <td><strong><?= htmlspecialchars(getTrayCount($totalOpeningBalanace))?></strong></td>
                <td><strong><?= htmlspecialchars(getTrayCount($totalProduction))?></strong></td>
                <td><strong><?= htmlspecialchars(getTrayCount($totalSale))?></strong></td>
                <td><strong><?= htmlspecialchars(getTrayCount($totalClosingBalance)) ?></strong></td>
			</tr>
        </table>
    </div>
	
<div class="container">
    <h1>Sales Data</h1>
    <table>
        <thead>
            <tr>
                <th>Sale To</th>
                <th>Total Stock (Trays)</th>
                <th>Return Damage Trays</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($sale_result->num_rows > 0): ?>
            <?php while ($row = $sale_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['sale']) ?></td>
					<?php
						$saler_name = $row['sale'];
						$return_query = "SELECT SUM(no_of_eggs) FROM egg_godown_stock WHERE remarks = 'Return' AND sale = ? AND DATE(TIMESTAMP) = ?";

						$return_query_stmt = $mysqli->prepare($return_query);
						$return_query_stmt->bind_param("ss", $saler_name, $selected_date);
						$return_query_stmt->execute();
						$return_query_stmt->bind_result($return_eggs);
						$return_query_stmt->fetch();
						$return_query_stmt->close();
						$total_stock = $row['total_stock'];
						$current_stock = $total_stock - $return_eggs;  
						$total_stock_in_trays = getTrayCount($current_stock);
					?>
					<td><?=$total_stock_in_trays?></td>
					<td><?= getTrayCount($return_eggs) ?></td>
                    <td>
                        <button onclick="toggleDetails('details-<?= md5($row['sale']) ?>')">▼</button>
                        <table id="details-<?= md5($row['sale']) ?>" class="details-table" style="display: none;">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Shed No</th>
                                    <th>Sale Quantity</th>
                                    <th>Type of eggs</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sale_date = $_POST['sale_date'] ?? date('Y-m-d');
                                $sale_name = $row['sale'];
                                $detail_query = "SELECT * FROM egg_godown_stock 
                                                 WHERE sale = '$sale_name' 
                                                 AND DATE(TIMESTAMP) = '$selected_date' 
                                                 ORDER BY TIMESTAMP DESC, remarks DESC";
                                $detail_result = $mysqli->query($detail_query);

                                if ($detail_result->num_rows > 0) {
                                    while ($detail_row = $detail_result->fetch_assoc()) {
                                        echo "<tr>
                                                <td>{$detail_row['timestamp']}</td>
                                                <td>{$detail_row['shead_name']}</td>
                                                <td>" . getTrayCount($detail_row['no_of_eggs']) . "</td>
                                                <td>{$detail_row['type_of_eggs']}</td>
                                                <td>{$detail_row['remarks']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No details available</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">No data available</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function toggleDetails(id) {
    const table = document.getElementById(id);
    table.style.display = table.style.display === "none" ? "table" : "none";
}
</script>
	 <div class="table-container">
        <h2>Production</h2>
        <table>
            <thead>
                <tr>
                    <th>Running week</th>
                    <th>Shead Name</th>
                    <th>Good </th>
                    <th>Damaged </th>
                    <th>Small </th>
                    <th>Big </th>
					<th>Production(%) </th>
               </tr>
            </thead>
            <tbody>
                <?php foreach ($egg_data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Running_week']) ?></td>
                        <td><?= htmlspecialchars($row['shead_name']) ?></td>
                        <td><?= htmlspecialchars($row['good_eggs']) ?></td>
                        <td><?= htmlspecialchars($row['damaged_eggs']) ?></td>
                        <td><?= htmlspecialchars($row['small_eggs']) ?></td>
                        <td><?= htmlspecialchars($row['big_eggs']) ?></td>
                        <td><?= htmlspecialchars($row['Production_Percentage']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
	</main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
	  const sidebar = document.getElementById('sidebar');
	  const mainContent = document.querySelector('.main-content');
	  const collapseBtn = document.getElementById('collapse-btn');

	  collapseBtn?.addEventListener('click', function () {
		sidebar.classList.toggle('collapsed');
		mainContent.classList.toggle('collapsed');
		const icon = this.querySelector('i');
		if (icon) {
		  icon.classList.toggle('fa-angle-double-left');
		  icon.classList.toggle('fa-angle-double-right');
		}
	  });

	  menuBtn?.addEventListener('click', function () {
		sidebar.classList.toggle('show');
	  });
	});
</script>
</body>
<?php $mysqli->close(); ?>
</html>
