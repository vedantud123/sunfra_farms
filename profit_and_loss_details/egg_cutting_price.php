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
$shead_name = $cutting_price = '';

if ($id >= 1) {
    $query = "SELECT * FROM egg_cutting_price WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $shead_name = $row["shead_name"];
        $cutting_price = $row["cutting_price"];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = $mysqli->real_escape_string($_POST['shead_name']);
    $cutting_price = floatval($_POST['cutting_price']); 

    if ($id > 0) {
        $query = "UPDATE egg_cutting_price SET shead_name = ?, cutting_price = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sdi", $shead_name, $cutting_price, $id);
    } else {
        $query = "INSERT INTO egg_cutting_price (shead_name, cutting_price) VALUES (?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sd", $shead_name, $cutting_price);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/profit_and_loss_details/egg_cutting_price.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$query = "SELECT * FROM egg_cutting_price";
$result = $mysqli->query($query);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egg Price Per Piece</title>
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
    <h1>Egg Price Per Piece</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/profitandloss_details.php';">Go Back</button>
    </div>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="shead_name">Shesd Name:</label>
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
            <label for="cutting_price">Cutting Price:</label>
            <input type="text" name="cutting_price" id="cutting_price" value="<?= htmlspecialchars($cutting_price) ?>" required>
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <h2>Records</h2>
    <table>
            <tr>
                <th>ID</th>
                <th>Shead Name</th>
                <th>Cutting Price(Per Egg)</th>
                <th>Edit</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['shead_name']) ?></td>
                <td><?= htmlspecialchars($row['cutting_price']) ?></td>
				<td>
					<a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>
    </table>
</body>
</html>
