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
$salary = $name = $position = '';

if ($id >= 1) {
    $query = "SELECT * FROM labour_salaries WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $salary = $row["salary"];
        $name = $row["name"];
        $position = $row["position"];
    }
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $salary = $_POST['salary'];
    $name = $_POST['name'];
    $position = $_POST['position'];

    if (empty($name) || empty($salary) || empty($position)) {
        echo "All fields are required.";
        exit;
    }

    if ($id > 0) {
        $query = "UPDATE labour_salaries SET salary = ?, name = ?, position = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssi", $salary, $name, $position, $id);
    } else {
        $checkQuery = "SELECT id FROM labour_salaries WHERE name = ? AND position = ?";
        $checkStmt = $mysqli->prepare($checkQuery);
        $checkStmt->bind_param("ss", $name, $position);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "<script>alert('Duplicate entry: This person and position already exist.'); window.history.back();</script>";
            $checkStmt->close();
            $mysqli->close();
            exit;
        }
        $checkStmt->close();

        $query = "INSERT INTO labour_salaries (name, salary, position) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $name, $salary, $position);
    }

    if ($stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        header("Location: https://sunfra.com/farm/profit_and_loss_details/labour_salary_details.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$sql = "SELECT name FROM labour_master 
        WHERE name NOT IN (SELECT name FROM labour_salaries) 
        ORDER BY name";
$result = $mysqli->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Details</title>
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
    <h1>Salary Details</h1>
    <div class="button-container">
		<button onclick="window.location.href='https://sunfra.com/farm/profitandloss_details.php';">Go Back</button>
    </div>
    <form action="" method="post">
		<input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

		<p>
			<label for="name">Select Name:</label>
			<select name="name" id="name" required>
				<option value="">--Select--</option>
				<?php
					if ($result->num_rows > 0) {
						while ($row = $result->fetch_assoc()) {
							$selected = ($name == $row['name']) ? 'selected' : '';
							echo '<option value="' . htmlspecialchars($row['name']) . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
						}
					} else {
						echo '<option value="">No names found</option>';
					}
				?>
			</select>
		</p>

		<p>
			<label for="salary">Salary (₹):</label>
			<input type="text"  name="salary" id="salary" value="<?= htmlspecialchars($salary) ?>" required>
		</p>

		<p>
			<label for="position">Position:</label>
			<select name="position" id="position" required>
				<o
				ption value="">--Select--</option>
				<option value="Labour" <?= ($position == "Labour") ? 'selected' : '' ?>>Labour</option>
				<option value="Manager" <?= ($position == "Manager") ? 'selected' : '' ?>>Manager</option>
				<option value="Supervisor" <?= ($position == "Supervisor") ? 'selected' : '' ?>>Supervisor</option>
			</select>
		</p>

		<p>
			<button type="submit">Submit</button>
		</p>
	</form>

    <?php
		$query = "SELECT * FROM labour_salaries";
		$result = $mysqli->query($query);
		if ($result && $result->num_rows > 0): ?>
			<table border="1" cellpadding="5" cellspacing="0">
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Salary</th>
					<th>Position</th>
					<th>Edit</th>
				</tr>
				<?php while ($row = $result->fetch_assoc()): ?>
					<tr>
						<td><?= htmlspecialchars($row['id']) ?></td>
						<td><?= htmlspecialchars($row['name']) ?></td>
						<td><?= htmlspecialchars($row['salary']) ?></td>
						<td><?= htmlspecialchars($row['position']) ?></td>
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