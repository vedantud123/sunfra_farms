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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sheadNo = $tons = $check_timestamp = '';

if ($id >= 1) {
    $stmt = $mysqli->prepare("SELECT * FROM feed_shead_feeding WHERE id = ? AND client_id = ?");
    if (!$stmt) die("Prepare failed: " . $mysqli->error);

    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $sheadNo = $row["sheadNo"];
        $tons = $row["tons"];
        $check_timestamp = $row["timestamp"];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $sheadNo = isset($_POST['sheadNo']) ? trim($_POST['sheadNo']) : '';
    $tons = isset($_POST['tons']) ? floatval($_POST['tons']) : 0.0;

    if ($sheadNo === '' || $tons <= 0) {
        echo "Invalid input.";
        exit;
    }

    $mysqli->begin_transaction();

    try {
        if (!empty($check_timestamp)) {
            $stmt = $mysqli->prepare("SELECT material_name, reduced_quantity FROM feed_material_reduction_logs WHERE timestamp = ? AND client_id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $mysqli->error);

            $stmt->bind_param("si", $check_timestamp, $client_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($log_row = $result->fetch_assoc()) {
                $material_name = $log_row['material_name'];
                $reduced_quantity = $log_row['reduced_quantity'];

                $restore_stmt = $mysqli->prepare("UPDATE feed_rawmaterial SET stock = stock + ? WHERE NAME = ? AND client_id = ?");
                if (!$restore_stmt) throw new Exception("Prepare failed: " . $mysqli->error);

                $restore_stmt->bind_param("dsi", $reduced_quantity, $material_name, $client_id);
                $restore_stmt->execute();
                $restore_stmt->close();
            }

            $stmt->close();

            $delete_stmt = $mysqli->prepare("DELETE FROM feed_material_reduction_logs WHERE timestamp = ? AND client_id = ?");
            if (!$delete_stmt) throw new Exception("Prepare failed: " . $mysqli->error);

            $delete_stmt->bind_param("si", $check_timestamp, $client_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }

        $stmt = $mysqli->prepare("SELECT feed_rawmaterial_name, quantity FROM feed_formula_detail WHERE feed_formulaType = ? AND client_id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $mysqli->error);

        $stmt->bind_param("si", $sheadNo, $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No formula found for sheadNo: " . $sheadNo);
        }

        $timestamp = date('Y-m-d H:i:s');

        while ($row = $result->fetch_assoc()) {
            $material_name = $row['feed_rawmaterial_name'];
            $quantity = $row['quantity'];
            $remove_quantity = $quantity * $tons;

            $log_stmt = $mysqli->prepare("INSERT INTO feed_material_reduction_logs (material_name, reduced_quantity, timestamp, client_id) VALUES (?, ?, ?, ?)");
            if (!$log_stmt) throw new Exception("Prepare failed: " . $mysqli->error);

            $log_stmt->bind_param("sdsi", $material_name, $remove_quantity, $timestamp, $client_id);
            $log_stmt->execute();
            $log_stmt->close();

            $update_stmt = $mysqli->prepare("UPDATE feed_rawmaterial SET stock = stock - ? WHERE NAME = ? AND client_id = ?");
            if (!$update_stmt) throw new Exception("Prepare failed: " . $mysqli->error);

            $update_stmt->bind_param("dsi", $remove_quantity, $material_name, $client_id);
            $update_stmt->execute();
            $update_stmt->close();
        }

        if ($id > 0) {
            $stmt = $mysqli->prepare("UPDATE feed_shead_feeding SET sheadNo = ?, tons = ?, timestamp = ? WHERE id = ? AND client_id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $mysqli->error);

            $stmt->bind_param("sdsii", $sheadNo, $tons, $timestamp, $id, $client_id);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO feed_shead_feeding (sheadNo, tons, timestamp, client_id) VALUES (?, ?, ?, ?)");
            if (!$stmt) throw new Exception("Prepare failed: " . $mysqli->error);

            $stmt->bind_param("sdsi", $sheadNo, $tons, $timestamp, $client_id);
        }

        $stmt->execute();
        $stmt->close();

        $mysqli->commit();
        header("Location: https://sunfra.com/farm/test/feedsheadfeeding/feed_shead_feeding.php");
        exit;

    } catch (Exception $e) {
        $mysqli->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feeds To Shead</title>
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
    <h1>Feeds To Shead</h1>
	<div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php';">Go Back</button>
    </div>	
    <!-- Form Section -->
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
		<p>
			<label for="sheadNo">Shead Number:</label>
            <select name="sheadNo" id="sheadNo">
				<option value="">Select option</option>
                <option value="shead_1" <?= $sheadNo === 'shead_1' ? 'selected' : '' ?>>shead_1</option>
                <option value="shead_2" <?= $sheadNo === 'shead_2' ? 'selected' : '' ?>>shead_2</option>
				<option value="shead_3" <?= $sheadNo === 'shead_3' ? 'selected' : '' ?>>shead_3</option>
				<option value="shead_4" <?= $sheadNo === 'shead_4' ? 'selected' : '' ?>>shead_4</option>
				<option value="shead_5" <?= $sheadNo === 'shead_5' ? 'selected' : '' ?>>shead_5</option>
				<option value="shead_6" <?= $sheadNo === 'shead_6' ? 'selected' : '' ?>>shead_6</option>
				<option value="shead_7" <?= $sheadNo === 'shead_7' ? 'selected' : '' ?>>shead_7</option>
				<option value="shead_8" <?= $sheadNo === 'shead_8' ? 'selected' : '' ?>>shead_8</option>
				<option value="chick" <?= $sheadNo === 'chick' ? 'selected' : '' ?>>chick</option>
				<option value="grower" <?= $sheadNo === 'grower' ? 'selected' : '' ?>>grower</option>
			</select>
        </p>
		
        <p>
            <label for="tons">No Of Tons:</label>
            <input type="text" name="tons" id="tons" value="<?= htmlspecialchars($tons) ?>" required>
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <?php
   $query = "SELECT * FROM feed_shead_feeding WHERE client_id = ? ORDER BY id DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

    if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Id</th>
                <th>Shead No</th>
                <th>Number of Tons</th>
				<th>Date and Time</th>
				<th>Edit</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['sheadNo']) ?></td>
                    <td><?= htmlspecialchars($row['tons']) ?></td>
                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                    <td>
                        <a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No data available.</p>
    <?php endif; ?>

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>