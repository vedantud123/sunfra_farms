<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
 
$current_feature = "Tractor Production Mortality"; // Set per page
 
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

$shead_url = "https://sunfra.com/farm/sunfra/configuration/shead_number_json.php?client_id=$client_id";
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
        preg_match('/\d+/', $item['shead_name'], $matches);
        if (!empty($matches)) {
            $shead_list[] = (int)$matches[0];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tractor Production Mortality</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #ADD8E6;
      margin: 0;
      padding: 0;
    }

    .header {
      background: linear-gradient(90deg, #4f46e5, #3b82f6);
      color: white;
      padding: 20px;
      text-align: center;
      font-size: 28px;
      font-weight: bold;
    }

    .container {
      padding: 15px;
      max-width: 1200px;
      margin: auto;
    }

    .filter-section {
      margin-bottom: 15px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      justify-content: space-between;
    }

    .filter-left {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }

	.filter-right {
	  display: flex;
	  justify-content: flex-end;  
	  width: auto;
	  margin-bottom: 0;
	}

	@media (max-width: 600px) {
	  .filter-right {
		justify-content: center;   
		width: 100%;
		margin-bottom: 10px;
		order: -1;
	  }

	  .add-btn {
		width: 100%;  
	  }
	}

	.add-btn {
	  padding: 12px 20px;
	  font-size: 16px;
	  border-radius: 8px;
	  background-color: #10b981;
	  color: white;
	  border: none;
	  cursor: pointer;
	  transition: background-color 0.3s;
	}

	.add-btn:hover {
	  background-color: #059669;
	}

    input[type="date"], input[type="number"], input[type="text"] {
      padding: 10px 14px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      background-color: white;
      box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
      font-size: 14px;
      color: #374151;
    }

    .button, .add-btn, .edit-btn {
      padding: 10px 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
      font-size: 14px;
    }

    .button { background-color: #3b82f6; color: white; }
    .button:hover { background-color: #2563eb; }

    .add-btn { background-color: #10b981; color: white; }
    .add-btn:hover { background-color: #059669; }

    .edit-btn { background-color: #f59e0b; color: white; font-size: 12px; padding: 6px 10px; }
    .edit-btn:hover { background-color: #d97706; }

    .summary-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .card {
      padding: 16px;
      border-radius: 10px;
      color: white;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .card:nth-child(1) { background: #3b82f6; }
    .card:nth-child(2) { background: #10b981; }
    .card:nth-child(3) { background: #f59e0b; }
    .card:nth-child(4) { background: #ef4444; }

    .table-container {
      overflow-x: auto;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    table { width: 100%; border-collapse: collapse; min-width: 750px; }
    th, td { padding: 10px 12px; text-align: center; border-bottom: 1px solid #f1f1f1; font-size: 14px; }
    th { background-color: #e5e7eb; font-weight: bold; }
    tbody tr:nth-child(even) { background-color: #f9fafb; }
    tbody tr:hover { background-color: #e0f2fe; }

    @media (max-width: 400px) {
      .filter-section { flex-direction: column; align-items: center; }
      .filter-left, .filter-right { width: 100%; justify-content: center; }
      .filter-right { order: -1; margin-bottom: 10px; }
    }
	 .modal {
		display: none;
		position: fixed;
		top: 0; left: 0; right: 0; bottom: 0;
		background-color: rgba(0, 0, 0, 0.5);
		z-index: 999;
		justify-content: center;
		align-items: center;
		padding: 10px;
	  }
	  .modal-content {
		background-color: white;
		padding: 20px;
		border-radius: 10px;
		max-width: 500px;
		width: 100%;
		box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
	  }
	  .modal-content h2 {
		margin-top: 0;
		text-align: center;
		font-size: 22px;
		margin-bottom: 15px;
	  }
	  .form-group {
		display: flex;
		flex-direction: column;
		margin-bottom: 12px;
	  }
	  .form-group label {
		margin-bottom: 6px;
		font-size: 14px;
		color: #374151;
	  }
	  .form-group input {
		padding: 10px;
		border: 1px solid #d1d5db;
		border-radius: 6px;
		font-size: 14px;
	  }

	  .form-buttons {
		display: flex;
		justify-content: space-between;
		gap: 10px;
		margin-top: 15px;
	  }

	  .form-buttons button {
		flex: 1;
		padding: 10px;
		border: none;
		border-radius: 6px;
		cursor: pointer;
		font-size: 14px;
	  }

	  .submit-btn {
		background-color: #10b981;
		color: white;
	  }

	  .submit-btn:hover {
		background-color: #059669;
	  }

	  .close-btn {
		background-color: #ef4444;
		color: white;
	  }

	  .close-btn:hover {
		background-color: #dc2626;
	  }

	  @media (max-width: 500px) {
		.modal-content {
		  padding: 15px;
		}

		.form-buttons {
		  flex-direction: column;
		}
	  }
	  @media (max-width: 500px) {
		  .add-btn {
			width: 100%;
		  }
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

<div class="header">🥚 Tractor Production Mortality
</div>
<div class="container">
  <div class="filter-section">
    <div class="filter-right">
  <button class="add-btn" onclick="openAddModal()">+ Add New</button>
  
</div>

    <div class="filter-left">
      <input type="date" id="dateFilter">
      <button class="button" onclick="filterByDate()">Filter</button>
    </div>
  </div>

  <div class="summary-cards">
    <div class="card"><h3>Total Production</h3><p id="totalProduction">0</p></div>
    <div class="card"><h3>Total Egg Trays</h3><p id="totalEggTrays">0</p></div>
    <div class="card"><h3>Total Loose Eggs</h3><p id="totalLooseEggs">0</p></div>
    <div class="card"><h3>Total Mortality</h3><p id="totalMortality">0</p></div>
  </div>

  <div class="table-container">
    <table id="productionTable">
      <thead>
        <tr>
          <th>Date</th>
          <th>Shed No</th>
          <th>Production</th>
          <th>Egg Trays</th>
          <th>Loose Eggs</th>
          <th>Mortality</th>
          <th>Batch ID</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<style>
  .modal {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    justify-content: center;
    align-items: center;
    padding: 10px;
  }

  .modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  }

  .modal-content h2 {
    margin-top: 0;
    text-align: center;
    font-size: 22px;
    margin-bottom: 15px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 12px;
  }

  .form-group label {
    margin-bottom: 6px;
    font-size: 14px;
    color: #374151;
  }

  .form-group input {
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
  }

  .form-buttons {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 15px;
  }

  .form-buttons button {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
  }

  .submit-btn {
    background-color: #10b981;
    color: white;
  }

  .submit-btn:hover {
    background-color: #059669;
  }

  .close-btn {
    background-color: #ef4444;
    color: white;
  }

  .close-btn:hover {
    background-color: #dc2626;
  }

  @media (max-width: 500px) {
    .modal-content {
      padding: 15px;
    }

    .form-buttons {
      flex-direction: column;
    }
  }
  @media (min-width: 700px) {
  .modal-content {
    max-width: 700px;
  }
	}.form-group select {
	  padding: 10px;
	  border: 1px solid #d1d5db;
	  border-radius: 6px;
	  font-size: 14px;
	  background-color: white;
	  color: #374151;
	  appearance: none; /* For consistent styling across browsers */
	  -webkit-appearance: none;
	  -moz-appearance: none;
	  background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"><path fill="%23737484" d="M2 0L0 2h4L2 0zM2 5L0 3h4l-2 2z"/></svg>');
	  background-repeat: no-repeat;
	  background-position: right 10px center;
	  background-size: 12px;
	}

	.form-group select:focus {
	  outline: none;
	  border-color: #10b981;
	  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
	}
</style>

<div class="modal" id="dataModal">
  <div class="modal-content">
    <h2>Add / Edit Data</h2>
    <form id="dataForm">
      <input type="hidden" name="id" id="formId">
		<input type="hidden" name="client_id" id="formClientId" value="<?php echo htmlspecialchars($client_id); ?>">

      <div class="form-group">
        <label for="formDate">Date</label>
        <input type="date" name="date" id="formDate" required>
      </div>

     <div class="form-group">
	  <label for="formSheadNo">Shed No</label>
	  <select name="sheadNo" id="formSheadNo" class="form-control" required>
		<option value="" disabled selected>Select Shed Number</option>
		<?php foreach ($shead_list as $shead_no): ?>
		  <option value="<?= $shead_no ?>"><?= $shead_no ?></option>
		<?php endforeach; ?>
	  </select>
	 </div>

      <div class="form-group">
        <label for="formEggTrays">Egg Trays</label>
        <input type="number" name="eggTrays" id="formEggTrays" required placeholder="Enter Egg Trays">
      </div>

      <div class="form-group">
        <label for="formLooseEggs">Loose Eggs</label>
        <input type="number" name="looseEggs" id="formLooseEggs" required placeholder="Enter Loose Eggs">
      </div>

      <div class="form-group">
        <label for="formMortality">Mortality</label>
        <input type="number" name="mortality" id="formMortality" required placeholder="Enter Mortality">
      </div>

      <div class="form-buttons">
        <button type="submit" class="submit-btn">Submit</button>
        <button type="button" class="close-btn" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>
</main>
</div>

<script>
	let allData = [];
	const clientId = <?php echo json_encode($client_id); ?>;

	function fetchData() {
	  const url = `https://sunfra.com/farm/sunfra/tractor_production_mortality/tractor_production_mortality_json.php?client_id=${clientId}`;
	  
	  fetch(url)
		.then(response => response.json())
		.then(data => {
		  // Fix: If data[clientId] exists, use that; otherwise, use empty array
		  allData = (data && data[clientId]) ? data[clientId] : [];
		  setTodayDate();
		  filterByDate();
		})
		.catch(error => {
		  console.error("Error fetching data:", error);
		});
	}

	function setTodayDate() {
	  const today = new Date().toISOString().split('T')[0];
	  document.getElementById('dateFilter').value = today;
	}

	function filterByDate() {
	  const selectedDate = document.getElementById('dateFilter').value;
	  const filteredData = allData.filter(item => item.date === selectedDate);
	  displayData(filteredData);
	  updateSummary(filteredData);
	}

	function updateSummary(dataArray) {
	  let totalProduction = 0, totalEggTrays = 0, totalLooseEggs = 0, totalMortality = 0;
	  dataArray.forEach(item => {
		totalProduction += Number(item.production);
		totalEggTrays += Number(item.eggTrays);
		totalLooseEggs += Number(item.looseEggs);
		totalMortality += Number(item.mortality);
	  });
	  document.getElementById('totalProduction').innerText = totalProduction;
	  document.getElementById('totalEggTrays').innerText = totalEggTrays;
	  document.getElementById('totalLooseEggs').innerText = totalLooseEggs;
	  document.getElementById('totalMortality').innerText = totalMortality;
	}

	function displayData(dataArray) {
	  const tableBody = document.querySelector('#productionTable tbody');
	  tableBody.innerHTML = '';
	  if (dataArray.length === 0) {
		tableBody.innerHTML = `<tr><td colspan="8" style="text-align:center; color: #9ca3af;">No data found for selected date</td></tr>`;
		return;
	  }
	  dataArray.forEach(item => {
		const row = `
		  <tr>
			<td>${item.date}</td>
			<td>${item.sheadNo}</td>
			<td>${item.production}</td>
			<td>${item.eggTrays}</td>
			<td>${item.looseEggs}</td>
			<td>${item.mortality}</td>
			<td>${item.batch_id}</td>
			<td>
			  <button class="edit-btn" onclick="openModal(${JSON.stringify(item).replace(/"/g, '&quot;')})">Edit</button>
			</td>
		  </tr>
		`;
		tableBody.insertAdjacentHTML('beforeend', row);
	  });
	}

	  function openModal(existingData = null) {
		document.getElementById('dataModal').style.display = 'flex';
		if (existingData) {
		  document.getElementById('formId').value = existingData.id || '';
		  document.getElementById('formDate').value = existingData.date || '';
		  document.getElementById('formSheadNo').value = existingData.sheadNo || '';
		  document.getElementById('formEggTrays').value = existingData.eggTrays || '';
		  document.getElementById('formLooseEggs').value = existingData.looseEggs || '';
		  document.getElementById('formMortality').value = existingData.mortality || '';
		} else {
		  document.getElementById('dataForm').reset();
		  document.getElementById('formId').value = '';
		}
	  }

	  function closeModal() {
		document.getElementById('dataModal').style.display = 'none';
	  }

	  document.getElementById('dataForm').addEventListener('submit', function(event) {
		event.preventDefault();

		const formData = {
		  id: document.getElementById('formId').value,
		  date: document.getElementById('formDate').value,
		  sheadNo: document.getElementById('formSheadNo').value,
		  eggTrays: document.getElementById('formEggTrays').value,
		  looseEggs: document.getElementById('formLooseEggs').value,
		  mortality: document.getElementById('formMortality').value,
		  client_id: clientId 
		};

		fetch('https://sunfra.com/farm/sunfra/tractor_production_mortality/tractor_production_mortality_save.php', {
		  method: 'POST',
		  headers: { 'Content-Type': 'application/json' },
		  body: JSON.stringify(formData)
		})
		.then(response => response.json())
		.then(result => {
		  alert(result.message);
		  closeModal();
		  fetchData();  // Reload table after submit
		})
		.catch(error => {
		  console.error('Error:', error);
		});
	  });

	function openAddModal() {
	  openModal('add');
	}

	window.onload = fetchData;
	const sidebar = document.getElementById('sidebar');
	const mainContent = document.querySelector('.content'); 
	const toggleBtn = document.getElementById('sidebarToggleBtn');

	toggleBtn.addEventListener('click', () => {
	  sidebar.classList.toggle('expanded');
	  mainContent.classList.toggle('expanded');  

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
