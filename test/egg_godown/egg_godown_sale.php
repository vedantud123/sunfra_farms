<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'];
$username = $_SESSION['username'];

date_default_timezone_set('Asia/Kolkata');
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$shead_name = $no_of_eggs = $type_of_eggs = $remarks = $sale = $sale_price = '';
$no_of_trays = $no_of_loose_eggs = '';

if ($id >= 1) {
    $query = "SELECT * FROM egg_godown_stock WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $shead_name = $row["shead_name"];
        $type_of_eggs = $row["type_of_eggs"];
        $sale = $row["sale"];
        $sale_price = $row["sale_price"];
        $remarks = $row["remarks"];
        $no_of_eggs = $row["no_of_eggs"];
        $no_of_trays = floor($no_of_eggs / 30);
        $no_of_loose_eggs = $no_of_eggs % 30;
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
    $sale = $mysqli->real_escape_string($_POST['sale']);
	$sale_price = isset($_POST['sale_price']) ? floatval($_POST['sale_price']) : 0.00;
    $remarks = $mysqli->real_escape_string($_POST['remarks']);

    if ($id > 0) {
        $query = "UPDATE egg_godown_stock SET shead_name = ?, no_of_eggs = ?, type_of_eggs = ?, sale = ?, sale_price = ?, remarks = ? WHERE id = ? AND client_id = ?";
        $stmt = $mysqli->prepare($query);
		$stmt->bind_param("sissdsii", $shead_name, $no_of_eggs, $type_of_eggs, $sale, $sale_price, $remarks, $id, $client_id);
    } else {
        $timestamp = date('Y-m-d H:i:s');
        $query = "INSERT INTO egg_godown_stock (shead_name, no_of_eggs, type_of_eggs, sale, sale_price, remarks, timestamp, client_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
		$stmt->bind_param("sisssdsi", $shead_name, $no_of_eggs, $type_of_eggs, $sale, $sale_price, $remarks, $timestamp, $client_id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/test/egg_godown/egg_godown_sale.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$query = "SELECT * FROM egg_godown_stock WHERE sale IS NOT NULL AND client_id = ? ORDER BY id DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Egg Sale Management</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
       /* Reset Tailwind base reset defaults that are problematic here */
html, body {
  font-family: 'Arial', sans-serif !important;
  line-height: 1.5 !important;
  background-color: #f4f4f9 !important;
  margin: 0 !important;
  padding: 20px !important;
  text-align: center !important;
  color: #333 !important;
}

h1, h2 {
  font-weight: 700 !important;
  text-align: center !important;
  color: #333 !important;
  margin-bottom: 1.25rem !important;
  font-size: 1.75rem !important;
  line-height: 1.2 !important;
}

.button-container {
  margin-bottom: 20px !important;
  text-align: center !important;
}

button, .add-data a {
  background-color: #007bff !important;
  color: #fff !important;
  border-radius: 5px !important;
  padding: 10px 15px !important;
  font-weight: 600 !important;
  font-size: 14px !important;
  cursor: pointer !important;
  border: none !important;
  user-select: none !important;
  white-space: nowrap !important;
  display: inline-block !important;
  transition: background-color 0.3s ease !important;
  text-decoration: none !important;
}

button:hover, .add-data a:hover {
  background-color: #0056b3 !important;
}

form {
  max-width: 500px !important;
  margin: 30px auto 40px !important;
  background-color: #fff !important;
  padding: 20px !important;
  border-radius: 10px !important;
  box-shadow: 0 0 10px rgba(0,0,0,0.1) !important;
  font-family: Arial, sans-serif !important;
}

p {
  display: flex !important;
  justify-content: space-between !important;
  align-items: center !important;
  margin: 10px 0 !important;
}

label {
  flex: 1 1 40% !important;
  font-weight: 700 !important;
  text-align: left !important;
  padding-right: 10px !important;
  white-space: nowrap !important;
}

input[type="text"],
select {
  flex: 2 1 55% !important;
  padding: 8px !important;
  font-size: 14px !important;
  border: 1px solid #ccc !important;
  border-radius: 5px !important;
  box-sizing: border-box !important;
  background-color: #fff !important;
}

button[type="submit"] {
  width: 100% !important;
  background-color: #007bff !important;
  font-weight: 700 !important;
  padding: 12px !important;
}

button[type="submit"]:hover {
  background-color: #0056b3 !important;
}

table {
  width: 80% !important;
  border-collapse: collapse !important;
  background: #fff !important;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
  border-radius: 8px !important;
  font-size: 14px !important;
  text-align: center !important;
  margin: 30px auto !important;
  overflow: hidden !important;
}

th, td {
  border: 1px solid #ddd !important;
  padding: 10px !important;
  vertical-align: middle !important;
}

th {
  background-color: #007bff !important;
  color: white !important;
  font-weight: 700 !important;
  white-space: nowrap !important;
}

td:first-child, th:first-child {
  background-color: #28a745 !important;
  color: white !important;
  font-weight: 700 !important;
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
  font-weight: 700 !important;
  text-decoration: none !important;
}

td a:hover {
  text-decoration: underline !important;
}

/* Sidebar */
.sidebar {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 250px !important;
  height: 100vh !important;
  background-color: rgb(30, 64, 175); /* Tailwind bg-blue-800 */
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

/* Responsive */
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
    <script>
        window.onload = function () {
            fetch('https://sunfra.com/farm/test/egg_godown/egg_godown_status.php');
        };
    </script>
</head>
<body>
<div>
	<aside id="sidebar" class="sidebar bg-blue-800 text-white p-4">
	<div class="d-flex align-items-center justify-content-between mb-3">
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
    <h1>Egg Sale Management</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/egg_godown/egg_godown.php';">Go Back</button>
    </div>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/egg_godown/egg_godown_sale_damage.php';">Damage Eggs During Sale</button>
    </div>

    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="shead_name">Shead Name:</label>
            <select name="shead_name" id="shead_name">
                <option value="">Select option</option>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="Shead <?= $i ?>" <?= $shead_name === "Shead $i" ? 'selected' : '' ?>>Shead <?= $i ?></option>
                <?php endfor; ?>
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
                <?php foreach (['Good', 'Damaged', 'Small', 'Big'] as $type): ?>
                    <option value="<?= $type ?>" <?= $type_of_eggs === $type ? 'selected' : '' ?>><?= $type ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="sale">Sale To:</label>
            <input type="text" name="sale" id="sale" value="<?= htmlspecialchars($sale) ?>">
        </p>
        <p>
            <label for="sale_price">Sale Price:</label>
            <input type="text" name="sale_price" id="sale_price" value="<?= htmlspecialchars($sale_price) ?>">
        </p>
        <p>
            <label for="remarks">Remarks:</label>
            <input name="remarks" id="remarks" value="<?= htmlspecialchars($remarks) ?>">
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
            <th>No of Trays</th>
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
            <td><a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
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
