<?php
session_start();

date_default_timezone_set('Asia/Kolkata');

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$client_id = $_SESSION['client_id'] ?? 0;

$locations = ["Feed_Godown", "Egg_Godown", "Gate_Manager", "Shead_1", "Shead_2", "Shead_3", "Shead_4", "Shead_5", "Shead_6", "Shead_7", "Shead_8", "Chick", "Grower"];
$date = date('Y-m-d');

foreach ($locations as $location) {
    $check_sql = "SELECT id FROM task_master WHERE location = ? AND assigned_date = ? AND client_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ssi", $location, $date, $client_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $insert_sql = "INSERT INTO task_master (location, assigned_date, client_id) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("ssi", $location, $date, $client_id);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt->close();
}

$timestamp = date('Y-m-d H:i:s');
$employee_names = [];

$emp_query = "SELECT name FROM labour_master WHERE client_id = ?";
$emp_stmt = $conn->prepare($emp_query);
$emp_stmt->bind_param("i", $client_id);
$emp_stmt->execute();
$emp_result = $emp_stmt->get_result();

while ($row = $emp_result->fetch_assoc()) {
    $employee_names[] = $row['name'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["assignments"])) {
    foreach ($_POST["assignments"] as $location => $person_name) {
        $update_sql = "UPDATE task_master SET person_name = ?, assigned_at = ? WHERE location = ? AND assigned_date = ? AND client_id = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("ssssi", $person_name, $timestamp, $location, $date , $client_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
}

$stmt = $conn->prepare("SELECT * FROM task_master WHERE assigned_date = ? AND client_id = ?");
$stmt->bind_param("si", $date, $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign People to Locations</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; text-align: center; }
        .container { width: 70%; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #007bff; color: white; }
        input[type="radio"] { margin: 5px; }
        .submit-btn { background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; margin-top: 10px; }
        .submit-btn:hover { background: #218838; }.back-button {
		  display: block;
		  width: fit-content;
		  margin: 20px auto; /* Centers horizontally */
		  padding: 10px 20px;
		  background-color: #3498db;
		  color: white;
		  text-decoration: none;
		  border-radius: 5px;
		  font-weight: bold;
		  transition: background-color 0.3s ease;
		}

		.back-button:hover {
		  background-color: #2980b9;
		}.sidebar {
		  position: fixed;
		  top: 0;
		  left: 0;
		  width: 250px;
		  height: 100vh;
		  background: #0d6efd;
		  color: #fff;
		  padding: 20px 10px;
		  transition: width 0.3s;
		  overflow-y: auto;
		  z-index: 1050;
		}

		.sidebar.collapsed {
		  width: 50px !important;
		  padding: 20px 0 !important;
		}

		.sidebar.collapsed .sidebar-text {
		  display: none !important;
		}

		.main-content {
		  margin-left: 250px;
		  transition: margin-left 0.3s;
		}

		.main-content.collapsed {
		  margin-left: 50px;
		}

		@media (max-width: 768px) {
		  .sidebar {
			position: fixed;
			left: 0; top: 0;
			height: 100vh;
			width: 250px;
			transform: translateX(-100%);
			transition: transform 0.3s;
			z-index: 1100;
			background: #0d6efd;
		  }
		  .sidebar.show {
			transform: translateX(0);
		  }
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
		  }
		}@media (max-width: 768px) {
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
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

    <div class="container">
		<a class="back-button" href="https://sunfra.com/farm/test/test_show_attendance.php">Go Back</a>
        <h2>Assign People to Locations</h2>
        <form action="" method="post">
            <table>
                <tr>
                    <th>Location</th>
                    <th>Person Assigned</th>
                    <th>Assign / Update</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["location"]) ?></td>
                        <td><?= $row["person_name"] ? htmlspecialchars($row["person_name"]) : "Not Assigned" ?></td>
                        <td>
                            <?php foreach ($employee_names as $emp): ?>
                                <label>
                                    <input type="radio" name="assignments[<?= htmlspecialchars($row["location"]) ?>]" value="<?= htmlspecialchars($emp) ?>" <?= ($row["person_name"] == $emp) ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($emp) ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <button type="submit" class="submit-btn">Submit Assignments</button>
        </form>
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

<?php
$conn->close();
?>

