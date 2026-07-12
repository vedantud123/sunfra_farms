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
$name_result = '';
if ($id > 0) {
    // In edit mode - allow the current name to be included even if it exists in labour_salaries
    $sql = "SELECT name FROM labour_master 
            WHERE client_id = ? AND (name NOT IN 
            (SELECT name FROM labour_salaries WHERE client_id = ?) OR name = ?) 
            ORDER BY name";
    $name_stmt = $mysqli->prepare($sql);
    $name_stmt->bind_param("iis", $client_id, $client_id, $name);
    $name_stmt->execute(); // ✅ FIXED
    $name_result = $name_stmt->get_result();

} else {
    // In add mode - exclude already used names
    $sql = "SELECT name FROM labour_master 
            WHERE client_id = ? AND name NOT IN 
            (SELECT name FROM labour_salaries WHERE client_id = ?) 
            ORDER BY name";
    $name_stmt = $mysqli->prepare($sql);
    $name_stmt->bind_param("ii", $client_id, $client_id);
    $name_stmt->execute(); // ✅ FIXED
    $name_result = $name_stmt->get_result();
}

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
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
   /* Global box-sizing for predictable widths */
	*, *::before, *::after {
	  box-sizing: border-box !important;
	}

	/* Body and base styles reset */
	html, body {
	  margin: 0 !important;
	  padding: 20px !important;
	  font-family: Arial, sans-serif !important;
	  background-color: #f8f9fa !important;
	  color: #333 !important;
	  text-align: center !important;
	  line-height: 1.5 !important;
	}

	/* Container for centered content */
	.container {
	  max-width: 1200px !important;
	  margin: 20px auto !important;
	  padding: 10px 15px !important;
	  text-align: center !important;
	}

	/* Headings */
	h1 {
	  font-weight: 700 !important;
	  font-size: 24px !important;
	  color: #007bff !important;
	  margin: 20px 0 !important;
	  text-align: center !important;
	  line-height: 1.2 !important;
	}

	/* Button container for spacing and center */
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

	/* Form container */
	form {
	  display: block !important;
	  width: 100% !important;
	  max-width: 400px !important;
	  margin: 0 auto 40px !important;
	  padding: 20px !important;
	  background: #fff !important;
	  border: 1px solid #ddd !important;
	  border-radius: 10px !important;
	  box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
	  font-family: Arial, sans-serif !important;
	  text-align: left !important;
	}

	/* Form paragraphs layout */
	form p {
	  margin-bottom: 15px !important;
	}

	/* Form label */
	form label {
	  display: block !important;
	  font-weight: 700 !important;
	  margin-bottom: 5px !important;
	  color: #555 !important;
	}

	/* Form inputs, selects, buttons */
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
	  background-color: #fff !important;
	}

	/* Submit button override */
	form button {
	  background-color: #007bff !important;
	  color: white !important;
	  font-weight: 700 !important;
	  border: none !important;
	  cursor: pointer !important;
	  transition: background-color 0.3s ease !important;
	  white-space: nowrap !important;
	}

	form button:hover {
	  background-color: #0056b3 !important;
	}

	/* Table container for horizontal scroll on small devices */
	.table-container {
	  width: 100% !important;
	  max-width: 800px !important;
	  margin: 30px auto !important;
	  overflow-x: auto !important;
	}

	/* Table styles */
	table {
	  width: 100% !important;
	  border-collapse: collapse !important;
	  background: white !important;
	  box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
	  border-radius: 8px !important;
	  font-size: 14px !important;
	  text-align: center !important;
	  overflow: hidden !important;
	}

	/* Table headers and cells */
	table th,
	table td {
	  padding: 10px !important;
	  border: 1px solid #ddd !important;
	  vertical-align: middle !important;
	  text-align: center !important;
	  color: #333 !important;
	}

	/* Header background */
	table th {
	  background-color: #007bff !important;
	  color: white !important;
	  font-weight: 700 !important;
	  white-space: nowrap !important;
	}

	/* First column highlighting */
	td:first-child, th:first-child {
	  background-color: #28a745 !important;
	  color: white !important;
	  font-weight: 700 !important;
	  white-space: nowrap !important;
	}

	/* Zebra striping and hover */
	table tr:nth-child(even) {
	  background-color: #f8f9fa !important;
	}

	table tr:hover {
	  background-color: #f1f1f1 !important;
	}

	/* Links in table cells */
	td a {
	  color: #007bff !important;
	  font-weight: 700 !important;
	  text-decoration: none !important;
	}

	td a:hover {
	  text-decoration: underline !important;
	}

	/* Sidebar styling */
	.sidebar {
	  position: fixed !important;
	  top: 0 !important;
	  left: 0 !important;
	  width: 250px !important;
	  height: 100vh !important;
	  background-color: #0d6efd;
	  color: white !important;
	  padding: 20px 10px !important;
	  overflow-y: auto !important;
	  box-sizing: border-box !important;
	  transition: width 0.3s ease !important;
	  z-index: 1050 !important;
	}

	/* Sidebar when collapsed */
	.sidebar.collapsed {
	  width: 50px !important;
	  padding: 20px 5px !important;
	  overflow-x: hidden !important;
	}

	/* Sidebar text hide on collapse */
	.sidebar.collapsed .sidebar-text {
	  display: none !important;
	}

	/* Main content to adapt margin based on sidebar width */
	.main-content {
	  margin-left: 250px !important;
	  padding: 20px !important;
	  box-sizing: border-box !important;
	  min-height: 100vh !important;
	  transition: margin-left 0.3s ease !important;
	  text-align: left !important;
	}

	/* Collapsed main content margin */
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

	/* Small screen adjustments */
	@media (max-width: 600px) {
	  h1 {
		font-size: 20px !important;
	  }

	  form {
		width: 90% !important;
		padding: 15px !important;
	  }

	  table th,
	  table td {
		font-size: 12px !important;
		padding: 8px !important;
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
					if ($name_result && $name_result->num_rows > 0) {
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