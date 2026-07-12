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
$shead_name = "";
$tray_1 = $tray_2 = $tray_3 = $tray_4 = $tray_5 = $tray_6 = $tray_7 = $tray_8 = $average = 0;
$current_date = date('Y-m-d');

if ($id >= 1) {
    $query = "SELECT * FROM egg_weight WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $shead_name = $row["shead_name"];
            $tray_1 = $row["tray_1"];
            $tray_2 = $row["tray_2"];
            $tray_3 = $row["tray_3"];
            $tray_4 = $row["tray_4"];
            $tray_5 = $row["tray_5"];
            $tray_6 = $row["tray_6"];
            $tray_7 = $row["tray_7"];
            $tray_8 = $row["tray_8"];
            $average = $row["average"];
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['form_type'] === 'shed_form') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = $mysqli->real_escape_string($_POST['shead_name']);
    
    $tray_1 = isset($_POST['tray_1']) ? intval($_POST['tray_1']) : 0;
    $tray_2 = isset($_POST['tray_2']) ? intval($_POST['tray_2']) : 0;
    $tray_3 = isset($_POST['tray_3']) ? intval($_POST['tray_3']) : 0;
    $tray_4 = isset($_POST['tray_4']) ? intval($_POST['tray_4']) : 0;
    $tray_5 = isset($_POST['tray_5']) ? intval($_POST['tray_5']) : 0;
    $tray_6 = isset($_POST['tray_6']) ? intval($_POST['tray_6']) : 0;
    $tray_7 = isset($_POST['tray_7']) ? intval($_POST['tray_7']) : 0;
    $tray_8 = isset($_POST['tray_8']) ? intval($_POST['tray_8']) : 0;

    $average = number_format(($tray_1 + $tray_2 + $tray_3 + $tray_4 + $tray_5 + $tray_6 + $tray_7 + $tray_8) / 240.0, 2, '.', '');

    if ($id > 0) {
        $query = "UPDATE egg_weight SET shead_name = ?, tray_1 = ?, tray_2 = ?, tray_3 = ?, tray_4 = ?, tray_5 = ?, tray_6 = ?, tray_7 = ?, tray_8 = ?, average = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("siiiiiiiidi", $shead_name, $tray_1, $tray_2, $tray_3, $tray_4, $tray_5, $tray_6, $tray_7, $tray_8, $average, $id);
        }
    } else {
        $current_date = date("Y-m-d");
        $query = "INSERT INTO egg_weight (date, shead_name, tray_1, tray_2, tray_3, tray_4, tray_5, tray_6, tray_7, tray_8, average) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ssiiiiiiiid", $current_date, $shead_name, $tray_1, $tray_2, $tray_3, $tray_4, $tray_5, $tray_6, $tray_7, $tray_8, $average);
        }
    }

    if ($stmt && $stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/egg_godown/egg_weight.php");
        exit;
    } else {
        echo "Error: " . ($stmt ? $stmt->error : $mysqli->error);
    }
}



if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['form_type'] === 'date_form') {
    $selected_date = $_POST['selected_date'] ?? date('Y-m-d');
   
}else{
	 $selected_date = date('Y-m-d');
}

$query = "SELECT * FROM egg_weight WHERE `date` = ?";
$stmt = $mysqli->prepare($query); 
$stmt->bind_param("s", $selected_date); 
$stmt->execute();
$result = $stmt->get_result();


#$query = "SELECT * FROM egg_weight ";
#$result = $mysqli->query($query);
?>

<!DOCTYPE html>
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
</head>
<body>
    <h1>Egg Weight Management</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/egg_godown/egg_godown.php';">Go Back</button>
    </div>
    <form action="" method="post">
		<input type="hidden" name="form_type" value="shed_form">
		<input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">   

		<p>
            <label for="shead_name">Shead Name:</label>
            <select name="shead_name" id="shead_name">
                <option value="">Select option</option>
                <option value="Shead 1" <?= $shead_name === 'Shead 1' ? 'selected' : '' ?>>Shead 1</option>
                <option value="Shead 2" <?= $shead_name === 'Shead 2' ? 'selected' : '' ?>>Shead 2</option>
                <option value="Shead 3" <?= $shead_name === 'Shead 3' ? 'selected' : '' ?>>Shead 3</option>
                <option value="Shead 4" <?= $shead_name === 'Shead 4' ? 'selected' : '' ?>>Shead 4</option>
                <option value="Shead 5" <?= $shead_name === 'Shead 5' ? 'selected' : '' ?>>Shead 5</option>
                <option value="Shead 6" <?= $shead_name === 'Shead 6' ? 'selected' : '' ?>>Shead 6</option>
                <option value="Shead 7" <?= $shead_name === 'Shead 7' ? 'selected' : '' ?>>Shead 7</option>
                <option value="Shead 8" <?= $shead_name === 'Shead 8' ? 'selected' : '' ?>>Shead 8</option>
            </select>
        </p>
        <p>
            <label for="tray_1">Tray 1:</label>
            <input type="text" name="tray_1" id="tray_1" value="<?= htmlspecialchars($tray_1) ?>" required>
        </p>
		 <p>
            <label for="tray_2">Tray 2:</label>
            <input type="text" name="tray_2" id="tray_2" value="<?= htmlspecialchars($tray_2) ?>" required>
        </p> <p>
            <label for="tray_3">Tray 3:</label>
            <input type="text" name="tray_3" id="tray_3" value="<?= htmlspecialchars($tray_3) ?>" required>
        </p> <p>
            <label for="tray_4">Tray 4:</label>
            <input type="text" name="tray_4" id="tray_4" value="<?= htmlspecialchars($tray_4) ?>" required>
        </p> <p>
            <label for="tray_5">Tray 5:</label>
            <input type="text" name="tray_5" id="tray_5" value="<?= htmlspecialchars($tray_5) ?>" required>
        </p> <p>
            <label for="tray_6">Tray 6:</label>
            <input type="text" name="tray_6" id="tray_6" value="<?= htmlspecialchars($tray_6) ?>" required>
        </p> <p>
            <label for="tray_7">Tray 7:</label>
            <input type="text" name="tray_7" id="tray_7" value="<?= htmlspecialchars($tray_7) ?>" required>
        </p> <p>
            <label for="tray_8">Tray 8:</label>
            <input type="text" name="tray_8" id="tray_8" value="<?= htmlspecialchars($tray_8) ?>" required>
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <h2>Records</h2>
	<form method="POST">
	    <input type="hidden" name="form_type" value="date_form">
			<p>
				<label for="selected_date">Select Date :</label>
				<input type="date" name="selected_date" id="selected_date" value="<?= htmlspecialchars($selected_date) ?>">
			</p>	
			<button type="submit">Submit</button>

	</form>
    <table>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Shead Name</th>
                <th>Tray 1</th>
                <th>Tray 2</th>
                <th>Tray 3</th>
                <th>Tray 4</th>
                <th>Tray 5</th>
                <th>Tray 6</th>
                <th>Tray 7</th>
                <th>Tray 8</th>
                <th>Average</th>
                <th>EDIT</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['shead_name']) ?></td>
                <td><?= htmlspecialchars($row['tray_1']) ?></td>
                <td><?= htmlspecialchars($row['tray_2']) ?></td>
                <td><?= htmlspecialchars($row['tray_3']) ?></td>
                <td><?= htmlspecialchars($row['tray_4']) ?></td>
                <td><?= htmlspecialchars($row['tray_5']) ?></td>
                <td><?= htmlspecialchars($row['tray_6']) ?></td>
                <td><?= htmlspecialchars($row['tray_7']) ?></td>
                <td><?= htmlspecialchars($row['tray_8']) ?></td>
                <td><?= htmlspecialchars($row['average']) ?></td>
				<td>
                        <a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>
    </table>
</body>
</html>
