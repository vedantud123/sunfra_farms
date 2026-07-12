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
$place = $name = $description = $quantity = '';

if ($id >= 1) {
    $query = "SELECT * FROM `water_medicine` WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $place = $row["place"];
        $name = $row["name"];
        $quantity = $row["quantity"];
        $description = $row["description"];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $place = $mysqli->real_escape_string($_POST['place']);
    $name = $mysqli->real_escape_string($_POST['name']);
    $quantity = floatval($_POST['quantity']);
    $description = $mysqli->real_escape_string($_POST['description']);

    if ($id > 0) {
        $query = "UPDATE water_medicine SET place = ?, name = ?, quantity = ?, description = ? WHERE id = ? AND client_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssdssi", $place, $name, $quantity, $description, $id, $client_id);
    } else {
        $timestamp = date('Y-m-d H:i:s');
        $query = "INSERT INTO water_medicine (place, name, quantity, description, timestamp, client_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssdssi", $place, $name, $quantity, $description, $timestamp, $client_id);

        $water_medicine_query = "UPDATE feed_rawmaterial SET stock = stock - ? WHERE name = ? AND client_id = ?";
        $water_medicine_stmt = $mysqli->prepare($water_medicine_query);
        $water_medicine_stmt->bind_param("dsi", $quantity, $name, $client_id);
        $water_medicine_stmt->execute();
        $water_medicine_stmt->close();
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/test/sanitization/sanitization.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$query = "SELECT * FROM water_medicine WHERE client_id = ? ORDER BY id DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$raw_material_query = "SELECT * FROM feed_rawmaterial WHERE TYPE = 'Water Medicine' AND client_id = ? ORDER BY name ASC";
$raw_material_stmt = $mysqli->prepare($raw_material_query);
$raw_material_stmt->bind_param("i", $client_id);
$raw_material_stmt->execute();
$raw_material_result = $raw_material_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Medicine</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
       /* Body and fonts */
		body {
		  font-family: 'Roboto', sans-serif;
		  background-color: #f4f4f9;
		  margin: 0;
		  padding: 0;
		  color: #333;
		}

		/* Container */
		.container {
		  max-width: 850px;
		  margin: 50px auto;
		  padding: 25px;
		  background-color: #fff;
		  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
		  border-radius: 10px;
		}

		/* Headings */
		h1, h2 {
		  text-align: center;
		  margin-bottom: 20px;
		  color: #333;
		}

		/* Buttons */
		button, .add-data a {
		  padding: 10px 18px;
		  background-color: #007bff;
		  color: white;
		  border: none;
		  cursor: pointer;
		  border-radius: 6px;
		  font-size: 14px;
		  text-decoration: none;
		  font-weight: 600;
		  display: inline-block;
		  transition: background-color 0.3s ease;
		  margin-bottom: 10px;
		}

		button:hover, .add-data a:hover {
		  background-color: #0056b3;
		}

		/* Specific green button */
		.add-data a {
		  background-color: #28a745;
		}

		.add-data a:hover {
		  background-color: #218838;
		}

		/* Form */
		form {
		  background: white;
		  padding: 20px;
		  border-radius: 10px;
		  box-shadow: 0 0 10px rgba(0,0,0,0.1);
		  max-width: 500px;
		  margin: 30px auto 40px;
		}

		form p {
		  display: flex;
		  margin-bottom: 15px;
		  align-items: center;
		  justify-content: space-between;
		}

		form label {
		  flex: 1;
		  font-weight: 600;
		  text-align: left;
		}

		form input[type="text"],
		form select {
		  flex: 2;
		  padding: 8px;
		  font-size: 14px;
		  border: 1px solid #ccc;
		  border-radius: 5px;
		}

		/* Table container for responsiveness */
		.table-container {
		  width: 100%;
		  max-width: 850px;
		  margin: 0 auto 50px;
		  overflow-x: auto;
		}

		/* Table styling */
		table {
		  width: 100%;
		  border-collapse: collapse;
		  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
		  background: white;
		  border-radius: 8px;
		  overflow: hidden;
		}

		th, td {
		  padding: 12px;
		  text-align: center;
		  border: 1px solid #ddd;
		  font-size: 14px;
		}

		th {
		  background-color: #007bff;
		  color: white;
		  font-weight: 700;
		}

		td:first-child, th:first-child {
		  background-color: #28a745;
		  color: white;
		  font-weight: 600;
		}

		tr:nth-child(even) {
		  background-color: #f8f9fa;
		}

		tr:hover {
		  background-color: #e9ecef;
		}

		td a {
		  text-decoration: none;
		  color: #007bff;
		  font-weight: 700;
		}

		td a:hover {
		  text-decoration: underline;
		}

		/* Sidebar styling */
		.sidebar {
		  position: fixed;
		  top: 0; left: 0;
		  width: 250px;
		  height: 100vh;
		  background: #0d6efd;
		  color: #fff;
		  padding: 20px 10px;
		  transition: width 0.3s ease;
		  overflow-y: auto;
		  z-index: 1050;
		  box-sizing: border-box;
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
		  transition: margin-left 0.3s ease;
		  padding: 20px;
		  box-sizing: border-box;
		  min-height: 100vh; /* Ensure it fills viewport height */
		}

		.main-content.collapsed {
		  margin-left: 50px;
		}

		/* Responsive adjustments for mobile */
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
		  }
		}

		/* Button container for consistent spacing */
		.button-container {
		  margin-bottom: 20px;
		  text-align: center; /* Center 'Go Back' button */
		}

		/* Back button styling */
		.back-button {
		  display: inline-block;
		  background-color: #3498db;
		  color: white;
		  padding: 10px 16px;
		  border-radius: 6px;
		  text-decoration: none;
		  font-weight: 600;
		  transition: background-color 0.3s ease;
		  margin-bottom: 20px;
		}

		.back-button:hover {
		  background-color: #217dbb;
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
    <h1>Water Medicine</h1>
    <div class="button-container">
        <a href="https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php" class="back-button">Go Back</a>
    </div>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="place">Shead Name:</label>
            <select name="place" id="place">
                <option value="">Select option</option>
                <option value="Shead 1" <?= $place === 'Shead 1' ? 'selected' : '' ?>>Shead 1</option>
                <option value="Shead 2" <?= $place === 'Shead 2' ? 'selected' : '' ?>>Shead 2</option>
                <option value="Shead 3" <?= $place === 'Shead 3' ? 'selected' : '' ?>>Shead 3</option>
                <option value="Shead 4" <?= $place === 'Shead 4' ? 'selected' : '' ?>>Shead 4</option>
                <option value="Shead 5" <?= $place === 'Shead 5' ? 'selected' : '' ?>>Shead 5</option>
                <option value="Shead 6" <?= $place === 'Shead 6' ? 'selected' : '' ?>>Shead 6</option>
                <option value="Shead 7" <?= $place === 'Shead 7' ? 'selected' : '' ?>>Shead 7</option>
                <option value="Shead 8" <?= $place === 'Shead 8' ? 'selected' : '' ?>>Shead 8</option>
                <option value="Chick" <?= $place === 'Chick' ? 'selected' : '' ?>>Chick</option>
				<option value="Grower" <?= $place === 'Grower' ? 'selected' : '' ?>>Grower</option>
				<option value="other_place" <?= $place === 'other_place' ? 'selected' : '' ?>>Other Place</option>

            </select>
        </p>
		<p>
            <label for="name">Name Of Medicine:</label>
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
            <label for="quantity">Quantity Used:</label>
            <input type="text" name="quantity" id="quantity" value="<?= htmlspecialchars($quantity) ?>">
        </p>
        <p>
            <label for="description">Description:</label>
            <input type="text" name="description" id="description" value="<?= htmlspecialchars($description) ?>" >
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
                <th>Name</th>
                <th>Quantity</th>
                <th>Description</th>
                <th>Edit</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['timestamp']) ?></td>
                <td><?= htmlspecialchars($row['place']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
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
