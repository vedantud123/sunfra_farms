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

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$name = $price = '';

if ($id >= 1) {
    $query = "SELECT * FROM water_medicine_and_sanitization_price WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $name = $row["medicine_name"];
        $price = $row["price"];
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = isset($_POST['name']) ? $mysqli->real_escape_string($_POST['name']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;

    if (!empty($name) && $price > 0) {
        if ($id > 0) {
            $query = "UPDATE water_medicine_and_sanitization_price SET medicine_name = ?, price = ? WHERE id = ? AND client_id = ?";
            $stmt = $mysqli->prepare($query);
            if ($stmt) {
                $stmt->bind_param("sdii", $name, $price, $id, $client_id);
            }
        } else {
            $query = "INSERT INTO water_medicine_and_sanitization_price (medicine_name, price, client_id) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            if ($stmt) {
                $stmt->bind_param("sdi", $name, $price, $client_id);
            }
        }

        if ($stmt && $stmt->execute()) {
            $stmt->close();
            header("Location: https://sunfra.com/farm/test/profit_and_loss_details/water_medicine_and_sanitization_price.php");
            exit;
        } else {
            echo "Error: " . ($stmt ? $stmt->error : $mysqli->error);
        }
    } else {
        echo "Invalid input data!";
    }
}

// Fetch all records for current tenant
$query = "SELECT * FROM water_medicine_and_sanitization_price WHERE client_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

// Optional: Fetch raw materials (also filter by client if table supports it)
$raw_material_query = "SELECT * FROM feed_rawmaterial WHERE client_id = ? AND type = 'Water Medicine'";
$raw_stmt = $mysqli->prepare($raw_material_query);
$raw_stmt->bind_param("i", $client_id);
$raw_stmt->execute();
$raw_material_result = $raw_stmt->get_result();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Medicine And Sanitization Price</title>
	 <script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
       /* Global box-sizing reset */
*, *::before, *::after {
  box-sizing: border-box !important;
}

/* Reset body and base font styles */
html, body {
  margin: 0 !important;
  padding: 20px !important;
  font-family: Arial, sans-serif !important;
  background-color: #f4f4f4 !important;
  color: #333 !important;
  text-align: center !important;
  line-height: 1.5 !important;
}

/* Headings */
h1 {
  color: #333 !important;
  font-weight: 700 !important;
  text-align: center !important;
  font-size: 1.75rem !important;
  margin-bottom: 20px !important;
  line-height: 1.2 !important;
}

/* Button styles */
button, .add-data a {
  background-color: #007bff !important;
  color: white !important;
  padding: 10px 15px !important;
  border-radius: 5px !important;
  border: none !important;
  cursor: pointer !important;
  font-weight: 600 !important;
  font-size: 14px !important;
  display: inline-block !important;
  text-decoration: none !important;
  user-select: none !important;
  white-space: nowrap !important;
  transition: background-color 0.3s ease !important;
}

button:hover, .add-data a:hover {
  background-color: #0056b3 !important;
}

/* Button container */
.button-container {
  margin-bottom: 20px !important;
  text-align: center !important;
}

/* Form styling */
form {
  background: white !important;
  padding: 20px !important;
  max-width: 500px !important;
  margin: 0 auto 40px !important;
  border-radius: 10px !important;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1) !important;
  font-family: Arial, sans-serif !important;
  text-align: left !important;
}

/* Flex for form rows */
form p {
  display: flex !important;
  justify-content: space-between !important;
  align-items: center !important;
  margin: 10px 0 !important;
}

/* Labels */
label {
  flex: 1 1 40% !important;
  font-weight: 700 !important;
  text-align: left !important;
  padding-right: 10px !important;
  white-space: nowrap !important;
}

/* Inputs and selects */
input, select {
  flex: 2 1 55% !important;
  padding: 8px !important;
  font-size: 14px !important;
  border: 1px solid #ccc !important;
  border-radius: 5px !important;
  box-sizing: border-box !important;
  background-color: white !important;
}

/* Submit button full width */
button[type="submit"] {
  width: 100% !important;
  background-color: #007bff !important;
  font-weight: 700 !important;
  padding: 12px !important;
  border-radius: 5px !important;
  border: none !important;
  cursor: pointer !important;
  transition: background-color 0.3s ease !important;
}

button[type="submit"]:hover {
  background-color: #0056b3 !important;
}

/* Table container */
.table-container {
  width: 40% !important;
  margin: 30px auto !important;
}

/* Table styling */
table {
  border-collapse: collapse !important;
  width: 100% !important;
  max-width: 800px !important;
  margin: 0 auto !important;
  background: white !important;
  text-align: center !important;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
  border-radius: 8px !important;
  overflow: hidden !important;
  font-size: 14px !important;
}

table th, table td {
  border: 1px solid #ddd !important;
  padding: 10px !important;
  text-align: center !important;
  vertical-align: middle !important;
}

table th {
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

table tr:nth-child(even) {
  background-color: #f8f9fa !important;
}

table tr:hover {
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
  background-color: #0d6efd ; /* your original blue */
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
    <h1>Water Medicine And Sanitization Price</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/test_show_profit_loss_details.php';">Go Back</button>
    </div>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="name">Matrial Name:</label>
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
            <label for="price">Price:</label>
            <input type="text" name="price" id="price" value="<?= htmlspecialchars($price) ?>" required>
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <h2>Records</h2>
    <table>
            <tr>
                <th>ID</th>
                <th>Material Name</th>
                <th>Price (Per KG/LIT)</th>
                <th>Edit</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['medicine_name']) ?></td>
                <td><?= htmlspecialchars($row['price']) ?></td>
				<td>
					<a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a>
                </td>
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
