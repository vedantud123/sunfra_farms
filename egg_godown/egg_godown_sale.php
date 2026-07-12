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
$shead_name = $no_of_eggs = $type_of_eggs = $remarks = $sale = $sale_price = '';

if ($id >= 1) {
    $query = "SELECT * FROM egg_godown_stock WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $shead_name = $row["shead_name"];
        $type_of_eggs = $row["type_of_eggs"];
        $sale = $row["sale"];
        $sale_price = $row["sale_price"];
        $remarks = $row["remarks"];
    }
    $stmt->close();
}

function getTrayCount($trays) {
    $wholeTrays = floor($trays / 30);
    $remainder = $trays % 30; 
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = $mysqli->real_escape_string($_POST['shead_name']);
    $no_of_trays = $mysqli->real_escape_string($_POST['no_of_trays']);
	$no_of_loose_eggs = $mysqli->real_escape_string($_POST['no_of_loose_eggs'] ?? 0);
    $no_of_eggs = is_numeric($no_of_trays) ? ($no_of_trays * 30) + $no_of_loose_eggs : 0; 
    $type_of_eggs = $mysqli->real_escape_string($_POST['type_of_eggs']);
    $sale =  $mysqli->real_escape_string($_POST['sale']);
    $sale_price =  $mysqli->real_escape_string($_POST['sale_price']);
    $remarks = $mysqli->real_escape_string($_POST['remarks']);

    if ($id > 0) {
        $query = "UPDATE egg_godown_stock SET shead_name = ?, no_of_eggs = ?, type_of_eggs = ?, sale = ?, sale_price = ?, remarks = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sissisi", $shead_name, $no_of_eggs, $type_of_eggs, $sale, $sale_price, $remarks, $id);
    } else {
        $timestamp = date('Y-m-d H:i:s');
        $query = "INSERT INTO egg_godown_stock (shead_name, no_of_eggs, type_of_eggs, sale, sale_price, remarks, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sisssss", $shead_name, $no_of_eggs, $type_of_eggs, $sale, $sale_price, $remarks, $timestamp);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/egg_godown/egg_godown_sale.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$query = "SELECT * FROM egg_godown_stock WHERE sale IS NOT NULL ORDER BY id DESC";
$result = $mysqli->query($query);
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
	<script>
        window.onload = function () {
            fetch('https://sunfra.com/farm/egg_godown/egg_godown_status.php')
                .then(response => {
                    if (!response.ok) {
                        console.error('Script request failed.');
                    }
                })
                .catch(error => console.error('Error:', error));
        };
    </script>
</head>
<body>
    <h1>Egg Sale Management</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/egg_godown/egg_godown.php';">Go Back</button>
    </div>
		<div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/egg_godown/egg_godown_sale_damage.php';">Damage Eggs During Sale</button>
    </div> 
	<?php
        $allowedUsers = ['vedant', 'divya', 'venkat'];

        if (isset($_SESSION['username']) && in_array($_SESSION['username'], $allowedUsers)) {
            echo '
            <div class="add-data">
                <a href="remove_data.php">Remove Data</a>
            </div>';
        }
	?>
    <form action="" method="post">
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
            <label for="no_of_trays">No Of Trays:</label>
            <input type="text" name="no_of_trays" id="no_of_trays" value="<?= htmlspecialchars($no_of_trays) ?>" required>
        </p>
		<p>
            <label for="no_of_loose_eggs">No Of Loose Eggs:</label>
            <input type="text" name="no_of_loose_eggs" id="no_of_loose_eggs" value="<?= htmlspecialchars($no_of_loose_eggs) ?>">
        </p>
        <p>
            <label for="type_of_eggs">Type of Eggs:</label>
            <select name="type_of_eggs" id="type_of_eggs" required>
                <option>Select Option</option>
                <option value="Good" <?= $type_of_eggs === 'Good' ? 'selected' : '' ?>>Good</option>
                <option value="Damaged" <?= $type_of_eggs === 'Damaged' ? 'selected' : '' ?>>Damaged</option>
				<option value="Small" <?= $type_of_eggs === 'Small' ? 'selected' : '' ?>>Small</option>
                <option value="Big" <?= $type_of_eggs === 'Big' ? 'selected' : '' ?>>Big</option>
            </select>
        </p>
        <p>
            <label for="sale">Sale To:</label>
            <input type="text" name="sale" id="sale" value="<?= htmlspecialchars($sale) ?>" >
        </p>
        <p>
            <label for="sale_price">Sale Price:</label>
            <input type="text" name="sale_price" id="sale_price" value="<?= htmlspecialchars($sale_price) ?>" >
        </p>
        <p>
            <label for="remarks">Remarks:</label>
            <input name="remarks" id="remarks" rows="4"><?= htmlspecialchars($remarks) ?></input>
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
                <th>No of Eggs</th>
                <th>Type of Eggs</th>
                <th>Sale</th>
                <th>Sale Price</th>
                <th>Remarks</th>
                <th>Edit</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['timestamp']) ?></td>
                <td><?= htmlspecialchars($row['shead_name']) ?></td>
                <td><?= htmlspecialchars(getTrayCount($row['no_of_eggs'])) ?></td>
                <td><?= htmlspecialchars($row['type_of_eggs']) ?></td>
                <td><?= htmlspecialchars($row['sale']) ?></td>
                <td><?= htmlspecialchars($row['sale_price']) ?></td>
                <td><?= htmlspecialchars($row['remarks']) ?></td>
				<td>
                        <a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>
    </table>
</body>
</html>
