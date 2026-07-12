<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$links = [
    ["name" => "Egg Production From Shead", "url" => "https://sunfra.com/farm/egg_godown/egg_godown_stock.php"],
    ["name" => "Eggs for Sale", "url" => "https://sunfra.com/farm/egg_godown/egg_godown_sale.php"],
    ["name" => "Egg Weight", "url" => "https://sunfra.com/farm/egg_godown/egg_weight.php"],
	["name" => "Egg Damage Summary", "url" => "https://sunfra.com/farm/egg_godown/damaged_report/egg_damaged.php"],
];

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

function gettotaleggs($trays) {
    $wholeTrays = floor($trays); 
    $decimalPart = $trays - $wholeTrays; 
    $partialEggs = round($decimalPart * 100);

    $total_no_of_eggs = ($wholeTrays * 30) + $partialEggs;
    return $total_no_of_eggs;
}

$selected_date = $_POST['selected_date'] ?? date('Y-m-d');

$shead_queries = ['shead 1', 'shead 2', 'shead 3', 'shead 4', 'shead 5', 'shead 6', 'shead 7', 'shead 8'];
$egg_data = [];

foreach ($shead_queries as $shead) {
    $available_eggs = 0;
    $available_damage_eggs = 0;
	$good_eggs = $damage_eggs = $small_eggs = $big_eggs = $bullet_eggs = $scrap_eggs = $duration = $runningWeeks = $hatchDate = $live_birds = 0;
    if ($selected_date) {
        $query = "
            SELECT 
                SUM(CASE WHEN type_of_eggs = 'Good' THEN no_of_eggs ELSE 0 END) AS good_eggs,
                SUM(CASE WHEN type_of_eggs IN ('Damaged', 'Scrap') THEN no_of_eggs ELSE 0 END) AS damaged_eggs,
                SUM(CASE WHEN type_of_eggs IN ('Bullet', 'Small') THEN no_of_eggs ELSE 0 END) AS small_eggs,
                SUM(CASE WHEN type_of_eggs = 'Big' THEN no_of_eggs ELSE 0 END) AS big_eggs
            FROM egg_godown_stock 
            WHERE DATE(TIMESTAMP) = ? 
            AND sale IS NULL 
            AND shead_name = ?
            GROUP BY shead_name;
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $selected_date, $shead);
        $stmt->execute();
        $stmt->bind_result(
            $good_eggs, 
            $damage_eggs, 
            $small_eggs, 
            $big_eggs 
        );
        $stmt->fetch();
        $stmt->close();

		$sale_damage_query = "SELECT shead_name, SUM(no_of_eggs) AS new_damage_eggs 
                      FROM egg_godown_stock 
                      WHERE sale IS NOT NULL 
                      AND remarks = 'Return' 
                      AND shead_name = ? 
                      AND DATE(TIMESTAMP) = ?";

		$sale_damage_stmt = $mysqli->prepare($sale_damage_query);
		$sale_damage_stmt->bind_param("ss", $shead, $date_condition);
		$sale_damage_stmt->execute();
		$sale_damage_stmt->bind_result($shead_name, $new_damage_eggs);
		$sale_damage_stmt->fetch();
		$sale_damage_stmt->close();

		$good_eggs = ($good_eggs ?? 0);
		$damage_eggs = ($damage_eggs ?? 0);
		$small_eggs = ($small_eggs ?? 0);
		$big_eggs = ($big_eggs ?? 0);
		$bullet_eggs = ($bullet_eggs ?? 0);
		$scrap_eggs = ($scrap_eggs ?? 0);
		
		$total_damage_eggs = $damage_eggs + $scrap_eggs + $new_damage_eggs;
		
        $good_eggs_intrays = getTrayCount($good_eggs);
        $damage_eggs_intrays = getTrayCount($total_damage_eggs);
        $small_eggs_intrays = getTrayCount($small_eggs);
        $big_eggs_intrays = getTrayCount($big_eggs);
		
        $number = preg_replace("/[^0-9]/", "", $shead);
		
        $day_query = "SELECT hatchDate, live_birds FROM `batch` WHERE cullDate = '0000-00-00' AND sheadNo = ?";
		$day_stmt = $mysqli->prepare($day_query);

		if ($day_stmt) {
			$day_stmt->bind_param("s", $number); 
			$day_stmt->execute();
			$day_stmt->bind_result($hatchDate , $live_birds); 

			if ($day_stmt->fetch()) {
			}
			$day_stmt->close(); 
		} else {
			echo "Error in preparing statement: " . $mysqli->error;
		}		
	
        if (!empty($hatchDate)) {
			$startDateObj = new DateTime($hatchDate);
			$diff = $startDateObj->diff(new DateTime());
			$runningDays = $diff->days + 1; 
		} else {
			$runningDays = "N/A";
		}

		if (is_numeric($runningDays)) {
			$runningWeeks = floor($runningDays / 7);  
			$remainingDays = $runningDays % 7;        
		} else {
			$runningWeeks = "N/A";
			$remainingDays = "N/A";
		}
		
        $runningWeeks = (is_numeric($runningDays) && $runningDays !== "Done") ? floor($runningDays / 7) : "Done";
        $duration = ($runningDays !== "Done") ? "$runningWeeks" : "Done";
		
		$average = ($live_birds > 0) ? (($good_eggs + $damage_eggs + $small_eggs + $big_eggs + $bullet_eggs + $scrap_eggs)/ $live_birds) * 100 : 0;
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
}

$balance_query = "SELECT * FROM egg_godown_status WHERE DATE = '$selected_date'";
$balance_result = $mysqli->query($balance_query);

$totalOpeningBalanace = 0;
$totalProduction = 0;
$totalSale = 0;
$totalClosingBalance = 0;

$total_query = "SELECT opening_balance, production, sale, closing_balance FROM `egg_godown_status` WHERE DATE = '$selected_date'";
$total_result = $mysqli->query($total_query);

if ($total_result->num_rows > 0) {
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
}

$sale_query = "SELECT SUM(no_of_eggs) AS total_stock, sale 
               FROM egg_godown_stock 
               WHERE sale IS NOT NULL AND DATE(TIMESTAMP) = '$selected_date'
               GROUP BY sale 
               ORDER BY total_stock DESC";
$sale_result = $mysqli->query($sale_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egg Godown Dashboard</title>
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
		}

    </style>
	<script>
		window.onload = function () {
			fetch('https://sunfra.com/farm/egg_godown/egg_godown_status.php')
				.then(response => {
					if (!response.ok) {
						console.error('Script request failed: egg_godown_status.php');
					}
				})
				.catch(error => console.error('Error:', error));

			fetch('https://sunfra.com/farm/profit_and_loss_details/profit_and_loss_daily.php')
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
    <button class="go-back-btn" onclick="window.location.href='https://sunfra.com/farm/index.php'">Go Back</button>

    <h1>Egg Godown</h1>

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
			<button type="submit">Submit</button>
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
</body>
<?php $mysqli->close(); ?>
</html>
