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
$name = $price = '';

if ($id >= 1) {
    $stmt = $mysqli->prepare("SELECT * FROM feed_rawmaterial_price WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $name = $row["name"];
        $price = $row["price"];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = $mysqli->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);

  if ($id > 0) {
    $stmt = $mysqli->prepare("UPDATE feed_rawmaterial_price SET name = ?, price = ? WHERE id = ? AND client_id = ?");
    $stmt->bind_param("sdii", $name, $price, $id, $client_id);
} else {
    $check_stmt = $mysqli->prepare("SELECT id FROM feed_rawmaterial_price WHERE name = ? AND client_id = ?");
    $check_stmt->bind_param("si", $name, $client_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<p style='color:red;'>Material with the same name already exists for this client.</p>";
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // Proceed with insert
    $stmt = $mysqli->prepare("INSERT INTO feed_rawmaterial_price (name, price, client_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $name, $price, $client_id);
}

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/test/profit_and_loss_details/feed_material_price_perkg.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$price_stmt = $mysqli->prepare("SELECT * FROM feed_rawmaterial_price WHERE client_id = ? ORDER BY id DESC");
$price_stmt->bind_param("i", $client_id);
$price_stmt->execute();
$price_result = $price_stmt->get_result();

$material_stmt = $mysqli->prepare("SELECT name, type FROM feed_rawmaterial WHERE client_id = ? AND type IN ('Feed Medicine', 'Raw Material')");
$material_stmt->bind_param("i", $client_id);
$material_stmt->execute();
$material_result = $material_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Price Per KG/LIT</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Global box sizing */
*,
*::before,
*::after {
  box-sizing: border-box !important;
}

/* HTML and body reset */
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
h1, h2 {
  font-weight: 700 !important;
  color: #333 !important;
  text-align: center !important;
  font-size: 1.75rem !important;
  margin-bottom: 20px !important;
  line-height: 1.2 !important;
}

/* Button styling */
button, .add-data a {
  background-color: #007bff !important;
  color: #fff !important;
  padding: 10px 15px !important;
  font-weight: 600 !important;
  font-size: 14px !important;
  border-radius: 5px !important;
  border: none !important;
  cursor: pointer !important;
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
  max-width: 500px !important;
  margin: 0 auto 40px !important;
  background-color: #fff !important;
  padding: 20px !important;
  border-radius: 10px !important;
  box-shadow: 0 0 10px rgba(0,0,0,0.1) !important;
  font-family: Arial, sans-serif !important;
  text-align: left !important;
}

/* Flex layout for form rows */
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
  background-color: #fff !important;
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

/* Table styles */
table {
  width: 80% !important;
  max-width: 800px !important;
  margin: 30px auto !important;
  border-collapse: collapse !important;
  background: #fff !important;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
  border-radius: 8px !important;
  overflow: hidden !important;
  font-size: 14px !important;
  text-align: center !important;
}

th, td {
  padding: 10px !important;
  border: 1px solid #ddd !important;
  vertical-align: middle !important;
}

th {
  background-color: #007bff !important;
  color: #fff !important;
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
.sidebar {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 250px !important;
  height: 100vh !important;
	background: #0d6efd;
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

/* Responsive sidebar */
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
    <h1>Material Price Per KG/LIT</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/test_show_profit_loss_details.php';">Go Back</button>
    </div>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="name">Material Name:</label>
            <select name="name" id="name">
                <option value="">Select option</option>
                <?php
                if ($material_result->num_rows > 0) {
                    while ($row = $material_result->fetch_assoc()) {
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
        <?php while ($row = $price_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['price']) ?></td>
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
