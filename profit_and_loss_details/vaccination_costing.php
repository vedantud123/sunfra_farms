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

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$vaccine_name = $vaccine_cost = $labour_cost = $shead_number = '';

if ($id >= 1) {
    $query = "SELECT * FROM vaccination_costing WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $shead_number = $row["shead_number"];
        $vaccine_name = $row["vaccine_name"];
        $vaccine_cost = $row["vaccine_cost"];
        $labour_cost = $row["labour_cost"];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_number = $_POST['shead_number'];
    $vaccine_name = $_POST['vaccine_name'];
    $labour_cost = $_POST['labour_cost'];
    $vaccine_cost = $_POST['vaccine_cost'];
    $timestamp = date('Y-m-d H:i:s');

    if (empty($shead_number) || empty($vaccine_name) || empty($labour_cost) || empty($vaccine_cost)) {
        echo "All fields are required.";
        exit;
    }

    if ($id > 0) {
        $query = "UPDATE vaccination_costing SET shead_number = ?, vaccine_name = ?, labour_cost = ?, vaccine_cost = ?, timestamp = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssssi", $shead_number, $vaccine_name, $labour_cost, $vaccine_cost, $timestamp, $id);
    } else {
        $query = "INSERT INTO vaccination_costing (shead_number, vaccine_name, labour_cost, vaccine_cost, timestamp) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssss", $shead_number, $vaccine_name, $labour_cost, $vaccine_cost, $timestamp);
    }

    if ($stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        header("Location: https://sunfra.com/farm/profit_and_loss_details/vaccination_costing.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Summary</title>
    <style>
    /* General Body Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        margin: 0;
        padding: 0;
        text-align: center;
    }

    /* Container for Centered Content */
    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 10px 15px;
        text-align: center;
    }

    /* Form Styles */
    form {
        display: inline-block;
        text-align: left;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    form p {
        margin-bottom: 15px;
    }

    form label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }

    form input, form select, form button {
        width: 100%;
        padding: 10px;
        font-size: 14px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
    }

    form button {
        background-color: #007bff;
        color: white;
        font-weight: bold;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    form button:hover {
        background-color: #0056b3;
    }

    /* Back Button */
    .button-container {
        margin-bottom: 20px;
    }

    .button-container button {
        background-color: #6c757d;
        color: white;
        font-size: 14px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .button-container button:hover {
        background-color: #5a6268;
    }

    /* Title Styles */
    h1 {
        color: #007bff;
        margin: 20px 0;
        font-size: 24px;
    }

    /* Table Styles */
    .table-container {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        overflow-x: auto; /* Adds horizontal scroll for small screens */
    }

    table {
        border-collapse: collapse;
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
        background: white;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    table th, table td {
        border: 1px solid #ddd;
        padding: 10px;
        font-size: 14px;
        text-align: center;
    }

    table th {
        background-color: #007bff;
        color: white;
        font-weight: bold;
    }

    table tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    /* Responsive Design for Smaller Screens */
    @media (max-width: 600px) {
        h1 {
            font-size: 20px;
        }

        form {
            width: 90%;
            padding: 15px;
        }

        table th, table td {
            font-size: 12px;
            padding: 8px;
        }
    }
</style>
</head>
<body>
    <h1>Vaccination Summary</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/profitandloss_details.php';">Go Back</button>
    </div>
    <form action="" method="post">
		<input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
		<p>
			<label for="shead_number">Shead Number:</label>
            <select name="shead_number" id="shead_number">
				<option value="">Select option</option>
                <option value="Shead 1" <?= $shead_number === 'Shead 1' ? 'selected' : '' ?>>Shead 1</option>
                <option value="Shead 2" <?= $shead_number === 'Shead 2' ? 'selected' : '' ?>>Shead 2</option>
				<option value="Shead 3" <?= $shead_number === 'Shead 3' ? 'selected' : '' ?>>Shead 3</option>
				<option value="Shead 4" <?= $shead_number === 'Shead 4' ? 'selected' : '' ?>>Shead 4</option>
				<option value="Shead 5" <?= $shead_number === 'Shead 5' ? 'selected' : '' ?>>Shead 5</option>
				<option value="Shead 6" <?= $shead_number === 'Shead 6' ? 'selected' : '' ?>>Shead 6</option>
				<option value="Shead 7" <?= $shead_number === 'Shead 7' ? 'selected' : '' ?>>Shead 7</option>
				<option value="Shead 8" <?= $shead_number === 'Shead 8' ? 'selected' : '' ?>>Shead 8</option>
				<option value="Chick" <?= $shead_number === 'Chick' ? 'selected' : '' ?>>Chick</option>
				<option value="Grower" <?= $shead_number === 'Grower' ? 'selected' : '' ?>>Grower</option>
			</select>
        </p>
		
		<p>
			<label for="vaccine_name">Vaccine Name:</label>
			<input type="text"  name="vaccine_name" id="vaccine_name" value="<?= htmlspecialchars($vaccine_name) ?>" >
		</p>

		<p>
			<label for="vaccine_cost">Vaccine Cost:</label>
			<input type="text"  name="vaccine_cost" id="vaccine_cost" value="<?= htmlspecialchars($vaccine_cost) ?>" >
		</p>

		<p>
			<label for="labour_cost">Labour Cost:</label>
			<input type="text"  name="labour_cost" id="labour_cost" value="<?= htmlspecialchars($labour_cost) ?>" >
		</p>

		<p>
			<button type="submit">Submit</button>
		</p>
	</form>

    <?php
		$query = "SELECT * FROM vaccination_costing ORDER BY id DESC";
		$result = $mysqli->query($query);
		if ($result && $result->num_rows > 0): ?>
			<table border="1" cellpadding="5" cellspacing="0">
				<tr>
					<th>Shead Name</th>
					<th>Vaccine Name</th>
					<th>Vaccine Cost</th>
					<th>Labour Cost</th>
					<th>Date & Time</th>
					<th>Edit</th>
				</tr>
				<?php while ($row = $result->fetch_assoc()): ?>
					<tr>
						<td><?= htmlspecialchars($row['shead_number']) ?></td>
						<td><?= htmlspecialchars($row['vaccine_name']) ?></td>
						<td><?= htmlspecialchars($row['vaccine_cost']) ?></td>
						<td><?= htmlspecialchars($row['labour_cost']) ?></td>
						<td><?= htmlspecialchars($row['timestamp']) ?></td>
						<td>
							<a href="?id=<?= $row['id'] ?>">Edit</a>
						</td>
					</tr>
				<?php endwhile; ?>
			</table>
		<?php else: ?>
			<p>No data available to display.</p>
		<?php endif; ?>

</div>
</body>
</html>

<?php $mysqli->close(); ?>