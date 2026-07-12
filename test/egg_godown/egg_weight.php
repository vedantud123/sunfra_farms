<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'];

date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$shead_name = "";
$tray_1 = $tray_2 = $tray_3 = $tray_4 = $tray_5 = $tray_6 = $tray_7 = $tray_8 = $average = 0;
$current_date = date('Y-m-d');

if ($id >= 1) {
    $query = "SELECT * FROM egg_weight WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ii", $id, $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $shead_name = $row["shead_name"];
            $tray_1 = $row["tray_1"];
            $tray_2 = $row["tray_2"];
            $tray_3 = $row["tray_3"];
            $tray_4 = $row["tray_4"];
            $tray_5 = $row["tray_5"];
            $tray_6 = $row["tray_6"];
            $tray_7 = $row["tray_7"];
            $tray_8 = $row["tray_8"];
            $average = $row["average"];
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['form_type'] === 'shed_form') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = $mysqli->real_escape_string($_POST['shead_name']);

    $tray_1 = isset($_POST['tray_1']) ? intval($_POST['tray_1']) : 0;
    $tray_2 = isset($_POST['tray_2']) ? intval($_POST['tray_2']) : 0;
    $tray_3 = isset($_POST['tray_3']) ? intval($_POST['tray_3']) : 0;
    $tray_4 = isset($_POST['tray_4']) ? intval($_POST['tray_4']) : 0;
    $tray_5 = isset($_POST['tray_5']) ? intval($_POST['tray_5']) : 0;
    $tray_6 = isset($_POST['tray_6']) ? intval($_POST['tray_6']) : 0;
    $tray_7 = isset($_POST['tray_7']) ? intval($_POST['tray_7']) : 0;
    $tray_8 = isset($_POST['tray_8']) ? intval($_POST['tray_8']) : 0;

    $average = number_format(($tray_1 + $tray_2 + $tray_3 + $tray_4 + $tray_5 + $tray_6 + $tray_7 + $tray_8) / 240.0, 2, '.', '');

    if ($id > 0) {
        $query = "UPDATE egg_weight SET shead_name = ?, tray_1 = ?, tray_2 = ?, tray_3 = ?, tray_4 = ?, tray_5 = ?, tray_6 = ?, tray_7 = ?, tray_8 = ?, average = ? 
                  WHERE id = ? AND client_id = ?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("siiiiiiiidii", $shead_name, $tray_1, $tray_2, $tray_3, $tray_4, $tray_5, $tray_6, $tray_7, $tray_8, $average, $id, $client_id);
        }
    } else {
        $current_date = date("Y-m-d");
        $query = "INSERT INTO egg_weight (date, shead_name, tray_1, tray_2, tray_3, tray_4, tray_5, tray_6, tray_7, tray_8, average, client_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ssiiiiiiiidi", $current_date, $shead_name, $tray_1, $tray_2, $tray_3, $tray_4, $tray_5, $tray_6, $tray_7, $tray_8, $average, $client_id);
        }
    }

    if ($stmt && $stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/test/egg_godown/egg_weight.php");
        exit;
    } else {
        echo "Error: " . ($stmt ? $stmt->error : $mysqli->error);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['form_type'] === 'date_form') {
    $selected_date = $_POST['selected_date'] ?? date('Y-m-d');
} else {
    $selected_date = date('Y-m-d');
}

$query = "SELECT * FROM egg_weight WHERE `date` = ? AND client_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("si", $selected_date, $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egg Production Management</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
       /* 1. Global box-sizing to avoid sizing problems */
*, *::before, *::after {
  box-sizing: border-box;
}

/* 2. Reset Tailwind's base resets affecting content */

html, body {
  font-family: Arial, sans-serif !important;
  margin: 0 !important;
  padding: 20px !important;
  background-color: #f4f4f9 !important;
  color: #333 !important;
  text-align: center !important;
  line-height: 1.5 !important;
}

/* 3. Headings */
h1, h2 {
  font-weight: 700 !important;
  color: #333 !important;
  text-align: center !important;
  margin-bottom: 1.25rem !important;
  font-size: 1.75rem !important;
  line-height: 1.2 !important;
}

/* 4. Button styling */
button, .add-data a {
  background-color: #007bff !important;
  color: #fff !important;
  border-radius: 5px !important;
  padding: 10px 15px !important;
  font-weight: 600 !important;
  font-size: 14px !important;
  cursor: pointer !important;
  border: none !important;
  white-space: nowrap !important;
  display: inline-block !important;
  transition: background-color 0.3s ease !important;
  user-select: none !important;
  text-decoration: none !important;
}

button:hover, .add-data a:hover {
  background-color: #0056b3 !important;
}

/* 5. Button containers for spacing */
.button-container {
  text-align: center !important;
  margin-bottom: 20px !important;
}

/* 6. Form styling */
form {
  background-color: #fff !important;
  padding: 20px !important;
  border-radius: 10px !important;
  box-shadow: 0 0 10px rgba(0,0,0,0.1) !important;
  max-width: 500px !important;
  margin: 0 auto 40px !important;
  font-family: Arial, sans-serif !important;
}

/* 7. Form layout */
form p {
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

input[type="text"], select {
  flex: 2 1 55% !important;
  padding: 8px !important;
  font-size: 14px !important;
  border: 1px solid #ccc !important;
  border-radius: 5px !important;
  box-sizing: border-box !important;
  background-color: #fff !important;
}

/* 8. Submit button full width */
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

/* 9. Table container */
.table-container {
  max-width: 850px !important;
  margin: 30px auto !important;
  overflow-x: auto !important;
}

/* 10. Table styling */
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

.sidebar {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 250px !important;
  height: 100vh !important;
  background-color: rgb(30, 64, 175);
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
    <h1>Egg Weight Management</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/egg_godown/egg_godown.php';">Go Back</button>
    </div>

    <form action="" method="post">
        <input type="hidden" name="form_type" value="shed_form">
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
        <?php for ($i = 1; $i <= 8; $i++): ?>
        <p>
            <label for="tray_<?= $i ?>">Tray <?= $i ?>:</label>
            <input type="text" name="tray_<?= $i ?>" id="tray_<?= $i ?>" value="<?= htmlspecialchars(${'tray_' . $i}) ?>" required>
        </p>
        <?php endfor; ?>

        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <h2>Records</h2>
    <form method="POST">
        <input type="hidden" name="form_type" value="date_form">
        <p>
            <label for="selected_date">Select Date :</label>
            <input type="date" name="selected_date" id="selected_date" value="<?= htmlspecialchars($selected_date) ?>">
        </p>    
        <button type="submit">Submit</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Shead Name</th>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <th>Tray <?= $i ?></th>
            <?php endfor; ?>
            <th>Average</th>
            <th>EDIT</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['shead_name']) ?></td>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <td><?= htmlspecialchars($row["tray_$i"]) ?></td>
            <?php endfor; ?>
            <td><?= htmlspecialchars($row['average']) ?></td>
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
