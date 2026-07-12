<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
if (empty($username)) {
    header("Location: ../login/login.php");
    exit;
}

$api_url = "https://sunfra.com/farm/sunfra_clients/login/farm_users_list.php";
$response = file_get_contents($api_url);

if ($response === false) {
    header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
    exit;
}

$users = json_decode($response, true);

$is_admin = false;
if (is_array($users)) {
    foreach ($users as $user) {
        if (isset($user['username']) && $user['username'] === $username) {
            if (isset($user['status']) && $user['status'] === 'admin') {
                $is_admin = true;
            }
            break;
        }
    }
}

if (!$is_admin) {
    header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Configuration Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
		body {
		  margin: 0;
		  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
		  background-color: #f5f7fa;
		  height: 100vh;
		  overflow: hidden;
		  display: flex;
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

		/* Content */
		.content {
		  margin-left: 70px;
		  padding: 20px;
		  flex-grow: 1;
		  overflow-y: auto;
		  height: 100vh;
		  transition: margin-left 0.3s ease;
		}

		.sidebar.expanded ~ .content {
		  margin-left: 250px;
		}

		.card-container {
		  display: grid;
		  grid-template-columns: repeat(4, 1fr); /* ✅ exactly 4 cards per row */
		  gap: 20px;
		  padding: 20px;
		}


		.card-container::-webkit-scrollbar {
		  height: 8px;
		}

		.card-container::-webkit-scrollbar-thumb {
		  background: #ccc;
		  border-radius: 4px;
		}

		.card {
		  flex: 0 0 auto;        /* Prevent shrinking */
		  width: 280px;          /* Fixed width for each card */
		  border-radius: 12px;
		  background: #fff;
		  padding: 16px;
		  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
		}


		.card {
		  background-color: #ffffff;
		  border-radius: 12px;
		  padding: 20px;
		  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
		  border-top: 5px solid #016795;
		  display: flex;
		  flex-direction: column;
		  justify-content: space-between;
		  transition: transform 0.2s ease;
		}

		.card:hover {
		  transform: translateY(-5px);
		}

		.card h3 {
		  margin: 0 0 10px;
		  font-size: 20px;
		  color: #016795;
		}

		.card p {
		  flex-grow: 1;
		  color: #444;
		  font-size: 14px;
		  margin-bottom: 15px;
		}

		.card-btn {
		  display: inline-block;
		  padding: 8px 16px;
		  background-color: #016795;
		  color: white;
		  text-decoration: none;
		  border-radius: 5px;
		  text-align: center;
		  transition: background-color 0.3s ease;
		  cursor: pointer;
		}

		.card-btn:hover {
		  background-color: #0194c7;
		}

		/* Modals (Generic) */
		.modal-overlay {
		  display: none;
		  position: fixed;
		  inset: 0;
		  background-color: rgba(0,0,0,0.5);
		  z-index: 2000;
		  justify-content: center;
		  align-items: center;
		  padding: 15px;
		  box-sizing: border-box;
		}

		.modal-overlay.active {
		  display: flex;
		}

		.modal-content {
		  background: white;
		  border-radius: 12px;
		  max-width: 400px;
		  width: 100%;
		  padding: 25px 30px;
		  box-shadow: 0 10px 30px rgb(0 0 0 / 0.2);
		  position: relative;
		  outline: none;
		  max-height: 90vh;
		  overflow-y: auto;
		}

		.close-modal-btn {
		  position: absolute;
		  right: 15px;
		  top: 15px;
		  background: transparent;
		  color: #333;
		  border: none;
		  font-size: 24px;
		  font-weight: 700;
		  cursor: pointer;
		  line-height: 1;
		  user-select: none;
		}

		.close-modal-btn:hover {
		  color: #e63946;
		}

		/* Form Elements */
		form label {
		  display: block;
		  font-weight: 600;
		  margin-bottom: 6px;
		  color: #444;
		}

		form input[type="number"],
		form input[type="text"],
		form textarea {
		  width: 90%;
		  padding: 10px 12px;
		  font-size: 15px;
		  border-radius: 6px;
		  border: 1px solid #ccc;
		  transition: border-color 0.3s;
		  font-family: inherit;
		}

		form input[type="number"]:focus,
		form input[type="text"]:focus,
		form textarea:focus {
		  border-color: #016795;
		  outline: none;
		  background-color: #fff;
		}

		form button[type="submit"] {
		  background-color: #016795;
		  color: white;
		  font-weight: 700;
		  padding: 12px 20px;
		  font-size: 16px;
		  border: none;
		  border-radius: 8px;
		  cursor: pointer;
		  transition: background-color 0.3s ease;
		  width: 100%;
		  margin-top: 10px;
		}

		form button[type="submit"]:hover {
		  background-color: #014f69;
		}

		/* Attendance Submenu */
		.attendance-submenu {
		  display: none;
		  flex-direction: column;
		  background: #1e293b;
		  width: 100%;
		  padding-left: 40px;
		  transition: all 0.3s ease;
		}

		.attendance-submenu button:hover {
		  background-color: #2563EB;
		}

		/* Location Modal List */
		#locationList {
		  margin-top: 15px;
		}

		.location-item {
		  display: flex;
		  justify-content: space-between;
		  align-items: center;
		  background: #f0f0f0;
		  padding: 10px 12px;
		  margin-bottom: 10px;
		  border-radius: 6px;
		  font-size: 15px;
		  color: #333;
		}

		.location-item button {
		  background-color: #e63946;
		  color: white;
		  border: none;
		  padding: 6px 10px;
		  border-radius: 4px;
		  cursor: pointer;
		  font-size: 13px;
		}

		.location-item button:hover {
		  background-color: #b92d3a;
		}

		/* Responsive Adjustments */
		@media (max-width: 768px) {
		  .sidebar {
			position: fixed;
			width: 250px;
			transform: translateX(-100%);
			transition: transform 0.3s ease;
		  }
		  .sidebar.show {
			transform: translateX(0);
		  }
		  .content {
			margin-left: 0 !important;
			padding: 15px !important;
		  }
		}

		@media (max-width: 700px) {
		  form {
			padding: 20px;
			width: 95%;
		  }
		  .content {
			padding: 15px;
			margin-left: 70px;
		  }
		}#sheadList {
		  margin: 20px 0;
		  text-align: center;
		}

		.shead-grid {
		  display: flex;
		  flex-wrap: wrap;
		  justify-content: center;
		  gap: 12px;
		}

		.shead-card {
		  background-color: #f3f4f6;
		  border: 1px solid #ccc;
		  padding: 10px 18px;
		  border-radius: 8px;
		  font-weight: 500;
		  font-size: 14px;
		  box-shadow: 2px 2px 5px rgba(0,0,0,0.08);
		  transition: transform 0.2s ease;
		}

		.shead-card:hover {
		  transform: scale(1.05);
		  background-color: #e5e7eb;
		  cursor: default;
		}.shead-grid {
		  display: grid;
		  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
		  gap: 12px;
		  margin-top: 15px;
		}

		.shead-card {
		  background-color: #f0f0f0;
		  padding: 12px;
		  border-radius: 8px;
		  text-align: center;
		  font-weight: bold;
		  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
		}@media (max-width: 1200px) {
		  .card-container {
			grid-template-columns: repeat(3, 1fr); /* 3 per row */
		  }
		}

		@media (max-width: 900px) {
		  .card-container {
			grid-template-columns: repeat(2, 1fr); /* 2 per row */
		  }
		}

		@media (max-width: 600px) {
		  .card-container {
			grid-template-columns: 1fr; /* stack cards */
		  }
		}

		.sidebar {
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
			}#supervisorForm label {
		  display: block;
		  font-weight: 500;
		  margin-bottom: 6px;
		  color: #444;
		  font-size: 14px;
		}

		/* Dropdown */
		#supervisorForm select {
		  width: 100%;
		  padding: 10px;
		  border-radius: 8px;
		  border: 1px solid #ccc;
		  outline: none;
		  font-size: 14px;
		  margin-bottom: 16px;
		  transition: border-color 0.2s;
		}
		#supervisorForm select:focus {
		  border-color: #007bff;
		}

		/* User Checkbox List */
		#userCheckboxes {
		  max-height: 200px;
		  overflow-y: auto;
		  border: 1px solid #ddd;
		  border-radius: 8px;
		  padding: 10px;
		  background: #fafafa;
		  margin-bottom: 16px;
		}
		#userCheckboxes div {
		  margin-bottom: 8px;
		}
		#userCheckboxes input[type="checkbox"] {
		  accent-color: #007bff;
		  cursor: pointer;
		}
		#userCheckboxes label {
		  margin-left: 6px;
		  cursor: pointer;
		  color: #333;
		}

		/* Submit Button */
		#supervisorForm button[type="submit"] {
		  width: 100%;
		  padding: 12px;
		  background: #007bff;
		  color: #fff;
		  font-size: 15px;
		  font-weight: 500;
		  border: none;
		  border-radius: 8px;
		  cursor: pointer;
		  transition: background 0.2s ease-in-out;
		}
		#supervisorForm button[type="submit"]:hover {
		  background: #0056b3;
		}

		/* Response Message */
		#supervisorMsg {
		  margin-top: 15px;
		  text-align: center;
		  font-size: 14px;
		  font-weight: 500;
		}

		/* Animation */
		@keyframes fadeIn {
		  from { transform: scale(0.9); opacity: 0; }
		  to { transform: scale(1); opacity: 1; }
		}.supervisor-table {
		  width: 100%;
		  border-collapse: collapse;
		  margin-top: 15px;
		  font-family: Arial, sans-serif;
		  font-size: 14px;
		}

		.supervisor-table th,
		.supervisor-table td {
		  border: 1px solid #ddd;
		  padding: 10px;
		  text-align: left;
		}

		.supervisor-table th {
		  background-color: #007bff;
		  color: white;
		}

		.supervisor-table tr:nth-child(even) {
		  background-color: #f9f9f9;
		}

		.supervisor-table tr:hover {
		  background-color: #f1f1f1;
		}

		.remove-btn {
		  background: #dc3545;
		  color: white;
		  border: none;
		  padding: 5px 10px;
		  cursor: pointer;
		  border-radius: 4px;
		}

		.remove-btn:hover {
		  background: #c82333;
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
  <a href="https://sunfra.com/farm/sunfra_clients/index.php"><i class="fas fa-home"></i><span class="label">My Dashboard</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/batch/batch_json_to_web.php"><i class="fas fa-globe"></i><span class="label">Batch</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/weighbridge/weighbridge_json_to_web.php"><i class="fas fa-truck"></i><span class="label">WeighBridge</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/tractor_production_mortality/tractor_production_mortality_json_to_web.php"><i class="fas fa-tractor"></i><span class="label">Tractor Production</span></a>

  <div class="attendance-dropdown">
    <a onclick="toggleAttendance()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-user-check"></i>
      <span class="label">Attendance <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="attendanceSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/attendance/labour_master_json_to_web.php'">👷‍♂️ Labour Details</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/attendance/labour_attendance_json_to_web.php'">📅 Labour Attendance</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/attendance/assigned_master_json_to_web.php'">📝 Assigned Master</button>
    </div>
  </div>
	<div class="attendance-dropdown">
	  <a onclick="toggleShed()" class="attendance-toggle">
		<i class="fas fa-users-cog"></i>
		<span class="label">Shead Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
	  </a>
	  <div class="attendance-submenu" id="shedSubmenu">
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_feed_feeding_shead_json_to_web.php'">🥣 Feed Feeding Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_shead_mortality_json_to_web.php'">💀 Shead Mortality</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_shead_production_json_to_web.php'">📦 Production Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_birds_weight_json_to_web.php'">🐥 Birds Weight</button>
	  </div>
	</div>
  <div class="attendance-dropdown">
    <a onclick="toggleFeedPlant()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-industry"></i>
      <span class="label">Feed Plant Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="feedPlantSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/feed_formula/feed_formula_json_to_web.php'">⚙️ Feed Formula</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/feedrawmaterial/feed_raw_material_json_to_web.php'">📥 Feed Raw Material</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/feedrawmaterial/feed_to_shead_json_to_web.php'">🚚 Feed Material To Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/feedrawmaterial/water_medicine_json_to_web.php'">🧪 Water Medicine</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleEggGodown()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-warehouse"></i>
      <span class="label">Egg Godown <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="eggGodownSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/egg_godown/egg_godown_stock_json_to_web.php'">🥚 Egg Production From Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/egg_godown/egg_godown_sale_json_to_web.php'">💰 Eggs for Sale</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/egg_godown/egg_weight_json_to_web.php'">⚖️ Egg Weight</button>
    	<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/egg_godown/egg_damaged_json_to_web.php'">⚖️ Weekly Egg Damages</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleProfitLoss()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-chart-line"></i>
      <span class="label">Profit & Loss <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="profitLossSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/material_cost_json_to_web.php'">Feed Material Price</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/egg_cutting_json_to_web.php'">Egg Price Per Piece</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/profit_loss_json_to_web.php'">Profit & Loss Summary</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/summary_report_json_to_web.php'">Summary Report</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/vaccination_json_to_web.php'">Vaccination</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/labour_salary_json_to_web.php'">Labour Salary</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/sunfra_clients/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
    <div class="card-container">
      <div class="card">
        <h3>Shead Configuration</h3>
        <p>Configure all your sheads here.</p>
        <a  class="card-btn" id="openSheadModalBtn">Configure Sheads</a>
      </div>
		<div class="card">
        <h3>Location Configuration</h3>
        <p>Configure all your location here.</p>
        <a class="card-btn" id="openLocationModalBtn">Configure Working Locations</a>
      </div>
	  <div class="card">
        <h3>Shead Box Configuration</h3>
        <p>Configure all your shead boxes.</p>
        <a class="card-btn" id="openSheadBoxModalBtn">Configure Shead Boxes</a>
      </div>
		<div class="card">
		  <h3>Supervisor Configuration</h3>
		  <p>Configure supervisor for the feature.</p>
		  <a class="card-btn" id="openSupervisorForm">Configure Supervisor</a>
		</div>
		<div class="card">
		  <h3>Chick Configuration</h3>
		  <p>Configure all your chick sheads here.</p>
		  <a class="card-btn" id="openChickModalBtn">Configure Chicks</a>
		</div>
		<div class="card">
		  <h3>Grower Configuration</h3>
		  <p>Configure all your grower sheads here.</p>
		  <a class="card-btn" id="openGrowerModalBtn">Configure Growers</a>
		</div>
    </div>

    <div id="sheadModalOverlay" class="modal-overlay" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <div class="modal-content">
        <button class="close-modal-btn" aria-label="Close modal" id="closeSheadModalBtn">×</button>
        <h2 id="modalTitle">Add Sheads</h2>
			<div id="sheadList"></div> 

			<form id="modalSheadForm">
			  <label for="modalSheadNumber">How many sheads you have:</label>
			  <input type="number" id="modalSheadNumber" name="shead_number" min="1" required />
			  <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">
			  <button type="submit">Save Sheads</button>
			  <p id="modalResponseMsg" style="margin-top: 15px; text-align: center;"></p>
			</form>
      </div>
    </div>
	
	<div id="locationModalOverlay" class="modal-overlay" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="locationModalTitle">
	  <div class="modal-content">
		<button class="close-modal-btn" aria-label="Close modal" id="closeLocationModalBtn">×</button>
		<h2 id="locationModalTitle">Manage Locations</h2>
		<div id="locationList"></div>
		<form id="addLocationForm" style="margin-top: 20px;">
		  <input type="text" id="newLocationInput" placeholder="Enter new location" required />
		  <button type="submit">Add Location</button>
		</form>
		<p id="locationMsg" style="text-align: center; margin-top: 10px;"></p>
	  </div>
	</div>
	
	<div id="sheadBoxModalOverlay" class="modal-overlay" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="sheadBoxModalTitle">
	  <div class="modal-content">
		<button class="close-modal-btn" aria-label="Close modal" id="closeSheadBoxModalBtn">×</button>
		<h2 id="sheadBoxModalTitle">Add Shead Boxes</h2>

		<div id="sheadBoxList" style="margin-bottom: 20px;"></div> <!-- ✅ Added here -->

		<form id="modalSheadBoxForm">
		  <label for="modalSheadBoxNumber">Enter Number of Feed Box in sheads:</label>
		  <input type="number" id="modalSheadBoxNumber" name="shead_box_number" min="1" required />
		  <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">
		  <button type="submit">Save Shead Boxes</button>
		  <p id="modalBoxResponseMsg" style="margin-top: 15px; text-align: center;"></p>
		</form>
	  </div>
	</div>
	
	<div id="supervisorModalOverlay" class="modal-overlay" style="display:none;" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="supervisorModalTitle">
	  <div class="modal-content">
		<button class="close-modal-btn" aria-label="Close modal" id="closeSupervisorModalBtn">×</button>
		<h2 id="supervisorModalTitle">Assign Supervisor</h2>
		<table id="supervisorTable" class="supervisor-table">
		  <thead>
			<tr>
			  <th>Feature</th>
			  <th>Username</th>
			  <th>Action</th>
			</tr>
		  </thead>
		  <tbody id="supervisorList">
			<!-- Data will be injected here by JS -->
		  </tbody>
		</table>
		<form id="supervisorForm">
		  <label for="roleSelect">Select Location:</label>
		  <select id="roleSelect" name="role" required>
			<option value="">-- Select Location --</option>
			<option value="Egg Godown">Egg Godown</option>
			<option value="Shead Supervisor">Shead Supervisor</option>
			<option value="Weighbridge">Weighbridge</option>
			<option value="Tractor Production Mortality">Tractor Production Mortality</option>
			<option value="Attendance">Attendance</option>
			<option value="Feed Plant Supervisor">Feed Plant Supervisor</option>
		  </select>

		  <label>Select Users:</label>
		  <div id="userCheckboxes" style="max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:8px;">
			<p>Loading users...</p>
		  </div>

		  <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">

		  <button type="submit">Save Supervisor</button>

		  <p id="supervisorMsg" style="margin-top: 15px; text-align: center;"></p>
		</form>
	  </div>
	</div>

	<div id="chickModalOverlay" class="modal-overlay" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="chickModalTitle">
	  <div class="modal-content">
		<button class="close-modal-btn" aria-label="Close modal" id="closeChickModalBtn">×</button>
		<h2 id="chickModalTitle">Add Chicks</h2>
		
		<div id="chickList"></div> 

		<form id="modalChickForm">
		  <label for="modalChickNumber">How many chicks you have:</label>
		  <input type="number" id="modalChickNumber" name="chick_number" min="1" required />
		  <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">
		  <button type="submit">Save Chicks</button>
		  <p id="modalChickResponseMsg" style="margin-top: 15px; text-align: center;"></p>
		</form>
	  </div>
	</div>
	<div id="growerModalOverlay" class="modal-overlay" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="growerModalTitle">
	  <div class="modal-content">
		<button class="close-modal-btn" aria-label="Close modal" id="closeGrowerModalBtn">×</button>
		<h2 id="growerModalTitle">Add Grower</h2>
		
		<div id="growerList"></div> 

		<form id="modalGrowerForm">
		  <label for="modalGrowerNumber">How many growers you have:</label>
		  <input type="number" id="modalGrowerNumber" name="grower_number" min="1" required />
		  <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">
		  <button type="submit">Save Grower</button>
		  <p id="modalGrowerResponseMsg" style="margin-top: 15px; text-align: center;"></p>
		</form>
	  </div>
	</div>
  </main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
  const clientId = <?php echo json_encode($client_id); ?>;

  const sheadModal = document.getElementById('sheadModalOverlay');
  const openSheadModalBtn = document.getElementById('openSheadModalBtn');
  const closeSheadModalBtn = document.getElementById('closeSheadModalBtn');
  const modalSheadForm = document.getElementById('modalSheadForm');
  const modalResponseMsg = document.getElementById('modalResponseMsg');
  const modalSheadNumber = document.getElementById('modalSheadNumber');

  openSheadModalBtn.addEventListener('click', (e) => {
	  e.preventDefault();
	  sheadModal.classList.add('active');
	  modalResponseMsg.textContent = '';
	  modalSheadNumber.value = '';
	  modalSheadNumber.focus();
	  fetchSheads(); 
	});

  closeSheadModalBtn.addEventListener('click', () => {
    sheadModal.classList.remove('active');
  });

  sheadModal.addEventListener('click', (e) => {
    if (e.target === sheadModal) {
      sheadModal.classList.remove('active');
    }
  });
	
	setTimeout(() => {
	  sheadModal.classList.remove('active');
	  fetchSheads(); // refresh after save
	}, 1500);

	 modalSheadForm.addEventListener('submit', async (e) => {
	  e.preventDefault();
	  modalResponseMsg.textContent = '';
	  try {
		const formData = new FormData(modalSheadForm);
		const res = await fetch('shead_status_save.php', {
		  method: 'POST',
		  body: formData
		});
		const data = await res.json();
		if (data.status === 'success') {
		  modalResponseMsg.style.color = 'green';
		  modalResponseMsg.textContent = data.message || 'Saved successfully!';
		  setTimeout(() => {
			sheadModal.classList.remove('active');
			fetchSheads(); 
		  }, 1500);
		} else {
		  modalResponseMsg.style.color = 'red';
		  modalResponseMsg.textContent = data.message || 'Failed to save data.';
		}
	  } catch (err) {
		modalResponseMsg.style.color = 'red';
		modalResponseMsg.textContent = 'Error occurred while saving.';
		console.error(err);
	  }
	});

	const openLocationBtn = document.getElementById('openLocationModalBtn');
  const locationModal = document.getElementById('locationModalOverlay');
  const closeLocationModalBtn = document.getElementById('closeLocationModalBtn');
  const locationListDiv = document.getElementById('locationList');
  const addLocationForm = document.getElementById('addLocationForm');
  const newLocationInput = document.getElementById('newLocationInput');
  const locationMsg = document.getElementById('locationMsg');

  openLocationBtn.addEventListener('click', () => {
    locationModal.style.display = 'flex';
    fetchLocations();
  });

  closeLocationModalBtn.addEventListener('click', () => {
    locationModal.style.display = 'none';
    locationMsg.textContent = '';
    newLocationInput.value = '';
    locationListDiv.innerHTML = '';
  });

  function fetchLocations() {
    locationListDiv.innerHTML = 'Loading...';
    fetch(`https://sunfra.com/farm/sunfra_clients/configuration/config_location_json.php?client_id=${clientId}`)
      .then(response => response.json())
      .then(data => {
        locationListDiv.innerHTML = '';
        const locations = data[clientId];
        if (!locations || locations.length === 0) {
          locationListDiv.innerHTML = '<p>No locations found.</p>';
          return;
        }
        locations.forEach(loc => {
          const locationName = loc.location;
          const div = document.createElement('div');
          div.className = 'location-item';
          div.innerHTML = `
            <span>${locationName}</span>
            <button onclick="removeLocation('${locationName}')">Remove</button>
          `;
          locationListDiv.appendChild(div);
        });
      })
      .catch(err => {
        console.error(err);
        locationListDiv.innerHTML = '<p style="color: red;">Error fetching locations.</p>';
      });
  }

addLocationForm.addEventListener('submit', e => {
  e.preventDefault();
  const locationName = newLocationInput.value.trim();
  if (!locationName) return;
  
  fetch('https://sunfra.com/farm/sunfra_clients/configuration/config_location_save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `client_id=${clientId}&location=${encodeURIComponent(locationName)}&operation=add`
  })
  .then(response => response.text())
  .then(result => {
    locationMsg.textContent = 'Location added successfully!';
    newLocationInput.value = '';
    fetchLocations();
  })
  .catch(() => {
    locationMsg.textContent = 'Error adding location.';
  });
});

window.removeLocation = function(locationName) {
  if (!confirm(`Remove location: ${locationName}?`)) return;

  fetch('https://sunfra.com/farm/sunfra_clients/configuration/config_location_save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `client_id=${clientId}&location=${encodeURIComponent(locationName)}&operation=delete`
  })
  .then(response => response.text())
  .then(result => {
    locationMsg.textContent = 'Location removed.';
    fetchLocations();
  })
  .catch(() => {
    locationMsg.textContent = 'Error removing location.';
  });
};

const openSheadBoxModalBtn = document.getElementById('openSheadBoxModalBtn');
const sheadBoxModal = document.getElementById('sheadBoxModalOverlay');
const closeSheadBoxModalBtn = document.getElementById('closeSheadBoxModalBtn');
const modalSheadBoxForm = document.getElementById('modalSheadBoxForm');
const modalSheadBoxNumber = document.getElementById('modalSheadBoxNumber');
const modalBoxResponseMsg = document.getElementById('modalBoxResponseMsg');

openSheadBoxModalBtn.addEventListener('click', (e) => {
  e.preventDefault();
  sheadBoxModal.classList.add('active');
  modalBoxResponseMsg.textContent = '';
  modalSheadBoxNumber.value = '';
  modalSheadBoxNumber.focus();
  fetchSheadBoxes();
});

closeSheadBoxModalBtn.addEventListener('click', () => {
  sheadBoxModal.classList.remove('active');
});

sheadBoxModal.addEventListener('click', (e) => {
  if (e.target === sheadBoxModal) {
    sheadBoxModal.classList.remove('active');
  }
});

modalSheadBoxForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  modalBoxResponseMsg.textContent = '';

  try {
    const formData = new FormData(modalSheadBoxForm);

    const res = await fetch('https://sunfra.com/farm/sunfra_clients/configuration/config_shead_box_save.php', {
      method: 'POST',
      body: formData
    });

    const data = await res.json(); 

    if (data.status === 'success') {
      modalBoxResponseMsg.style.color = 'green';
      modalBoxResponseMsg.textContent = data.message || 'Shead boxes saved successfully!';

      setTimeout(() => {
        sheadBoxModal.classList.remove('active');
        fetchSheadBoxes();
      }, 1500);
      
    } else {
      modalBoxResponseMsg.style.color = 'red';
      modalBoxResponseMsg.textContent = data.message || 'Failed to save shead boxes.';
    }
  } catch (err) {
    modalBoxResponseMsg.style.color = 'red';
    modalBoxResponseMsg.textContent = 'Error occurred while saving.';
    console.error('Fetch error:', err);
  }
});

function fetchSheads() {
  const sheadListDiv = document.getElementById('sheadList');
  sheadListDiv.innerHTML = 'Loading...';

  fetch(`https://sunfra.com/farm/sunfra_clients/configuration/shead_number_json.php?client_id=${clientId}`)
    .then(response => response.json())
    .then(data => {
      sheadListDiv.innerHTML = '';

      if (!data || data.length === 0) {
        sheadListDiv.innerHTML = '<p style="text-align: center;">No sheads configured yet.</p>';
        return;
      }

      const container = document.createElement('div');
      container.classList.add('shead-grid');

      data.forEach((shead, index) => {
        const box = document.createElement('div');
        box.className = 'shead-card';
        box.textContent = shead.shead_name;
        container.appendChild(box);
      });

      sheadListDiv.appendChild(container);
    })
    .catch(err => {
      console.error(err);
      sheadListDiv.innerHTML = '<p style="color: red;">Error fetching sheads.</p>';
    });
}
function fetchSheadBoxes() {
  const boxListDiv = document.getElementById('sheadBoxList');
  boxListDiv.innerHTML = 'Loading...';

  fetch(`https://sunfra.com/farm/sunfra_clients/configuration/config_shead_box_json.php?client_id=${clientId}`)
    .then(response => response.json())
    .then(data => {
      boxListDiv.innerHTML = '';

      const boxes = data[clientId];
      if (!boxes || boxes.length === 0) {
        boxListDiv.innerHTML = '<p style="text-align: center;">No shead boxes configured yet.</p>';
        return;
      }

      const container = document.createElement('div');
      container.classList.add('shead-grid');

      boxes.forEach((boxObj) => {
        const box = document.createElement('div');
        box.className = 'shead-card';
        box.textContent = boxObj.box_numbers;
        container.appendChild(box);
      });

      boxListDiv.appendChild(container);
    })
    .catch(err => {
      console.error(err);
      boxListDiv.innerHTML = '<p style="color: red;">Error fetching shead boxes.</p>';
    });
}

const chickModal = document.getElementById('chickModalOverlay');
const openChickModalBtn = document.getElementById('openChickModalBtn');
const closeChickModalBtn = document.getElementById('closeChickModalBtn');
const modalChickForm = document.getElementById('modalChickForm');
const modalChickResponseMsg = document.getElementById('modalChickResponseMsg');
const modalChickNumber = document.getElementById('modalChickNumber');

openChickModalBtn.addEventListener('click', (e) => {
  e.preventDefault();
  chickModal.classList.add('active');
  modalChickResponseMsg.textContent = '';
  modalChickNumber.value = '';
  modalChickNumber.focus();
  fetchChicks();
});

closeChickModalBtn.addEventListener('click', () => {
  chickModal.classList.remove('active');
});

chickModal.addEventListener('click', (e) => {
  if (e.target === chickModal) {
    chickModal.classList.remove('active');
  }
});

modalChickForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  modalChickResponseMsg.textContent = '';
  try {
    const formData = new FormData(modalChickForm);
    const res = await fetch('https://sunfra.com/farm/sunfra_clients/configuration/config_chick_shead_save.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.status === 'success') {
      modalChickResponseMsg.style.color = 'green';
      modalChickResponseMsg.textContent = data.message || 'Saved successfully!';
      setTimeout(() => {
        chickModal.classList.remove('active');
        fetchChicks(); // Refresh after saving
      }, 1500);
    } else {
      modalChickResponseMsg.style.color = 'red';
      modalChickResponseMsg.textContent = data.message || 'Failed to save data.';
    }
  } catch (err) {
    modalChickResponseMsg.style.color = 'red';
    modalChickResponseMsg.textContent = 'Error occurred while saving.';
    console.error(err);
  }
});

function fetchChicks() {
  const chickListDiv = document.getElementById('chickList');
  chickListDiv.innerHTML = 'Loading...';

  fetch(`https://sunfra.com/farm/sunfra_clients/configuration/config_chick_shead_json.php?client_id=${clientId}`)
    .then(response => response.json())
    .then(data => {
      chickListDiv.innerHTML = '';

      if (!data || data.length === 0) {
        chickListDiv.innerHTML = '<p style="text-align: center;">No chicks configured yet.</p>';
        return;
      }

      const container = document.createElement('div');
      container.classList.add('shead-grid'); // you can create separate chick-grid if you want

      data.forEach((chick, index) => {
        const box = document.createElement('div');
        box.className = 'shead-card'; 
        box.textContent = chick.shead_name; 
        container.appendChild(box);
      });

      chickListDiv.appendChild(container);
    })
    .catch(err => {
      console.error(err);
      chickListDiv.innerHTML = '<p style="color: red;">Error fetching chicks.</p>';
    });
}


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

const growerModal = document.getElementById('growerModalOverlay');
const openGrowerModalBtn = document.getElementById('openGrowerModalBtn');
const closeGrowerModalBtn = document.getElementById('closeGrowerModalBtn');
const modalGrowerForm = document.getElementById('modalGrowerForm');
const modalGrowerResponseMsg = document.getElementById('modalGrowerResponseMsg');
const modalGrowerNumber = document.getElementById('modalGrowerNumber');

openGrowerModalBtn.addEventListener('click', (e) => {
  e.preventDefault();
  growerModal.classList.add('active');
  modalGrowerResponseMsg.textContent = '';
  modalGrowerNumber.value = '';
  modalGrowerNumber.focus();
  fetchGrowers(); 
});

closeGrowerModalBtn.addEventListener('click', () => {
  growerModal.classList.remove('active');
});

growerModal.addEventListener('click', (e) => {
  if (e.target === growerModal) {
    growerModal.classList.remove('active');
  }
});

modalGrowerForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  modalGrowerResponseMsg.textContent = '';
  try {
    const formData = new FormData(modalGrowerForm);
    const res = await fetch('https://sunfra.com/farm/sunfra_clients/configuration/config_grower_shead_save.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.status === 'success') {
      modalGrowerResponseMsg.style.color = 'green';
      modalGrowerResponseMsg.textContent = data.message || 'Saved successfully!';
      setTimeout(() => {
        growerModal.classList.remove('active');
        fetchGrowers(); 
      }, 1500);
    } else {
      modalGrowerResponseMsg.style.color = 'red';
      modalGrowerResponseMsg.textContent = data.message || 'Failed to save data.';
    }
  } catch (err) {
    modalGrowerResponseMsg.style.color = 'red';
    modalGrowerResponseMsg.textContent = 'Error occurred while saving.';
    console.error(err);
  }
});

function fetchGrowers() {
  const growerListDiv = document.getElementById('growerList');
  growerListDiv.innerHTML = 'Loading...';

  fetch(`https://sunfra.com/farm/sunfra_clients/configuration/config_grower_shead_json.php?client_id=${clientId}`)
    .then(response => response.json())
    .then(data => {
      growerListDiv.innerHTML = '';

      if (!data || data.length === 0) {
        growerListDiv.innerHTML = '<p style="text-align: center;">No growers configured yet.</p>';
        return;
      }

      const container = document.createElement('div');
      container.classList.add('shead-grid'); // or create grower-grid for styling

      data.forEach((grower, index) => {
        const box = document.createElement('div');
        box.className = 'shead-card'; 
        box.textContent = grower.shead_name; 
        container.appendChild(box);
      });

      growerListDiv.appendChild(container);
    })
    .catch(err => {
      console.error(err);
      growerListDiv.innerHTML = '<p style="color: red;">Error fetching growers.</p>';
    });
}
document.addEventListener("DOMContentLoaded", function () {
  const openBtn = document.getElementById("openSupervisorForm");
  const modalOverlay = document.getElementById("supervisorModalOverlay");
  const closeBtn = document.getElementById("closeSupervisorModalBtn");
  const userCheckboxes = document.getElementById("userCheckboxes");
  const supervisorForm = document.getElementById("supervisorForm");
  const supervisorMsg = document.getElementById("supervisorMsg");

  const supervisorList = document.getElementById("supervisorList"); // tbody for supervisors
  const clientId = <?php echo json_encode($client_id); ?>;

  // 🔹 Fetch supervisors and display in a table
  function loadSupervisors() {
    fetch(`https://sunfra.com/farm/sunfra_clients/configuration/config_supervisor_json.php?client_id=${clientId}`)
      .then(res => res.json())
      .then(data => {
        supervisorList.innerHTML = ""; // clear old list

        if (data.length === 0) {
          supervisorList.innerHTML = `<tr><td colspan="3" style="text-align:center;">No supervisors assigned yet.</td></tr>`;
          return;
        }

        data.forEach(item => {
          const row = document.createElement("tr");

          row.innerHTML = `
            <td>${item.feature}</td>
            <td>${item.username}</td>
            <td>
              <button class="remove-btn btn btn-danger btn-sm" data-id="${item.id}">
                ❌ Remove
              </button>
            </td>
          `;

          supervisorList.appendChild(row);
        });

        // Add remove functionality
        document.querySelectorAll(".remove-btn").forEach(btn => {
          btn.addEventListener("click", function () {
            const supervisorId = this.getAttribute("data-id");
            removeSupervisor(supervisorId);
          });
        });
      })
      .catch(err => {
        console.error("Error fetching supervisors:", err);
        supervisorList.innerHTML = `<tr><td colspan="3" style="text-align:center;color:red;">Error loading supervisors.</td></tr>`;
      });
  }

  // 🔹 Remove supervisor
  function removeSupervisor(id) {
    if (!confirm("Are you sure you want to remove this supervisor?")) return;

    fetch("https://sunfra.com/farm/sunfra_clients/configuration/config_supervisor_delete.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}`
    })
      .then(res => res.text())
      .then(response => {
        alert(response);
        loadSupervisors(); // reload list after removal
      })
      .catch(err => {
        console.error("Error removing supervisor:", err);
      });
  }

  // 🔹 Open modal
  openBtn.addEventListener("click", function () {
    modalOverlay.style.display = "flex";

    // Fetch users for assigning
    fetch("https://sunfra.com/farm/sunfra_clients/login/farm_users_list.php")
      .then(res => res.json())
      .then(data => {
        userCheckboxes.innerHTML = ""; // clear old list

        const filteredUsers = data.filter(user => user.client_id == clientId);

        if (filteredUsers.length === 0) {
          userCheckboxes.innerHTML = "<p>No users available</p>";
          return;
        }

        filteredUsers.forEach(user => {
          let wrapper = document.createElement("div");
          wrapper.style.marginBottom = "5px";

          let checkbox = document.createElement("input");
          checkbox.type = "checkbox";
          checkbox.name = "username[]"; // important: array
          checkbox.value = user.username;
          checkbox.id = "user_" + user.id;

          let label = document.createElement("label");
          label.setAttribute("for", "user_" + user.id);
          label.textContent = user.username;
          label.style.marginLeft = "5px";

          wrapper.appendChild(checkbox);
          wrapper.appendChild(label);
          userCheckboxes.appendChild(wrapper);
        });
      })
      .catch(err => {
        console.error("Error fetching users:", err);
        userCheckboxes.innerHTML = "<p>Error loading users</p>";
      });
  });

  // 🔹 Close modal
  closeBtn.addEventListener("click", function () {
    modalOverlay.style.display = "none";
  });

  // 🔹 Handle form submit
  supervisorForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(supervisorForm);

    fetch("https://sunfra.com/farm/sunfra_clients/configuration/config_supervisor_save.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.text())
      .then(response => {
        supervisorMsg.textContent = "✅ Supervisor(s) assigned successfully!";
        supervisorMsg.style.color = "green";
        console.log("Response:", response);

        setTimeout(() => {
          modalOverlay.style.display = "none";
          loadSupervisors(); // reload list
        }, 1200);
      })
      .catch(err => {
        supervisorMsg.textContent = "❌ Error saving supervisor.";
        supervisorMsg.style.color = "red";
        console.error("Error:", err);
      });
  });

  // 🔹 Load supervisors on page load
  loadSupervisors();
});

</script>

</body>
</html>
