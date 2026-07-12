<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
 
$current_feature = "Shead Supervisor"; // Set per page
 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
$username  = $_SESSION['username'] ?? '';
$client_id = $_SESSION['client_id'] ?? 0;
 
if (empty($username) || !$client_id) {
    header("Location: ../login/login.php");
    exit;
}
 
$users_url = "https://sunfra.com/farm/sunfra/login/farm_users_list.php";
$users_response = @file_get_contents($users_url);
 
if ($users_response === false) {
    error_log("Admin API failure: $users_url");
    header("Location: https://sunfra.com/farm/sunfra/index.php");
    exit;
}
 
$users = json_decode($users_response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Admin JSON parse error: " . json_last_error_msg());
    header("Location: https://sunfra.com/farm/sunfra/index.php");
    exit;
}
 
$is_admin = false;
if (is_array($users)) {
    foreach ($users as $u) {
        if ($u['username'] === $username &&
            intval($u['client_id']) === intval($client_id) &&
            $u['status'] === 'admin'
        ) {
            $is_admin = true;
            break;
        }
    }
}
 
if (!$is_admin) {
    $feature_url = "https://sunfra.com/farm/sunfra/configuration/config_supervisor_json.php?client_id=" . urlencode($client_id);
    $feature_response = @file_get_contents($feature_url);
 
    if ($feature_response === false) {
        error_log("Feature API failure: $feature_url");
        header("Location: https://sunfra.com/farm/sunfra/index.php");
        exit;
    }
 
    $features = json_decode($feature_response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Feature JSON parse error: " . json_last_error_msg());
        header("Location: https://sunfra.com/farm/sunfra/index.php");
        exit;
    }
 
    $has_feature = false;
    if (is_array($features)) {
        foreach ($features as $f) {
            if ($f['username'] === $username &&
                intval($f['client_id']) === intval($client_id) &&
                $f['feature'] === $current_feature
            ) {
                $has_feature = true;
                break;
            }
        }
    }
 
    if (!$has_feature) {
        header("Location: https://sunfra.com/farm/sunfra/index.php");
        exit;
    }
}

$shead_url = "https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=$client_id";
$shead_response = file_get_contents($shead_url);

if ($shead_response === false) {
    echo json_encode(["error" => "Unable to fetch shead data"]);
    exit;
}

$shead_data = json_decode($shead_response, true);
if (!is_array($shead_data)) {
    echo json_encode(["error" => "Invalid JSON received"]);
    exit;
}

$shead_list = [];

foreach ($shead_data as $item) {
    if (isset($item['shead_name'])) {
        $shead_list[] = str_replace(' ', '_', $item['shead_name']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Birds Weekly Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #ADD8E6;
      margin: 0;
      padding: 20px;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    h1 {
      color: #333;
    }

    .controls {
      display: flex;
      gap: 10px;
      align-items: center;
      margin-top: 10px;
    }

    select, input[type="text"] {
      padding: 8px 12px;
      font-size: 16px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    button {
	  background-color: #10b981; /* Green background */
	  color: white;              /* White text */
	  padding: 10px 20px;        /* Padding around text */
	  font-size: 16px;           /* Text size */
	  border: none;              /* Remove default border */
	  border-radius: 8px;        /* Rounded corners */
	  cursor: pointer;           /* Pointer cursor on hover */
	  transition: background-color 0.3s ease; /* Smooth hover transition */
	}

	button:hover {
	  background-color: #059669; /* Darker green on hover */
	}

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      margin-top: 20px;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
      border-radius: 8px;
    }

    th, td {
      padding: 10px;
      text-align: center;
      border-bottom: 1px solid #eee;
    }

    th {
      background-color: #4CAF50;
      color: white;
    }

    .week-title {
      margin-top: 25px;
      font-weight: bold;
      font-size: 18px;
    }

    .hidden {
      display: none;
    }

    .modal {
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      visibility: hidden;
      opacity: 0;
      transition: 0.3s ease;
    }

    .modal.active {
      visibility: visible;
      opacity: 1;
    }

    .modal-content {
      background: white;
      padding: 20px 30px;
      border-radius: 10px;
      width: 90%;
      max-width: 500px;
      position: relative;
      animation: scaleIn 0.3s ease-in-out;
    }

    @keyframes scaleIn {
      from {
        transform: scale(0.8);
        opacity: 0;
      }
      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .modal-content h2 {
      margin-top: 0;
    }

    .modal-content label {
      display: block;
      margin-top: 12px;
    }

    .modal-content input[type="number"], .modal-content select {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .modal-content button, .modal-content input[type="submit"] {
      margin-top: 15px;
      margin-right: 10px;
    }

    .no-data {
      text-align: center;
      color: red;
    }

    .success {
      text-align: center;
      color: green;
      margin-top: 10px;
    }

    .error {
      text-align: center;
      color: red;
      margin-top: 10px;
    }.form-grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 15px 20px;
	  }

	  .form-group {
		display: flex;
		flex-direction: column;
	  }

	  .form-group label {
		font-weight: 500;
		margin-bottom: 6px;
		color: #333;
	  }

	  .form-group input,
	  .form-group select {
		padding: 10px;
		font-size: 15px;
		border: 1px solid #ccc;
		border-radius: 8px;
		outline: none;
		background-color: #f9f9f9;
		transition: 0.3s ease;
	  }

	  .form-group input:focus,
	  .form-group select:focus {
		border-color: #4CAF50;
		background-color: #fff;
		box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
	  }

	  .form-group.full {
		grid-column: span 2;
	  }

	  .form-actions {
		grid-column: span 2;
		text-align: center;
		margin-top: 20px;
	  }

	  .submit-btn {
		background-color: #4CAF50;
		border: none;
		color: white;
		padding: 10px 25px;
		font-size: 16px;
		border-radius: 6px;
		cursor: pointer;
		margin-right: 10px;
	  }

	  .cancel-btn {
		background-color: #999;
		border: none;
		color: white;
		padding: 10px 25px;
		font-size: 16px;
		border-radius: 6px;
		cursor: pointer;
	  }

	  .submit-btn:hover {
		background-color: #45a049;
	  }

	  .cancel-btn:hover {
		background-color: #777;
	  }@media (max-width: 768px) {
		  header {
			flex-direction: column;
			align-items: flex-start;
			gap: 10px;
		  }

		  .controls {
			flex-direction: column;
			align-items: stretch;
			width: 100%;
		  }

		  .controls select,
		  .controls input[type="text"],
		  .controls button {
			width: 95%;
		  }

		  table {
			display: block;
			overflow-x: auto;
			white-space: nowrap;
		  }

		  .modal-content {
			width: 80% !important;
			padding: 15px;
		  }

		  .form-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr); 
			gap: 15px 20px;
		  }

		  .form-group {
			width: 100%;
		  }

		  .form-group.full {
			grid-column: span 2;
		  }

		  .form-group input,
		  .form-group select {
			width: 100%;
			box-sizing: border-box;
		  }

		  .form-actions {
			grid-column: span 2;
			text-align: center;
			margin-top: 20px;
		  }
		}.back-button {
		  display: inline-block;
		  margin-bottom: 1rem;
		  background-color: #3498db;
		  color: white;
		  padding: 10px 16px;
		  border-radius: 6px;
		  text-decoration: none;
		  font-weight: 600;
		  transition: background-color 0.3s ease;
		}

		.back-button:hover {
		  background-color: #217dbb;
		}.add-data-btn {
		  background-color: #10b981;
		  color: white;
		  padding: 10px 20px;
		  font-size: 16px;
		  border: none;
		  border-radius: 8px;
		  cursor: pointer;
		  transition: background-color 0.3s ease;
		}

		.add-data-btn:hover {
		  background-color: #059669;
		}.dropdown-checkbox {
		  position: relative;
		  display: inline-block;
		  min-width: 200px;
		}

		.dropdown-btn {
		  background-color: #fff;
		  padding: 8px 12px;
		  border: 1px solid #ccc;
		  border-radius: 5px;
		  cursor: pointer;
		}

		.checkbox-list {
		  display: none;
		  position: absolute;
		  background-color: white;
		  border: 1px solid #ccc;
		  padding: 10px;
		  max-height: 200px;
		  overflow-y: auto;
		  z-index: 1000;
		  width: 100%;
		  border-radius: 5px;
		  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		}.week-title {
		  font-weight: bold;
		  font-size: 1.2em;
		  margin: 20px 0 10px;
		}.sidebar {
			position: fixed;
			top: 0;
			left: 0;
			width: 70px;
			height: 100vh;
			background-color: #016795;
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			padding-top: 10px;
			overflow-y: auto;
			transition: width 0.3s ease;
			z-index: 1000;
			box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
		  }
		  .sidebar.expanded {
			width: 250px;
		  }
		  .sidebar a {
			color: white;
			text-decoration: none;
			width: 100%;
			padding: 14px 20px;
			display: flex;
			align-items: center;
			font-size: 15px;
			transition: background-color 0.2s ease-in-out;
			white-space: nowrap;
		  }
		  .sidebar a:hover {
			background-color: #0194c7;
		  }
		  .sidebar i {
			font-size: 16px;
			min-width: 30px;
			text-align: center;
		  }
		  .label {
			margin-left: 10px;
			white-space: nowrap;
			display: none;
		  }
		  .sidebar.expanded .label {
			display: inline;
		  }
		  .toggle-btn {
			width: 100%;
			cursor: pointer;
			padding: 10px 20px;
			background: none;
			border: none;
			color: white;
			font-size: 18px;
			text-align: left;
			outline: none;
			user-select: none;
			display: flex;
			align-items: center;
		  }
		  .toggle-btn i {
			margin-right: 10px;
		  }
		  .attendance-submenu {
			display: none;
			flex-direction: column;
			background: #1e293b;
			width: 100%;
			padding-left: 40px;
			transition: all 0.3s ease;
		  }
		  .attendance-submenu button {
			background: none;
			border: none;
			color: white;
			text-align: left;
			padding: 10px 20px;
			font-size: 14px;
			cursor: pointer;
			transition: background-color 0.2s ease;
		  }
		  .attendance-submenu button:hover {
			background-color: #2563EB;
		  }.main-content {
			  margin-left: 250px;
			  transition: margin-left 0.3s;
			}

			.main-content.collapsed {
			  margin-left: 50px;
			}.content {
			  margin-left: 70px;
			  transition: margin-left 0.3s ease;
			}

			.sidebar.expanded ~ .content {
			  margin-left: 250px;
			}.content.expanded {
			  margin-left: 250px;
			}
  </style>
</head>
<body>
<div>
<div class="sidebar" id="sidebar">
  <button class="toggle-btn" id="sidebarToggleBtn" aria-label="Toggle menu" title="Toggle menu">
    <i class="fas fa-bars"></i>
    <span class="label">Menu</span>
  </button>
  <a href="https://sunfra.com/farm/sunfra/index.php"><i class="fas fa-home"></i><span class="label">My Dashboard</span></a>
  <a href="https://sunfra.com/farm/sunfra/batch/batch_json_to_web.php"><i class="fas fa-globe"></i><span class="label">Batch</span></a>
  <a href="https://sunfra.com/farm/sunfra/sensor/iot_web_page.php"><i class="fas fa-microchip"></i><span class="label">IOT</span></a>
  <a href="https://sunfra.com/farm/sunfra/weighbridge/weighbridge_json_to_web.php"><i class="fas fa-truck"></i><span class="label">WeighBridge</span></a>
  <a href="https://sunfra.com/farm/sunfra/tractor_production_mortality/tractor_production_mortality_json_to_web.php"><i class="fas fa-tractor"></i><span class="label">Tractor Production</span></a>

  <div class="attendance-dropdown">
    <a onclick="toggleAttendance()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-user-check"></i>
      <span class="label">Attendance <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="attendanceSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra/attendance/labour_master_json_to_web.php'">👷‍♂️ Labour Details</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/attendance/labour_attendance_json_to_web.php'">📅 Labour Attendance</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/attendance/assigned_master_json_to_web.php'">📝 Assigned Master</button>
    </div>
  </div>
	<div class="attendance-dropdown">
	  <a onclick="toggleShed()" class="attendance-toggle">
		<i class="fas fa-users-cog"></i>
		<span class="label">Shead Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
	  </a>
	  <div class="attendance-submenu" id="shedSubmenu">
		<button onclick="location.href='https://sunfra.com/farm/sunfra/supervisor/supervisor_feed_feeding_shead_json_to_web.php'">🥣 Feed Feeding Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra/supervisor/supervisor_shead_mortality_json_to_web.php'">💀 Shead Mortality</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra/supervisor/supervisor_shead_production_json_to_web.php'">📦 Production Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra/supervisor/supervisor_birds_weight_json_to_web.php'">🐥 Birds Weight</button>
	  </div>
	</div>
  <div class="attendance-dropdown">
    <a onclick="toggleFeedPlant()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-industry"></i>
      <span class="label">Feed Plant Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="feedPlantSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra/feed_formula/feed_formula_json_to_web.php'">⚙️ Feed Formula</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/feedrawmaterial/feed_raw_material_json_to_web.php'">📥 Feed Raw Material</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/feedrawmaterial/feed_to_shead_json_to_web.php'">🚚 Feed Material To Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/feedrawmaterial/water_medicine_json_to_web.php'">🧪 Water Medicine</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleEggGodown()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-warehouse"></i>
      <span class="label">Egg Godown <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="eggGodownSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra/egg_godown/egg_godown_stock_json_to_web.php'">🥚 Egg Production From Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/egg_godown/egg_godown_sale_json_to_web.php'">💰 Eggs for Sale</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/egg_godown/egg_weight_json_to_web.php'">⚖️ Egg Weight</button>
    	<button onclick="location.href='https://sunfra.com/farm/sunfra/egg_godown/egg_damaged_json_to_web.php'">⚖️ Weekly Egg Damages</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleProfitLoss()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-chart-line"></i>
      <span class="label">Profit & Loss <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="profitLossSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/material_cost_json_to_web.php'">Feed Material Price</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/egg_cutting_json_to_web.php'">Egg Price Per Piece</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/profit_loss_json_to_web.php'">Profit & Loss Summary</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/summary_report_json_to_web.php'">Summary Report</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/vaccination_json_to_web.php'">Vaccination</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/labour_salary_json_to_web.php'">Labour Salary</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/sunfra/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <!--<a href="https://sunfra.com/farm/sunfra/settings.php"><i class="fas fa-sliders-h"></i><span class="label">Feature Settings</span></a>-->
  <a href="https://sunfra.com/farm/sunfra/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
  <header>
    <h1>Birds Weekly Report</h1>
    <div class="controls">
      <select id="monthSelect"></select>
	  <div class="dropdown-checkbox">
		  <div class="dropdown-btn" onclick="toggleDropdown()">Select Sheads</div>
		  <div id="sheadCheckboxes" class="checkbox-list">
			<?php foreach ($shead_list as $shead): ?>
			  <label><input type="checkbox" class="shead-filter" value="<?= htmlspecialchars($shead) ?>"> <?= htmlspecialchars($shead) ?></label><br>
			<?php endforeach; ?>
		  </div>
		</div>
		<button class="add-data-btn" onclick="toggleModal()">Add New Data</button>
    </div>
  </header>

  <div id="reportContainer"></div>

  <div class="modal" id="formModal">
	  <div class="modal-content">
		<h2 style="text-align:center; margin-bottom: 20px;">Add Bird Weights</h2>
		<form id="birdForm" class="form-grid">
		  <div class="form-group full">
				<label for="sheadNo">Shead No</label>
				<select id="sheadNo" name="sheadNo" required>
					<option value="">Select Shead</option>
					<?php foreach ($shead_list as $shead): ?>
						<option value="<?= htmlspecialchars($shead) ?>"><?= htmlspecialchars($shead) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		  <div class="form-group"><label>Bird 1</label><input type="number" name="bird1" required></div>
		  <div class="form-group"><label>Bird 2</label><input type="number" name="bird2" required></div>
		  <div class="form-group"><label>Bird 3</label><input type="number" name="bird3" required></div>
		  <div class="form-group"><label>Bird 4</label><input type="number" name="bird4" required></div>
		  <div class="form-group"><label>Bird 5</label><input type="number" name="bird5" required></div>
		  <div class="form-group"><label>Bird 6</label><input type="number" name="bird6" required></div>
		  <div class="form-group"><label>Bird 7</label><input type="number" name="bird7" required></div>
		  <div class="form-group"><label>Bird 8</label><input type="number" name="bird8" required></div>

		  <div class="form-actions">
			<input type="submit" value="Submit" class="submit-btn">
			<button type="button" onclick="toggleModal()" class="cancel-btn">Cancel</button>
			<div id="formMessage" style="margin-top:10px;"></div>
		  </div>
		</form>
	  </div>
	</div>
</main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

  <script>
	const clientId = <?php echo json_encode($client_id); ?>;
    const apiUrl = "https://sunfra.com/farm/sunfra/supervisor/supervisor_birds_weight_json.php";
    const saveApi = "https://sunfra.com/farm/sunfra/supervisor/supervisor_birds_weight_save.php";

    const reportContainer = document.getElementById("reportContainer");
    const monthSelect = document.getElementById("monthSelect");
    const searchInput = document.getElementById("searchInput");
    const modal = document.getElementById("formModal");
    const birdForm = document.getElementById("birdForm");
    const formMessage = document.getElementById("formMessage");

    let groupedData = {};

    function toggleModal() {
	  modal.classList.toggle("active");
	  formMessage.innerHTML = '';
	  birdForm.reset();
	}

    function getMonthKey(dateStr) {
      const date = new Date(dateStr);
      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    }

    function getWeekNumber(day) {
      return Math.ceil(day / 7);
    }

    function formatMonthYear(dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleString('default', { month: 'long', year: 'numeric' });
    }

    function groupByMonthShead(data) {
	  const result = {};
	  data.forEach(item => {
		const date = new Date(item.timestamp);
		const monthKey = getMonthKey(item.timestamp);
		const shead = item.sheadNo;
		const weekNumber = `Week ${getWeekNumber(date.getDate())}`;
		
		if (!result[monthKey]) result[monthKey] = {};
		if (!result[monthKey][shead]) result[monthKey][shead] = [];

		item.weekLabel = weekNumber; // Add week label to item
		result[monthKey][shead].push(item);
	  });
	  return result;
	}

    function populateMonthDropdown(months) {
      monthSelect.innerHTML = '';
      months.sort().reverse().forEach(monthKey => {
        const option = document.createElement("option");
        option.value = monthKey;
        option.textContent = formatMonthYear(`${monthKey}-01`);
        monthSelect.appendChild(option);
      });
    }

    function renderReport(monthKey) {
		reportContainer.innerHTML = '';
		const sheads = groupedData[monthKey];
		if (!sheads) {
			reportContainer.innerHTML = '<p class="no-data">No data available for this month.</p>';
			return;
		}

		Object.keys(sheads).sort().forEach(shead => {
			const rows = sheads[shead];
			let tableHTML = `<div class="week-title">${shead}</div><table class="searchable-table">
				<thead><tr>
					<th>Week</th>
					<th>Bird 1</th><th>Bird 2</th><th>Bird 3</th><th>Bird 4</th>
					<th>Bird 5</th><th>Bird 6</th><th>Bird 7</th><th>Bird 8</th>
					<th>Average</th><th>Timestamp</th>
				</tr></thead><tbody>`;

			rows.forEach(row => {
				tableHTML += `<tr>
					<td>${row.weekLabel}</td>
					<td>${row.bird1}</td><td>${row.bird2}</td><td>${row.bird3}</td><td>${row.bird4}</td>
					<td>${row.bird5}</td><td>${row.bird6}</td><td>${row.bird7}</td><td>${row.bird8}</td>
					<td><strong>${row.birds_average}</strong></td>
					<td>${row.timestamp}</td>
				</tr>`;
			});

			tableHTML += '</tbody></table>';
			reportContainer.innerHTML += tableHTML;
		});

		applySearch(); // 👈 Make sure to filter after rendering
	}

    function toggleDropdown() {
		  const checkboxList = document.getElementById("sheadCheckboxes");
		  checkboxList.style.display = checkboxList.style.display === "block" ? "none" : "block";
		}

		// Close dropdown if clicked outside
		document.addEventListener('click', function(e) {
		  const dropdown = document.querySelector('.dropdown-checkbox');
		  if (!dropdown.contains(e.target)) {
			document.getElementById("sheadCheckboxes").style.display = "none";
		  }
		});

		function applySearch() {
			const selectedSheads = Array.from(document.querySelectorAll(".shead-filter:checked"))
				.map(checkbox => checkbox.value.toLowerCase());

			const sheadSections = document.querySelectorAll(".searchable-table");

			sheadSections.forEach(table => {
				const sheadTitle = table.previousElementSibling?.textContent.toLowerCase(); // .week-title div
				if (selectedSheads.length === 0 || selectedSheads.includes(sheadTitle)) {
					table.style.display = '';
					table.previousElementSibling.style.display = ''; // show the shead title
				} else {
					table.style.display = 'none';
					table.previousElementSibling.style.display = 'none'; // hide the shead title
				}
			});
		}
		function filterSheadBlocks() {
		  const checkedSheads = Array.from(document.querySelectorAll('.shead-filter:checked')).map(cb => cb.value);
		  const blocks = document.querySelectorAll('.card-body'); // assuming each block is inside .card-body

		  blocks.forEach(block => {
			const blockShead = block.getAttribute('data-shead');
			if (checkedSheads.length === 0 || checkedSheads.includes(blockShead)) {
			  block.style.display = 'block';
			} else {
			  block.style.display = 'none';
			}
		  });
		}

    birdForm.addEventListener("submit", function (e) {
	  e.preventDefault();
	  const formData = new FormData(birdForm);

	  formData.append("client_id", "<?php echo $client_id; ?>");

	  fetch(saveApi, {
		method: "POST",
		body: formData
	  })
	  .then(res => res.text())
	  .then(response => {
		formMessage.innerHTML = `<div class="success">Data saved successfully!</div>`;
		setTimeout(() => {
		  toggleModal();
		  fetchData();
		}, 1000);
	  })
	  .catch(err => {
		console.error(err);
		formMessage.innerHTML = `<div class="error">Failed to save data.</div>`;
	  });
	});

   function fetchData() {
	  fetch('https://sunfra.com/farm/sunfra/supervisor/supervisor_birds_weight_json.php?client_id=' + clientId)
		.then(response => response.json())
		.then(json => {
		  const dataKey = Object.keys(json)[0]; // e.g., "1", "2", "5", etc.
		  const data = json[dataKey] || [];

		  groupedData = groupByMonthShead(data);
		  populateMonthDropdown(Object.keys(groupedData));
		  renderReport(monthSelect.value);
		})
		.catch(error => {
		  console.error("Error fetching data:", error);
		});
	}

    fetchData();
	document.addEventListener("change", function (e) {
	  if (e.target.classList.contains("shead-filter")) {
		applySearch();
		filterSheadBlocks(); 
		document.getElementById("sheadCheckboxes").style.display = "none"; 
	  }
	});

    monthSelect.addEventListener("change", () => renderReport(monthSelect.value));
	applySearch();
	const sidebar = document.getElementById('sidebar');
	const mainContent = document.querySelector('.content'); // or '.main-content'
	const toggleBtn = document.getElementById('sidebarToggleBtn');

	toggleBtn.addEventListener('click', () => {
	  sidebar.classList.toggle('expanded');
	  mainContent.classList.toggle('expanded');  // toggle expanded class for margin shift

	  const icon = toggleBtn.querySelector('i');
	  if (sidebar.classList.contains('expanded')) {
		icon.classList.remove('fa-bars');
		icon.classList.add('fa-times');
	  } else {
		icon.classList.add('fa-bars');
		icon.classList.remove('fa-times');
	  }
	});

	  function toggleAttendance() {
		toggleSubmenu('attendanceSubmenu');
	  }
	  function toggleFeedPlant() {
		toggleSubmenu('feedPlantSubmenu');
	  }
	  function toggleEggGodown() {
		toggleSubmenu('eggGodownSubmenu');
	  }
	  function toggleProfitLoss() {
		toggleSubmenu('profitLossSubmenu');
	  }
	  function toggleShed() {
		toggleSubmenu('shedSubmenu');
	  }
	  function toggleSubmenu(id) {
		const submenu = document.getElementById(id);
		if (!submenu) return;
		if (submenu.style.display === 'flex') {
		  submenu.style.display = 'none';
		} else {
		  submenu.style.display = 'flex';
		}
	  }
  </script>
</body>
</html>
