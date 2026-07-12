<?php

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$from_date = $_POST['from_date'] ?? date('Y-m-d');
$to_date = $_POST['to_date'] ?? date('Y-m-d');

$sql = "SELECT * FROM `summary_report` WHERE `date` BETWEEN ? AND ? order by date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Summary</title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
     body {
		font-family: Arial, sans-serif;
	}

	h2 {
		text-align: center;
		margin-top: 20px;
	}

	.filter-form {
		width: fit-content;
		margin: 20px auto;
		display: flex;
		gap: 10px;
		align-items: center;
		background-color: #f2f2f2;
		padding: 10px 15px;
		border: 1px solid #ccc;
		border-radius: 6px;
	}

	.filter-form label {
		font-size: 14px;
		color: #333;
		margin-right: 5px;
	}

	.filter-form input[type="date"] {
		padding: 5px;
		font-size: 14px;
		border: 1px solid #aaa;
		border-radius: 4px;
		width: 140px;
	}

	.filter-form input[type="submit"] {
		padding: 6px 12px;
		font-size: 14px;
		background-color: #2e8b57;
		color: white;
		border: none;
		border-radius: 4px;
		cursor: pointer;
	}

	.filter-form input[type="submit"]:hover {
		background-color: #256f45;
	}

	.table-container {
		overflow-x: auto;
		max-width: 90%;
		margin: 0 auto;
		border: 1px solid #ccc;
	}

	table {
		border-collapse: collapse;
		width: max-content;
		font-size: 15px;
		min-width: 1200px;
	}

	.separator {
		background-color: green !important;
		border-left: none !important;
		border-right: none !important;
		width: 4px;
		padding: 1 !important;
	}

	th, td {
		border: 1px solid #999;
		padding: 6px;
		text-align: center;
		background-color: white;
		white-space: nowrap;
	}

	th {
		background-color: #2e8b57;
		color: white;
	}

	tr:nth-child(even) {
		background-color: #f9f9f9;
	}

	/* Sticky first column (Date) */
	.sticky-col {
		position: sticky;
		left: 0;
		background-color: #ffffff;
		z-index: 2;
		min-width: 120px;
	}

	/* Also sticky header with better visibility */
	thead th {
		position: sticky;
		top: 0;
		z-index: 3;
	}
	.sticky-col-2 {
		position: sticky;
		left: 120px; /* Adjust based on the width of the first column */
		background-color: green !important;
		z-index: 2;
		width: 4px;
	}.back-button {
	  display: block;
	  width: fit-content;
	  margin: 20px auto; /* Centers horizontally */
	  padding: 10px 20px;
	  background-color: #3498db;
	  color: white;
	  text-decoration: none;
	  border-radius: 5px;
	  font-weight: bold;
	  transition: background-color 0.3s ease;
	}

	.back-button:hover {
	  background-color: #2980b9;
	}

    </style>
	<script>
		window.onload = function () {
			fetch('https://sunfra.com/farm/dashboard_data.php')
				.then(response => {
					if (!response.ok) {
						console.error('Script request failed: dashboard_data.php');
					}
				})
				.catch(error => console.error('Fetch error:', error));
		};
	</script>
</head>
<body>
	<h2>Summary Report</h2>
	<a class="back-button" href="https://sunfra.com/farm/profitandloss_details.php">Go Back</a>
	<form method="post" class="filter-form">
		<label for="from_date">From:</label>
		<input type="date" name="from_date" id="from_date" >

		<label for="to_date">To:</label>
		<input type="date" name="to_date" id="to_date" >

		<input type="submit" value="Filter">
	</form>

<table>
	<tr>
        <th class="sticky-col" rowspan="2" style="background-color: #FFA500; color: white; rowspan="2">Date</th>
		<th class="separator sticky-col-2" rowspan="2"></th>
		<th colspan="10" style="background-color: #FFA500; color: white;">Production</th>
        <th class="separator" rowspan="2"></th>
        <th colspan="9" style="background-color: #FFA500; color: white;">Damage</th>
		<th class="separator" rowspan="2"></th>
        <th colspan="9" style="background-color: #FFA500; color: white;">Percentage</th>
		<th class="separator" rowspan="2"></th>
        <th colspan="10" style="background-color: #FFA500; color: white;">Feed Intake</th>
		<th class="separator" rowspan="2"></th>
        <th colspan="10" style="background-color: #FFA500; color: white;">Mortality</th>
		<th class="separator" rowspan="2"></th>
        <th colspan="8" style="background-color: #FFA500; color: white;">Egg Weight</th>
		<th class="separator" rowspan="2"></th>
        <th colspan="11" style="background-color: #FFA500; color: white;">Profit And Loss</th>
    </tr>
    <tr>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>Total</th>
        <th>Scrap</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>Total</th>
		<th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>Average</th>
		<th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>CH</th>
		<th>GR</th>
		<th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>CH</th>
		<th>GR</th>
		<th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
		<th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>CH</th>
		<th>GR</th>
		<th>Total</th>
    </tr>

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
				<td class="sticky-col"><?php echo $row["date"]; ?></td>
				<th class="separator sticky-col-2" rowspan="1"></th>
                <td><?php echo $row["production1"]; ?></td>
                <td><?php echo $row["production2"]; ?></td>
                <td><?php echo $row["production3"]; ?></td>
                <td><?php echo $row["production4"]; ?></td>
                <td><?php echo $row["production5"]; ?></td>
                <td><?php echo $row["production6"]; ?></td>
                <td><?php echo $row["production7"]; ?></td>
                <td><?php echo $row["production8"]; ?></td>
                <td><?php echo $row["total_production"]; ?></td>
                <td><?php echo $row["total_scrap"]; ?></td>
                <td class="separator"></td> 
                <td><?php echo $row["damage1"]; ?></td>
                <td><?php echo $row["damage2"]; ?></td>
                <td><?php echo $row["damage3"]; ?></td>
                <td><?php echo $row["damage4"]; ?></td>
                <td><?php echo $row["damage5"]; ?></td>
                <td><?php echo $row["damage6"]; ?></td>
                <td><?php echo $row["damage7"]; ?></td>
                <td><?php echo $row["damage8"]; ?></td>
				<td><?php echo $row["total_damage"]; ?></td>
                <td class="separator"></td> 
				<td><?php echo $row["percentage1"]; ?></td>
                <td><?php echo $row["percentage2"]; ?></td>
                <td><?php echo $row["percentage3"]; ?></td>
                <td><?php echo $row["percentage4"]; ?></td>
                <td><?php echo $row["percentage5"]; ?></td>
                <td><?php echo $row["percentage6"]; ?></td>
                <td><?php echo $row["percentage7"]; ?></td>
                <td><?php echo $row["percentage8"]; ?></td>
				<td><?php echo $row["average_percentage"]; ?></td>
				<td class="separator"></td>
				<td><?php echo $row["feedintake1"]; ?></td>
                <td><?php echo $row["feedintake2"]; ?></td>
                <td><?php echo $row["feedintake3"]; ?></td>
                <td><?php echo $row["feedintake4"]; ?></td>
                <td><?php echo $row["feedintake5"]; ?></td>
                <td><?php echo $row["feedintake6"]; ?></td>
                <td><?php echo $row["feedintake7"]; ?></td>
                <td><?php echo $row["feedintake8"]; ?></td>
				<td><?php echo $row["feedintakeChick"]; ?></td>
                <td><?php echo $row["feedintakeGrower"]; ?></td>
				<td class="separator"></td>
				<td><?php echo $row["mortality1"]; ?></td>
                <td><?php echo $row["mortality2"]; ?></td>
                <td><?php echo $row["mortality3"]; ?></td>
                <td><?php echo $row["mortality4"]; ?></td>
                <td><?php echo $row["mortality5"]; ?></td>
                <td><?php echo $row["mortality6"]; ?></td>
                <td><?php echo $row["mortality7"]; ?></td>
                <td><?php echo $row["mortality8"]; ?></td>
				<td><?php echo $row["mortalityChick"]; ?></td>
                <td><?php echo $row["mortalityGrower"]; ?></td>
				<td class="separator"></td>
				<td><?php echo $row["eggweight1"]; ?></td>
                <td><?php echo $row["eggweight2"]; ?></td>
                <td><?php echo $row["eggweight3"]; ?></td>
                <td><?php echo $row["eggweight4"]; ?></td>
                <td><?php echo $row["eggweight5"]; ?></td>
                <td><?php echo $row["eggweight6"]; ?></td>
                <td><?php echo $row["eggweight7"]; ?></td>
                <td><?php echo $row["eggweight8"]; ?></td>
				<td class="separator"></td>
				<td><?php echo $row["profitloss1"]; ?></td>
                <td><?php echo $row["profitloss2"]; ?></td>
                <td><?php echo $row["profitloss3"]; ?></td>
                <td><?php echo $row["profitloss4"]; ?></td>
                <td><?php echo $row["profitloss5"]; ?></td>
                <td><?php echo $row["profitloss6"]; ?></td>
                <td><?php echo $row["profitloss7"]; ?></td>
                <td><?php echo $row["profitloss8"]; ?></td>
				<td><?php echo $row["profitlossChick"]; ?></td>
                <td><?php echo $row["profitlossGrower"]; ?></td>
                <td><?php echo $row["total_profit_loss"]; ?></td>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="17">No data found</td></tr>
    <?php endif; ?>

</table>

</body>
</html>
