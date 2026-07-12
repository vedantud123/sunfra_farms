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
$vaccine_name = $vaccine_cost = $labour_cost = $shead_number = '';

if ($id >= 1) {
    $query = "SELECT * FROM vaccination_costing WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id, $client_id);
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
        $query = "UPDATE vaccination_costing SET shead_number = ?, vaccine_name = ?, labour_cost = ?, vaccine_cost = ?, timestamp = ? WHERE id = ? AND client_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssssssi", $shead_number, $vaccine_name, $labour_cost, $vaccine_cost, $timestamp, $id, $client_id);
    } else {
        $query = "INSERT INTO vaccination_costing (shead_number, vaccine_name, labour_cost, vaccine_cost, timestamp, client_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssssi", $shead_number, $vaccine_name, $labour_cost, $vaccine_cost, $timestamp, $client_id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        header("Location: https://sunfra.com/farm/test/profit_and_loss_details/vaccination_costing.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$query = "SELECT * FROM vaccination_costing WHERE client_id = ? ORDER BY id DESC";
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
    <title>Vaccination Summary</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
   /* Reset box-sizing globally */
*, *::before, *::after {
  box-sizing: border-box !important;
}

/* Body & general typography */
html, body {
  margin: 0 !important;
  padding: 20px !important;
  font-family: Arial, sans-serif !important;
  background-color: #f8f9fa !important;
  color: #333 !important;
  text-align: center !important;
  line-height: 1.5 !important;
}

/* Titles */
h1 {
  font-weight: 700 !important;
  font-size: 24px !important;
  color: #007bff !important;
  margin: 20px 0 !important;
  text-align: center !important;
  line-height: 1.2 !important;
}

/* Button container */
.button-container {
  margin-bottom: 20px !important;
  text-align: center !important;
}

.button-container button {
  background-color: #6c757d !important;
  color: white !important;
  font-size: 14px !important;
  padding: 10px 20px !important;
  border: none !important;
  border-radius: 5px !important;
  cursor: pointer !important;
  transition: background-color 0.3s ease !important;
  white-space: nowrap !important;
  user-select: none !important;
}

.button-container button:hover {
  background-color: #5a6268 !important;
}

/* Form styling */
form {
  background: white !important;
  padding: 20px !important;
  max-width: 400px !important;
  margin: 0 auto 40px !important;
  border: 1px solid #ddd !important;
  border-radius: 10px !important;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1) !important;
  font-family: Arial, sans-serif !important;
  text-align: left !important;
}

/* Form element spacing */
form p {
  margin-bottom: 15px !important;
}

/* Form labels */
form label {
  display: block !important;
  font-weight: bold !important;
  margin-bottom: 5px !important;
  color: #555 !important;
}

/* Form inputs/select/buttons */
form input,
form select,
form button {
  width: 100% !important;
  padding: 10px !important;
  font-size: 14px !important;
  border: 1px solid #ddd !important;
  border-radius: 5px !important;
  box-sizing: border-box !important;
  color: #000 !important;
}

/* Form submit button */
form button {
  background-color: #007bff !important;
  color: white !important;
  font-weight: bold !important;
  border: none !important;
  cursor: pointer !important;
  transition: background-color 0.3s ease !important;
}

form button:hover {
  background-color: #0056b3 !important;
}

/* Table container with horizontal scroll */
.table-container {
  display: flex !important;
  justify-content: center !important;
  margin-top: 20px !important;
  overflow-x: auto !important;
}

/* Table styles */
table {
  border-collapse: collapse !important;
  width: 100% !important;
  max-width: 800px !important;
  margin: 0 auto !important;
  background: white !important;
  text-align: center !important;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1) !important;
  border-radius: 8px !important;
  overflow: hidden !important;
  font-size: 14px !important;
}

table th,
table td {
  border: 1px solid #ddd !important;
  padding: 10px !important;
  vertical-align: middle !important;
  text-align: center !important;
  color: #333 !important;
}

table th {
  background-color: #007bff !important;
  color: white !important;
  font-weight: bold !important;
  white-space: nowrap !important;
}

tr:nth-child(even) {
  background-color: #f8f9fa !important;
}

tr:hover {
  background-color: #f1f1f1 !important;
}

td a {
  color: #007bff !important;
  font-weight: bold !important;
  text-decoration: none !important;
}

td a:hover {
  text-decoration: underline !important;
}

/* Sidebar styles */
.sidebar {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 250px !important;
  height: 100vh !important;
  background-color: #0d6efd; 
  color: #fff !important;
  padding: 20px 10px !important;
  overflow-y: auto !important;
  box-sizing: border-box !important;
  transition: width 0.3s ease !important;
  z-index: 1050 !important;
}

.sidebar.collapsed {
  width: 50px !important;
  padding: 20px 5px !important;
  overflow-x: hidden !important;
}

.sidebar.collapsed .sidebar-text {
  display: none !important;
}

/* Main content */
.main-content {
  margin-left: 250px !important;
  padding: 20px !important;
  box-sizing: border-box !important;
  min-height: 100vh !important;
  transition: margin-left 0.3s ease !important;
  text-align: left !important;
}

.main-content.collapsed {
  margin-left: 50px !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%) !important;
    width: 250px !important;
  }
  .sidebar.show {
    transform: translateX(0) !important;
  }
  .main-content,
  .main-content.collapsed {
    margin-left: 0 !important;
    padding: 10px !important;
    text-align: left !important;
  }
}
</style>
</head>
<body>
<div>
	<aside id="sidebar" class="sidebar bg-blue-800 text-white p-4">
	<div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="text-xl font-semibold sidebar-text"><?= htmlspecialchars($clientName) ?></h2>
		<button id="collapse-btn" class="text-white">
		  <i class="fas fa-angle-double-left"></i>
		</button>
      </div>

      <nav class="space-y-2">
         <a href="https://sunfra.com/farm/test/test_dashboard.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-home"></i> <span class="sidebar-text">Home</span>
        </a>
        <a href="https://sunfra.com/farm/test/batch_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-globe"></i> <span class="sidebar-text">Batch</span>
        </a>
        <a href="https://sunfra.com/farm/test/weighbridge_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-truck"></i> <span class="sidebar-text">WeighBridge</span>
        </a>
        <a href="https://sunfra.com/farm/test/tractor_production_mortality_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-tractor"></i> <span class="sidebar-text">Tractor Production Mortality</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_attendance.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-check"></i> <span class="sidebar-text">Attendance</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-tie"></i> <span class="sidebar-text">Shead Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-warehouse"></i> <span class="sidebar-text">Feed Plant Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/egg_godown/egg_godown.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-egg"></i> <span class="sidebar-text">Egg Godown Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_profit_loss_details.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-chart-line"></i> <span class="sidebar-text">Profit And Loss</span>
        </a>
        <a href="https://sunfra.com/farm/test/settings.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-sliders-h"></i> <span class="sidebar-text">Feature Settings</span>
        </a>
        <a href="https://sunfra.com" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-life-ring"></i> <span class="sidebar-text">Support</span>
        </a>
        <a href="https://sunfra.com/farm/test/logout.php" class="flex items-center gap-3 p-2 rounded hover:bg-red-600">
          <i class="fas fa-sign-out-alt"></i> <span class="sidebar-text">Logout</span>
        </a>
      </nav>
    </aside>
<main class="main-content">
    <h1>Vaccination Summary</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/test_show_profit_loss_details.php';">Go Back</button>
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
</main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
	  const sidebar = document.getElementById('sidebar');
	  const mainContent = document.querySelector('.main-content');
	  const collapseBtn = document.getElementById('collapse-btn');

	  collapseBtn?.addEventListener('click', function () {
		sidebar.classList.toggle('collapsed');
		mainContent.classList.toggle('collapsed');
		const icon = this.querySelector('i');
		if (icon) {
		  icon.classList.toggle('fa-angle-double-left');
		  icon.classList.toggle('fa-angle-double-right');
		}
	  });

	  menuBtn?.addEventListener('click', function () {
		sidebar.classList.toggle('show');
	  });
	});
	</script>
</body>
</html>

<?php $mysqli->close(); ?>