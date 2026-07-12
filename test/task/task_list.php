<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$client_id = $_SESSION['client_id'] ?? 0;

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$task_location = $time = $repetation = $task_name = '';

if ($id >= 1) {
    $query = "SELECT * FROM farm_task_list WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $task_location = $row["task_location"];
        $time = $row["time"];
        $repetation = $row["repetation"];
        $task_name = $row["task_name"];
    }
    $stmt->close();
}

$location_map = [
    '1' => 'Shead 1', '2' => 'Shead 2', '3' => 'Shead 3', '4' => 'Shead 4',
    '5' => 'Shead 5', '6' => 'Shead 6', '7' => 'Shead 7', '8' => 'Shead 8',
    'C' => 'Chick', 'G' => 'Grower', 'GM' => 'Gate_Manager',
    'FG' => 'Feed_Godown', 'EG' => 'Egg_Godown', 'TPM' => 'Tractor_production_mortality'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) && is_numeric($_POST['id']) ? intval($_POST['id']) : 0;
    $task_locations = explode(',', $_POST['task_location']);
    $time_arrays = explode(',', $_POST['time']);
    $repetation = $mysqli->real_escape_string($_POST['repetation']);
    $task_name = $mysqli->real_escape_string($_POST['task_name']);

    if ($id > 0) {
        $task_location_str = implode(', ', $task_locations);
        $time_str = implode(', ', $time_arrays);
        $query = "UPDATE farm_task_list SET task_location = ?, time = ?, repetation = ?, task_name = ? 
                  WHERE id = ? AND client_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssssii", $task_location_str, $time_str, $repetation, $task_name, $id, $client_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $query = "INSERT INTO farm_task_list (task_location, time, repetation, task_name, client_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);

        foreach ($task_locations as $location) {
            $location = trim($location);
            $mapped_location = $location_map[$location] ?? $location;

            foreach ($time_arrays as $time) {
                $time = trim($time);
                $stmt->bind_param("ssssi", $mapped_location, $time, $repetation, $task_name, $client_id);
                $stmt->execute();
            }
        }

        $stmt->close();
    }

    header("Location: https://sunfra.com/farm/test/task/task_list.php");
    exit;
}

$query = "SELECT * FROM farm_task_list WHERE client_id = ? ORDER BY task_location ASC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
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
    <h1>Task Management</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/task/task_status.php';">Go Back</button>
    </div>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
		 <p>
			<label for="task_location">Location:</label>
			<input type="text" name="task_location" id="task_location" value="<?= htmlspecialchars($task_location) ?>" required>
		</p>
		 <p>
			<label for="time">Time:</label>
			<input type="text" name="time" id="time" value="<?= htmlspecialchars($time) ?>" required>
		</p>
		<p>
            <label for="repetation">Repetation:</label>
            <input type="text" name="repetation" id="repetation" value="<?= htmlspecialchars($repetation) ?>">
        </p>
        <p>
            <label for="task_name">task_name:</label>
            <input type="text" name="task_name" id="task_name" value="<?= htmlspecialchars($task_name) ?>" >
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <h2>Records</h2>
    <table>
            <tr>
                <th>ID</th>
                <th>Task Location</th>
                <th>Time</th>
                <th>Repetation</th>
                <th>Task Name</th>
                <th>Edit</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['task_location']) ?></td>
                <td><?= htmlspecialchars($row['time']) ?></td>
                <td><?= htmlspecialchars($row['repetation']) ?></td>
                <td><?= htmlspecialchars($row['task_name']) ?></td>
				<td>
                     <a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>
    </table>
</body>
</html>
