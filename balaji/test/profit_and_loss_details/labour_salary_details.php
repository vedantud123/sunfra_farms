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
$salary = $name = $position = '';

// Fetch data to edit
if ($id >= 1) {
    $stmt = $mysqli->prepare("SELECT * FROM labour_salaries WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $salary = $row["salary"];
        $name = $row["name"];
        $position = $row["position"];
    }
    $stmt->close();
}

// Form submission logic
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
        $stmt = $mysqli->prepare("UPDATE labour_salaries SET salary = ?, name = ?, position = ? WHERE id = ? AND client_id = ?");
        $stmt->bind_param("sssii", $salary, $name, $position, $id, $client_id);
    } else {
        // Prevent duplicate entry for the same tenant
        $checkStmt = $mysqli->prepare("SELECT id FROM labour_salaries WHERE name = ? AND position = ? AND client_id = ?");
        $checkStmt->bind_param("ssi", $name, $position, $client_id);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "<script>alert('Duplicate entry: This person and position already exist.'); window.history.back();</script>";
            $checkStmt->close();
            $mysqli->close();
            exit;
        }
        $checkStmt->close();

        $stmt = $mysqli->prepare("INSERT INTO labour_salaries (name, salary, position, client_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $salary, $position, $client_id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        header("Location: https://sunfra.com/farm/test/profit_and_loss_details/labour_salary_details.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Dropdown options from labour_master scoped by tenant
$sql = "SELECT name FROM labour_master 
        WHERE client_id = ? AND name NOT IN 
        (SELECT name FROM labour_salaries WHERE client_id = ?) 
        ORDER BY name";
$name_stmt = $mysqli->prepare($sql);
$name_stmt->bind_param("ii", $client_id, $client_id);
$name_stmt->execute();
$name_result = $name_stmt->get_result();

// Salary listing table scoped by tenant
$data_stmt = $mysqli->prepare("SELECT * FROM labour_salaries WHERE client_id = ?");
$data_stmt->bind_param("i", $client_id);
$data_stmt->execute();
$data_result = $data_stmt->get_result();
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
    <h1>Salary Details</h1>
    <div class="button-container">
		<button onclick="window.location.href='https://sunfra.com/farm/test/test_show_profit_loss_details.php';">Go Back</button>
    </div>
    <form action="" method="post">
		<input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

		<p>
			<label for="name">Select Name:</label>
		<select name="name" id="name" required>
    <option value="">--Select--</option>
    <?php
        if ($name_result->num_rows > 0) {
            while ($row = $name_result->fetch_assoc()) {
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

    <?php if ($data_result && $data_result->num_rows > 0): ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Salary</th>
            <th>Position</th>
            <th>Edit</th>
        </tr>
        <?php while ($row = $data_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['salary']) ?></td>
                <td><?= htmlspecialchars($row['position']) ?></td>
                <td><a href="?id=<?= $row['id'] ?>">Edit</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No data available to display.</p>
<?php endif; ?>


</div>

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>

<?php $mysqli->close(); ?>