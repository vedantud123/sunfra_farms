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
$place = $name = $description = $quantity = '';

if ($id >= 1) {
    $query = "SELECT * FROM sanitization WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $place = $row["place"];
        $name = $row["name"];
        $quantity = $row["quantity"];
        $description = $row["description"];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $place = $mysqli->real_escape_string($_POST['place']);
    $name = $mysqli->real_escape_string($_POST['name']);
    $quantity = floatval($_POST['quantity']);  
    $description = $mysqli->real_escape_string($_POST['description']);
    
    if ($id > 0) {
        $query = "UPDATE sanitization SET place = ?, name = ?, quantity = ?, description = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssdsi", $place, $name, $quantity, $description, $id); 
    } else {
        $timestamp = date('Y-m-d H:i:s');
        $query = "INSERT INTO sanitization (place, name, quantity, description, timestamp) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssdss", $place, $name, $quantity, $description, $timestamp); 
        
        $water_medicine_query = "UPDATE feed_rawMaterial SET stock = stock - ? WHERE name = ?";
        $water_medicine_stmt = $mysqli->prepare($water_medicine_query);
        $water_medicine_stmt->bind_param("ds", $quantity, $name);  
        
        if (!$water_medicine_stmt->execute()) {
        }
        $water_medicine_stmt->close();
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/sanitization/sanitization.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$query = "SELECT * FROM sanitization ORDER BY id DESC";
$result = $mysqli->query($query);

$raw_material_query = "SELECT * FROM `feed_rawMaterial` WHERE TYPE = 'Water Medicine' order by name ASC"; 
$raw_material_result = $mysqli->query($raw_material_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Medicine</title>
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

	form {
		background: white;
		padding: 20px;
		max-width: 500px;
		margin: auto;
		border-radius: 10px;
		box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
	}

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

	button[type="submit"] {
		width: 100%;
		background-color: #007bff;
	}

	button[type="submit"]:hover {
		background-color: #0056b3;
	}

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

	tr:nth-child(even) {
		background-color: #f9f9f9;
	}

	tr:hover {
		background-color: #f1f1f1;
	}

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
    <h1>Water Medicine</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/feed_plant_supervisor.php';">Go Back</button>
    </div>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="place">Shead Name:</label>
            <select name="place" id="place">
                <option value="">Select option</option>
                <option value="Shead 1" <?= $place === 'Shead 1' ? 'selected' : '' ?>>Shead 1</option>
                <option value="Shead 2" <?= $place === 'Shead 2' ? 'selected' : '' ?>>Shead 2</option>
                <option value="Shead 3" <?= $place === 'Shead 3' ? 'selected' : '' ?>>Shead 3</option>
                <option value="Shead 4" <?= $place === 'Shead 4' ? 'selected' : '' ?>>Shead 4</option>
                <option value="Shead 5" <?= $place === 'Shead 5' ? 'selected' : '' ?>>Shead 5</option>
                <option value="Shead 6" <?= $place === 'Shead 6' ? 'selected' : '' ?>>Shead 6</option>
                <option value="Shead 7" <?= $place === 'Shead 7' ? 'selected' : '' ?>>Shead 7</option>
                <option value="Shead 8" <?= $place === 'Shead 8' ? 'selected' : '' ?>>Shead 8</option>
                <option value="Chick" <?= $place === 'Chick' ? 'selected' : '' ?>>Chick</option>
				<option value="Grower" <?= $place === 'Grower' ? 'selected' : '' ?>>Grower</option>
				<option value="other_place" <?= $place === 'other_place' ? 'selected' : '' ?>>Other Place</option>

            </select>
        </p>
		<p>
            <label for="name">Name Of Medicine:</label>
            <select name="name" id="name">
				<option value="">Select option</option>
				<?php
				if ($raw_material_result->num_rows > 0) {
					while ($row = $raw_material_result->fetch_assoc()) {
						$selected = ($name === $row['name']) ? 'selected' : '';
						echo "<option value='" . htmlspecialchars($row['name']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
					}
				} else {
					echo "<option value=''>No data found</option>";
				}
				?>
			</select>
        </p>
		<p>
            <label for="quantity">Quantity Used:</label>
            <input type="text" name="quantity" id="quantity" value="<?= htmlspecialchars($quantity) ?>">
        </p>
        <p>
            <label for="description">Description:</label>
            <input type="text" name="description" id="description" value="<?= htmlspecialchars($description) ?>" >
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>
    <h2>Records</h2>
    <table>
            <tr>
                <th>ID</th>
                <th>Date & Time</th>
                <th>Shead Name</th>
                <th>Name</th>
                <th>Quantity</th>
                <th>Description</th>
                <th>Edit</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['timestamp']) ?></td>
                <td><?= htmlspecialchars($row['place']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
				<td>
                     <a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>
    </table>
</body>
</html>
