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
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
	/* Global box-sizing & Reset */
*,
*::before,
*::after {
  box-sizing: border-box;
}

body {
  font-family: 'Roboto', Arial, sans-serif;
  background-color: #f4f4f9;
  margin: 0;
  padding: 20px;
  color: #333;
  text-align: center;
  line-height: 1.5;
}

/* Container */
.container {
  max-width: 850px;
  margin: 50px auto;
  padding: 25px;
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  text-align: left; /* keep form and table LTR */
}

/* Headings */
h1, h2 {
  color: #333;
  margin-bottom: 20px;
  font-weight: 700;
  font-size: 1.75rem;
  line-height: 1.2;
  text-align: center;
}

/* Button styling */
button, .add-data a {
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  padding: 10px 15px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  transition: background-color 0.3s ease;
  user-select: none;
  white-space: nowrap;
}

button:hover, .add-data a:hover {
  background-color: #0056b3;
}

/* Button container for spacing and centering */
.button-container {
  margin-bottom: 20px;
  text-align: center;
}

/* Form styling */
form {
  background-color: #fff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  max-width: 500px;
  margin: 0 auto 40px;
}

/* Form layout */
p {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 10px 0;
}

label {
  flex: 1 1 40%;
  font-weight: 700;
  text-align: left;
  padding-right: 10px;
  white-space: nowrap;
}

input[type="text"], select {
  flex: 2 1 55%;
  padding: 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
}

/* Egg categories group */
.egg-category {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.egg-category label {
  flex: 2 1 45%;
  font-weight: 700;
  text-align: left;
  padding-right: 10px;
  white-space: nowrap;
}

.egg-category input {
  flex: 1 1 25%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  text-align: center;
}

/* Submit button full width */
button[type="submit"] {
  width: 100%;
  background-color: #007bff;
  white-space: nowrap;
}

button[type="submit"]:hover {
  background-color: #0056b3;
}

/* Table container for scroll on small devices */
.table-container {
  max-width: 850px;
  margin: 30px auto;
  overflow-x: auto;
}

/* Table styling */
table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  border-radius: 8px;
  overflow: hidden;
  font-size: 14px;
  text-align: center;
}

th, td {
  border: 1px solid #ddd;
  padding: 12px;
  vertical-align: middle;
}

th {
  background-color: #007bff;
  color: white;
  font-weight: 700;
  white-space: nowrap;
}

td:first-child, th:first-child {
  background-color: #28a745;
  color: white;
  font-weight: 700;
  white-space: nowrap;
}

tr:nth-child(even) {
  background-color: #f8f9fa;
}

tr:hover {
  background-color: #e9ecef;
}

td a {
  color: #007bff;
  font-weight: 700;
  text-decoration: none;
}

td a:hover {
  text-decoration: underline;
}

/* Sidebar styling */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 250px;
  height: 100vh;
  background-color: #0d6efd;
  color: #fff;
  padding: 20px 10px;
  overflow-y: auto;
  box-sizing: border-box;
  transition: width 0.3s ease;
  z-index: 1050;
}

.sidebar.collapsed {
  width: 50px !important;
  padding: 20px 5px !important;
  overflow-x: hidden;
}

.sidebar.collapsed .sidebar-text {
  display: none !important;
}

/* Main content spacing */
.main-content {
  margin-left: 250px;
  padding: 20px;
  transition: margin-left 0.3s ease;
  box-sizing: border-box;
  min-height: 100vh;
}

.main-content.collapsed {
  margin-left: 50px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    width: 250px;
  }
  .sidebar.show {
    transform: translateX(0);
  }
  .main-content,
  .main-content.collapsed {
    margin-left: 0 !important;
    padding: 10px;
  }
}form p {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  gap: 12px;
}

form label {
  flex: 1 1 35%;
  font-weight: 600;
  text-align: left;
  white-space: nowrap;
}

form input[type="text"],
form select {
  flex: 1 1 60%;
  padding: 8px;
  font-size: 14px;
  border-radius: 5px;
  border: 1px solid #ccc;
  background-color: white;
  box-sizing: border-box;
  min-width: 120px;
}

.egg-category {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.egg-category label {
  flex: 2 1 45%;
  font-weight: 700;
  text-align: left;
  white-space: nowrap;
}

.egg-category input {
  flex: 1 1 25%;
  border: 1px solid #ccc;
  border-radius: 5px;
  padding: 8px;
  text-align: center;
  background-color: white;
  box-sizing: border-box;
  min-width: 100px;
}.back-button {
		  display: inline-block;
		  margin-bottom: 1rem;
		  background-color: #3498db; /* Blue background */
		  color: white;
		  padding: 10px 16px;
		  border-radius: 6px;
		  text-decoration: none;
		  font-weight: 600;
		  transition: background-color 0.3s ease;
		}

		.back-button:hover {
		  background-color: #217dbb; /* Darker blue on hover */
		}.centered {
		  text-align: center;
		  margin-bottom: 1rem; /* optional spacing */
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
    <h1>Egg Production Management</h1>

   <div class="centered">
	  <a href="https://sunfra.com/farm/test/egg_godown/egg_godown.php" class="back-button">
		← Go Back
	  </a>
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
