<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$client_id = $_SESSION['client_id'] ?? 0;

date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$shead_name = $remarks = '';
$shead_name = isset($_REQUEST['shead_name']) ? $_REQUEST['shead_name'] : '';
$date = date('Y-m-d'); 

if (!empty($shead_name)) {
    $query = "SELECT * FROM egg_godown_stock WHERE shead_name = ? AND DATE(timestamp) = ? AND client_id = ? AND sale IS NULL ORDER BY CASE type_of_eggs WHEN 'Good' THEN 1 WHEN 'Damaged' THEN 2 WHEN 'Small' THEN 3 WHEN 'Big' THEN 4 ELSE 5 END;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssi", $shead_name, $date, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $egg_categories = [
        'Good' => ['trays' => 0, 'loose' => 0],
        'Damaged' => ['trays' => 0, 'loose' => 0],
        'Small' => ['trays' => 0, 'loose' => 0],
        'Big' => ['trays' => 0, 'loose' => 0]
    ];

    while ($row = $result->fetch_assoc()) {
        $shead_name = $row['shead_name'];
        $type_of_eggs = $row["type_of_eggs"];
        $no_of_eggs = $row["no_of_eggs"];
        $egg_categories[$type_of_eggs] = getTrayCount($no_of_eggs);
    }
    $stmt->close();
}

function getTrayCount($no_of_eggs) {
    $trays = floor($no_of_eggs / 30);
    $loose = $no_of_eggs % 30;
    return ['trays' => $trays, 'loose' => $loose];
}

function getTrayCount2($trays) {
    $wholeTrays = floor($trays / 30);
    $remainder = $trays % 30; 
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shead_name = $mysqli->real_escape_string($_POST['shead_name']);
    $remarks = $mysqli->real_escape_string($_POST['remarks']);

    $egg_categories = [
        'Good' => ['trays' => $_POST['good_trays'] ?? 0, 'loose' => $_POST['good_loose'] ?? 0],
        'Damaged' => ['trays' => $_POST['damaged_trays'] ?? 0, 'loose' => $_POST['damaged_loose'] ?? 0],
        'Small' => ['trays' => $_POST['small_trays'] ?? 0, 'loose' => $_POST['small_loose'] ?? 0],
        'Big' => ['trays' => $_POST['big_trays'] ?? 0, 'loose' => $_POST['big_loose'] ?? 0],
    ];

    $timestamp = date('Y-m-d H:i:s');
    $updated = false;
    $inserted = false;

    foreach ($egg_categories as $type_of_eggs => $counts) {
        $no_of_trays = intval($counts['trays']);
        $no_of_loose_Eggs = intval($counts['loose']);
        $no_of_eggs = ($no_of_trays * 30) + $no_of_loose_Eggs;

        if ($no_of_eggs > 0) {
            $query = "SELECT id, no_of_eggs FROM egg_godown_stock WHERE shead_name = ? AND type_of_eggs = ? AND DATE(timestamp) = ? AND sale IS NULL AND client_id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sssi", $shead_name, $type_of_eggs, $date, $client_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id = $row['id'];
                $existing_eggs = $row['no_of_eggs'];

              $update_query = "UPDATE egg_godown_stock SET no_of_eggs = ?, remarks = ?, timestamp = ? WHERE id = ? AND client_id = ?";
             $update_stmt = $mysqli->prepare($update_query);
             $update_stmt->bind_param("issii", $no_of_eggs, $remarks, $timestamp, $id, $client_id);


                if ($update_stmt->execute()) {
                    $updated = true;
                } else {
                    echo "Error updating: " . $update_stmt->error;
                }
                $update_stmt->close();
            } else {
             $insert_query = "INSERT INTO egg_godown_stock (shead_name, no_of_eggs, type_of_eggs, remarks, timestamp, client_id) VALUES (?, ?, ?, ?, ?, ?)";
             $insert_stmt = $mysqli->prepare($insert_query);
             $insert_stmt->bind_param("sisssi", $shead_name, $no_of_eggs, $type_of_eggs, $remarks, $timestamp, $client_id);


                if ($insert_stmt->execute()) {
                    $inserted = true;
                } else {
                    echo "Error inserting: " . $insert_stmt->error;
                }
                $insert_stmt->close();
            }
            $stmt->close();
        }
    }

    if ($inserted || $updated) {
        header("Location: https://sunfra.com/farm/test/egg_godown/egg_godown_stock.php");
        exit;
    }
}
?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egg Production Management</title>
    <style>
	body {
		font-family: Arial, sans-serif;
		background-color: #f4f4f4;
		text-align: center;
		padding: 20px;
	}

	h1 {
		color: #333;
	}

	/* Button Styling */
	.button-container {
		margin-bottom: 20px;
	}

	button {
		background-color: #007bff;
		color: white;
		padding: 10px 15px;
		border: none;
		cursor: pointer;
		border-radius: 5px;
	}

	button:hover {
		background-color: #0056b3;
	}

	/* Form Styling */
	form {
		background: white;
		padding: 20px;
		max-width: 500px;
		margin: auto;
		border-radius: 10px;
		box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
	}

	/* Input & Labels */
	p {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin: 10px 0;
	}

	label {
		flex: 1;
		font-weight: bold;
		text-align: left;
	}

	input, select {
		flex: 2;
		padding: 8px;
		border: 1px solid #ccc;
		border-radius: 5px;
	}

	/* Egg Categories */
	.egg-category {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
		margin-bottom: 10px;
	}

	.egg-category label {
		flex: 2;
		text-align: left;
		font-weight: bold;
	}

	.egg-category input {
		flex: 1;
		padding: 8px;
		border: 1px solid #ccc;
		border-radius: 5px;
		text-align: center;
	}

	/* Submit Button */
	button[type="submit"] {
		width: 100%;
		background-color: #007bff;
	}

	button[type="submit"]:hover {
		background-color: #0056b3;
	}

	/* Table Styling */
	.table-container {
		width: 40%;
		margin: 30px auto; 
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
	th {
		background-color: #007bff;
		color: white;
	}

	/* Alternating Row Colors */
	tr:nth-child(even) {
		background-color: #f9f9f9;
	}

	/* Hover effect for rows */
	tr:hover {
		background-color: #f1f1f1;
	}

	/* Actions Column */
	td a {
		text-decoration: none;
		color: #007bff;
		font-weight: bold;
	}

	td a:hover {
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
    <h1>Egg Production Management</h1>

    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/egg_godown/egg_godown.php';">Go Back</button>
    </div>  

    <form action="" method="post">
		<p>
			<label for="shead_name">Shead Name:</label>
			<select name="shead_name" id="shead_name">
				<option value="">Select option</option>
				<option value="Shead 1" <?= (isset($shead_name) && $shead_name == 'Shead 1') ? 'selected' : ''; ?>>Shead 1</option>
				<option value="Shead 2" <?= (isset($shead_name) && $shead_name == 'Shead 2') ? 'selected' : ''; ?>>Shead 2</option>
				<option value="Shead 3" <?= (isset($shead_name) && $shead_name == 'Shead 3') ? 'selected' : ''; ?>>Shead 3</option>
				<option value="Shead 4" <?= (isset($shead_name) && $shead_name == 'Shead 4') ? 'selected' : ''; ?>>Shead 4</option>
				<option value="Shead 5" <?= (isset($shead_name) && $shead_name == 'Shead 5') ? 'selected' : ''; ?>>Shead 5</option>
				<option value="Shead 6" <?= (isset($shead_name) && $shead_name == 'Shead 6') ? 'selected' : ''; ?>>Shead 6</option>
				<option value="Shead 7" <?= (isset($shead_name) && $shead_name == 'Shead 7') ? 'selected' : ''; ?>>Shead 7</option>
				<option value="Shead 8" <?= (isset($shead_name) && $shead_name == 'Shead 8') ? 'selected' : ''; ?>>Shead 8</option>
			</select>
		</p>

		<div class="egg-category">
			<label>Good:</label>
			<input type="text" name="good_trays" placeholder="Trays" value="<?= isset($egg_categories['Good']['trays']) ? $egg_categories['Good']['trays'] : ''; ?>">
			<input type="text" name="good_loose" placeholder="Loose Eggs" value="<?= isset($egg_categories['Good']['loose']) ? $egg_categories['Good']['loose'] : ''; ?>">
		</div>

		<div class="egg-category">
			<label>Damaged:</label>
			<input type="text" name="damaged_trays" placeholder="Trays" value="<?= isset($egg_categories['Damaged']['trays']) ? $egg_categories['Damaged']['trays'] : ''; ?>">
			<input type="text" name="damaged_loose" placeholder="Loose Eggs" value="<?= isset($egg_categories['Damaged']['loose']) ? $egg_categories['Damaged']['loose'] : ''; ?>">
		</div>

		<div class="egg-category">
			<label>Small:</label>
			<input type="text" name="small_trays" placeholder="Trays" value="<?= isset($egg_categories['Small']['trays']) ? $egg_categories['Small']['trays'] : ''; ?>">
			<input type="text" name="small_loose" placeholder="Loose Eggs" value="<?= isset($egg_categories['Small']['loose']) ? $egg_categories['Small']['loose'] : ''; ?>">
		</div>

		<div class="egg-category">
			<label>Big:</label>
			<input type="text" name="big_trays" placeholder="Trays" value="<?= isset($egg_categories['Big']['trays']) ? $egg_categories['Big']['trays'] : ''; ?>">
			<input type="text" name="big_loose" placeholder="Loose Eggs" value="<?= isset($egg_categories['Big']['loose']) ? $egg_categories['Big']['loose'] : ''; ?>">
		</div>

		<p>
			<label for="remarks">Remarks:</label>
			<input type="text" name="remarks" id="remarks" value="<?= isset($remarks) ? $remarks : ''; ?>">
		</p>

		<p>
			<button type="submit">Submit</button>
		</p>
	</form>


<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>

	<?php
       $query = "SELECT * FROM egg_godown_stock WHERE client_id = $client_id AND sale IS NULL ORDER BY id DESC";
		$result = $mysqli->query($query);
		if ($result && $result->num_rows > 0): ?>
			<table>
				<tr>
					<th>ID</th>
					<th>Date and Time</th>
					<th>Shead Name</th>
					<th>Number of Eggs</th>
					<th>Type of Eggs</th>
					<th>Remarks</th>
					<th>Edit</th>
				</tr>
				<?php while ($row = $result->fetch_assoc()): ?>
					<tr>
						<td><?= htmlspecialchars($row['id']) ?></td>
						<td><?= htmlspecialchars($row['timestamp']) ?></td>
						<td><?= htmlspecialchars($row['shead_name']) ?></td>
						<td><?= htmlspecialchars(getTrayCount2($row['no_of_eggs'])) ?></td>
						<td><?= htmlspecialchars($row['type_of_eggs']) ?></td>
						<td><?= htmlspecialchars($row['remarks']) ?></td>
						<td>
							<a href="?shead_name=<?= htmlspecialchars($row['shead_name']) ?>&date=<?= htmlspecialchars(date('Y-m-d', strtotime($row['timestamp']))) ?>">Edit</a>
						</td>
					</tr>
				<?php endwhile; ?>
			</table>
    <?php else: ?>
        <p>No data available.</p>
    <?php endif; ?>
	<?php
		if (isset($_GET['success']) && $_GET['success'] == 1) {
			echo "<script>alert('Thanks for submitting!');</script>";
		}
	?>

</html>
