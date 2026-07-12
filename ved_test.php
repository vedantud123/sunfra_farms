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

$selected_date = date('Y-m-d');

$balance_query = "SELECT * FROM egg_godown_status WHERE DATE = '$selected_date'";
$balance_result = $mysqli->query($balance_query);

$total_query = "SELECT 
    SUM(opening_balance) AS opening_total, 
    SUM(production) AS total_production, 
    SUM(sale) AS total_sale, 
    SUM(closing_balance) AS total 
FROM egg_godown_status WHERE DATE = '$selected_date'";

$total_result = $mysqli->query($total_query);
$total_row = $total_result->fetch_assoc();

function getTrayCount($trays) {
    $wholeTrays = floor($trays / 30);
    $remainder = $trays % 30; 
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
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
    <title>Tables Layout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            margin: 0;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1800px;
        }
        .table-container {
            width: 32%; /* Adjust to fit three tables per row */
            background: white;
            margin: 10px;
            padding: 0px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background: #007BFF;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="table-container">
        <h3>Egg Godown Status (<?= $selected_date ?>)</h3>
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
                <td><?= htmlspecialchars(getTrayCount($row['opening_balance'] * 30)) ?></td>
                <td><?= htmlspecialchars(getTrayCount($row['production'] * 30)) ?></td>
                <td><?= htmlspecialchars($row['sale']) ?></td>
                <td><?= htmlspecialchars(getTrayCount($row['closing_balance'] * 30)) ?></td>
            </tr>
            <?php endwhile; ?>

            <tr>
                <td></td>
                <td><strong>Total</strong></td>
                <td><strong><?= htmlspecialchars(getTrayCount($total_row['opening_total'] * 30)) ?></strong></td>
                <td><strong><?= htmlspecialchars(getTrayCount($total_row['total_production'] * 30)) ?></strong></td>
                <td><strong><?= htmlspecialchars($total_row['total_sale']) ?></strong></td>
                <td><strong><?= htmlspecialchars(getTrayCount($total_row['total'] * 30)) ?></strong></td>
            </tr>
        </table>
    </div>

    <?php
$sale_query = "SELECT sale, SUM(no_of_eggs) AS total_stock FROM egg_godown_stock 
               WHERE DATE(TIMESTAMP) = ? GROUP BY sale";
$sale_stmt = $mysqli->prepare($sale_query);
$sale_stmt->bind_param("s", $selected_date);
$sale_stmt->execute();
$sale_result = $sale_stmt->get_result();
?>

<div class="table-container">
    <h3>Table 2</h3>
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
    var table = document.getElementById(id);
    if (table.style.display === "none") {
        table.style.display = "table";
    } else {
        table.style.display = "none";
    }
}
</script>


    <div class="table-container">
        <h3>Table 3</h3>
        <table>
            <tr><th>ID</th><th>Name</th></tr>
            <tr><td>3</td><td>Emma</td></tr>
        </table>
    </div>
	<?php 
if (!isset($mysqli)) {
    die("Database connection is missing.");
}

$date_condition = '2024-03-27'; 

$mortality_query = "SELECT * FROM supervisor_shead_mortality 
                    WHERE `date` = ? 
                    ORDER BY FIELD(sheadNo, 'Shead_1', 'Shead_2', 'Shead_3', 'Shead_4', 'Shead_5', 
                                              'Shead_6', 'Shead_7', 'Shead_8', 'Chick', 'Grower')";

$mortality_stmt = $mysqli->prepare($mortality_query);

if (!$mortality_stmt) {
    die("Query preparation failed: " . $mysqli->error);
}

$mortality_stmt->bind_param("s", $date_condition);
$mortality_stmt->execute();
$result = $mortality_stmt->get_result();
?>
<div class="table-container">
    <h3>Table 4</h3>
    <table>
        <tr>
            <th>Date</th>
            <th>Shead Name</th>
            <th>No Of Birds</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['date']) ?></td> 
            <td>
                <?php 
                    $converted_shead = (strpos($row['sheadNo'], 'Shead_') !== false) 
                                       ? str_replace('Shead_', '', $row['sheadNo']) 
                                       : $row['sheadNo'];
                    echo htmlspecialchars($converted_shead);
                ?>
            </td>
            <td>
				<?= htmlspecialchars($row['noOfBirds']) ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php
$mortality_stmt->close();
?>


    <div class="table-container">
    <h3>Feed Intake</h3>
    <table>
        <thead>
            <tr>
                <th>Shead Name</th>
                <th>Feeding Avg Per Bird</th>
            </tr>
        </thead>
        <tbody>
            <?php	
            if ($feeding_result->num_rows > 0) {
                while ($detail_row = $feeding_result->fetch_assoc()) {
                    $total_feeding = $detail_row['Box_1'] + $detail_row['Box_2'] + $detail_row['Box_3'] + 
                                     $detail_row['Box_4'] + $detail_row['Box_5'] + $detail_row['Box_6'] + 
                                     $detail_row['Box_7'] + $detail_row['Box_8'] + $detail_row['Box_9'] + 
                                     $detail_row['Box_10'];

                    $converted_shead = (strpos($detail_row['sheadNo'], 'Shead_') !== false) 
                                       ? str_replace('Shead_', '', $detail_row['sheadNo']) 
                                       : $detail_row['sheadNo'];

                    $live_bird_query = "SELECT live_birds FROM batch WHERE sheadNo = ? AND cullDate = '0000-00-00'";
                    $live_bird_stmt = $mysqli->prepare($live_bird_query);
                    $live_bird_stmt->bind_param("s", $converted_shead);
                    $live_bird_stmt->execute();
                    $live_bird_stmt->bind_result($live_birds);
                    $live_bird_stmt->fetch();
                    $live_bird_stmt->close();

                    $avg_feeding = ($live_birds > 0) ? ($total_feeding / $live_birds) * 1000 : 0;

                    echo "<tr>
                            <td>" . htmlspecialchars($converted_shead) . "</td>
                            <td>" . htmlspecialchars(number_format($avg_feeding, 2)) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No data available</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

   <?php
$feeding_query = "SELECT * FROM `supervisor_feed_feeding_shead` WHERE DATE(TIMESTAMP) = ?
ORDER BY FIELD (sheadNo, 'Shead_1', 'Shead_2', 'Shead_3', 'Shead_4', 'Shead_5', 'Shead_6', 'Shead_7', 'Shead_8', 'Chick', 'Grower')";

$feeding_stmt = $mysqli->prepare($feeding_query);
$feeding_stmt->bind_param("s", $date);
$feeding_stmt->execute();
$feeding_result = $feeding_stmt->get_result();

if ($feeding_result->num_rows > 0) {
    while ($detail_row = $feeding_result->fetch_assoc()) {
        $total_feeding = $detail_row['Box_1'] + $detail_row['Box_2'] + $detail_row['Box_3'] + $detail_row['Box_4'] +
                         $detail_row['Box_5'] + $detail_row['Box_6'] + $detail_row['Box_7'] + $detail_row['Box_8'] +
                         $detail_row['Box_9'] + $detail_row['Box_10'];

        $sheadNo = $detail_row['sheadNo'];
        $converted_shead = (strpos($sheadNo, 'Shead_') !== false) 
                           ? str_replace('Shead_', '', $sheadNo) 
                           : $sheadNo;

        $live_bird_query = "SELECT live_birds FROM batch WHERE sheadNo = ? AND cullDate = '0000-00-00'";
        $live_bird_stmt = $mysqli->prepare($live_bird_query);
        $live_bird_stmt->bind_param("s", $converted_shead);
        $live_bird_stmt->execute();
        $live_bird_stmt->bind_result($live_birds);
        $live_bird_stmt->fetch();
        $live_bird_stmt->close();

        $avg_feeding = ($live_birds > 0) ? ($total_feeding / $live_birds) * 1000 : 0;

        echo "<tr>
                <td>" . htmlspecialchars($sheadNo) . "</td>
                <td>" . htmlspecialchars(number_format($avg_feeding, 2)) . "</td>
              </tr>";
    }
}
?>

</div>

</body>
</html>
